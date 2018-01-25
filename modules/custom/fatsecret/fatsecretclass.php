<?php

namespace Drupal\fatsecret;

class Fatsecret{

	public function search($expression, $key, $secret, $page){

		$rand = rand(1000, 10000);

		//Signature Base String
		//<HTTP Method>&<Request URL>&<Normalized Parameters>
		$base = rawurlencode("GET")."&";
		$base .= "http%3A%2F%2Fplatform.fatsecret.com%2Frest%2Fserver.api&";

		//sort params by abc....necessary to build a correct unique signature
		$params = "format=json&";
		$params .= "max_results=50&";
		$params .= "method=foods.search&";
		$params .= "oauth_consumer_key=".$key."&"; // ur consumer key
		$params .= "oauth_nonce=".$rand."&";
		$params .= "oauth_signature_method=HMAC-SHA1&";
		$params .= "oauth_timestamp=".time()."&";
		$params .= "oauth_version=1.0&";
		$params .= "page_number=".$page."&";
		$params .= "search_expression=".rawurlencode($expression);
		$params2 = rawurlencode($params);
		$base .= $params2;

		//encrypt it!
		$sig= base64_encode(hash_hmac('sha1', $base, $secret."&", true)); // replace xxx with Consumer Secret

		//kint($base);die();

		//now get the search results and write them down
		$url = "http://platform.fatsecret.com/rest/server.api?".$params."&oauth_signature=".rawurlencode($sig);

		return file_get_contents($url);
	}

	public function getFood($id, $key, $secret){

		$rand = rand(1000, 10000);
		//Signature Base String
		//<HTTP Method>&<Request URL>&<Normalized Parameters>
		$base = rawurlencode("GET")."&";
		$base .= "http%3A%2F%2Fplatform.fatsecret.com%2Frest%2Fserver.api&";

		//sort params by abc....necessary to build a correct unique signature
		$params  = "food_id=".$id."&";
		$params .= "format=json&";
		$params .= "method=food.get&";
		$params .= "oauth_consumer_key=".$key."&"; // ur consumer key
		$params .= "oauth_nonce=".$rand.$id."&";
		$params .= "oauth_signature_method=HMAC-SHA1&";
		$params .= "oauth_timestamp=".time()."&";
		$params .= "oauth_version=1.0";
		$params2 = rawurlencode($params);
		$base .= $params2;

		//kint($base);die();

		//encrypt it!
		$sig= base64_encode(hash_hmac('sha1', $base, $secret."&", true)); // replace xxx with Consumer Secret

		//now get the search results and write them down
		$url = "http://platform.fatsecret.com/rest/server.api?".$params."&oauth_signature=".rawurlencode($sig);

		return file_get_contents($url);
	}
}