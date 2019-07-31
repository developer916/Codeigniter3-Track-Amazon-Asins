<?php
class StripeSubscriptions_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function getSubscription(){
        $subscription = $this->db->query("SELECT * FROM stripe_subscriptions WHERE user_id = '".$_SESSION['uid']."'")->row();
        return $subscription;
    }

    public function cancelSubscription($subscription){
        $updateData = array(
            'ends_at' =>  (date('Y-m-d H:i:s ',($subscription->current_period_end)))
        );
        $this->db->where('user_id', $_SESSION['uid']);
        $this->db->update('stripe_subscriptions', $updateData);
    }

    public function resumeSubscription($subscription){
        $updateData = array(
            'ends_at' => null,
            'ends_date_subscription' => (date('Y-m-d H:i:s ',($subscription->current_period_end)))
        );
        $this->db->where('user_id', $_SESSION['uid']);
        $this->db->update('stripe_subscriptions', $updateData);
    }
}