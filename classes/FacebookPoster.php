<?php
class Core_FacebookPoster{

	private $facebook;
	private $config;


	function __construct($config){
		$this->config = $config;
	}

	function postToFacebook($params){

		$facebook = new Facebook(array('appId'=>$this->config['app_id'],
									   'secret'=>$this->config['app_secret'])
								);
		$params = array_merge(array(
		  "access_token" => $this->config['access_token'], // see: https://developers.facebook.com/docs/facebook-login/access-tokens/
		),$params);
		try {
		  $ret = $facebook->api('/dhakal.sujan/feed', 'POST', $params);
		} catch(Exception $e) {
		}
	}


	
}