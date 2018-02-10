<?php


//Signature Base String
//<HTTP Method>&<Request URL>&<Normalized Parameters>
$base = rawurlencode("GET")."&";
$base .= "http%3A%2F%2Fplatform.fatsecret.com%2Frest%2Fserver.api&";

//sort params by abc....necessary to build a correct unique signature
$params = "format=json&";
$params .= "max_results=50&";
$params .= "method=foods.search&";
$params .= "oauth_consumer_key=dffd341a5f584836bd7623840018196d&"; // ur consumer key
$params .= "oauth_nonce=123&";
$params .= "oauth_signature_method=HMAC-SHA1&";
$params .= "oauth_timestamp=".time()."&";
$params .= "oauth_version=1.0&";
$params .= "page_number=0&";
$params .= "search_expression=".urlencode($_GET['pasVar']);
$params2 = rawurlencode($params);
$base .= $params2;

//encrypt it!
$sig= base64_encode(hash_hmac('sha1', $base, "9d5ffa7f812044c38d494c46d97aa5b4&", true)); // replace xxx with Consumer Secret


//now get the search results and write them down
$url = "http://platform.fatsecret.com/rest/server.api?".$params."&oauth_signature=".rawurlencode($sig);

$food_feed = file_get_contents($url);
$food_feed = json_decode($food_feed);
echo "<pre>";
   print_r($food_feed);
echo "</pre>";

?>