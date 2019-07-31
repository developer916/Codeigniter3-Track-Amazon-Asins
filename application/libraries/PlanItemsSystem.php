<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class PlanItemsSystem {

    private $_CI;
    public function __construct()
    {
        $this->_CI =& get_instance();

        $this->_CI->load->helper(array('form', 'url'));
        $this->_CI->load->database();
        $this->_CI->load->Model('StripeSubscriptions_model');
        $this->_CI->load->Model('TrackSupports_model');
        $this->_CI->load->Model('Supports_model');
    }


    public function  check_expiration_date(){
        $data = array();
        $difference_date = 0;

        if(isset($_SESSION['uid'])) {
            $user = $this->_CI->db->query("SELECT * FROM users WHERE ID='" . $_SESSION['uid'] . "'")->row();
            if (isset($user)) {
                if (isset($user->created_at)) {
                    $today = date_create(date('Y-m-d'));
                    $created = date_create(substr($user->created_at, 0, 10));
                    $diff = date_diff($created, $today);
                    $difference_date = $diff->days;
                }
            } else {
                redirect('Login');
            }

            $stripeSubscription = $this->_CI->StripeSubscriptions_model->getSubscription();
            if(isset($stripeSubscription)){
                if($stripeSubscription->ends_at != ""){
                    $getSupport = $this->_CI->Supports_model->getCurrentUserSupport();
                    if(isset($getSupport)) {
                        $getTrackSupport = $this->_CI->TrackSupports_model->getTrackItem($getSupport->track_support);
                        if ($getTrackSupport->price == "99999") {
                            $selectedUser = $this->_CI->db->query("SELECT * FROM users where ID='" . $_SESSION['uid'] . "'")->row();
                            $countItem = $selectedUser->track_count;
                            $currentAsinsCount = $this->getAllAmazonAsins();
                            if ($countItem * 1 <= $currentAsinsCount) {
                                $data['result'] = "failed";
                                $data['message'] = "You have to upgrade plan.";
                                $data['subscription'] = "cancelled";
                                $data['plan_count'] = $countItem;
                                $data['current_count'] = $currentAsinsCount;
                            } else {
                                $data['result'] = "success";
                                $data['message'] = "";
                                $data['subscription'] = "cancelled";
                                $data['plan_count'] = $countItem;
                                $data['current_count'] = $currentAsinsCount;
                            }
                        } else {
                            $countItem = $getTrackSupport->count;
                            $currentAsinsCount = $this->getAllAmazonAsins();
                            if ($countItem * 1 <= $currentAsinsCount) {
                                $data['result'] = "failed";
                                $data['message'] = "You have to upgrade plan.";
                                $data['subscription'] = "cancelled";
                                $data['plan_count'] = $countItem;
                                $data['current_count'] = $currentAsinsCount;
                            } else {
                                $data['result'] = "success";
                                $data['message'] = "";
                                $data['subscription'] = "cancelled";
                                $data['plan_count'] = $countItem;
                                $data['current_count'] = $currentAsinsCount;
                            }
                        }
                    } else {
                            $data['result'] = "failed";
                            $data['message'] = "You have to upgrade plan.";
                    }
                } else {
                    $getSupport = $this->_CI->Supports_model->getCurrentUserSupport();
                    if(isset($getSupport)){
                        $getTrackSupport = $this->_CI->TrackSupports_model->getTrackItem($getSupport->track_support);
                        if($getTrackSupport->price == "99999"){
                            $selectedUser = $this->_CI->db->query("SELECT * FROM users where ID='".$_SESSION['uid']."'")->row();
                            $countItem = $selectedUser->track_count;
                            $currentAsinsCount = $this->getAllAmazonAsins();
                            if($countItem*1 <= $currentAsinsCount){
                                $data['result'] = "failed";
                                $data['message'] = "You have to upgrade plan.";
                                $data['subscription'] = "enable";
                                $data['plan_count'] = $countItem;
                                $data['current_count']  =$currentAsinsCount;
                            } else {
                                $data['result'] = "success";
                                $data['message'] = "";
                                $data['subscription'] = "enable";
                                $data['plan_count'] = $countItem;
                                $data['current_count']  =$currentAsinsCount;
                            }
                        } else {
                                $countItem = $getTrackSupport->count;
                                $currentAsinsCount = $this->getAllAmazonAsins();
//                                echo $countItem;
//                                echo $currentAsinsCount;
//                                exit;
//                                if($countItem*1 <=  $currentAsinsCount){
                            if($countItem*1 <=  $currentAsinsCount){
                                    $data['result'] = "failed";
                                    $data['message'] = "You have to upgrade plan.";
                                    $data['subscription'] = "cancelled";
                                    $data['plan_count'] = $countItem;
                                    $data['current_count']  =$currentAsinsCount;
                                } else {
                                    $data['result'] = "success";
                                    $data['message'] = "";
                                    $data['subscription'] = "cancelled";
                                    $data['plan_count'] = $countItem;
                                    $data['current_count']  =$currentAsinsCount;
                                }
                        }
                    } else {
                        $data['result'] = "failed";
                        $data['message'] = "You have to upgrade plan.";
                    }
                }

            } else {
                $currentAsinsCount = $this->getAllAmazonAsins();
                if($difference_date<= 14){
                    if( $currentAsinsCount<= 80){
                        $data['result'] = "success";
                        $data['message'] = "";
                        $data['subscription'] = "trial";
                        $data['plan_count'] = 80;
                        $data['current_count']  =$currentAsinsCount;
                    } else {
                        $data['result'] = "failed";
                        $data['message'] = "You have to upgrade plan.";
                        $data['subscription'] = 'trial';
                        $data['plan_count'] = 80;
                        $data['current_count']  =$currentAsinsCount;
                    }
                } else {
                    $data['result'] = "failed";
                    $data['message'] = "You have to upgrade plan.";
                    $data['subscription'] = "trial";
                    $data['plan_count'] = 80;
                    $data['current_count']  =$currentAsinsCount;
                }
            }
//            if(isset($stripeSubscription)) {
//                if($stripeSubscription->ends_at != ""){
//                    $startDate =  date('Y-m-01 00:00:00',strtotime('this month'));
//                    $endDate = date('Y-m-t 23:59:59',strtotime('this month'));
//                    $subscriptionEnds = strtotime($stripeSubscription->ends_at);
//                    $checkStartDate = strtotime($startDate);
//                    $checkEndDate = strtotime($endDate);
//                    if( ($subscriptionEnds >= $checkStartDate) && ($subscriptionEnds <= $checkEndDate) ){
//                        $getSupport = $this->_CI->Supports_model->getCurrentUserSupport();
//                        if(isset($getSupport)){
//                            $getTrackSupport = $this->_CI->TrackSupports_model->getTrackItem($getSupport->track_support);
//                            if($getTrackSupport->price == "99999"){
//                                $selectedUser = $this->_CI->db->query("SELECT * FROM users where ID='".$_SESSION['uid']."'")->row();
//                                $countItem = $selectedUser->track_count;
//                                $currentAsinsCount = $this->getMonthAmazonAsins();
//                                if($countItem*1 <  $currentAsinsCount){
//                                    $data['result'] = "failed";
//                                    $data['message'] = "You have to upgrade plan.";
//                                    $data['subscription'] = "cancelled";
//                                    $data['plan_count'] = $countItem;
//                                    $data['current_count']  =$currentAsinsCount;
//                                } else {
//                                    $data['result'] = "success";
//                                    $data['message'] = "";
//                                    $data['subscription'] = "cancelled";
//                                    $data['plan_count'] = $countItem;
//                                    $data['current_count']  =$currentAsinsCount;
//                                }
//                            } else {
//                                $countItem = $getTrackSupport->count;
//                                $currentAsinsCount = $this->getMonthAmazonAsins();
//                                if($countItem*1 <  $currentAsinsCount){
//                                    $data['result'] = "failed";
//                                    $data['message'] = "You have to upgrade plan.";
//                                    $data['subscription'] = "cancelled";
//                                    $data['plan_count'] = $countItem;
//                                    $data['current_count']  =$currentAsinsCount;
//                                } else {
//                                    $data['result'] = "success";
//                                    $data['message'] = "";
//                                    $data['subscription'] = "cancelled";
//                                    $data['plan_count'] = $countItem;
//                                    $data['current_count']  =$currentAsinsCount;
//                                }
//                            }
//                        } else{
//                            $data['result'] = "failed";
//                            $data['message'] = "You have to upgrade plan.";
//                        }
//                    } else {
//                        $data['result'] = "failed";
//                        $data['message'] = "You have to resume subscriptions";
//                    }
//                } else {
//                    //check current enabled
//                    $getSupport = $this->_CI->Supports_model->getCurrentUserSupport();
//                    if(isset($getSupport)){
//                        $getTrackSupport = $this->_CI->TrackSupports_model->getTrackItem($getSupport->track_support);
//                        if($getTrackSupport->price == "99999"){
//                            $selectedUser = $this->_CI->db->query("SELECT * FROM users where ID='".$_SESSION['uid']."'")->row();
//                            $countItem = $selectedUser->track_count;
//                            $currentAsinsCount = $this->getMonthAmazonAsins();
//                            if($countItem*1 < $currentAsinsCount){
//                                $data['result'] = "failed";
//                                $data['message'] = "You have to upgrade plan.";
//                                $data['subscription'] = "enable";
//                                $data['plan_count'] = $countItem;
//                                $data['current_count']  =$currentAsinsCount;
//                            } else {
//                                $data['result'] = "success";
//                                $data['message'] = "";
//                                $data['subscription'] = "enable";
//                                $data['plan_count'] = $countItem;
//                                $data['current_count']  =$currentAsinsCount;
//                            }
//                        } else {
//                            $countItem = $getTrackSupport->count;
//                            $currentAsinsCount = $this->getMonthAmazonAsins();
//                            if($countItem*1 < $currentAsinsCount){
//                                $data['result'] = "failed";
//                                $data['message'] = "You have to upgrade plan.";
//                                $data['subscription'] = "enable";
//                                $data['plan_count'] = $countItem;
//                                $data['current_count']  =$currentAsinsCount;
//                            } else {
//                                $data['result'] = "success";
//                                $data['message'] = "";
//                                $data['subscription'] = "enable";
//                                $data['plan_count'] = $countItem;
//                                $data['current_count']  =$currentAsinsCount;
//                            }
//                        }
//
//                    } else{
//                        $data['result'] = "failed";
//                        $data['message'] = "You have to upgrade plan.";
//                    }
//                }
//
//            } else {
//                if($difference_date<= 14)
//                    $currentAsinsCount = $this->getAllAmazonAsins();
//                    if( $currentAsinsCount<= 80){
//                        $data['result'] = "success";
//                        $data['message'] = "";
//                        $data['subscription'] = "trial";
//                        $data['plan_count'] = 80;
//                        $data['current_count']  =$currentAsinsCount;
//                    } else {
//                        $data['result'] = "failed";
//                        $data['message'] = "You have to upgrade plan.";
//                        $data['subscription'] = 'trial';
//                        $data['plan_count'] = 80;
//                        $data['current_count']  =$currentAsinsCount;
//                    }
//
//            }

        } else {
            redirect('Login');
            $data['result'] = "failed";
            $data['message'] = "Your session has been expired.";
        }

        return $data;
    }


    public function getAllAmazonAsins(){
        $asinsCount = $this->_CI->db->query("SELECT * FROM amaz_aug WHERE user_id ='".$_SESSION['uid']."'  and  (tracking = 1 or stock_noti = 'true') " )->num_rows();
        return $asinsCount;
    }

//    public function getAllAmazonAsins(){
//        $asinsCount = $this->_CI->db->query("SELECT * FROM amaz_aug WHERE user_id ='".$_SESSION['uid']."'")->num_rows();
//        return $asinsCount;
//    }

}
