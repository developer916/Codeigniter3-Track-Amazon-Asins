<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends CI_Controller
{


    // private $res = array();

    public function __construct()
    {
        parent::__construct();
        if (!($this->session->userdata('user_id'))) {
            redirect('Login');
        }
        if (!($_SESSION['uid'])){
            redirect('Login');
        }
         $this->load->Model('Settings_model');
         $this->load->Model('EmailSupports_model');
         $this->load->Model('TrackSupports_model');
         $this->load->Model('Supports_model');
         $this->load->Model('StripeSubscriptions_model');
         $this->load->helper(array('form', 'url'));
         $this->load->database();
         $this->load->library('StripeSystem');
         $this->load->library('PlanItemsSystem');
         $this->load->library('SessionTimeout');
         $sessionTimeout = new SessionTimeout();
         $sessionTimeout->checkTimeOut();


    }

    public function index()
    {
        // Pass the site info
        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'settings';

        // Title
        $data['title_addition'] = 'Settings';

        // Load stuff
        $data['stylesheet'] = 'settings';
        $data['javascript'] = 'settings';

        // Load header library
        //$this->load->library('ForgotPasswordSystem.php');
        $this->load->library('src/User.php');

        // load the view
        $this->load->view('templates/header.php', $data);
        $this->load->view('home/settings/index');
        $this->load->view('templates/footer.php');
    }

    public function membership_account()
    {
        // Pass the site info
        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'settings';

        // Title
        $data['title_addition'] = 'Upgrade Plan';

        // Load stuff
        $data['stylesheet'] = 'settings';
        $data['javascript'] = 'settings';
        $data['javascript_item'] = 'upgrade_plan';
        //Get Supports from Database
        $data['support'] = $this->Supports_model->getCurrentUserSupport();
        $data['email_supports'] = $this->EmailSupports_model->getAllEmailSupports();
        $data['track_supports'] = $this->TrackSupports_model->getAllTrackSupports();
        $data['subscription'] = $this->StripeSubscriptions_model->getSubscription();
        $data['current_total_value'] = $this->Supports_model->getTotalValue();
        $planItemsSystem = new PlanItemsSystem();
        $data['planItems'] = $planItemsSystem->check_expiration_date();


        // Load header library
        //$this->load->library('ForgotPasswordSystem.php');

        // load the view
        $this->load->view('templates/header.php', $data);
        $this->load->view('home/settings/upgrade_plan');
        $this->load->view('templates/footer.php');
    }

    public function notification_settings()
    {
        // Pass the site info
        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'settings';

        // Title
        $data['title_addition'] = 'Notification Settings';

        // Load stuff
        $data['stylesheet'] = 'settings';
        $data['javascript'] = 'settings';

        // Load header library
        //$this->load->library('ForgotPasswordSystem.php');

        // load the view
        $this->load->view('templates/header.php', $data);
        $this->load->view('home/settings/notification_settings');
        $this->load->view('templates/footer.php');
    }

    public function security_settings()
    {
        // Pass the site info
        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'settings';

        // Title
        $data['title_addition'] = 'Security Settings';

        // Load stuff
        $data['stylesheet'] = 'settings';
        $data['javascript'] = 'settings';

        // Load header library
        //$this->load->library('ForgotPasswordSystem.php');

        // load the view
        $this->load->view('templates/header.php', $data);
        $this->load->view('home/settings/security_settings');
        $this->load->view('templates/footer.php');
    }

    public function amazon_api_settings()
    {
        // Pass the site info
        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'settings';

        // Title
        $data['title_addition'] = 'Amazon API Settings';

        // Load stuff
        $data['stylesheet'] = 'settings';
        $data['javascript'] = 'settings';

        // Load header library
        //$this->load->library('ForgotPasswordSystem.php');

        // load the view
        $this->load->view('templates/header.php', $data);
        $this->load->view('home/settings/amazon_api_settings');
        $this->load->view('templates/footer.php');
    }

    public function change_password()
    {
        // Pass the site info
        $data['site_info'] = $this->config->item('site_info');
        $data['base_url'] = $this->config->item('base_url');
        $data['site_page'] = 'settings';

        // Title
        $data['title_addition'] = 'Change Password';

        // Load stuff
        $data['stylesheet'] = 'settings';
        $data['javascript'] = 'settings';

        // Load header library
        //$this->load->library('ForgotPasswordSystem.php');

        // load the view
        $this->load->view('templates/header.php', $data);
        $this->load->view('home/settings/change_password');
        $this->load->view('templates/footer.php');
    }

    /*
     * AJAX CALLS
     */
    public function change_profile_picture()
    {
        if (isset($_FILES['profile_picture_file'])) {
            $this->Settings_model->changeProfilePic($_FILES['profile_picture_file']);
        } else {
            $this->_res['code'] = 0;
            $this->_res['string'] = "Invalid Request";

            print_r($this->_res) ;
        }
    }

    public function get_total_value()
    {
        $data = array();
        if(isset($_POST['email_support_id'])){
            $email_support_id = $_POST['email_support_id'];
        }
        if(isset($_POST['track_support_id'])){
            $track_support_id = $_POST['track_support_id'];
        }
        $totalValue = $this->Supports_model->getTotalValueFromAjax($email_support_id, $track_support_id);
        $data['status'] = "success";
        $data['total'] =  $totalValue;

        echo json_encode($data);
    }

    public function change_basic_information()
    {
        if (isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['company_name']) && isset($_POST['seller_id']) && isset($_POST['phone_number'])) {
            $firstname = $this->input->post('firstname');
            $lastname = $this->input->post('lastname');
            $company = $this->input->post('company_name');
            $seller_id = $this->input->post('seller_id');
            $phone = $this->input->post('phone_number');
            $email = $this->input->post('email');

            if (!empty($firstname) && !empty($lastname) && !empty($seller_id) && !empty($company) && !empty($phone) && !empty($email)) {
                // Wrap info in this array
                $data = array('firstname' => $firstname, 'lastname' => $lastname, 'company' => $company, 'seller_id' => $seller_id, 'phone' => $phone, 'email' => $email);
                $stripeSystem = new StripeSystem();
                $user = $this->db->query("SELECT * FROM users where ID='".$_SESSION['uid']."'")->row();
                if(isset($user)){
                    if(isset($user->stripe_id) && ($user->additional_email != $email)){
                        $customer_id = $user->stripe_id;
                        $customer = $stripeSystem->stripeRetrieveCustomer($customer_id);
                        $customer->email = $email;
                        $customer->save();
                    }
                }
                // Now call function
                $this->Settings_model->changeBasicInfo($data);

            } else {
                $this->res['code'] = 0;
                $this->res['string'] = "Please fill in all of the fields!";

                echo json_encode($this->res);
                return false;
            }
        } else {
            $this->_res['code'] = 0;
            $this->_res['string'] = "Invalid Request";

            echo json_encode($this->_res);
        }
    }
    //resume subscription for stripe

    public function resume_subscription(){
        $data = array();
        $stripeSystem = new StripeSystem();
        $stripeSubscription = $this->StripeSubscriptions_model->getSubscription();
        if($stripeSubscription->stripe_id !=""){
            $resumeArray = $stripeSystem->stripeSubscriptionResume($stripeSubscription->stripe_id, $stripeSubscription->stripe_plan);
            if($resumeArray['result'] =="success"){
                $subscription = $resumeArray['subscription'];
                $this->StripeSubscriptions_model->resumeSubscription($subscription);
                $data['result'] = "success";
                $data['message'] = "Your subscription resumed successfully";
            } else {
                echo json_encode($resumeArray);
                exit;
            }

        } else {
            $data['result'] ="failed";
            $data['message'] = "You didn't have subscription.";
        }
        echo json_encode($data);
        exit;
    }

    // cancel subscription for stripe

    public function cancel_subscription(){
        $data = array();
        $stripeSystem = new StripeSystem();
        $stripeSubscription = $this->StripeSubscriptions_model->getSubscription();
        if($stripeSubscription->stripe_id !=""){
            $subscription = $stripeSystem->stripeSubscriptionRetrieve($stripeSubscription->stripe_id);
            $subscription->cancel_at_period_end  = true;
//            $subscription->cancel(['cancel_at_period_end' => true]);
            $subscription->save();
//            $subscription->cancel();
            $this->StripeSubscriptions_model->cancelSubscription($subscription);
            $data['result'] ="success";
            $data['message'] = "You cancelled subscription successfully.";
        }else {
            $data['result'] ="failed";
            $data['message'] = "You didn't have subscription.";
        }
        echo json_encode($data);
        exit;
    }

    //upgrade plan

    public function upgrade_plan_process()
    {
        $data = array();
        $data['result'] = "failed";
        $stripeSystem = new StripeSystem();
        $planItemsSystem = new PlanItemsSystem();
        $email = "";
        if( ( isset($_POST['token']) && $_POST['token'] != "") && (isset($_POST['total_value']) && $_POST['total_value'] !=0) ){
           $token = $_POST['token'];
           $amount = $_POST['total_value'];
            $track_support = $_POST['track_support_id'];
            $email_support = $_POST['email_support_id'];

            $trackSupportItem = $this->TrackSupports_model->getTrackItem($track_support);
            if($trackSupportItem->count < $planItemsSystem->getAllAmazonAsins()){
                $data['result'] ='failed';
                $data['message'] = 'The plan you are attempting to subscribe to is insufficient due to the amount of ASINS that you are currently tracking. Please turn tracking off on some items or choose a higher plan';
                echo json_encode($data);
                exit;
            }
            $sql_user = "select * from users where ID= ?";
            $result = $this->db->query($sql_user, array($_SESSION['uid']));
            $row = $result->row();
            if (isset($row)){
                $name = $row->fname." ".$row->lname;
                $email = $row->additional_email;
            }
           if(isset($_POST['payment_name']) && $_POST['payment_name'] !="") {
               $name = $_POST['payment_name'];
           }
           $existPlanArray = $stripeSystem->getPlan($amount);
            if($existPlanArray['result'] == "success"){
                $plan = $existPlanArray['plan'];
                $this->stripeCreateSubscription($email, $token, $plan,$track_support, $email_support, $amount);
            } else if($existPlanArray['result'] == "empty") {
                $productArray = $stripeSystem->createProduct($name);
                if($productArray['result'] == "success"){
                    $product = $productArray['product'];
                    $planArray = $stripeSystem->createPlan($name, $amount, $product->id);
                    if($planArray['result'] =="success"){
                        $plan = $planArray['plan'];
                        $this->stripeCreateSubscription($email, $token, $plan,$track_support, $email_support, $amount);
                    }else {
                        echo json_encode($planArray);
                        exit;
                    }
                }else {
                    echo json_encode($productArray);
                    exit;
                }
            }else {
                echo json_encode($existPlanArray);
                exit;
            }

        } else{
            $subscription =$this->StripeSubscriptions_model->getSubscription();
            if(isset($subscription)){
                $track_support = $_POST['track_support_id'];
                $email_support = $_POST['email_support_id'];
                $trackSupportItem = $this->TrackSupports_model->getTrackItem($track_support);
                if($trackSupportItem->count < $planItemsSystem->getAllAmazonAsins()){
                    $data['result'] ='failed';
                    $data['message'] = 'The plan you are attempting to subscribe to is insufficient due to the amount of ASINS that you are currently tracking. Please turn tracking off on some items or choose a higher plan';
                    echo json_encode($data);
                    exit;
                }
                $user = $this->db->query("SELECT * FROM users WHERE ID='".$_SESSION['uid']."'")->row();
                if(isset($user)) {
                    $name = $user->fname." ".$user->lname;
                    $amount = $_POST['total_value'];
                    $existPlanArray = $stripeSystem->getPlan($amount);
                    if($existPlanArray['result'] == "success") {
                        $plan = $existPlanArray['plan'];
                        $resumeArray = $stripeSystem->stripeSubscriptionResume($subscription->stripe_id, $plan->id);
                        if($resumeArray['result'] == "success"){
                            $subscription = $resumeArray['subscription'];
                            $oldPrice = $this->Supports_model->getTotalValue();
                            $this->storeSupports($track_support, $email_support);
                            $this->updateUserStripeSubscription($plan, $amount,$subscription);
                            $newPrice = $this->Supports_model->getTotalValue();
                            $data['result'] = "success";
                            if($newPrice > $oldPrice){
//                                $price = $newPrice - $oldPrice;
//                                $data['message'] = "You were charged a prorated amount of: $".$price." and your subscription renewal date remains the same. ";
                                $data['message'] = "We have successfully upgraded your plan to the ". $plan->nickname. "You have been changed a prorated amount of: $". $newPrice ." which will reflect on your account immediately.";
                            } else {
                               $data['message'] = "We have successfully downgraded your plan to the ". $plan->nickname. " You have a received a prorated credit of: $" .$newPrice ." which will be applied to your next billing cycle.";
                            }

                            echo json_encode($data);
                            exit;
                        }else {
                            echo json_encode($resumeArray);
                            exit;
                        }

                    } else if($existPlanArray['result'] == "empty") {
                        $productArray = $stripeSystem->createProduct($name);
                        if ($productArray['result'] == "success") {
                            $product = $productArray['product'];
                            $planArray = $stripeSystem->createPlan($name, $amount, $product->id);
                            if ($planArray['result'] == "success") {
                                $plan = $planArray['plan'];

                                $resumeArray = $stripeSystem->stripeSubscriptionResume($subscription->stripe_id, $plan->id);
                                if($resumeArray['result'] == "success"){
                                    $subscription = $resumeArray['subscription'];
                                    $oldPrice = $this->Supports_model->getTotalValue();
                                    $this->storeSupports($track_support, $email_support);
                                    $this->updateUserStripeSubscription($plan, $amount,$subscription);
                                    $newPrice = $this->Supports_model->getTotalValue();
                                    $data['result'] = "success";
                                    if($newPrice > $oldPrice){
//                                  $price = $newPrice - $oldPrice;
//                                  $data['message'] = "You were charged a prorated amount of: $".$price." and your subscription renewal date remains the same. ";
                                        $data['message'] = "We have successfully upgraded your plan to the ". $plan->nickname. "You have been changed a prorated amount of: $". $newPrice ." which will reflect on your account immediately.";
                                    } else {
                                        $data['message'] = "We have successfully downgraded your plan to the " . $plan->nickname . " You have a received a prorated credit of: $" . $newPrice . " which will be applied to your next billing cycle.";
                                    }

                                    echo json_encode($data);
                                    exit;
                                }else {
                                    echo json_encode($resumeArray);
                                    exit;
                                }
                            }else {
                                echo json_encode($planArray);
                                exit;
                            }
                        } else {
                            echo json_encode($productArray);
                            exit;
                        }
                    } else {
                        echo json_encode($existPlanArray);
                        exit;
                    }
                }

            } else {
                $data['result'] = "failed";
                $data['message'] = "Your stripe token is invalid";
            }
        }
        echo json_encode($data);
        exit;
    }

    public function stripeCreateSubscription($email, $token, $plan,$track_support, $email_support, $amount ){
        $stripeSystem = new StripeSystem();
        $customerArray = $stripeSystem->stripeCustomer($email, $token);
        $data = array();
        if($customerArray['result'] == "success"){
            $customer = $customerArray['customer'];
            $subscriptionArray = $stripeSystem->stripeSubscription($customer, $plan->id);
            if($subscriptionArray['result'] == "success"){
                $subscription = $subscriptionArray['subscription'];
                $this->storeSupports($track_support, $email_support);
                $this->storeStripeSubscription($subscription, $amount);
                $this->updateUser($customer, $subscription);
                $data['result'] = "success";
                $data['message'] = "Your subscription has been saved successfully. ";
                echo json_encode($data);
                exit;
            }else {
                echo json_encode($subscriptionArray);
                exit;
            }
        }else {
            echo json_encode($customerArray);
            exit;
        }
    }

    public function updateUser($customer, $subscription){
        $res = $this->db->query("SELECT * FROM users where ID='".$_SESSION['uid']."'")->result();
        if($res){
            $updateData = array(
                'stripe_id' => $customer->id,
                'card_brand' => $customer->sources->data[0]->brand,
                'card_last_four' => $customer->sources->data[0]->last4,
                'trial_ends_at' => null
            );
            $this->db->where('ID', $_SESSION['uid']);
            $this->db->update('users', $updateData);
        }
    }

    public function updateUserStripeSubscription($plan, $amount, $subscription){
        $updateData = array(
            'stripe_plan' => $plan->id,
            'price' => $amount,
            'plan_name' => $plan->nickname,
            'ends_date_subscription' => (date('Y-m-d H:i:s ',($subscription->current_period_end)))
        );

        $this->db->where('user_id', $_SESSION['uid']);
        $this->db->update('stripe_subscriptions', $updateData);
    }
    public function storeStripeSubscription($subscription, $amount){
        $res = $this->db->query("SELECT * FROM stripe_subscriptions where user_id='".$_SESSION['uid']."'")->result();
        if(!$res){
            $updateData = array(
                'user_id' => $_SESSION['uid'],
                'name' => "main",
                'stripe_id' => $subscription->id,
                'stripe_plan' => $subscription->plan->id,
                'quantity' => $subscription->quantity,
                'trial_ends_at' =>  null,
                'price' => $amount,
                'plan_name' => $subscription->plan->nickname,
                'ends_date_subscription' => (date('Y-m-d H:i:s ',($subscription->current_period_end)))
            );
            $this->db->insert('stripe_subscriptions', $updateData);
        } else {
            $updateData = array(
                'name' => "main",
                'stripe_id' => $subscription->id,
                'stripe_plan' => $subscription->plan->id,
                'quantity' => $subscription->quantity,
                'trial_ends_at' =>  null,
                'price' => $amount,
                'plan_name' => $subscription->plan->nickname,
                'ends_date_subscription' => (date('Y-m-d H:i:s ',($subscription->current_period_end)))
            );
            $this->db->where('user_id', $_SESSION['uid']);
            $this->db->update('stripe_subscriptions', $updateData);
        }
    }

    public function storeSupports($track_support, $email_support){
        $res = $this->db->query("SELECT * FROM supports where user_id='".$_SESSION['uid']."'")->result();
        if(!$res){
            $updateData = array(
                'user_id' => $_SESSION['uid'],
                'track_support' => $track_support,
                'email_support' => $email_support,
            );
            $this->db->insert('supports', $updateData);
        } else {
            $updateData = array(
                'track_support' => $track_support,
                'email_support' => $email_support,
            );
            $this->db->where('user_id', $_SESSION['uid']);
            $this->db->update('supports', $updateData);
        }
    }

    public function change_notification_settings()
    {
        if (isset($_POST['enable_notifications']) && isset($_POST['email']) && isset($_POST['phone_number']) && isset($_POST['location']) && isset($_POST['timezone'])) {
            $notifications = $this->input->post('enable_notifications');
            $email = $this->input->post('email');
            $phone = $this->input->post('phone_number');
            $location = $this->input->post('location');
            $timezone = $this->input->post('timezone');
            $userId = $this->session->userdata('user_id');
            $user = $this->db
                ->from('users')
                ->where('ID', $userId)
                ->get()
                ->result()[0];

            $email = explode(',', $email);

            if (count($email) != 0 && !empty($phone)) {

                $data = array(
                    'global_noti' => $notifications,
                    'notification_phone' => $phone,
                    'notification_email' => implode(',', $email),
                    'location' => $location,
                    'timezone' => $timezone
                );

                $this->db->where('ID', $userId);
                if($this->db->update('users', $data)) {
                    $this->res['code'] = 1;
                    $this->res['string'] = "Your setting has been updated!";
                    echo json_encode($this->res);
                    return false;
                }
            } else {
                $this->res['code'] = 0;
                $this->res['string'] = "Please make sure all fields are checked and filled it!";

                echo json_encode($this->res);
                return false;
            }
        } else {
            $this->_res['code'] = 0;
            $this->_res['string'] = "Invalid Request";

            echo json_encode($this->_res);
        }
    }

    public function changePasswordProcess()
    {
        if (isset($_POST['current_password']) && isset($_POST['new_password']) && isset($_POST['confirm_new_password'])) {
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password');
            $confirm_new_password = $this->input->post('confirm_new_password');

            if (!empty($current_password) && !empty($new_password) && !empty($confirm_new_password)) {
                $this->Settings_model->changePasswordProcess($current_password, $new_password, $confirm_new_password);
            } else {
                $this->res['code'] = 0;
                $this->res['string'] = "Please make sure all fields are checked and filled it!";

                echo json_encode($this->res);
                return false;
            }
        } else {
            $this->_res['code'] = 0;
            $this->_res['string'] = "Invalid Request";

            echo json_encode($this->_res);
        }
    }

    public function amazon_api_update()
    {
        if (isset($_POST['api_connection']) && isset($_POST['seller_id']) && isset($_POST['marketplace_id']) && isset($_POST['associate_tag']) && isset($_POST['dev_account_number']) && isset($_POST['access_key_id']) && isset($_POST['secret_key'])) {
            $api_connection = $this->input->post('api_connection');
            $seller_id = $this->input->post('seller_id');
            $marketplace_id = $this->input->post('marketplace_id');
            $associate_tag = $this->input->post('associate_tag');
            $dev_account_number = $this->input->post('dev_account_number');
            $access_key_id = $this->input->post('access_key_id');
            $secret_key = $this->input->post('secret_key');

            if ($api_connection != "" && $seller_id != "" && $marketplace_id != "" && $associate_tag != "" && $dev_account_number != "" && $access_key_id != "" && $secret_key != "") {
                $data = array('api_connection' => $api_connection, 'seller_id' => $seller_id, 'marketplace_id' => $marketplace_id, 'associate_tag' => $associate_tag, 'dev_account_number' => $dev_account_number, 'access_key_id' => $access_key_id, 'secret_key' => $secret_key);
                $this->Settings_model->amazonAPIProcess($data);
            } else {
                $this->_res['code'] = 0;
                $this->_res['string'] = "Invalid Request";

                echo json_encode($this->_res);
            }
        } else {
            $this->_res['code'] = 0;
            $this->_res['string'] = "Invalid Request";

            echo json_encode($this->_res);
        }
    }

    public function change_security_settings()
    {
        if(isset($_POST['current_email']) && isset($_POST['new_email']) && isset($_POST['confirm_new_email']) ){
            $current_email = $this->input->post('current_email');
            $new_email = $this->input->post('new_email');
            $confirm_new_email = $this->input->post('confirm_new_email');
            $this->Settings_model->changeEmail($current_email, $new_email, $confirm_new_email);
        }else {
            $this->_res['code'] = 0;
            $this->_res['string'] = "Invalid Request";

            echo json_encode($this->_res);
        }
//        if(isset($_POST['email']) && isset($_POST['phone_number'])){
//            $email = $this->input->post('email');
//            $phone_number = $this->input->post('phone_number');
//            $this->Settings_model->changeSecuritySettings($email, $phone_number);
//        } else {
//            $this->_res['code'] = 0;
//            $this->_res['string'] = "Invalid Request";
//
//            echo json_encode($this->_res);
//        }
    }

    public function remove_profile_picture()
    {
        if (isset($_POST['run'])) {
            if ($this->input->post('run') == 1) {
                $this->Settings_model->removePP();
            } else {
                $this->_res['code'] = 0;
                $this->_res['string'] = "Invalid Request";

                echo json_encode($this->_res);
            }
        } else {
            redirect('/', 'location');
        }
    }

    public function webhook(){
        $stripeSystem = new StripeSystem();
        $stripeSystem->stripeWebhook();
    }


    public function deleteAccount(){
        $data = array();
        $stripeSystem = new StripeSystem();
        $stripeSubscription = $this->StripeSubscriptions_model->getSubscription();
        if(isset($stripeSubscription)){
            if($stripeSubscription->stripe_id !="") {
                $subscription = $stripeSystem->stripeSubscriptionRetrieve($stripeSubscription->stripe_id);
                $subscription->cancel_at_period_end = true;
                $subscription->save();
            }
        }
        $this->db->query("DELETE FROM amaz_aug where user_id = '".$_SESSION['uid']."'");
        $this->db->query("DELETE FROM notification where user_id = '".$_SESSION['uid']."'");
        $this->db->query("DELETE FROM supports where user_id = '".$_SESSION['uid']."'");
        $this->db->query("DELETE FROM users where id ='".$_SESSION['uid']."'   ");
        $data['result'] = 'success';
        $data['message'] = "Your account has been closed successfully";
        echo json_encode($data);
        exit;
    }
}