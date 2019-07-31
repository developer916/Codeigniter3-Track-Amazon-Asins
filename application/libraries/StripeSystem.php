<?php
//require_once('/vendor/stripe/stripe-php/init.php');

require FCPATH . 'vendor/autoload.php';

class StripeSystem{

    private $response = array();

    private $stripe_key;
    private $token;
    private $product;
    public function __construct()
    {
        $this->_CI =& get_instance();

        // Load libraries
        $this->_CI->load->library("src/Validation.php");
        $this->_CI->load->library("src/Response.php");
        $this->_CI->load->library("src/Encryption.php");
        $this->_CI->load->library("src/User.php");
        $this->_CI->load->library("src/Upload.php");

        // Load database
        $this->pdo = $this->_CI->load->database('pdo', true)->conn_id;
        $this->stripe_key = ($this->_CI->config->item('STRIPE_SECRET')) ;
        \Stripe\Stripe::setApiKey($this->stripe_key);
    }

    public function stripeToken($card_number, $card_month, $card_year, $card_cvv){
        $token = \Stripe\Token::create(
            array(
                "card" => array(
                    "number"    => $card_number,
                    "exp_month" => $card_month,
                    "exp_year"  => $card_year,
                    "cvc"       => $card_cvv
                )
            )
        );
        return $this->token = $token;
    }

    public function createProduct($name){
        $data = array();
        try {
            $product = \Stripe\Product::create([
                'name' => time().$name,
                'type' => 'service',
            ]);
            $this->product = $product;
            $data['result'] = 'success';
            $data['product'] = $product;
        } catch (\Exception $e) {
            $data['result'] = 'failed';
            $data['message'] = $e->getMessage();
        }
        return $data;
    }

    public function  createPlan($nickName, $amount, $product){
        $data = array();
        try {
            $plan = \Stripe\Plan::create([
                'currency' => 'usd',
                'interval' => 'month',
                'product' => $product,
                'nickname' => "$".$amount. " asins plan",
                'amount' => $amount* 100,
            ]);
            $data['result'] = 'success';
            $data['plan'] = $plan;

        }catch (\Exception $e) {
            $data['result'] = 'failed';
            $data['message'] = $e->getMessage();
        }
        return $data;

    }

    public function getPlan($price){
        $data = array();
        try{
            $plans = \Stripe\Plan::all();
            $check = 0;
            if(count($plans) >0) {
                foreach($plans as $key =>$plan){
                    if($plan->amount == $price* 100 ){
                        $check = 1;
                        $data['result'] = 'success';
                        $data['plan'] = $plan;
                    }
                }
            }
            if($check ==0) {
                $data['result'] ='empty';
            }


        } catch (\Exception $e) {
            $data['result'] = 'failed';
            $data['message'] = $e->getMessage();
        }
        return $data;
    }

    public function stripeCharge($token , $plan){
        $data = array();
        try{
            $charge = \Stripe\Charge::create(array(
                "amount" => (($plan->amount *100)),
                "currency" => $plan->currency_code,
                "description" => "subscribe",
                "source" => $token,
            ));
            $data['result'] = 'success';
            $data['charge'] = $charge;
        } catch (\Exception $e){
            $data['result'] = 'failed';
            $data['message'] = $e->getMessage();
        }

        return $data;
    }

    public function stripeCustomer($email, $token) {
        $data = array();
        try{
            $customer = \Stripe\Customer::create(array(
                "email"     => $email,
                "source"    => $token
            ));
            $data['result'] = 'success';
            $data['customer'] = $customer;

        }catch (\Exception $e){
            $data['result'] = 'failed';
            $data['message'] = $e->getMessage();
        }
        return $data;
    }

    public function stripeRetrieveCustomer($customer_id) {

        $customer = \Stripe\Customer::retrieve($customer_id);

        return $customer;
    }

    public function stripeSubscription($customer, $plan,  $coupon=null){
        $data = array();
        try{
//            if($plan->trial_days != '') {
//                $final_time = strtotime("+".$plan->trial_days." day", time());
//            }else{
//                $final_time = strtotime("+1 month", time());
//            }


//            $final_time = strtotime("+1 month", time());

            if($coupon) {
                $subscription = \Stripe\Subscription::create([
                    'customer' => $customer->id,
                    'items' => [['plan' => $plan]],
                    'coupon' => $coupon,
                ]);
            }else{
                $subscription = \Stripe\Subscription::create([
                    'customer' => $customer->id,
                    'items' => [['plan' => $plan]],
                ]);
            }
            $data['result'] = 'success';
            $data['subscription'] = $subscription;
        } catch (\Exception $e){
            $data['result'] = 'failed';
            $data['message'] = $e->getMessage();
        }
        return $data;
    }

    public function stripeSubscriptionRetrieve($subscription_id){
        $subscription = \Stripe\Subscription::retrieve($subscription_id);
        return $subscription;
    }

    public function stripeSubscriptionUpdate($subscription_id){

        try{
            $subscription = \Stripe\Subscription::update($subscription_id, [
                'trial_end' => 'now',
                'billing_cycle_anchor' => 'now'
            ]);
            $data['result'] = 'success';
            $data['subscription'] = $subscription;

        } catch (\Exception $e){
            $data['result'] = 'failed';
            $data['message'] = $e->getMessage();
        }
        return $data;
    }


    public function stripeSubscriptionResume($subscription_id, $plan){
        $subscription  = $this->stripeSubscriptionRetrieve($subscription_id);
        try{
             $subscription= \Stripe\Subscription::update($subscription_id, [
                'cancel_at_period_end' => false,
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'plan' => $plan,
                    ],
                ],
            ]);
            $data['result'] = 'success';
            $data['subscription'] = $subscription;
        } catch (\Exception $e){
            $data['result'] = 'failed';
            $data['message'] = $e->getMessage();
        }
        return $data;

    }


    public function stripeWebhook(){
//        $payload = @file_get_contents('php://input');
//        $endpoint_secret = 'whsec_iruumO5s8yTNNx5BDgEr2yRzleGKo4wf';
//        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
//        $event = null;
//
//        try {
//            $event = \Stripe\Webhook::constructEvent(
//                $payload, $sig_header, $endpoint_secret
//            );
//        } catch(\UnexpectedValueException $e) {
//            // Invalid payload
//            http_response_code(400); // PHP 5.4 or greater
//            exit();
//        } catch(\Stripe\Error\SignatureVerification $e) {
//            // Invalid signature
//            http_response_code(400); // PHP 5.4 or greater
//            exit();
//        }

//        $event_json = json_decode($input);
//        print_r($event_json);
//        exit;
//
//        $event = \Stripe\Event::retrieve($event_json->id);
    }
}