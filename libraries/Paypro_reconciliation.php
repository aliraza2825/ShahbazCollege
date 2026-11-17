<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paypro_reconciliation
{
    public function ensure_schema($db)
    {
        return $db->query("CREATE TABLE IF NOT EXISTS paypro_bank_channel_tags (
            bank_trans_id INT NOT NULL PRIMARY KEY, settlement_id INT NOT NULL,
            paid_via VARCHAR(32) NOT NULL, amount DECIMAL(15,2) NOT NULL,
            created_by INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY settlement_channel (settlement_id,paid_via)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }

    public function channel($value)
    {
        $v = strtolower(preg_replace('/[^a-z0-9]/i','',(string)$value));
        if ($v === '1link') return '1Link';
        if ($v === 'mbl') return 'MBL';
        if ($v === 'debitcredit') return 'Debit/Credit';
        return (string)$value;
    }

    public function amounts_match_for_tag($remaining, $credit)
    {
        return $remaining > 0 && $credit > 0 && floor($remaining) === floor($credit);
    }

    public function groups($db,$settlement_id,$persist_legacy = false)
    {
        $groups=array(); $ambiguous=false;
        $rows=$db->get_where('settlement_payments',array('settlement_id'=>$settlement_id))->result_array();
        foreach($rows as $row) {
            $ch=$this->channel($row['paid_via']);
            if(!isset($groups[$ch])) $groups[$ch]=array('paid_via'=>$ch,'total'=>0,'tagged'=>0,'entry_count'=>0,'entries'=>array());
            $groups[$ch]['total']+=(float)$row['received_amount'];
            $groups[$ch]['entry_count']++;
            $groups[$ch]['entries'][]=array('customer'=>$row['customer'],'paypro_id'=>$row['paypro_id'],'amount'=>(float)$row['received_amount']);
        }
        $banks=$db->query('SELECT b.id,b.credit,t.paid_via,t.amount FROM bank_reconciliation_statement b LEFT JOIN paypro_bank_channel_tags t ON t.bank_trans_id=b.id WHERE b.paypro_id=? ORDER BY b.id',array($settlement_id))->result_array();
        // Explicit allocations are authoritative. Infer old tags only when
        // exactly one channel remainder matches the bank credit.
        $legacy=array();
        foreach($banks as $bank) {
            if(!empty($bank['paid_via']) && isset($groups[$bank['paid_via']])) $groups[$bank['paid_via']]['tagged']+=(float)$bank['amount'];
            else $legacy[]=$bank;
        }
        foreach($legacy as $bank) {
            $credit=(float)str_replace(',','',$bank['credit']); $matches=array();
            foreach($groups as $ch=>$group) if(abs($group['total']-$group['tagged']-$credit)<0.005) $matches[]=$ch;
            if(count($matches)===1) {
                $groups[$matches[0]]['tagged']+=$credit;
                if($persist_legacy) $db->insert('paypro_bank_channel_tags',array('bank_trans_id'=>$bank['id'],'settlement_id'=>$settlement_id,'paid_via'=>$matches[0],'amount'=>$credit,'created_by'=>0));
            }
            else $ambiguous=true;
        }
        foreach($groups as &$group) {
            $group['total']=round($group['total'],2); $group['tagged']=round($group['tagged'],2);
            $group['remaining']=round($group['total']-$group['tagged'],2);
            $group['needs_review']=$ambiguous;
        } unset($group);
        return $groups;
    }

    public function untag($db,$bank_id)
    {
        $initial=$db->get_where('bank_reconciliation_statement',array('id'=>$bank_id))->row_array();
        $settlement=!empty($initial['paypro_id']) ? $db->get_where('pay_pro_settlement',array('id'=>$initial['paypro_id']))->row_array() : null;
        $lock=$settlement ? 'paypro_settlement_'.$settlement['settlement_date'] : 'paypro_bank_'.$bank_id;
        $got=$db->query('SELECT GET_LOCK(?,10) AS acquired',array($lock))->row_array();
        if(empty($got['acquired'])) throw new Exception('Settlement is being updated. Please retry.');
        $db->trans_begin();
        try {
            $bank=$db->query('SELECT * FROM bank_reconciliation_statement WHERE id=? FOR UPDATE',array($bank_id))->row_array();
            if(!$bank) throw new Exception('Bank transaction not found.');
            if((int)$bank['paypro_id']!==(int)$initial['paypro_id']) throw new Exception('Tag changed. Refresh and retry.');
            if(!empty($bank['paypro_id'])) {
                $amount=(float)str_replace(',','',$bank['credit']);
                $db->set('tagged_amount','GREATEST(0,COALESCE(tagged_amount,0) - '.$amount.')',false)->where('id',(int)$bank['paypro_id'])->update('pay_pro_settlement');
            }
            $db->where('bank_trans_id',$bank_id)->delete('paypro_bank_channel_tags');
            $db->where('id',$bank_id)->update('bank_reconciliation_statement',array('paypro_id'=>null));
            if(!$db->trans_status()) throw new Exception('Unable to untag PayPro.');
            $db->trans_commit();
        } catch(Exception $e) { $db->trans_rollback(); $db->query('SELECT RELEASE_LOCK(?)',array($lock)); throw $e; }
        $db->query('SELECT RELEASE_LOCK(?)',array($lock));
    }

    public function tag($db,$bank_id,$settlement_id,$channel,$actor)
    {
        $channel=$this->channel($channel);
        if(!in_array($channel,array('1Link','MBL','Debit/Credit'),true)) throw new Exception('Select Paid Via.');
        $settlement=$db->get_where('pay_pro_settlement',array('id'=>$settlement_id))->row_array();
        if(!$settlement) throw new Exception('Settlement not found.');
        $lock='paypro_settlement_'.$settlement['settlement_date'];
        $got=$db->query('SELECT GET_LOCK(?,10) AS acquired',array($lock))->row_array();
        if(empty($got['acquired'])) throw new Exception('Settlement is being updated. Please retry.');
        $db->trans_begin();
        try {
            $bank=$db->query('SELECT * FROM bank_reconciliation_statement WHERE id=? FOR UPDATE',array($bank_id))->row_array();
            if(!$bank) throw new Exception('Bank transaction not found.');
            $old=$db->get_where('paypro_bank_channel_tags',array('bank_trans_id'=>$bank_id))->row_array();
            if($old) {
                if((int)$old['settlement_id']!==$settlement_id || $old['paid_via']!==$channel || (int)$bank['paypro_id']!==$settlement_id) throw new Exception('Bank entry already tagged or needs review. Untag it first.');
                $db->trans_commit(); $db->query('SELECT RELEASE_LOCK(?)',array($lock)); return;
            }
            foreach(array('paypro_id','payment_id','expense_id','statement_id','closing_id','bank_transfer_id','salary_expense_ids','is_council_fee') as $field) {
                if(!empty($bank[$field])) throw new Exception('Bank entry already tagged. Untag it first.');
            }
            if($old && (int)$old['settlement_id']===$settlement_id && $old['paid_via']===$channel && (int)$bank['paypro_id']===$settlement_id) {
                $db->trans_commit(); $db->query('SELECT RELEASE_LOCK(?)',array($lock)); return;
            }
            foreach(array('paypro_id','payment_id','expense_id','statement_id','closing_id','bank_transfer_id','salary_expense_ids','is_council_fee') as $field) if(!empty($bank[$field])) throw new Exception('Bank transaction is already tagged. Untag it first.');
            $groups=$this->groups($db,$settlement_id);
            if(!isset($groups[$channel])) throw new Exception('No entries for the selected channel.');
            $group=$groups[$channel];
            if($group['needs_review']) throw new Exception('Existing bank tags need channel review before adding another tag.');
            $credit=round((float)str_replace(',','',$bank['credit']),2);
            if(!$this->amounts_match_for_tag($group['remaining'], $credit)) throw new Exception('Bank credit must match the remaining channel amount. Refresh settlements.');
            $db->insert('paypro_bank_channel_tags',array('bank_trans_id'=>$bank_id,'settlement_id'=>$settlement_id,'paid_via'=>$channel,'amount'=>$group['remaining'],'created_by'=>$actor));
            $db->where('id',$bank_id)->update('bank_reconciliation_statement',array('paypro_id'=>$settlement_id));
            $db->set('tagged_amount','COALESCE(tagged_amount,0) + '.$credit,false)->where('id',$settlement_id)->update('pay_pro_settlement');
            if(!$db->trans_status()) throw new Exception('Unable to save PayPro tag.');
            $db->trans_commit();
        } catch(Exception $e) {
            $db->trans_rollback(); $db->query('SELECT RELEASE_LOCK(?)',array($lock)); throw $e;
        }
        $db->query('SELECT RELEASE_LOCK(?)',array($lock));
    }
}
