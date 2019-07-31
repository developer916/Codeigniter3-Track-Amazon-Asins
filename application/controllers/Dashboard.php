<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('America/New_York');


class Dashboard extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        if (!($this->session->userdata('user_id'))) {
            redirect('Login');
        }
        if (!($_SESSION['uid'])){
            redirect('Login');
        }
        $this->load->helper(array('form', 'url'));
        $this->load->Model('StripeSubscriptions_model');
        $this->load->Model('TrackSupports_model');
        $this->load->Model('Supports_model');
        $this->load->library('SessionTimeout');
        $this->load->library('PlanItemsSystem');
//        $this->load->library('AsinsManagementSystem');
        $sessionTimeout = new SessionTimeout();
        $sessionTimeout->checkTimeOut();
    }

    public function index()
    {
        // Pass the site info
        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'dashboard';

        // Title
        $data['title_addition'] = 'Dashboard';

        // Load stuff
        $data['stylesheet'] = 'dashboard';
        $data['javascript'] = 'dashboard';

        // Load header library
        //$this->load->library('ForgotPasswordSystem.php');

        // load the view
        $this->load->view('templates/header.php', $data);
        $this->load->view('dashboard');
        $this->load->view('templates/footer.php');
    }

    /*
     * Bulk asins delete
     *
     */

    public function delete_bulk_asins(){
        $listArray = $_POST['list'];
        if(count($listArray) >0){
            foreach($listArray as $key => $array){
                $query = "DELETE FROM amaz_aug  WHERE id = '".$array."' and user_id ='".$_SESSION['uid']."'";
                $this->db->query($query);
            }
            $data['result'] = 'success';
            $data['show_result'] = $this->onGetDataTableContent();
        } else {
            $data['result'] = 'failed';
            $data['message'] = "You have to select any one item.";
        }
        echo json_encode($data);
        exit;
    }
    /*
     * Bulk action change
     *
     */

    public function change_bulk_notifications(){
        $data = array();
        $listArray = $_POST['list'];
        $type = $_POST['type'];
        if(count($listArray) >0){
            if($type == 'stock_on'){
                $sub_query = " tracking = 1 ";
            } elseif($type =='stock_off'){
                $sub_query = " tracking = 0 ";
            } elseif($type =='back_stock_on'){
                $sub_query = " stock_noti = 'true' ";
            } elseif($type =='back_stock_off'){
                $sub_query = " stock_noti = 'false' ";
            } elseif($type =='email_on'){
                $sub_query = " email_noti = 'true' ";
            } elseif($type =='email_off'){
                $sub_query = " email_noti = 'false' ";
            } elseif($type =='sms_on'){
                $sub_query = " phone_noti = 'true' ";
            } elseif($type =='sms_off'){
                $sub_query = " phone_noti = 'false' ";
            }
            foreach($listArray as $key => $array){
                $query = "UPDATE amaz_aug set ". $sub_query. " WHERE id = '".$array."' and user_id ='".$_SESSION['uid']."'";
                $this->db->query($query);
            }
            $data['result'] = 'success';
            $data['show_result'] = $this->onGetDataTableContent();
        } else {
            $data['result'] = 'failed';
            $data['message'] = "You have to select any one item.";
        }
        echo json_encode($data);
        exit;
    }

    /////////////////////////----------TRACKING--------------------------////////////////
    public function checkAndUncheck($amz_id, $status)
    {
        $ajaxData = array();
//        $data['site_info'] = $this->config->item('site_info');
//        $data['base_url'] = $this->config->item('base_url');
//        $data['site_page'] = 'dashboard';
//        // Title
//        $data['title_addition'] = 'Dashboard';
//        // Load stuff
//        $data['stylesheet'] = 'dashboard';
//        $data['javascript'] = 'dashboard';
//        $this->load->view('templates/header.php', $data);
//        $this->load->view('dashboard');
//        $this->load->view('templates/footer.php');

        $planItemsSystem = new PlanItemsSystem();
        $planItems = $planItemsSystem->check_expiration_date();
        $selectedItemList = $this->db->query("SELECT * FROM amaz_aug WHERE  id= '".$amz_id."'")->row();
        if($selectedItemList->tracking == 1 && $status ==0){
            $query = $this->db->query("UPDATE `amaz_aug` SET `tracking`='$status' WHERE `id`='$amz_id'");
            if ($query) {
                $resultCount = $this->db->query("SELECT * FROM `amaz_aug` where tracking = 1 AND user_id = '" . $_SESSION['uid'] . "'")->num_rows();
                $ajaxData['result']='success';
                $ajaxData['count'] = $resultCount;
                $ajaxData['show_result'] = $this->onGetDataTableContent();
            } else {
                $ajaxData['result']='failed';
                $ajaxData['message'] ='Update has been failed.';
                $ajaxData['show_result'] = $this->onGetDataTableContent();
            }
        }else{
            if(isset($planItems)) {
                if($planItems['plan_count'] > $planItems['current_count'] && $planItems['result'] =='success'){
                    $query = $this->db->query("UPDATE `amaz_aug` SET `tracking`='$status' WHERE `id`='$amz_id'");
                    if ($query) {
                        $resultCount = $this->db->query("SELECT * FROM `amaz_aug` where tracking = 1 AND user_id = '" . $_SESSION['uid'] . "'")->num_rows();
                        $ajaxData['result']='success';
                        $ajaxData['count'] = $resultCount;
                        $ajaxData['show_result'] = $this->onGetDataTableContent();
                    } else {
                        $ajaxData['result']='failed';
                        $ajaxData['message'] ='Update has been failed.';
                        $ajaxData['show_result'] = $this->onGetDataTableContent();
                    }
                } else {
                    $ajaxData['result'] ='failed';
                    $ajaxData['message'] ='oops! You are attempting to exceed your plan by enabling tracking on too many items. Please upgrade your plan to something more suitable or turn tracking off on some other items in order to free up some space.';
                    $ajaxData['show_result'] = $this->onGetDataTableContent();
                }
            } else {
                $ajaxData['result']='failed';
                $ajaxData['message'] ='Update has been failed.';
                $ajaxData['show_result'] = $this->onGetDataTableContent();
            }
        }
        echo json_encode($ajaxData);
        exit;

    }
///////////////////------------------TRACKING USE END--------------//////////////
    /*********************stock start**********************/
    public function stockinsert($s_id, $stocktatus)
    {
//        $data['site_info'] = $this->config->item('site_info');
//        $data['base_url'] = $this->config->item('base_url');
//        $data['site_page'] = 'dashboard';
//        // Title
//        $data['title_addition'] = 'Dashboard';
//        // Load stuff
//        $data['stylesheet'] = 'dashboard';
//        $data['javascript'] = 'dashboard';
//        $this->load->view('templates/header.php', $data);
//        $this->load->view('dashboard');
//        $this->load->view('templates/footer.php');

        $ajaxData = array();
        $planItemsSystem = new PlanItemsSystem();
        $planItems = $planItemsSystem->check_expiration_date();
        $selectedItemList = $this->db->query("SELECT * FROM amaz_aug WHERE  id= '".$s_id."'")->row();

        if($selectedItemList->stock_noti == "true" && $stocktatus == "false"){
            $query = $this->db->query("UPDATE `amaz_aug` SET `stock_noti`='$stocktatus' WHERE `id`='$s_id'");
            if ($query) {
                $count = $this->db->query("SELECT * FROM amaz_aug WHERE  stock_noti ='true' and user_id='".$_SESSION['uid']."'")->num_rows();
                $ajaxData['result'] = 'success';
                $ajaxData['count'] = $count;
                $ajaxData['show_result'] = $this->onGetDataTableContent();
            } else {
                $ajaxData['result'] = 'failed';
                $ajaxData['message'] = "Can not update it now.";
                $ajaxData['show_result'] = $this->onGetDataTableContent();
            }
        } else {
            if(isset($planItems)) {
                if($planItems['plan_count'] > $planItems['current_count'] && $planItems['result'] =='success'){
                    $query = $this->db->query("UPDATE `amaz_aug` SET `stock_noti`='$stocktatus' WHERE `id`='$s_id'");
                    if ($query) {
                        $count = $this->db->query("SELECT * FROM amaz_aug WHERE  stock_noti ='true' and user_id='".$_SESSION['uid']."'")->num_rows();
                        $ajaxData['result'] = 'success';
                        $ajaxData['count'] = $count;
                        $ajaxData['show_result'] = $this->onGetDataTableContent();
                    } else {
                        $ajaxData['result'] = 'failed';
                        $ajaxData['message'] = "Can not update it now.";
                        $ajaxData['show_result'] = $this->onGetDataTableContent();
                    }
                } else {
                    $ajaxData['result']='failed';
                    $ajaxData['message'] ='oops! You are attempting to exceed your plan by enabling tracking on too many items. Please upgrade your plan to something more suitable or turn tracking off on some other items in order to free up some space.';
                    $ajaxData['show_result'] = $this->onGetDataTableContent();
                }
            } else{
                $ajaxData['result']='failed';
                $ajaxData['message'] ='Update has been failed.';
                $ajaxData['show_result'] = $this->onGetDataTableContent();
            }
        }



        echo json_encode($ajaxData);
    }
    /**********************stock end**************/

//////////////////------------------EMAIL PHONE AND STOCK CHECK----/////////////
    /***********************EMAIL START********************/
    public function emailinsert($e_id, $e_status)
    {
        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'dashboard';
        // Title
        $data['title_addition'] = 'Dashboard';
        // Load stuff
        $data['stylesheet'] = 'dashboard';
        $data['javascript'] = 'dashboard';
        $query = $this->db->query("UPDATE `amaz_aug` SET `email_noti`='$e_status' WHERE `id`='$e_id'");
        if ($query) {
            echo "done";
        } else {
            echo "failed";
        }
        $this->load->view('templates/header.php', $data);
        $this->load->view('dashboard');
        $this->load->view('templates/footer.php');
    }
    /**********************EMAIL END***********************/

    /*********************phone start**********************/
    public function phoneinsert($p_id, $phonestatus)
    {
        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'dashboard';
        // Title
        $data['title_addition'] = 'Dashboard';
        // Load stuff
        $data['stylesheet'] = 'dashboard';
        $data['javascript'] = 'dashboard';
        $query = $this->db->query("UPDATE `amaz_aug` SET `phone_noti`='$phonestatus' WHERE `id`='$p_id'");
        if ($query) {
            echo "done";
        } else {
            echo "failed";
        }
        $this->load->view('templates/header.php', $data);
        $this->load->view('dashboard');
        $this->load->view('templates/footer.php');
    }
    /**********************phone end**************/


    /*********************Global start**********************/
    public function globalinsert($g_id, $globalstatus)
    {
        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'dashboard';
        // Title
        $data['title_addition'] = 'Dashboard';
        // Load stuff
        $data['stylesheet'] = 'dashboard';
        $data['javascript'] = 'dashboard';
        $query = $this->db->query("UPDATE `users` SET `global_noti`='$globalstatus' WHERE `id`='$g_id'");
        if ($query) {
            echo "done";
        } else {
            echo "failed";
        }
        $this->load->view('templates/header.php', $data);
        $this->load->view('dashboard');
        $this->load->view('templates/footer.php');
    }
    /**********************Global end**************/
//////////////////------------------EMAIL PHONE AND STOCK CHECK END/////////////
//////////////////--------------function for delete---------------///////////////
    public function delete_checkbox()
    {
        $ids = $this->input->post('ids');
        $mutiid = explode(",", $ids);
        foreach ($mutiid as $valID) {
            $query = $this->db->query("DELETE from amaz_aug where id='$valID'");
            mysqli_query($query);
            echo json_encode("true");
        }
    }
/////////////////---------------delete function end---------------////////////// 

////////////////----------------Insert Into DB--------------------//////////////
    public function SaveToDB()
    {
        $data1 = array();
//        $data['site_info'] = $this->config->item('site_info');
//        $data['base_url'] = $this->config->item('base_url');
//        $data['site_page'] = 'dashboard';
//
//        // Title
//        $data['title_addition'] = 'Dashboard';
//
//        // Load stuff
//        $data['stylesheet'] = 'dashboard';
//        $data['javascript'] = 'dashboard';
        $planItemsSystem = new PlanItemsSystem();
        $planItems = $planItemsSystem->check_expiration_date();
        if(isset($planItems)) {
            if($planItems['plan_count'] > $planItems['current_count'] && $planItems['result'] =='success'){
                $user_id = ($this->session->userdata('user_id'));
                $d = date("Y-m-d H:i:s");

                $user_id = $this->session->userdata('user_id');
                $img = $this->input->post('img');
                $title_name = $this->input->post('title_name');
                $asin = $this->input->post('asin');
                $amznotseller = $this->input->post('amznotseller');
                $sellerstock = $this->input->post('sellerstock');
                $rating = $this->input->post('rating');
                $review = $this->input->post('reviews');
                $seller_name = $this->input->post('seller_name');
                $seller_url = $this->input->post('seller_url');
                $seller_id = $this->input->post('seller_ids');
                $selling_price = $this->input->post('price');
                $shipping_price = $this->input->post('shipping');
                /*echo json_encode($img);*/
                $res = $this->db->query("SELECT * FROM amaz_aug where user_id=$user_id AND asin = '" . $asin . "'")->result();
                if (($amznotseller == "1") && ($sellerstock == "0")) {
                    $status = 0;/* print_r($status);exit;*/
                }
                if (($amznotseller == "1") && ($sellerstock == "1")) {
                    $status = 1;/*print_r($status);exit;*/
                }
                if (($amznotseller == "0") && ($sellerstock == "1")) {
                    $status = 2;/*print_r($status);exit;*/
                }
                if (($amznotseller == "0") && ($sellerstock == "0")) {
                    $status = 2;/*print_r($status);exit;*/
                }
                /* print_r($res);
                exit;
                */
                if ($res) {
                    /*echo 'Thos Asin Track is Already present';*/
                    $chkdata = 0;
                    $data1['result'] = 'failed';
                    $data1['message'] = "This asin exist in your list";
                } else {
                    $user_id = $this->session->userdata('user_id');

                    $data_insert = array(
                        'user_id'        => $user_id,
                        'image'          => $img,
                        'title_name'     => $title_name,
                        'tracking'       => 1,
                        'email_noti'     => 'true',
                        'asin'           => $asin,
                        'amznotseller'   => $amznotseller,
                        'sellerstock'    => $sellerstock,
                        'date'           => $d,
                        'rating'         => $rating,
                        'review'         => $review,
                        'seller_name'    => $seller_name,
                        'seller_url'     => $seller_url,
                        'seller_id'      => $seller_id,
                        'selling_price'  => $selling_price,
                        'shipping_price' => $shipping_price,
                        'status'         => $status
                    );
                    if ($this->db->insert('amaz_aug', $data_insert)) {
                        $latestID = $this->db->insert_id();
                        $data1['result'] = 'success';
                        $data1['message'] = "ASIN successfully added to item list.";
                        $data1['show_result'] = $this->onGetDataTableContent();
                    }
                }
            } else {
                $data1['result'] = "failed";
                $data1['message'] = "oops! You are attempting to exceed your plan by enabling tracking on too many items. Please upgrade your plan to something more suitable or turn tracking off on some other items in order to free up some space.";
            }
        } else {
            $data1['result'] = "failed";
            $data1['message'] = "oops! You are attempting to exceed your plan by enabling tracking on too many items. Please upgrade your plan to something more suitable or turn tracking off on some other items in order to free up some space.";
        }

        echo json_encode($data1);
        exit;
//                /*print_r($latestID);exit;*/
//                $queryLatest = $this->db->query("SELECT * FROM amaz_aug WHERE `id`='$latestID' ORDER BY status ASC")->row();
//                ////////////////////////////-----------FOR AMZNOTSELLER-------------------////////////////////////
//                if (($queryLatest->amznotseller == "1")) {
//                    $amzinfo =
//                        "<span style='color:green; font-size:25px;margin-left: -20px;'>Yes</span>";
//                }
//                if (($queryLatest->amznotseller == "0")) {
//                    $amzinfo = "<span style='color:black; font-size:25px;margin-left: -20px;'>No</span>";
//                }
//
//////////////////////////////-----------AMZNOTSELLER END-------------------////////////////////////
//
/////////////////////////////------------SELLERSTOCK START------------------/////////////////////
//                if (($queryLatest->amznotseller == "1") && ($queryLatest->sellerstock == "1")) {
//                    $sellerinfo =
//                        "<span style='color:green; font-size:25px;margin-left: -20px;'>Yes</span>";
//                } else if (($queryLatest->amznotseller == "1") && ($queryLatest->sellerstock == "0")) {
//                    $sellerinfo =
//                        "<span style='color:red; font-size:25px;margin-left: -20px;'>No</span>";
//                } else {
//                    $sellerinfo =
//                        "<span style='color:black; font-size:25px;margin-left: -20px;'>No</span>";
//                }
//
/////////////////////////////-------------SELLERSTOCK END--------------------/////////////////////
/////////////////////////////----------star start---------------////////////////////////
//                if (($queryLatest->amznotseller == "1") && ($queryLatest->sellerstock == "1")) {
//
//                    $star = "<span style='color:green; font-size:25px;margin-left: -20px;'><i class='fa fa-star'></i></span>";
//                } else if (($queryLatest->amznotseller == "1") && ($queryLatest->sellerstock == "0")) {
//                    $star = "<span style='color:red; font-size:25px;margin-left: -20px;'><i class='fa fa-star'></i></span>";
//                } else {
//                    $star = "";
//                }
/////////////////////////////---------star end-------------//////////////////////////
//                $data1 = "<tr role='row' class='odd'><td class='text-center vertical-middle star-wrapper' style=''>" . $star . "<img src=" . $img . " alt='' height='60' width='50'></td><td class='text-center vertical-middle' title='" . $queryLatest->title_name . "'><a style='color:black' target='_blank' href='http://amazon.com/dp/" . $queryLatest->asin . "'>" . $queryLatest->title_name . "</a></td><td class='text-center vertical-middle'>" . $queryLatest->asin . "</td><th class='vertical-middle cb text-center'><label class='switch'><input type='checkbox' data-role='flipswitch' onclick='chackUncheck(" . $queryLatest->id . ")' name='switch" . $queryLatest->id . "' id='switch" . $queryLatest->id . "' value='true' checked><div class='slider round'></div></label></th><td class='vertical-middle'><label class='switch'><input type='checkbox' data-role='flipswitch' onclick='chackUncheck" . $queryLatest->id . "'name='switch" . $queryLatest->id . "' id='switch" . $queryLatest->id . "' value='switch" . $queryLatest->id . "' ><div class='slider round'></div></label></td><th class='text-center b red vertical-middle'>" . $amzinfo . "</th><th class='text-center b red vertical-middle'>" . $sellerinfo . "</th><td class='vertical-middle'><label class='switch'><input type='checkbox' data-role='flipswitch' onclick='chackUncheck(" . $queryLatest->id . ")' name='switch" . $queryLatest->id . "' id='switch" . $queryLatest->id . "' value='switch" . $queryLatest->id . "' ><div class='slider round'></div></label></td><td class='vertical-middle'><label class='switch'><input type='checkbox' data-role='flipswitch' onclick='chackUncheck(" . $queryLatest->id . ")' name='switch" . $queryLatest->id . "' id='switch" . $queryLatest->id . "' value='switch" . $queryLatest->id . "' ><div class='slider round'></div></label></td><th class='text-center c-hold vertical-middle'><input type='checkbox' value='" . $queryLatest->id . "'  name='checkbulk1[]' class='check'/><label for='checkbox1' data-for='checkbox1' class='cb-label'></label></th></tr>";

    }
/////////////////////////////////------------insert DB END--------------------//////////////



    /**
     *  check current user time
     *
     */

    public function  check_expiration_date(){
        $data = array();
        $planItemsSystem = new PlanItemsSystem();
        $planItems = $planItemsSystem->check_expiration_date();
        if(isset($planItems)) {
            if($planItems['plan_count'] > $planItems['current_count'] && $planItems['result'] =='success'){
                $data['result'] = "success";
            } else {
                $data['result'] = "failed";
                $data['message'] = "oops! You are attempting to exceed your plan by enabling tracking on too many items. Please upgrade your plan to something more suitable or turn tracking off on some other items in order to free up some space.";
            }
        } else {
            $data['result'] = "failed";
            $data['message'] = "oops! You are attempting to exceed your plan by enabling tracking on too many items. Please upgrade your plan to something more suitable or turn tracking off on some other items in order to free up some space.";
        }
        echo json_encode($data);
        exit;

//        $data = array();
//        $difference_date = 0;
//
//        if(isset($_SESSION['uid'])){
//            $user = $this->db->query("SELECT * FROM users WHERE ID='".$_SESSION['uid']."'")->row();
//            if(isset($user)){
//                if(isset($user->created_at)){
//                    $today = date_create(date('Y-m-d'));
//                    $created = date_create(substr($user->created_at,0,10));
//                    $diff = date_diff($created,$today);
//                    $difference_date = $diff->days;
//                }
//            } else {
//                redirect('Login');
//            }
//
//            $stripeSubscription = $this->StripeSubscriptions_model->getSubscription();
//            if(isset($stripeSubscription)) {
//                if($stripeSubscription->ends_at != ""){
//                    $startDate =  date('Y-m-01 00:00:00',strtotime('this month'));
//                    $endDate = date('Y-m-t 23:59:59',strtotime('this month'));
//                    $subscriptionEnds = strtotime($stripeSubscription->ends_at);
//                    $checkStartDate = strtotime($startDate);
//                    $checkEndDate = strtotime($endDate);
//                    if( ($subscriptionEnds >= $checkStartDate) && ($subscriptionEnds <= $checkEndDate) ){
//                        $getSupport = $this->Supports_model->getCurrentUserSupport();
//                        if(isset($getSupport)){
//                            $getTrackSupport = $this->TrackSupports_model->getTrackItem($getSupport->track_support);
//                            if($getTrackSupport->price == "99999"){
//                                $selectedUser = $this->db->query("SELECT * FROM users where ID='".$_SESSION['uid']."'")->row();
//                                $countItem = $selectedUser->track_count;
//                                $currentAsinsCount = $this->getMonthAmazonAsins();
//                                if($countItem*1 <= $currentAsinsCount){
//                                    $data['result'] = "failed";
//                                    $data['message'] = "You have to upgrade plan.";
//                                } else {
//                                    $data['result'] = "success";
//                                    $data['message'] = "";
//                                }
//                            } else {
//                                $countItem = $getTrackSupport->count;
//                                $currentAsinsCount = $this->getMonthAmazonAsins();
//                                if($countItem*1 <= $currentAsinsCount){
//                                    $data['result'] = "failed";
//                                    $data['message'] = "You have to upgrade plan.";
//                                } else {
//                                    $data['result'] = "success";
//                                    $data['message'] = "";
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
//                    $getSupport = $this->Supports_model->getCurrentUserSupport();
//                    if(isset($getSupport)){
//                        $getTrackSupport = $this->TrackSupports_model->getTrackItem($getSupport->track_support);
//                        if($getTrackSupport->price == "99999"){
//                            $selectedUser = $this->db->query("SELECT * FROM users where ID='".$_SESSION['uid']."'")->row();
//                            $countItem = $selectedUser->track_count;
//                            $currentAsinsCount = $this->getMonthAmazonAsins();
//                            if($countItem*1 <= $currentAsinsCount){
//                                $data['result'] = "failed";
//                                $data['message'] = "You have to upgrade plan.";
//                            } else {
//                                $data['result'] = "success";
//                                $data['message'] = "";
//                            }
//                        } else {
//                            $countItem = $getTrackSupport->count;
//                            $currentAsinsCount = $this->getMonthAmazonAsins();
//                            if($countItem*1 <= $currentAsinsCount){
//                                $data['result'] = "failed";
//                                $data['message'] = "You have to upgrade plan.";
//                            } else {
//                                $data['result'] = "success";
//                                $data['message'] = "";
//                            }
//                        }
//
//                    } else{
//                        $data['result'] = "failed";
//                        $data['message'] = "You have to upgrade plan.";
//                    }
//                }
//            } else {
//                if($difference_date<= 14){
//                    if($this->getAllAmazonAsins() <= 80){
//                        $data['result'] = "success";
//                        $data['message'] = "";
//                    } else {
//                        $data['result'] = "failed";
//                        $data['message'] = "You have to upgrade plan.";
//                    }
//
//                }else {
//                    $data['result'] = "failed";
//                    $data['message'] = "You have to upgrade plan.";
//                }
//            }
//        } else {
//            $data['result'] = "failed";
//            $data['message'] = "Your session has been expired.";
//        }
//
//        echo json_encode($data);
//        exit;
    }

    public function getAsinsResult(){
        $data = array();
        if(isset($_POST['asin'])) {
            $asin = $_POST['asin'];
            $main_url = "https://www.amazon.com/gp/offer-listing/" . $asin . "/ref=dp_olp_new?ie=UTF8&condition=new";
            $check_exist = $this->db->query("SELECT * FROM amaz_aug where asin='".$asin."'")->row();
            if (empty($check_exist)) {
                $amznotseller = get_amazon_not_seller($asin);
                $html = getPage($main_url);
                $html = str_get_html($html);
                echo "123";
                exit;
            }
//            $asinsManagementSystem = new AsinsManagementSystem();
//            $asinsManagementSystem->getAsinsFromNumber($asin);

        }else {
            $data['result'] ='failed';
        }


    }


    public function getAllAmazonAsins(){
        $asinsCount = $this->db->query("SELECT * FROM amaz_aug WHERE user_id ='".$_SESSION['uid']."'")->num_rows();
        return $asinsCount;
    }

    public function getMonthAmazonAsins(){
        $startDate =  date('Y-m-01 00:00:00',strtotime('this month'));
        $endDate = date('Y-m-t 23:59:59',strtotime('this month'));
        $asinsCount = $this->db->query("SELECT * FROM amaz_aug WHERE user_id ='".$_SESSION['uid']."' and date <= '".$startDate."' and date >= '".$endDate."'")->num_rows();
        return $asinsCount;
    }

    public function onGetDataTableContent(){
        $results = $this->db->query("SELECT * FROM amaz_aug WHERE `user_id`='".$_SESSION['uid']."' ORDER BY amznotseller DESC , sellerstock ASC ")->result();
        $show_result = '';
        foreach ($results as $query) {
            
                $show_result .= '<tr role="row" class="odd">
                                    <td class="text-center vertical-middle star-wrapper" style="position: relative">';

                                     if (($query->amznotseller == "1") && ($query->sellerstock == "1")) {
                                         $show_result .= '<div class="green-right-triangle"></div>';
                                    } else if (($query->amznotseller == "1") && ($query->sellerstock == "0")) {
                                         $show_result .= '<div class="red-right-triangle"></div>';
                                     }
                              if($query->image != ''){
                             $show_result .='<a href="'.$query->image.'" data-fancybox="images" data-caption="'. $query->title_name.'" class="fancybox">
                                                <img src ="'.$query->image.'" style="width: 60px" />   
                                             </a>';
                              }
                        $show_result .='</td>';
                    $show_result .='<td class="text-center vertical-middle" title="'. $query->title_name .'">
                                    <a style="" target="_blank" href="http://amazon.com/dp/'. $query->asin.'">'.$query->title_name.'</a>
                                </td>';

                    $show_result .= '<td class="text-center vertical-middle">
                                    <a style="" target="_blank" href="http://amazon.com/dp/'. $query->asin.'">'. $query->asin.'</a>
                                </td>';
                $show_result .= '<td class="vertical-middle cb text-center">';
                                    if ($query->tracking == "1") {
                                $show_result .='<label class="switch">
                                                    <input type="checkbox" data-role="flipswitch"
                                                           onclick="chackUncheck('.$query->id.' )"
                                                           name="switch'.$query->id.'"
                                                           id="switch'.$query->id.'" value="true" checked>
                                                    <div class="slider round"></div>
                                                </label>';
                                    } else {
                                $show_result .='<label class="switch">
                                                    <input type="checkbox" data-role="flipswitch"
                                                           onclick="chackUncheck('.$query->id.')"
                                                           name="switch'.$query->id.'"
                                                           id="switch'.$query->id.'"
                                                           value="switch'.$query->id.'">
                                                    <div class="slider round"></div>
                                                </label>';
                                    }
                $show_result .='</td>';
                $show_result .= '<td class="vertical-middle cb text-center">';
                                    if ($query->stock_noti == "true") {
                                $show_result .='<label class="switch">
                                                    <input type="checkbox" data-role="flipswitch"
                                                           onclick="stockcheck('.$query->id.')"
                                                           name="switch'.$query->id.'"
                                                           id="switchstock'.$query->id.'"
                                                           value="switch'.$query->id.'" checked>
                                                    <div class="slider round"></div>
                                                </label>';
                                    } else {
                                $show_result .= '<label class="switch">
                                                    <input type="checkbox" data-role="flipswitch"
                                                           onclick="stockcheck('.$query->id.')"
                                                           name="switch'.$query->id.'"
                                                           id="switchstock'.$query->id.'"
                                                           value="switch'.$query->id.'">
                                                    <div class="slider round"></div>
                                                </label>';
                                    }
                $show_result .='</td>';
                    if (($query->amznotseller == "1")) {
                $show_result .='<td class="text-center b red verticle-middle">
                                    <span style="color:green; font-size:25px;">Yes!</span>
                                </td>';
                    }
                    if (($query->amznotseller == "0")) {
                    $show_result .='<td class="text-center b red verticle-middle">
                                        <span style="color:black; font-size:25px;">No</span>
                                    </td>';
                    }
                    if (($query->sellerstock == "1")) {
                        if (($query->amznotseller == "1")) {
                            $show_result .='<td class="text-center b red verticle-middle">
                                            <span style="color:green; font-size:25px;">Yes!</span>
                                        </td>';
                        } else{
                            $show_result .='<td class="text-center b red verticle-middle">
                                            <span style="color:black; font-size:25px;">Yes</span>
                                        </td>';
                        }
                    } else{
                        if (($query->amznotseller == "1")) {
                            $show_result .=' <td class="text-center b red verticle-middle">
                                            <span style="color:red; font-size:25px;">No!</span>
                                        </td>';
                        } else {
                            $show_result .='<td class="text-center b red verticle-middle">
                                            <span style="color:black; font-size:25px;">No</span>
                                        </td>';
                        }
                    }
                $show_result .= '<td class="vertical-middle cb text-center">';
                if ($query->email_noti == "true") {
                    $show_result .= '<label class="switch">
                                            <input type="checkbox" data-role="flipswitch"
                                                   onclick="emailcheck('.$query->id.')"
                                                   name="switch'.$query->id.'"
                                                   id="switchid'.$query->id.'"
                                                   value="switchEmail'.$query->id.'" checked>
                                            <div class="slider round"></div>
                                        </label>';
                }   else {
                    $show_result .= '<label class="switch">
                                            <input type="checkbox" data-role="flipswitch"
                                                   onclick="emailcheck('.$query->id.')"
                                                   name="switch'.$query->id.'"
                                                   id="switchid'.$query->id.'"
                                                   value="switchEmail'.$query->id.'">
                                            <div class="slider round"></div>
                                        </label>';
                }
                $show_result .='</td>';
                $show_result .= '<td class="vertical-middle cb text-center">';
                if ($query->phone_noti == "true") {
                    $show_result .=' <label class="switch">
                                            <input type="checkbox" data-role="flipswitch"
                                                   onclick="phonecheck('.$query->id.')"
                                                   name="switch'.$query->id.'"
                                                   id="switchphone'.$query->id.'"
                                                   value="switch'.$query->id.'" checked>
                                            <div class="slider round"></div>
                                        </label>';
                } else {
                    $show_result .=' <label class="switch">
                                            <input type="checkbox" data-role="flipswitch"
                                                   onclick="phonecheck('.$query->id.')"
                                                   name="switch'.$query->id.'"
                                                   id="switchphone'.$query->id.'"
                                                   value="switch'.$query->id.'">
                                            <div class="slider round"></div>
                                        </label>';
                }
                $show_result .='</td>';
                $show_result .=' <td class="text-center c-hold verticle-middle" id="checkes">
                                    <form action="" method="post" enctype="multipart/form-data">
                                        <input type="checkbox" value="'.$query->id.'" name="checkbulk1[]"
                                               class="check"/>
                                        <label for="checkbox1" data-for="checkbox1" class="cb-label"></label>
                                    </form>
                                </td>';
                    $show_result .= '</tr>';
        }
        return $show_result;
    }

    public function check_session(){
        $sessionTimeout = new SessionTimeout();
        $dataArray = $sessionTimeout->checkTimeOut();
        echo json_encode($dataArray);
        exit;
    }


}

