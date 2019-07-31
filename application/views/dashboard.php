<?php
date_default_timezone_set('America/New_York');
    require_once("simple_html_dom.php");

ini_set('max_execution_time', 9999999);
ini_set('memory_limit', '9999M');
error_reporting(0);

$chkdata = 0;

function curl_get_file_contents($URL)
{
    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_URL, $URL);
    $contents = curl_exec($c);
    curl_close($c);

    if ($contents) return $contents;
    else return FALSE;
}

// NEW FUNCTION USING AMAZON API.
function readItem($asin)
{

    // Your AWS Access Key ID, as taken from the AWS Your Account page
    $aws_access_key_id = "AKIAIXQ4QTMGDJGVXUCA";

    // Your AWS Secret Key corresponding to the above ID, as taken from the AWS Your Account page
    $aws_secret_key = "tkmLW+C35Nyr6L1+kJmBtFgzQNxTDgG6muNY+Y1o";

    // The region you are interested in
    $endpoint = "webservices.amazon.com";

    $uri = "/onca/xml";

    $params = array(
        "Service" => "AWSECommerceService",
        "Operation" => "ItemLookup",
        "AWSAccessKeyId" => "AKIAIXQ4QTMGDJGVXUCA",
        "AssociateTag" => "baus019-20",
        "ItemId" => $asin,
        "IdType" => "ASIN",
        "ResponseGroup" => "OfferFull",
        "Condition" => "New"
    );

    //echo $params;exit();
    // Set current timestamp if not set
    if (!isset($params["Timestamp"])) {
        $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
    }

    // Sort the parameters by key
    ksort($params);

    $pairs = array();

    foreach ($params as $key => $value) {
        array_push($pairs, rawurlencode($key) . "=" . rawurlencode($value));
    }

    // Generate the canonical query
    $canonical_query_string = join("&", $pairs);

    // Generate the string to be signed
    $string_to_sign = "GET\n" . $endpoint . "\n" . $uri . "\n" . $canonical_query_string;

    // Generate the signature required by the Product Advertising API
    $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $aws_secret_key, true));

    // Generate the signed URL
    $request_url = 'http://' . $endpoint . $uri . '?' . $canonical_query_string . '&Signature=' . rawurlencode($signature);

    // echo "Signed URL: \"".$request_url."\"";
    //echo$request_url;exit;
    $xmlString = curl_get_file_contents($request_url);
    $xml = simplexml_load_string($xmlString);

    $available = $xml->Items->Item->Offers->Offer->Merchant->Name;
    //echo $available;exit;
    return $xml;
}

/*if(isset($_POST['submit']))
{
    echo 'hii';
    exit;
}
*/


function storeAjax($brand, $page)
{
    $http_head = array("Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
        "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3",
        "Accept-Language:en-US,en;q=0.8",
        "Connection:keep-alive");
    //$brand='a';
    $fields = array('marketplaceID' => 'ATVPDKIKX0DER',
        'seller' => 'A1PFQKIUGA07X8',
        'productSearchRequestData' => '{"marketplace":"ATVPDKIKX0DER", "seller":"A1PFQKIUGA07X8","url":"/sp/ajax/products", "pageSize":20,"lowPrice":"100","searchKeyword":"' . $brand . '","extraRestrictions":{},"pageNumber":"' . $page . '"}');
    $fields_string = '';
    foreach ($fields as $key => $value) {
       $fields_string .= $key . '=' . $value . '&';

    }
    rtrim($fields_string, '&');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.amazon.com/sp/ajax/products');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, FALSE);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_head);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function getCron()
{
    $http_head = array("Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
        "Accept-Language:en-US,en;q=0.8",
        "Connection:keep-alive",
        "Upgrade-Insecure-Requests:1",
        "User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://ezon.org/pr/rms/cron.php'); // Target URL
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, FALSE);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_head);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

if (!empty($_FILES["file_upload"]["name"])) {
    $moving_file_name = 'uploads/' . $_FILES["file_upload"]["name"];
    if (move_uploaded_file($_FILES["file_upload"]["tmp_name"], 'uploads/' . $_FILES["file_upload"]["name"])) {
        $row = 1;
        if (($handle = fopen($moving_file_name, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $old_ch = mysqli_fetch_array(mysqli_query("select * from amaz_aug_queue 
                    where asin = '" . mysqli_real_escape_string(trim($data[0])) . "' limit 1"));

                if (empty($old_ch)) {

                    $qry = "INSERT INTO amaz_aug_queue SET asin = '" . mysqli_real_escape_string(trim($data[0])) . "'";
                    mysqli_query($qry);
                }

            }
            fclose($handle);
            echo '<script>alert("Scrap will run in background.it will display here once scraping done.")</script>';
            getCron();
        }
    }
} else if (!empty($_POST['asin'])) {
    $chkdata = 1;
    $asin = $_POST["asin"];
    unset($_POST["asin"]);
    $main_url = "https://www.amazon.com/gp/offer-listing/" . $asin . "/ref=dp_olp_new?ie=UTF8&condition=new";
    $check_exist = mysqli_fetch_array(mysqli_query("select * from amaz_aug where asin='" . mysqli_real_escape_string($asin) . "'"));
    if (empty($check_exist)) {
        $amznotseller = get_amazon_not_seller($asin);
        $html = getPage($main_url);
        $html = str_get_html($html);
        $results = storeAjax($asin, 1);
        $jd = json_decode($results);

        if (!empty($jd->products)) {
            $sellerstock = 1;
        } else {
            $sellerstock = 0;
        }

        if (!empty($html)) {
            foreach ($html->find("div[id=olpOfferList] div[class=olpOffer]") as $elements) {
    
                $image = $html->find("div[id=olpProductImage] img", 0)->src;
                $title_name = $html->find("h1[class=a-size-large a-spacing-none]", 0)->plaintext;
                $title_name = trim($title_name);
                $rating = $html->find("i[class=a-icon-star]", 0)->plaintext;
                $reviews = $html->find("span[class=a-size-small]", 0)->plaintext;
                $seller_url = @$elements->find("div[class=olpSellerColumn] a", 0)->href;
                $ex_sell = explode("seller=", $seller_url);
                $seller_ids = trim(@$ex_sell[1]);
                $reviews = trim($reviews);

                $title_link = @$elements->find("h3[class=olpSellerName] a", 0)->href;
                $seller_link = 'http://www.amazon.com' . $title_link;
                $seller_name = @$elements->find("h3[class=olpSellerName] a", 0)->plaintext;
                if (empty($seller_name)) {
                    $seller_name = @$elements->find("h3[class=olpSellerName] img", 0)->alt;
                }

                $inStock = "0";

                $stock_url = @$elements->find("h3[class=olpSellerName] img", 0)->alt;

                if ($stock_url == "Amazon.com") {
                    $inStock = "1";
                }

                $amount = $elements->find("span[class=olpOfferPrice]", 0)->plaintext;
                $price = filter_var($amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                $ship = $elements->find("span[class=a-color-secondary]", 0)->plaintext;
                $shipp = filter_var($ship, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); //$ships=trim($ship); //$shipp=str_replace('                      shipping','',$ships); //$shipping=str_replace('+ ','',$shipp);
                $shipping = str_replace('+', '', $shipp);
            }

        }


    }
}




?>


<!DOCTYPE html>
<html>

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 55px;
        height: 25px;
    }

    .switch input {
        display: none;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 2px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #b65f2b;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(32px);
        -ms-transform: translateX(32px);
        transform: translateX(32px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    th {
        background: white;;
    }
</style>
<body>
<script type="text/javascript">

    </script>
<style>
    td:hover a, td:hover span {
        color: #d27842 !important;
        cursor: pointer;
    }
</style>

<div class="headline-site-color dashboardHeadline" >
    <div class="innerholder container">
        <div class="profile_Pic_left">
            <?php $user_id = ($this->session->userdata('user_id'));
            $result = $this->common_model->getDataSingleRow('users', array('id' => $user_id)); ?>
<!--            <form  method="POST" enctype="multipart/form-data">-->
                <input type="file" id="profilePicture"/>
                <img src="<?php echo base_url(); ?>assets2/user_data/<?php echo $result->profile_pic; ?>" style="height: 90px;" id="profilePicHold"/>

<!--            </form>-->


        </div>
        <div class="informationTextRight text-center col-lg-12" style="">
            <?php
            {
                ?>
                <h3>
                    Welcome
                    <?php
                    echo ($result->company);
                    ?>!
                </h3>
                <?php
            }
            ?>

        </div>
    </div>
</div>
<div class="container mainContainer" style="position: relative; width: 100%; ">
<!--<div class="container mainContainer" style="position: relative;">-->
    <div class="leftmajor col-lg-12 col-md-12 col-md-12 clearfix">
        <?php
        if(isset($_SESSION['uid'])){
            $user = $this->db->query("SELECT * FROM users WHERE ID='".$_SESSION['uid']."'")->row();
            if(isset($user)){
                if(isset($user->created_at)){
                    $today = date_create(date('Y-m-d'));
                    $created = date_create(substr($user->created_at,0,10));
                    $diff = date_diff($created,$today);
                    $difference_date = $diff->days;
                }
            }
        }
        ?>
        <div class="barOfInfo col-md-12  col-sm-12 col-x-12 clearfix <?php if($difference_date <= 14) {?> col-lg-2 col-lg-offset-1 <?php } else { ?>col-lg-3 <?php }?>">
            <div class="wideBar first clearfix">
                <div class="inner clearfix" style="display: flex; align-items: center;">
                    <div class="textMain col-lg-8  col-md-8 col-sm-6 col-xs-6 text-center verticle-middle">
                        <h3>ASINs currently out of stock by Amazon</h3>
                    </div>
                    <div class="numberMain col-lg-4 col-md-4 col-sm-6 col-xs-6" >
                        <?php
                        $user_id = $this->session->userdata('user_id');
                        $query = $this->db->query("SELECT * FROM `amaz_aug` where amznotseller = '1' AND user_id = '" . $user_id . "' ");
                        {
                            ?>
                            <h3>
                                <?php echo $query->num_rows(); ?>
                            </h3>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="barOfInfo col-md-12 <?php if($difference_date <= 14) {?> col-lg-2  <?php } else { ?>col-lg-3  <?php }?>  col-sm-12 col-x-12 clearfix ">
            <div class="wideBar first clearfix" >
                <div class="inner clearfix" style="display: flex; align-items: center;">
                    <div class="textMain col-lg-8  col-md-8 col-sm-6 col-xs-6 text-center verticle-middle">
                        <h3>Items currently tracking for out of stock notifications</h3>
                    </div>
                    <div class="numberMain col-lg-4 col-md-4 col-sm-6 col-xs-6" >
                        <?php
                        $user_id = $this->session->userdata('user_id');
                        $query = $this->db->query("SELECT * FROM `amaz_aug` where tracking = 1 AND user_id = '" . $user_id . "' ");

                        {
                            ?>
                            <h3 id="stockNotificationDiv"><?php echo $query->num_rows(); ?></h3>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="barOfInfo col-md-12 <?php if($difference_date <= 14) {?> col-lg-2  <?php } else { ?>col-lg-3 display-none <?php }?> col-sm-12 col-x-12 clearfix">
            <div class="wideBar first clearfix">
                <div class="inner clearfix" style="display: flex; align-items: center;">
                    <div class="textMain col-lg-8  col-md-8 col-sm-6 col-xs-6 text-center verticle-middle">
                        <h3>Days left on your trial</h3>
                    </div>
                    <div class="numberMain col-lg-4 col-md-4 col-sm-6 col-xs-6" >
                        <h3>
                           <?php   if($difference_date <= 14) {$remain_date = 14- $difference_date; echo $remain_date;} ?>
                        </h3>

                    </div>
                </div>
            </div>
        </div>
        <div class="barOfInfo col-md-12 <?php if($difference_date <= 14) {?> col-lg-2  <?php } else { ?>col-lg-3 <?php }?> col-sm-12 col-x-12 clearfix">
            <div class="wideBar first clearfix">
                <div class="inner clearfix" style="display: flex; align-items: center;">
                    <div class="textMain col-lg-8  col-md-8 col-sm-6 col-xs-6 text-center verticle-middle">
                        <h3>Items currently tracking for back in stock notifications</h3>
                    </div>
                    <div class="numberMain col-lg-4 col-md-4 col-sm-6 col-xs-6" >
                        <?php
                        $user_id = $this->session->userdata('user_id');
                        $query = $this->db->query("SELECT * FROM `amaz_aug` where stock_noti='true' and user_id = '" . $user_id . "'");
                        {
                            ?>
                            <h3 id="backStockNotificationsDiv">
                                <?php echo $query->num_rows(); ?>
                            </h3>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="barOfInfo col-md-12  <?php if($difference_date <= 14) {?> col-lg-2  <?php } else { ?>col-lg-3 <?php }?>  col-sm-12 col-x-12 clearfix">
            <div class="wideBar first clearfix">
                <div class="inner clearfix" style="display: flex; align-items: center;">
                    <div class="textMain col-lg-8  col-md-8 col-sm-6 col-xs-6 text-center verticle-middle">
                        <h3>Items in your list</h3>
                    </div>
                    <div class="numberMain col-lg-4 col-md-4 col-sm-6 col-xs-6" >
                        <?php
                        $user_id = $this->session->userdata('user_id');
                        $query = $this->db->query("SELECT * FROM `amaz_aug` where user_id = '" . $user_id . "'");
                        {
                            ?>
                            <h3>
                                <?php echo $query->num_rows(); ?>
                            </h3>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>

    </div> 
    <div class="leftmajor col-lg-12 col-md-12 col-sm-12 col-xs-12 clearfix">
        <div class="barOfInfo clearfix col-lg-10" style="float: none !important;margin:0% auto;padding-right: 0px !important;padding-left: 0px !important; display: none;">
            <div class="wideBar first clearfix col-lg-6 col-md-6 col-sm-6 col-xs-6" style="padding-left: 0px;">
                <div class="inner clearfix">
                    <div class="textMain col-lg-10 text-center verticle-middle">
                        <h3>ASINs currently out of stock by Amazon</h3>
                    </div>
                    <div class="numberMain col-lg-2" >
                        <?php
                        $user_id = $this->session->userdata('user_id');
                        $query = $this->db->query("SELECT * FROM `amaz_aug` where amznotseller = '1' AND user_id = '" . $user_id . "' ");
                        {
                            ?>
                            <h3>
                                <?php echo $query->num_rows(); ?>
                            </h3>
                        <?php } ?>

                    </div>
                </div>
            </div>
            <div class="wideBar second clearfix col-lg-6 col-md-6 col-sm-6 col-xs-6" style="padding-right: 0px;">
                <div class='inner clearfix'>
                    <div class="textMain col-lg-10 text-center verticle-middle">
                        <h3>Sales in the past 30 days</h3>
                    </div>
                    <div class="numberMain col-lg-2">
                        <h3>$0</h3>
                    </div>
                </div>
            </div>
            <div>
                <div class="wideBar first clearfix col-lg-6 col-md-6 col-sm-6 col-xs-6"
                     style="padding-left: 0px;margin-top: 20px;">
                    <div class="inner clearfix">
                        <div class="textMain col-lg-10 text-center verticle-middle">
                            <h3 style="line-height: 1.4;">Items currently tracking for out of stock notifications</h3>
                        </div>
                        <div class="numberMain col-lg-2" style="padding: 28px;">
                            <?php
                            $user_id = $this->session->userdata('user_id');
                            $query = $this->db->query("SELECT * FROM `amaz_aug` where tracking = 1
             AND user_id = '" . $user_id . "' ");

                            {
                                ?>
                                <h3><?php echo $query->num_rows(); ?></h3>
                            <?php } ?>

                        </div>
                    </div>
                </div>
                <br/>
                <div class="wideBar second clearfix col-lg-6 col-md-6 col-sm-6 col-xs-6"
                     style="padding-right: 0px;margin-top: 20px;">
                    <div class='inner clearfix'>
                        <div class="textMain col-lg-10 text-center verticle-middle">
                            <h3 style="line-height: 1.4;">Items currently tracking for back in stock notifications</h3>
                        </div>
                        <div class="numberMain col-lg-2" style="padding: 28px;">
                            <?php
                            $user_id = $this->session->userdata('user_id');
                            $query = $this->db->query("SELECT * FROM `amaz_aug` where user_id = '" . $user_id . "'");
                            {
                                ?>
                                <h3>
                                    <?php echo $query->num_rows(); ?>
                                </h3>
                            <?php } ?>

                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="wideBar first clearfix col-lg-6 col-md-6 col-sm-6 col-xs-6"
                     style="padding-left: 0px;margin-top: 20px;">
                    <div class="inner clearfix">
                        <div class="textMain col-lg-10 text-center verticle-middle">
                            <h3>Days left on your trial</h3>
                        </div>
                        <div class="numberMain col-lg-2">
                            <h3>25</h3>
                        </div>
                    </div>
                </div>
                <div class="wideBar second clearfix col-lg-6 col-md-6 col-sm-6 col-xs-6"
                     style="padding-right: 0px;margin-top: 20px;">
                    <div class='inner clearfix'>
                        <div class="textMain col-lg-10 text-center verticle-middle">
                            <h3>Items in your list</h3>
                        </div>
                        <div class="numberMain col-lg-2">
                            <?php
                            $user_id = $this->session->userdata('user_id');
                            $query = $this->db->query("SELECT * FROM `amaz_aug` where user_id = '" . $user_id . "'");
                            {
                                ?>
                                <h3>
                                    <?php echo $query->num_rows(); ?>
                                </h3>
                            <?php } ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="rightSide hidden-lg hidden-md hidden-sm hidden-xs" style="margin-bottom: 15px;padding-right: 0px !important;">
            <div class="longBox trial-left">
                <div class="topNumber">
                    <h3 style="font-size: 1.5em;font-weight: 400;">Summary</h3>
                </div>
            </div>
            <br/>
            <div class="longBox trial-left">
                <div class="topNumber">
                    <h3>25</h3>
                </div>
                <div class="bottomContent">
                    <p class="text-center">Days left on your trial</p>
                </div>
            </div>

            <br/>
            <div class="longBox trial-left">
                <div class="topNumber">
                    <?php
                    $user_id = $this->session->userdata('user_id');
                    $query = $this->db->query("SELECT * FROM `amaz_aug` where tracking = 1 AND user_id = '" . $user_id . "' ");

                    {
                        ?>
                        <h3><?php echo $query->num_rows(); ?></h3>
                    <?php } ?>
                </div>
                <div class="bottomContent">
                    <p class="text-center">Items currently tracking for out of stock notifications</p>
                </div>
            </div>
            <br/>

            <div class="longBox trial-left">
                <div class="topNumber">
                    <?php
                    $user_id = $this->session->userdata('user_id');
                    $query = $this->db->query("SELECT * FROM `amaz_aug` where user_id = '" . $user_id . "'");
                    {
                        ?>
                        <h3>
                            <?php echo $query->num_rows(); ?>
                        </h3>
                    <?php } ?>
                </div>
                <div class="bottomContent">
                    <p class="text-center">Items currently tracking for back in stock notifications</p>
                </div>
            </div>
        </div>
        <div class="topSearchAsins cont col-lg-12" style="float: none !important; padding-right: 0px !important;padding-left: 0px !important;">
            <div class="topBox text-left" style="padding-left: 20px;">
                <h3>What ASIN would you like to track?</h3>
            </div>
            <div class="bottomContent">
                <div class="formTop clearfix">
                    <form action="<?php echo site_url('Dashboard')?>" method="post" enctype="multipart/form-data" id="asins-search-form"> <!--form remove from here-->
                        <div class="inputType col-lg-9 col-md-9 col-sm-8 col-xs-7" style="margin-bottom: 0px;">
                            <input type="text" placeholder="Enter ASIN, URL or drag to upload a bulk file" name="asin"
                                   style="border-top-right-radius: 0px;border-bottom-right-radius: 0px;" id="asinName"/>
                        </div>
                        <input type="submit" name="submit" value="search_value" id="asinsSubmitButton" style="display: none;" >
                    </form>

                    <div class="inputType col-lg-3 col-md-3 col-sm-4 col-xs-5 buttons" style="margin-bottom: 0px;">
                        <button class='btn btn-embossed btn-primary btn-wide' id="asinsBulkActionButton">
                            <i class="fa fa-upload"  aria-hidden="true"></i>
                        </button>
                        <input type='button'  class='btn btn-embossed btn-primary btn-wide' value='Search' id="asinsSearchButton"/>


                        <style>
                            button, input[type="submit"]{
                                outline: none !important;
                            }
                        </style>
                    </div>
                </div>

            </div>
        </div>
        <br/>
        <!--insert form-->
        <?php if ($chkdata == 1) { ?>
            <div class="topSearchAsins cont">
                <div class="topBox text-left">
                    <!-- <h3>Insert form</h3> -->
                </div>

                <div class="bottomContent" id="shows">
                    <div class="formTop clearfix">
                        <!-- <form action="" method="post" enctype="multipart/form-data"> -->
                        <div class="col-lg-12" id="shows">
                            <div class="inputType col-lg-2 image-div" >
                                <img src="<?php if ( isset($image) && ($image != null) ) { echo $image; } else {  $image = "assets2/images/question-mark.png"; echo $image; } ?>" alt="" title="<?php if (isset($image) && $image != "assets2/user_data/question-mark.png") { echo $image; } else { echo 'Unable to fetch image from Amazon'; } ?>" >
                                <input type="hidden" name="img" id="img_1" value="<?php if (isset($image)) { echo $image; } ?>">
                            </div>
                            <div class="inputType col-lg-5 amazon-content" >
                                <h5 class="amazon-title-name">
                                    <?php if (isset($title_name) && $title_name != null) { echo $title_name; } else { $title_name =  "Cannot fetch title from Amazon at this time"; echo $title_name; }?>
                                </h5>
                                <h5 class="amazon-asin">
                                    <?php if (isset($asin)) {
                                        echo $asin;
                                    } ?>
                                </h5>
                                <h5 style="font-weight: bold !important;text-align: center">
                                    <input type="hidden" name="title_name" id="title_name_1"
                                           value="<?php if (isset($title_name)) {
                                               echo $title_name;
                                           } ?>">
                                </h5>
                                <h5 style="font-weight: bold !important;">
                                    <input type="hidden" name="asin" id="asin_1" value="<?php if (isset($asin)) {
                                        echo $asin;
                                    } ?>">
                                </h5>
                                <div class="inputType submit-button-div " >
                                    <!-- <input type='submit' name="submit1"  class='btn btn-embossed btn-primary btn-wide' style="border-top-left-radius: 0px;border-bottom-left-radius: 0px;background: #b65f2b;" value='Submit' /> -->

                                    <button onclick="saveTodatabase()" class='btn btn-embossed btn-primary btn-wide'>
                                        Submit
                                    </button>
                                </div>
                            </div>
                            <!--<div class="inputType col-lg-2 " style="margin-bottom: 0px;float:left;">

                            </div>-->
                            <input type="hidden" name="user_id" id="user_id_1" value="<?php echo $user_id; ?>">
                            <input type="hidden" name="id" id="id_1" value="<?php echo $id; ?>">
                            <input type="hidden" name="amznotseller" id="amznotseller_1"
                                   value="<?php if (isset($amznotseller)) {
                                       echo $amznotseller;
                                   } ?>">
                            <input type="hidden" name="stock_url" id="stock_url_1" value="<?php if (isset($stock_url)) {
                                echo $stock_url;
                            } ?>"><!--stock end-->
                            <input type="hidden" name="sellerstock" id="sellerstock_1"
                                   value="<?php if (isset($sellerstock)) {
                                       echo $sellerstock;
                                   } ?>">
                            <input type="hidden" name="rating" id="rating_1" value="<?php if (isset($rating)) {
                                echo $rating;
                            } ?>">
                            <input type="hidden" name="reviews" id="reviews_1" value="<?php if (isset($reviews)) {
                                echo $reviews;
                            } ?>">
                            <input type="hidden" name="seller_name" id="seller_name_1"
                                   value="<?php if (isset($seller_name)) {
                                       echo $seller_name;
                                   } ?>">
                            <input type="hidden" name="seller_url" id="seller_url_1"
                                   value="<?php if (isset($seller_url)) {
                                       echo $seller_url;
                                   } ?>">
                            <input type="hidden" name="seller_url" id="seller_url_1"
                                   value="<?php if (isset($seller_url)) {
                                       echo $seller_url;
                                   } ?>">
                            <input type="hidden" name="seller_ids" id="seller_ids_1"
                                   value="<?php if (isset($seller_ids)) {
                                       echo $seller_ids;
                                   } ?>">
                            <input type="hidden" name="price" id="price_1" value="<?php if (isset($price)) {
                                echo $price;
                            } ?>">
                            <input type="hidden" name="shipping" id="shipping_1" value="<?php if (isset($shipping)) {
                                echo $shipping;
                            } ?>">
                            <div class="col-lg-5 amazon-item-div" >
                                <h3  class="amazon-correct-item">Is this the
                                    correct item?</h3>
                                <div class="holder">
                                    <div class="selectMod pull-left" style="margin-right: 5px;">
                                        <h3 class="text-center" style="font-size: 1.2em;">Yes</h3>
                                        <div class="c-hold verticle-middle text-center">
                                            <input type="radio" name="ans" value="yes" checked="yes" />
                                            <label for="checkbox1" data-for="checkbox1" class="cb-label"></label>
<!--                                            <input type='checkbox' value='' id='checkboxall1' data-c="yes"/>-->
<!--                                            <label for='checkboxall1' data-for="checkboxall1"-->
<!--                                                   class='cb-label checkboxall1'></label>-->
                                        </div>
                                    </div>
                                    <div class="selectMod pull-right" style="margin-right: 5px;">
                                        <h3 class="text-center" style="font-size: 1.2em;">No</h3>
                                        <div class="c-hold verticle-middle text-center">
                                            <input type="radio" name="ans" value="no"/>
                                            <label for="checkbox1" data-for="checkbox1" class="cb-label"></label>
<!--                                            <input type='checkbox' value='' id='checkboxall1' data-c="no"/>-->
<!--                                            <label for='checkboxall1' data-for="checkboxall1"-->
<!--                                                   class='cb-label checkboxall1'></label>-->
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- </form> -->
                    </div>

                </div>

            </div><br/>
        <?php } ?>
        <!--insert form end here-->
        <!--<div class="topSearchAsins cont">
            <div class="topBox text-left">
                <h3>Recent Activity</h3>
            </div>
            <div class="bottomContent">
                <div class="innerCont">
                    <ul class="activityHolder">
                        <li class="clearfix">
                            <div class="col1 col-lg-10">
                                <div class="contm">
                                    <div class="cont-col1">
                                        <div class="label label-sm label-info">
                                            <i class="fa fa-check"></i>
                                        </div>
                                    </div>
                                    <div class="cont-col2">
                                        <div class="desc">You have 4 pending tasks.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col2 col-lg-2">
                                <div class="date"> Just now </div>
                            </div>
                        </li>
                        <li class="clearfix">
                            <div class="col1 col-lg-10">
                                <div class="contm">
                                    <div class="cont-col1">
                                        <div class="label label-sm label-info" style="background: #F1C40F">
                                            <i class="fa fa-bell-o"></i>
                                        </div>
                                    </div>
                                    <div class="cont-col2">
                                        <div class="desc">2 Asins have went out of stock</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col2 col-lg-2">
                                <div class="date"> Just now </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div><br />-->
        <div class="topSearchAsins cont" style="">
            <div class="topBox text-left">
                <h3 class="item-list">Items List</h3>
<!--                <div class="search-right-corner">-->
<!--                    <label>Search:<input type="search" class="form-control input-sm" placeholder="" aria-controls="DataTables_Table_0" id="bookSearch"></label>-->
<!--                </div>-->
            </div>
            <div class="bottomContent">
                <div class="listHolder">
                    <!-- <form action="" method="post" enctype="multipart/form-data"> -->
                    <div class="table-responsive">
                    <table
                        class="mainTable table table-striped table-bordered table-hover individual-item-report dataTable main-table"
                        id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info" style="width:100%" >
                        <thead>
                        <tr role="row" style='margin-top: 15px;'>
                            <th class="text-center a verticle-middle sorting_disabled" data-orderable="false"
                                rowspan="1" colspan="1" aria-label="Image" style="width: 53px;">
                                <div>Image</div>
                            </th>
                            <th class="text-center a t verticle-middle sorting" tabindex="0"
                                aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Title: activate to sort column ascending" style="width: 700px;">
                                Item Title
                            </th>
                            <th class="text-center a verticle-middle sorting" tabindex="0"
                                aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="ASIN: activate to sort column ascending" style="width: 110px">
                                ASIN
                            </th>
                            <th class="text-center a t-responsive  verticle-middle sorting" tabindex="0"
                                aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Report: activate to sort column ascending" >
                                Out of Stock Tracking
                            </th>
                            <th class="text-center a t-responsive  verticle-middle sorting" tabindex="0"
                                aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Report: activate to sort column ascending" >
                                Back In Stock Tracking
                            </th>
                            <th class="text-center a t-responsive verticle-middle sorting" tabindex="0"
                                aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Report: activate to sort column ascending" >
                                Is Amazon out of stock
                            </th>
                            <th class="text-center a t-responsive verticle-middle sorting" tabindex="0"
                                aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Report: activate to sort column ascending" >
                                Are you in stock?
                            </th>
                            <th class="text-center a t-responsive  verticle-middle sorting" tabindex="0"
                                aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Report: activate to sort column ascending" >
                                Email Notification
                            </th>
                            <th class="text-center a t-responsive  verticle-middle sorting" tabindex="0"
                                aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Report: activate to sort column ascending" >
                                Phone Notification
                            </th>

                            <th class="text-center a verticle-middle sorting menuListOpen dropbox "
                                aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Report: activate to sort column ascending" style="width:100px;">
                                <div class="dropdown-toggle" data-toggle="dropdown">
                                    Bulk Action<br/>
                                    <span class="car" id="bulkActionCar">
                                        <i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </span>
                                </div>
                                <ul class="dropdown-menu drop">
                                    <li><a href="javascript:void(0)" onclick="onSelectAll()">Select All/Deselect All</a></li>
                                    <li><a href="javascript:void(0)" onclick="onChangeTurnOnOff('stock_on')">Turn Out of Stock Tracking On</a></li>
                                    <li><a href="javascript:void(0)" onclick="onChangeTurnOnOff('stock_off')">Turn Out of Stock Tracking Off</a></li>
                                    <li><a href="javascript:void(0)" onclick="onChangeTurnOnOff('back_stock_on')">Turn Back in Stock Tracking On</a></li>
                                    <li><a href="javascript:void(0)" onclick="onChangeTurnOnOff('back_stock_off')">Turn Back in Stock Tracking Off</a></li>
                                    <li><a href="javascript:void(0)" onclick="onChangeTurnOnOff('email_on')">Turn Email Notifications On</a></li>
                                    <li><a href="javascript:void(0)" onclick="onChangeTurnOnOff('email_off')">Turn Email Notifications Off</a></li>
                                    <li><a href="javascript:void(0)" onclick="onChangeTurnOnOff('sms_on')">Turn SMS Notifications On </a></li>
                                    <li><a href="javascript:void(0)" onclick="onChangeTurnOnOff('sms_off')">Turn SMS Notifications Off</a></li>
<!--                                    <li><a href="javascript:void(0)" style="text-align:center" data-toggle="modal" data-target="#deleteAsinsModal">Delete</a></li>-->
                                    <li style="text-align:center;">
                                        <button style=" background: none;border: none;font-weight: 200; padding: 3px 20px;" name="delete" data-toggle="modal" data-target="#deleteAsinsModal">Delete</button>
                                    </li>
                                            <!--                                    <li></li>-->
                                    <!-- <form action="" method="post" enctype="multipart/form-data"> -->
                                    <!-- <button type="submit" class="btn" name="delete">Delete</button> -->
<!--                                    <center>-->
<!--                                        <li>-->
<!--                                            <button style=" background: none;border: none;font-weight: 200;" name="delete" onclick="toggle()">Delete-->
<!--                                            </button>-->
<!--                                        </li>-->
<!--                                    </center>-->
                                    <!-- </form> -->
                                </ul>
                            </th>
                        </tr>
                        </thead>

                        <tbody id="dashboardTbody">
                        <!-- <form action="" method="post" enctype="multipart/form-data"> -->
                        <?php

                        $user_id = $this->session->userdata('user_id');
                        /*$query = $this->db->query("SELECT * FROM amaz_aug WHERE `user_id`='$user_id' group by asin order by status ASC ")->result();*/
                        $query = $this->db->query("SELECT * FROM amaz_aug WHERE `user_id`='$user_id' ORDER BY amznotseller DESC , sellerstock ASC ")->result();
                        /*print_r($query);*/
                        foreach ($query as $query) {

                            ?>
                            <tr role="row" class="odd">
                                <!--start IMAGE-->
                                <td class="text-center vertical-middle star-wrapper" style="position: relative">

                                    <?php if (($query->amznotseller == "1") && ($query->sellerstock == "1")) { ?>
<!--                                        <span style="color:green; font-size:20px" class="product-star"><i class="fa fa-circle" aria-hidden="true"></i></span>-->
                                        <div class="green-right-triangle"></div>
                                    <?php } else if (($query->amznotseller == "1") && ($query->sellerstock == "0")) { ?>
<!--                                        <span style="color:red; font-size:20px" class="product-star"><i class="fa fa-circle" aria-hidden="true"></i></span>-->
                                        <div class="red-right-triangle"></div>
                                    <?php } ?>
                                    <?php if($query->image != ''){ ?>
                                    <a href="<?php echo $query->image; ?>" data-fancybox="images" data-caption="<?php echo $query->title_name; ?>" class="fancybox">
                                        <?php echo "<img src='" . $query->image . "' style='width:60px;'/>" ?>
                                    </a>
                                    <?php } ?>
                                </td>
                                <!--END IMAGE-->
                                <!--start TITLE NAME-->
                                <td class="text-center vertical-middle" title='<?php echo $query->title_name; ?>'>
                                    <a style="" target="_blank"
                                       href="http://amazon.com/dp/<?php echo $query->asin; ?>"><?php echo $query->title_name; ?></a>
                                </td>
                                <!--END TITLE NAME-->
                                <!--start ASIN-->
                                <td class="text-center vertical-middle">
                                    <a style="" target="_blank"
                                       href="http://amazon.com/dp/<?php echo $query->asin; ?>"><?php echo $query->asin; ?></a>
                                </td>
                                <!--END ASIN-->
                                <!--start TRACKING-->
                                <td class="vertical-middle cb text-center">
                                    <?php if ($query->tracking == "1") { ?>

                                        <label class="switch">
                                            <input type="checkbox" data-role="flipswitch"
                                                   onclick="chackUncheck(<?php echo $query->id; ?>)"
                                                   name="switch<?php echo $query->id; ?>"
                                                   id="switch<?php echo $query->id; ?>" value="true" checked>
                                            <div class="slider round"></div>
                                        </label>


                                    <?php } else { ?>

                                        <label class="switch">
                                            <input type="checkbox" data-role="flipswitch"
                                                   onclick="chackUncheck(<?php echo $query->id; ?>)"
                                                   name="switch<?php echo $query->id; ?>"
                                                   id="switch<?php echo $query->id; ?>"
                                                   value="switch<?php echo $query->id; ?>">
                                            <div class="slider round"></div>
                                        </label>

                                    <?php } ?>
                                </td>
                                <!--END TRACKING-->
                                <!--start STOCK NOTIFICATIION-->
                                <td class="vertical-middle cb text-center">
                                    <?php if ($query->stock_noti == "true") { ?>

                                        <label class="switch">
                                            <input type="checkbox" data-role="flipswitch"
                                                   onclick="stockcheck(<?php echo $query->id; ?>)"
                                                   name="switch<?php echo $query->id; ?>"
                                                   id="switchstock<?php echo $query->id; ?>"
                                                   value="switch<?php echo $query->id; ?>" checked>
                                            <div class="slider round"></div>
                                        </label>


                                    <?php } else { ?>

                                        <label class="switch">
                                            <input type="checkbox" data-role="flipswitch"
                                                   onclick="stockcheck(<?php echo $query->id; ?>)"
                                                   name="switch<?php echo $query->id; ?>"
                                                   id="switchstock<?php echo $query->id; ?>"
                                                   value="switch<?php echo $query->id; ?>">
                                            <div class="slider round"></div>
                                        </label>

                                    <?php } ?>
                                </td>
                                <!--END STOCK NOTIFICATIION-->
                                <!--start AMZNOTSELLER-->
                                <?php if (($query->amznotseller == "1")) { ?>
                                    <td class="text-center b red verticle-middle">
                                        <span style="color:green; font-size:25px;">Yes!</span>
                                    </td>
                                <?php }
                                ?>
                                <?php if (($query->amznotseller == "0")) { ?>
                                    <td class="text-center b red verticle-middle">
                                        <span style="color:black; font-size:25px;">No</span>
                                    </td>
                                <?php }
                                ?>
                                <!--END AMZNOTSELLER-->
                                <!--start SELLERSTOCK-->
                                <?php if (($query->sellerstock == "1")) {
                                    if (($query->amznotseller == "1")) { ?>
                                        <td class="text-center b red verticle-middle">
                                            <span style="color:green; font-size:25px;">Yes!</span>
                                        </td>
                                    <?php } else { ?>
                                        <td class="text-center b red verticle-middle">
                                            <span style="color:black; font-size:25px;">Yes</span>
                                        </td>
                                    <?php }
                                } else if (($query->sellerstock == "0")) {
                                    if (($query->amznotseller == "1")) { ?>
                                        <td class="text-center b red verticle-middle">
                                            <span style="color:red; font-size:25px;">No!</span>
                                        </td>
                                    <?php } else { ?>
                                        <td class="text-center b red verticle-middle">
                                            <span style="color:black; font-size:25px;">No</span>
                                        </td>
                                    <?php }
                                } ?>
                                <!--END SELLERSTOCK-->
                                <!--start EMAIL NOTIFICATIION-->
                                <td class="vertical-middle cb text-center">
                                    <?php if ($query->email_noti == "true") { ?>

                                        <label class="switch">
                                            <input type="checkbox" data-role="flipswitch"
                                                   onclick="emailcheck(<?php echo $query->id; ?>)"
                                                   name="switch<?php echo $query->id; ?>"
                                                   id="switchid<?php echo $query->id; ?>"
                                                   value="switchEmail<?php echo $query->id; ?>" checked>
                                            <div class="slider round"></div>
                                        </label>


                                    <?php } else { ?>

                                        <label class="switch">
                                            <input type="checkbox" data-role="flipswitch"
                                                   onclick="emailcheck(<?php echo $query->id; ?>)"
                                                   name="switch<?php echo $query->id; ?>"
                                                   id="switchid<?php echo $query->id; ?>"
                                                   value="switchEmail<?php echo $query->id; ?>">
                                            <div class="slider round"></div>
                                        </label>

                                    <?php } ?>
                                </td>
                                <!--END EMAIL NOTIFICATIION-->
                                <!--start PHONE NOTIFICATIION-->
                                <td class="vertical-middle cb text-center">
                                    <?php if ($query->phone_noti == "true") { ?>

                                        <label class="switch">
                                            <input type="checkbox" data-role="flipswitch"
                                                   onclick="phonecheck(<?php echo $query->id; ?>)"
                                                   name="switch<?php echo $query->id; ?>"
                                                   id="switchphone<?php echo $query->id; ?>"
                                                   value="switch<?php echo $query->id; ?>" checked>
                                            <div class="slider round"></div>
                                        </label>


                                    <?php } else { ?>

                                        <label class="switch">
                                            <input type="checkbox" data-role="flipswitch"
                                                   onclick="phonecheck(<?php echo $query->id; ?>)"
                                                   name="switch<?php echo $query->id; ?>"
                                                   id="switchphone<?php echo $query->id; ?>"
                                                   value="switch<?php echo $query->id; ?>">
                                            <div class="slider round"></div>
                                        </label>

                                    <?php } ?>
                                    <!--END PHONE NOTIFICATIION-->
                                    <!--start BULK ACTION-->
                                <td class="text-center c-hold verticle-middle" id="checkes">
                                    <form action="" method="post" enctype="multipart/form-data">
                                        <input type='checkbox' value="<?php echo $query->id; ?>" name="checkbulk1[]"
                                               class="check"/>
                                        <label for='checkbox1' data-for="checkbox1" class='cb-label'></label>
                                    </form>
                                </td>
                                <!--END BULK ACTION-->
                            </tr>


                        <?php  }?>
                        <!--  </form> -->
                        </tbody>

                    </table>
                    </div>
                    <!-- </form> -->
                    <?php
                    /*$user_id=$this->session->userdata('user_id');
                    $query  = $this->db->query("SELECT * FROM `amaz_aug` where user_id = '".$user_id."' ");
                    $num = $query->num_rows();

                    if(3 <= $num || '3' <= $num)
                    {
                    ?>
                  <div class="loadMoreHolder text-center" style="padding-top: 15px;">
                      <button class="btn btn-wide btn-embossed btn-primary" style='background: #b65f2b'><i class="fa fa-arrow-down" aria-hidden="true"></i></button>
                  </div>
                  <?php }*/

                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="rightSide col-lg-2 hidden-sm hidden-xs" style="display: none;">
        <div class="longBox trial-left">
            <div class="topNumber">
                <h3 style="font-size: 1.5em;font-weight: 400;">Summary</h3>
            </div>
        </div>
        <br/>
        <div class="longBox trial-left">
            <div class="topNumber">
                <h3>25</h3>
            </div>
            <div class="bottomContent">
                <p class="text-center">Days left on your trial</p>
            </div>
        </div>
        <br/>
    </div>
</div>
<br/><br/>

<!-- Delete item  modal start -->
<div class="modal face" id="deleteAsinsModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Delete Asins</h4>
            </div>
            <div class="modal-body">
                <p style="padding-bottom:10px;">Are you sure you want to delete the selected items from your list? </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-embossed btn-success primarycolorbtn" id="deleteAsinsConfirmButton">Yes I am sure</button>
                <button type="button" class="btn btn-default btn-embossed" data-dismiss="modal">No go back</button>
            </div>
        </div>
    </div>
</div>
<!-- <div id="ajax-modal" class="modal" tabindex="-1" role="dialog" data-backdrop="static"></div> -->
<script language="JavaScript">
    function toggle() {

        var ids = "";
        var divid = 'checkes';
        var checks = document.querySelectorAll('#' + divid + ' input[type="checkbox"]');
        console.log(checks);
        for (var i = 0; i < checks.length; i++) {
            var check = checks[i].checked;

            if (check == false) {

            } else {
                ids += checks[i].defaultValue + ",";
            }
        }
        var url_link = '<?php echo base_url(); ?>Dashboard/delete_checkbox/';
        $.ajax({
            url: url_link,
            data: "ids=" + ids,
            method: 'POST',
            success: function (res) {
                console.log(res);

                window.location.reload(true);
                // if(res=="true"){
                //     window.location.reload(true);
                // }

            }
        })


    }


</script>


<script type="text/javascript">

//    $('.mainTable').DataTable({
//        stateSave: true,
//        "pagingType": "full_numbers",
//        responsive:true,
//        stateSaveCallback: function (settings, data) {
//            localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data))
//        },
//        stateLoadCallback: function (settings) {
//            return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance))
//        },
//        "lengthMenu": [
//            [10, 25, 50, 100, 250, 500, 1000, 2000, -1],
//            [10, 25, 50, 100, 250, 500, 1000, 2000, "All"]
//        ],
//        "language": {
//            "lengthMenu": "Show _MENU_ products"
//        }
//    });

</script>
<!--SCRIPT IS USED TO EMAIL PHONE AND STOCK-->
<script>
    /*$(function () {
     $("#example1").DataTable();
     $('#example2').DataTable({
     "paging": false,
     "lengthChange": false,
     "searching": false,
     "ordering": false,
     "info": false,
     "autoWidth": false
     });
     });

     $(document).ready(function() {
     $('#example1').DataTable( {
     "paging":   false,
     "ordering": false,
     "info":     false
     } );
     } );



     function show_user(user_id){
     jQuery.ajax({
     type:'POST',
     url:'Dashboard.php',
     data:'method=1&user_id='+user_id,
     success:function(res){
     var jsonData = JSON.parse(res);
     // alert(res);
     console.log(jsonData);

     document.getElementById('user_id').value =jsonData.show_user.user_id;
     document.getElementById('img').value =jsonData.show_user.img1;
     document.getElementById('title_name').value =jsonData.show_user.title_name1;
     document.getElementById('asin').value =jsonData.show_user.asin1;
     document.getElementById('amznotseller').src =jsonData.show_user.amznotseller1;
     document.getElementById('stock_url').value =jsonData.show_user.stock_url1;
     document.getElementById('sellerstock').value =jsonData.show_user.sellerstock1;
     document.getElementById('rating').value =jsonData.show_user.rating1;
     document.getElementById('reviews').value =jsonData.show_user.reviews1;
     document.getElementById('seller_name').value =jsonData.show_user.seller_name1;
     document.getElementById('seller_url').value =jsonData.show_user.seller_url1;
     document.getElementById('seller_ids').value =jsonData.show_user.seller_ids1;
     document.getElementById('price').value =jsonData.show_user.price1;
     document.getElementById('shipping').value =jsonData.show_user.shipping1;



     }
     });
     }

     */</script>
</body>
</html>

