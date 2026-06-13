<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Webhooks extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function get_hooks()
    {
        $this->db->set("data",json_encode($this->input->post('phone')));
        $this->db->set("post_data", json_encode($this->input->post('name')));
        $this->db->insert("fb_response");

        $this->db->set("website",$this->input->post('page'). "( FB )");
        $this->db->set("fb_ad_name", $this->input->post('page'));
        $this->db->set("name", $this->input->post('name'));
        $this->db->set("mobile", $this->input->post('phone'));
        $this->db->set("emergency_no", $this->input->post('phone'));
        $this->db->set("campaign_name", $this->input->post('campaign_name'));
        $this->db->insert("apply_now");
        $insert_id = $this->db->insert_id();

        $accesses = $this->db->select("user_id")->where('online_application_access = "1"')->get('access')->result_array();
        foreach ($accesses as $access) {
            $exp = $this->db->get_where("users", "user_id = '" . $access["user_id"] . "'")->row();
            if ($exp->device_id != null && $exp->device_id != "" && $exp->status == "1")
                $this->sendGCM("New Lead has been posted with name ".$this->input->post('name'), $exp->device_id, "New Lead Added grab it",$insert_id);
        }

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Successfully inserted',
        );
        echo json_encode($result);
    }
    function sendGCM($message, $id,$title,$insert_id) {

        $url = 'https://fcm.googleapis.com/fcm/send';
        $api_key = 'AAAAiFb3m_A:APA91bGUYX7ggNRcv9tboFgdbwbBNhtYglWmXpMDESLE1QXheIn5h_3BsOiWnh6iX83b-y2yhk88h7SFnUIeuQvZ5GYShuwER6UPfsC3YxDF9Ri7e7ND0R2yAYe07NsfQiE1hd87-t88';
        $fields = array (
            'to'        => $id,
            // 'registration_ids' => array (
            //         $device_array
            // ),
            'data' => array (
                "title" => $title,
                "message" => $message,
                "lead_id" => $insert_id
            )
        );


        //header includes Content type and api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='.$api_key
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
    }

}