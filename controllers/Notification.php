<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Notification extends CI_Controller {
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */

	public function __construct()
	{
		parent::__construct();
		$this->load->model('accesses');
	}

    function opennotification($noty_id)
    {
        $ci =& get_instance();

        $ci->db->set('viewed','1');
        $ci->db->where("(notifications.id = '".$noty_id."')",NULL,FALSE);
        $ci->db->update('notifications');


        $notifications = $ci->db->get_where('notifications', array('id'=>$noty_id))->result_array();


        redirect(site_url().$notifications[0]['url']);

    }

    function getnotifications($user_id)
    {

        $notifications = $this->db->get_where('notifications', array('rel_id'=>$user_id,'viewed'=>0,'notification_date'=>date('Y-m-d')))->result_array();


        foreach($notifications as $notification)
        {
            echo '<li class="notification-box">
                            <div class="row">
                                <div class="col-lg-3 col-sm-3 col-3 text-center">
                                    <img src="https://img.icons8.com/dusk/64/000000/bell.png" class="w-50 rounded-circle">
                                </div>
                                
                                <div class="col-lg-8 col-sm-8 col-8">
                                    <strong class="text-info"><a href='.site_url().'/Notification/opennotification/'.$notification["id"].'>' .$notification["notify_type"].'</strong>
                                    
                                    <div><a href='.site_url().'/Notification/opennotification/'.$notification["id"].'>
                                        '.$notification["msg"].'
                                    
                                    </a>
                                    </div>
                                    <small class="text-warning">'.$notification["created_at"].'</small>
                                </div>
                            </div>
                        </li>
                        <hr>';

        }
    }

    function getnotificationscount($user_id)
    {

        $notifications = $this->db->get_where('notifications', array('rel_id'=>$user_id,'viewed'=>0,'notification_date'=>date('Y-m-d')))->result_array();

        $totalnoty=0;
        foreach($notifications as $notification)
        {
            $totalnoty++;
        }
        echo $totalnoty;
    }



}