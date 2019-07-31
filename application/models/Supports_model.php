<?php
class Supports_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function getCurrentUserSupport(){
        $support = $this->db->query("SELECT * FROM supports where user_id='".$_SESSION['uid']."'")->row();
        return $support;
    }

    public function getTotalValue(){
        $total = 0;
        $support = $this->db->query("SELECT * FROM supports where user_id='".$_SESSION['uid']."'")->row();
        if($support){
            $email_support = $this->db->query("SELECT * FROM email_supports where id='".$support->email_support."'")->row();
            if($email_support){
                $total += $email_support->price;
            }
            $track_support = $this->db->query("SELECT * FROM track_supports where id='".$support->track_support."'")->row();
            if($track_support){
                if($track_support->price != "99999"){
                    $total += $track_support->price;
                }
            }
        }

        return $total;
    }

    public function getTotalValueFromAjax($email_support_id, $track_support_id){
        $total = 0;
        $email_support = $this->db->query("SELECT * FROM email_supports where id='".$email_support_id."'")->row();
        if($email_support){
            $total += $email_support->price;
        }
        $track_support = $this->db->query("SELECT * FROM track_supports where id='".$track_support_id."'")->row();
        if($track_support){
            if($track_support->price != "99999"){
                $total += $track_support->price;
            }
        }
        return $total;
    }
}