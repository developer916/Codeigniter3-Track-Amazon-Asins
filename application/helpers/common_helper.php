<?php
/**
 * Created by IntelliJ IDEA.
 * User: anhnguyen
 * Date: 10/10/16
 * Time: 6:15 PM
 */

require APPPATH . 'libraries/phpQuery/phpQuery.php';
require APPPATH . 'libraries/dompdf/autoload.inc.php';

function debug($value, $label = null)
{
    $label = get_tracelog(debug_backtrace(), $label);
    echo getdebug($value, $label);
    exit();
}

function getdebug($value, $label = null)
{
    $value = htmlentities(print_r($value, true));
    return "<pre>$label$value</pre>";
}

function get_tracelog($trace, $label = null)
{
    $line = $trace[0]['line'];
    $file = is_set($trace[1]['file']);
    $func = $trace[1]['function'];
    $class = is_set($trace[1]['class']);
    $log = "<span style='color:#FF3300'>-- $file - line:$line - $class-$func()</span><br/>";
    if ($label)
        $log .= "<span style='color:#FF99CC'>$label</span> ";
    return $log;
}

function is_set(&$var, $substitute = null)
{
    return isset($var) ? $var : $substitute;
}

function dump($value, $label = null)
{
    $label = get_tracelog(debug_backtrace(), $label);
    $value = htmlentities(var_export($value, true));
    echo "<pre>$label$value</pre>";
}

function get_amazon_not_seller($asin)
{
    $url = "https://www.amazon.com/gp/offer-listing/$asin/ref=dp_olp_new_mbc?ie=UTF8&condition=new";
    $amznotseller = 1;
    do {
        $html = getPage($url);
    } while (empty($html));

    //echo $html;
    if(!empty($html)) {
        phpQuery::newDocument($html);

        foreach (pq('h3.olpSellerName') as $idPresent) {

           $stock_url = @pq($idPresent)->find("img")->eq(0)->attr('alt');
		//echo "<p>Inside Helper ASIN: ".$asin."<br />StockUrl ".$stock_url."</p>"; 
           if($stock_url == 'Amazon.com') {
                $amznotseller = 0;
                break;
            }
        }
    }

    return $amznotseller;
}

function get_seller_stock($asin, $seller = 'A1PFQKIUGA07X8')
{
    $url = "https://www.amazon.com/s/ref=nb_sb_noss?url=me%3D$seller&field-keywords=$asin";
    $sellerStock = 0;
    do {    
        $html = getPage($url);
    } while (empty($html));

    if(!empty($html)) {
        phpQuery::newDocument($html);

        foreach (pq('div#centerMinus') as $idPresent) {

            if(pq($idPresent)->find('li.s-result-item')->length() != 0) $sellerStock = 1;
        }
    }

    return $sellerStock;
}

function getPage($url) {
    $ch = curl_init();
    $apiString = "http://api.scraperapi.com/?key=c3f28aa3667ad3a2d65cf079d741e56f&url=".$url;
    curl_setopt($ch, CURLOPT_URL, $apiString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
