<?php

require APPPATH . 'libraries/mailgun-php/vendor/autoload.php';
use Mailgun\Mailgun;

class Cron extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
//
//        if (!$this->input->is_cli_request()) {
//            die("Your credentials do not allow access to this resource.");
//        }

        date_default_timezone_set('America/New_York');

        ini_set('max_execution_time', 9999999);
        ini_set('memory_limit', '9999M');
        error_reporting(E_ALL);

        $res = $this->db->query("SELECT * FROM amaz_aug where tracking=1")->result();
        $notifications = array();

        foreach ($res as $r) {

            //Get user marketplace
//            $market = $this->db
//                ->from('amazonapi')
//                ->where('UserID', $r->user_id)
//                ->get()
//                ->result();
//            $market = isset($market[0]) ? $market[0] : false;

            $amzNotSeller = get_amazon_not_seller($r->asin);
            $sellerStock = get_seller_stock($r->asin);

//		echo "<p>Asin: ".$r->asin."<br />ANS (Script/DB): ".$amzNotSeller."/".$r->amznotseller."<br />Stock (Script/DB): ".$sellerStock."/".$r->sellerstock.".<br/>stock_noti.$r->stock_noti</p>";
            $date = date('Y-m-d H:i:s');
            if ($r->amznotseller != $amzNotSeller && $r->stock_noti == 'true') {
                $data = array(
                    'user_id' => $r->user_id,
                    'image' => $r->image,
                    'title_name' => $r->title_name,
                    'asin' => $r->asin,
                    'sellerstock' => $sellerStock,
                    'amznotseller' => $amzNotSeller,
                    'date' => $date,
                    'amzoutofstock' => $amzNotSeller
                );

                //Insert report data
                $this->common_model->insertData('amz_report', array(
                    'asin'           => $r->asin,
                    'amz_not_seller' => $amzNotSeller,
                    'date'           => date('Y-m-d')
                ));

                $notification = $this->common_model->insertData('notification', $data);
                $this->common_model->updateData(
                    'amaz_aug',
                    array(
                        'amznotseller' => $amzNotSeller,
                        'sellerstock' => $sellerStock
                    ),
                    array(
                        'asin' => $r->asin
                    )
                );
                if(!isset($notifications[$r->user_id])) {
                    $notifications[$r->user_id] = array();
                }

                $notifications[$r->user_id][] = $notification;
            }
        }

        print_r($notifications);

        foreach($notifications as $user => $userNotifications) {
            $this->sendNotificationUser($user, $userNotifications);
        }

        echo "<br/>Done!;";

        return false;
    }

    private function sendNotificationUser($userId, $notificationIds)
    {
        echo $userId;
        exit;

        //Get notification data
        $notifications = $this->db
            ->from('notification')
            ->where_in('cron_id', $notificationIds)
            ->get();
        if($notifications->num_rows() == 0) return false;
        $notifications = $notifications->result();

        ///////////////////////------------mail start---------------------------//////////////////
        $user = $this->db->query("SELECT * FROM users where ID={$userId} ")->result();
        if(!isset($user[0])) return false;
        $user = $user[0];
        $global_check = $user->global_noti;

        if($global_check == 'false') return false;

        $asins = array();
        foreach($notifications as $notification) {
            $asins[] = $notification->asin;
        }

        $products = $this->db
            ->from('amaz_aug')
            ->where('user_id', $userId)
            ->where_in('asin', $asins)
            ->get()
            ->result();

        $emailAsins = array();
        $phoneAsins = array();
        foreach($products as $product) {
            if($product->email_noti == 'true') $emailAsins[] = $product->id;
            if($product->phone_noti == 'true') $phoneAsins[] = $product->id;
        }


        if(!empty($emailAsins)) {
            $mgClient = new Mailgun('key-f14cf94304da5471b926ec3e4487773f');
            $domain = "trackasins.com";


            $html = "
                                <html>
                                <head>
                                    <title>Trackasins</title>
                                </head>
                                <body>
                                    <h1>Trackasins</h1>";

            foreach ($notifications as $notification) {
                $date = date('d-m-Y', strtotime($notification->date));
                $message = $notification->amznotseller == '0' ? "Amazon back in stock on $date" : "Amazon ran out of stock on $date";

                $html .= "<br/>
                        <img src=\"{$notification->image}\" style= \"width: 50px; height: 60px;\">
                        <p>
                            ASIN  : <b><a href=\"https://www.amazon.com/dp/{$notification->asin}\">{$notification->asin}</a></b> <br/>
                            Title : <b>{$notification->title_name}</b><br>
                            Notification : <b>{$message}</b><br/>
                            Are you in stock : <b>".(($notification->sellerstock == '1') ? 'Yes' : 'No')."</b>
                        </p>";
            }


            $html .= "</body></html>";

            $email = $user->additional_email != '' ? $user->additional_email : $user->email;
            $result = $mgClient->sendMessage("$domain",
                array('from' => '<postmaster@trackasins.com>',
                    'to' => $email,
                    'subject' => 'Trackasins ',
                    'html' => $html
                ));
            if ($result) {
                echo 'mail successfully send using cron script...';
            }
        }

        if(!empty($phoneAsins)) {
            /////////////////////-------------sms start------------------------------///////////////
            $message = "Following ASINs ran out of Stock: " . implode(',', $phoneAsins);
            echo file_get_contents("http://api.clickatell.com/http/sendmsg?user=nateadmin&password=eTcZDaXPRFacGY&api_id=3593336&to=" . $user->phone . "&text=$message");
            ////////////////////--------------sms end--------------------------------///////////////
        }

    }
}
  
   
