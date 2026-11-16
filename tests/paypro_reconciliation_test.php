<?php
define('BASEPATH',__DIR__);
require __DIR__.'/../libraries/Paypro_reconciliation.php';
class PayproReconDb {
    public $banks=array(); public $tags=array(); public $receipts=array(); public $filter=array(); public $table; public $mode; public $bank_id; public $pending=array(); public $snapshot;
    public $total=100500; public $valid=true;
    function query($sql,$args=array()) {
        if(strpos($sql,'GET_LOCK')!==false) $this->mode='lock';
        elseif(strpos($sql,'SELECT b.id')!==false) { $this->mode='banks'; $this->bank_id=$args[0]; }
        elseif(strpos($sql,'FOR UPDATE')!==false) { $this->mode='bank'; $this->bank_id=$args[0]; }
        return $this;
    }
    function get_where($table,$filter){$this->mode='get';$this->table=$table;$this->filter=$filter;return $this;}
    function row_array(){
        if($this->mode==='lock')return array('acquired'=>1);
        if($this->mode==='bank')return isset($this->banks[$this->bank_id])?$this->banks[$this->bank_id]:array();
        if($this->table==='pay_pro_settlement')return array('id'=>1,'settlement_date'=>'2025-04-21');
        if($this->table==='bank_reconciliation_statement')return $this->banks[$this->filter['id']];
        if($this->table==='paypro_bank_channel_tags')return isset($this->tags[$this->filter['bank_trans_id']])?$this->tags[$this->filter['bank_trans_id']]:array();
        return array();
    }
    function result_array(){
        if($this->mode==='banks') {
            $out=array(); foreach($this->banks as $bank) if((int)$bank['paypro_id']===(int)$this->bank_id) $out[]=array_merge($bank,isset($this->tags[$bank['id']])?$this->tags[$bank['id']]:array('paid_via'=>null,'amount'=>null));
            return $out;
        }
        return $this->receipts;
    }
    function insert($table,$row){if(isset($this->tags[$row['bank_trans_id']]))throw new Exception('Duplicate bank tag');$this->tags[$row['bank_trans_id']]=$row;}
    function where($k,$v){$this->filter=array($k=>$v);return $this;}
    function set($k,$v,$escape=true){$this->pending[$k]=$v;return $this;}
    function update($t,$data=array()){
        if($t==='bank_reconciliation_statement')$this->banks[$this->filter['id']]=array_merge($this->banks[$this->filter['id']],$data);
        if($t==='pay_pro_settlement') {
            if(strpos($this->pending['tagged_amount'],'GREATEST')!==false)$this->total-=12000; else $this->total+=12000;
        }
        $this->pending=array();return true;
    }
    function delete($t){unset($this->tags[$this->filter['bank_trans_id']]);}
    function trans_begin(){$this->snapshot=array($this->banks,$this->tags,$this->total);}
    function trans_status(){return $this->valid;}
    function trans_commit(){}
    function trans_rollback(){list($this->banks,$this->tags,$this->total)=$this->snapshot;}
}
function reconCheck($ok,$label){if(!$ok)throw new Exception($label);echo "PASS: $label\n";}
$svc=new Paypro_reconciliation; $db=new PayproReconDb;
foreach(array(array('MBL',5000),array('MBL',7000),array('1Link',100500)) as $i=>$r)$db->receipts[]=array('paid_via'=>$r[0],'received_amount'=>$r[1],'customer'=>'Student '.$i,'paypro_id'=>'ID'.$i);
$db->banks=array(1=>array('id'=>1,'credit'=>'100,500','paypro_id'=>1),2=>array('id'=>2,'credit'=>'12,000','paypro_id'=>null));
$groups=$svc->groups($db,1);
reconCheck($groups['MBL']['remaining']===12000.0 && $groups['MBL']['entry_count']===2 && !$groups['MBL']['needs_review'],'two MBL receipts grouped and legacy 1Link excluded');
$svc->tag($db,2,1,'MBL',99);
reconCheck($db->tags[2]['paid_via']==='MBL' && $db->total===112500,'one bank credit tags combined MBL amount');
$svc->tag($db,2,1,'MBL',99);
reconCheck(count($db->tags)===1 && $db->total===112500,'repeated tag is idempotent');
$groups=$svc->groups($db,1);
reconCheck($groups['MBL']['remaining']===0.0,'channel remaining updated');
$svc->untag($db,2);
reconCheck(!$db->tags && $db->total===100500 && !$db->banks[2]['paypro_id'],'untag releases channel and amount');
$db->banks[2]['credit']=11000;
try{$svc->tag($db,2,1,'MBL',99);throw new RuntimeException('Mismatch accepted');}catch(RuntimeException $e){throw $e;}catch(Exception $e){reconCheck(!$db->tags,'amount mismatch blocked');}
$db->banks[2]['credit']=12000; $db->banks[2]['payment_id']=42;
try{$svc->tag($db,2,1,'MBL',99);throw new RuntimeException('Other tag overwritten');}catch(RuntimeException $e){throw $e;}catch(Exception $e){reconCheck(!$db->tags,'existing fee tag protected');}
unset($db->banks[2]['payment_id']); $db->banks[1]['credit']=999;
$groups=$svc->groups($db,1);
reconCheck($groups['MBL']['needs_review'],'ambiguous legacy tag flagged for review');
$db->banks[1]['credit']=100500; $svc->groups($db,1,true);
$db->receipts[]=array('paid_via'=>'1Link','received_amount'=>1000,'customer'=>'New','paypro_id'=>'NEW');
$groups=$svc->groups($db,1);
reconCheck($groups['1Link']['remaining']===1000.0 && !$groups['1Link']['needs_review'],'legacy allocation remains correct after missing receipt appended');
