<?php

require FCPATH . 'vendor/autoload.php';
use Mailgun\Mailgun;

//use MailgunMailgun;


class ForgotPasswordSystem
{
    private $_CI;
    
    private $response = array();
    
    private $string;

    private $username;
    private $email;

    private $type_request;

    private $request_id;

    private $password;
    private $confirm_password;
    private $unique_id;

    /*
     * Load up libraries that's gonna be needed in this class
     */
    public function __construct()
    {
        $this->_CI =& get_instance();
        
        // Load libraries
        $this->_CI->load->library("src/Validation.php");
        $this->_CI->load->library("src/Encryption.php");
        $this->_CI->load->library("src/Response.php");

        // Load database
        $this->pdo = $this->_CI->load->database('pdo', true)->conn_id;
    }
    
    /*
     * This will make sure the request id is valid
     */
    public function checkId($request_id, $unique_id)
    {
        if(!empty($request_id) && !empty($unique_id))
        {
            // Just do a simple check but also make sure this user exist
            $check = $this->pdo->prepare("SELECT unique_string_id FROM users WHERE unique_string_id=:unique_id");
            $check->execute(array(
                ':unique_id' => $unique_id
            ));

            if($check->rowCount() == 1)
            {
                // Now check to see if the request id exist alongside this user salt
                $lastcheck = $this->pdo->prepare("SELECT * FROM forgotpassword WHERE user_salt_id=:salt_id AND request_id=:request_id");
                $lastcheck->execute(array(
                    ':salt_id' => $unique_id,
                    ':request_id' => $request_id
                ));

                if($lastcheck->rowCount() == 1)
                {
                    return true;
                }else
                {
                    return false;
                }
            }else
            {
                return false;
            }
        }
    }

    /*
     * Check to see if this user has made a password recovery request prior to this one
     */
    private function check()
    {
        if(!empty($this->_type_request))
        {
            // Now see which one to use (username or email)
            switch($this->_type_request)
            {
                case 'username':
                    if(!empty($this->_username)){
                        // Now check using the username but get the user salt from database first
                        $query = $this->pdo->prepare("SELECT unique_string_id FROM users WHERE user_name=:username");
                        $query->execute(array(
                            ':username' => $this->_username
                        ));

                        if($query->rowCount() == 1)
                        {
                            $fetch = $query->fetch(PDO::FETCH_ASSOC);

                            // Now check to see if this person has made a request already
                            $check = $this->pdo->prepare("SELECT id FROM forgotpassword WHERE user_salt_id=:salt_id");
                            $check->execute(array(
                                ':salt_id' => $fetch['unique_string_id']
                            ));

                            if($check->rowCount() == 0)
                            {
                                $this->_response['code'] = 1;
                                return $this->_response;
                            }else{
                                $this->_response['code'] = 0;
                                $this->_response['string'] = "You've already asked for your password to be reset!";

                                return $this->_response;
                            }
                        }else
                        {
                            $this->_response['code'] = 0;
                            $this->_response['string'] = "There is no account with this username!";

                            return $this->_response;
                        }
                    }
                    break;
                case 'email':
                    if(!empty($this->_email)){
                        // Now check using the username but get the user salt from database first
                        $query = $this->pdo->prepare("SELECT unique_string_id FROM users WHERE email=:email");
                        $query->execute(array(
                            ':email' => $this->_email
                        ));

                        if($query->rowCount() == 1)
                        {
                            $fetch = $query->fetch(PDO::FETCH_ASSOC);

                            // Now check to see if this person has made a request already
                            $check = $this->pdo->prepare("SELECT id FROM forgotpassword WHERE user_salt_id=:salt_id");
                            $check->execute(array(
                                ':salt_id' => $fetch['unique_string_id']
                            ));

                            if($check->rowCount() == 0)
                            {
                                $this->_response['code'] = 1;
                                return $this->_response;
                            }else{
                                $this->_response['code'] = 0;
                                $this->_response['string'] = "You've already asked for your password to be reset!";

                                return $this->_response;
                            }
                        }else
                        {
                            $this->_response['code'] = 0;
                            $this->_response['string'] = "There is no account with this email!";

                            return $this->_response;
                        }
                    }
                    break;
            }
        }
    }

    /*
     * Make request using username
     */
    private function makeRequestUsingUsername()
    {
        if(!empty($this->_username))
        {
            // Now make sure this person exists in our database
            $query = $this->pdo->prepare("SELECT * FROM users WHERE user_name=:username");
            $query->execute(array(
                ':username' => $this->_username
            ));

            if($query->rowCount() == 1)
            {
                // Now construct the request by first making the request id and passing in the unique_salt_id
                $fetch = $query->fetch(PDO::FETCH_ASSOC);

                // Now use the users salt id to make a new request
                $this->_request_id = md5($fetch['unique_id_string']) . $this->_CI->encryption->randomHash();

                // Also since this is the username we need to send a email to the user. Now fetch the email and use it
                $this->_email = $fetch['email'];

                // Now construct the request and send everything
                $this->constructRequest($fetch['unique_id_string']);
                return false;
            }else{
                echo $this->_CI->response->make("There is no account with this username!", 'JSON', 0);
                return false;
            }
        }
    }

    /*
     * Make request using emails
     */
    private function makeRequestUsingEmail()
    {
        if(!empty($this->_email))
        {
            // Now make sure this person exists in our database
            $query = $this->pdo->prepare("SELECT * FROM users WHERE email=:email");
            $query->execute(array(
                ':email' => $this->_email
            ));

            if($query->rowCount() == 1)
            {
                // Now construct the request by first making the request id and passing in the unique_salt_id
                $fetch = $query->fetch(PDO::FETCH_ASSOC);
                // Now use the users salt id to make a new request
//                $this->_request_id = md5($fetch['unique_string_id']) . $this->_CI->encryption->randomHash();

                $this->_request_id = md5($fetch['unique_string_id']) . md5(uniqid(rand(), true));

                // Also since this is the username we need to send a email to the user. Now fetch the email and use it
                $this->_email = $fetch['email'];

                // Now construct the request and send everything
                $this->constructRequest($fetch['unique_string_id']);
                return false;
            }else{
                echo $this->_CI->response->make("There is no account with this email!", 'JSON', 0);
                return false;
            }
        }
    }

    /*
     * This will contruct the actual request and enter it into the database and send the email. Final step
     */
    function constructRequest($unique_id)
    {
        if($unique_id != "")
        {

            // Now lets make sure we have a email
            if(!empty($this->_email))
            {

                // Now insert the data
                $query = $this->pdo->prepare("INSERT INTO forgotpassword VALUES('', :unique_id, :request_id, now())");
                if($query->execute(array(':unique_id' => $unique_id, ':request_id' => $this->_request_id)))
                {
                    // Now lets just send the email and be done
                    $message = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
                    <html xmlns=\"http://www.w3.org/1999/xhtml\">
                     <head>
                      <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
                      <title>Demystifying Email Design</title>
                      <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/>
                       <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900' rel='stylesheet' type='text/css'>
                    </head>
                    <body style=\"margin: 0; padding: 0;\">
                      <style>
                        *{
                          font-family: 'Roboto';
                        }
                        
                        .tableHolder{
                          
                        }
                        
                        .tableHolder tr td{
                          padding: 20px;
                        }
                      </style>
                     <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
                      <tr>
                       <td>
                        <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\" class='tableHolder' style='border-right: 3px solid #292929;border-left: 3px solid #292929'>
                     <tr class='tableHolder'>
                      <td bgcolor='#292929' >
                        <h3 style='text-align: center;font-weight: 300;font-size: 2.0em;color: #fff;'>Welcome to Sitelyft</h3>
                      </td>
                     </tr>
                     <tr>
                      <td>
                        <h3 style='margin-top: 0px;padding-top: 0px;'>Hey!</h3>
                        <p style='color: #777;'>Looks like you've forgotten your password! Dont worry we are here to help you, just press the button below to change your password.</p>
                        <a href='".site_url()."/forgot_password/change_pass/" . $this->_request_id . "/".$unique_id."' style='display: inline-block;
                        margin-bottom: 0;
                        font-weight: normal;
                        text-align: center;
                        vertical-align: middle;
                        touch-action: manipulation;
                        cursor: pointer;
                        background-image: none;outline: none;
                        padding: 10px 15px;
                        font-size: 15px;
                        line-height: 1.4;
                        border: none;
                        border-radius: 4px;
                        -webkit-transition: border 0.25s linear,color 0.25s linear,background-color 0.25s linear;
                        transition: border 0.25s linear,color 0.25s linear,background-color 0.25s linear;
                        -webkit-font-smoothing: subpixel-antialiased;    box-shadow: inset 0 -2px 0 rgba(0,0,0,0.15);    color: #fff;
                        background-color: #1abc9c;text-decoration: none;'>Reset Password!</a>
                      </td>
                     </tr>
                     <tr bgcolor='#292929'>
                      <td>
                        <h3 style='text-align: center;font-weight: 300;font-size: 1.0em;color: #fff'>Trackasins &copy; 2019</h3>
                      </td>
                     </tr>
                    </table>
                       </td>
                      </tr>
                     </table>
                    </body>
                    </html>";

                    $mg = Mailgun::create('key-f14cf94304da5471b926ec3e4487773f');
                    $domain = "trackasins.com";
                    $mg->messages()->send($domain, [
                        'from'    => 'notifications@trackasins.com',
                        'to'      => $this->_email,
                        'subject' => 'Reset Password',
                        'html'    => $message
                    ]);
                    echo $this->_CI->response->make("Perfect we have sent you an email to reset your password!", 'JSON', 1);
                    return false;

//                    $mgClient = new Mailgun('key-f14cf94304da5471b926ec3e4487773f');
//                    $domain = "trackasins.com";
//                    $result = $mgClient->sendMessage($domain, array(
//                        "from" => "forestfuture89@gmail.com",
//                        "to" => $this->_email,
//                        "subject" => "Reset Password",
//                        "text" => $message
//                    ));
//                    echo "112312312";
//                    print_r($result);
//                    require 'mailgun-php/vendor/autoload.php';
//                    use Mailgun\Mailgun;
//
//                    $mgClient = new Mailgun('key-f14cf94304da5471b926ec3e4487773f');
//                    $domain = "trackasins.com";
//
//                    $result = $mgClient->sendMessage("$domain",
//                        array(            'from'    => '<postmaster@trackasins.com>',
//                            'to'      => $this->_email,
//                            'subject' => 'Trackasins ',
//                            'html'    => $message
//                        ));

//                    $this->_CI->email->from('passwordrecovery@trackasins.com', 'Trackasins');
//                    $this->_CI->email->to($this->_email);
//
//                    $this->_CI->email->subject('Reset Password');
//                    $this->_CI->email->message($message);
//
//                    if ($this->_CI->email->send()){
//                        echo $this->_CI->response->make("Perfect we have sent you an email to reset your password!", 'JSON', 1);
//                        return false;
//                    }else{
//                        echo $this->_CI->response->make("Something went wrong with emails", 'JSON', 0);
//                        return false;
//                    }
                }else
                {
                    echo $this->_CI->response->make("OOPS! An error has occurred", 'JSON', 0);
                    return false;
                }
            }
        }
    }

    /*
     * This will start the entire process of making the request
     */
    public function makePasswordRequest($string)
    {
        if(!empty($string))
        {
            // Now lets see if this is a username or email
            $this->_string = $this->_CI->validation->santitize($string);

            // Now render what kinda string
            if($this->_CI->validation->isValidEmail($string))
            {
                // Means its a email
                $this->_type_request = "email";
                $this->_email = $this->_string;
                // Now check some stuff
                $check = $this->check();
                if($check['code'] == 1) {
                    // Now initiate the request by using the username
                    $this->makeRequestUsingEmail();
                }else{
                    echo json_encode($this->_response);
                    return false;
                }
                return false;
            }else{
                // Means its a username
                $this->_type_request = "username";
                $this->_username = $this->_string;

                // Now check some stuff
                $check = $this->check();

                if($check['code'] == 1) {
                    // Now initiate the request by using the username
                    $this->makeRequestUsingUsername();
                }else{
                    echo json_encode($this->_response);
                    return false;
                }
                return false;
            }
        }
    }
    
    public function changePass($request_id, $unique_id, $password, $confirm_password)
    {
        if(!empty($request_id) && !empty($unique_id) && !empty($password) && !empty($confirm_password))
        {
            // Make the private vars!
            $this->_request_id = $this->_CI->validation->santitize($request_id);
            $this->_unique_id = $this->_CI->validation->santitize($unique_id);
            $this->_password = $this->_CI->validation->santitize($password);
            $this->_confirm_password = $this->_CI->validation->santitize($confirm_password);

            // Now we need to compare these passwords
            if($this->_password == $this->_confirm_password && $this->_confirm_password == $this->_password)
            {
                // now lets just encrypt the passwords and move on to updating the user
                $this->_password = md5($this->_password);
                $this->_confirm_password = md5($this->_confirm_password);

                // one last check
                if($this->_password != "" && $this->_confirm_password != "")
                {
                    // Now update the stuff
                    $query = $this->pdo->prepare("UPDATE users SET password=:new_pass WHERE unique_string_id=:unique_id");
                    if($query->execute(array(':new_pass' => $this->_confirm_password, ':unique_id' => $this->_unique_id)))
                    {
                        $check = $this->pdo->prepare("DELETE FROM forgotpassword WHERE user_salt_id=:unique_id");
                        $check->execute(array(
                            ':unique_id' => $unique_id
                        ));
                        echo $this->_CI->response->make("Your password has been changed! Now log in.", 'JSON', 1);
                        return false;
                    }else{
                        echo $this->_CI->response->make("Error has occurred!", 'JSON', 0);
                        return false;
                    }
                }else{
                    echo $this->_CI->response->make("Error has occurred!", 'JSON', 0);
                    return false;
                }
            }else{
                echo $this->_CI->response->make("These passwords dont match! They must match.", 'JSON', 0);
                return false;
            }
        }else{
            echo $this->_CI->response->make("Please enter both passwords!", 'JSON', 0);
            return false;
        }
    }
}