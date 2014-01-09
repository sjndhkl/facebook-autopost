<?php
class Core_FacebookPoster{

	private $facebook;
	private $config;


	function __construct($config){
		$this->config = $config;
		$this->facebook = new Facebook(array('appId'=>$this->config['app_id'],
									   'secret'=>$this->config['app_secret'])
								);
	}

	function postToFacebook($params){
		$params = array_merge(array(
		  "access_token" => $this->config['access_token'], // see: https://developers.facebook.com/docs/facebook-login/access-tokens/
		),$params);
		try {
		  $fbId ='me';
		  if(isset($this->config['profile_id'])){
		  	$fbId = $this->config['profile_id'];
		  }
		  $ret = $this->facebook->api('/'.$fbId.'/feed', 'POST', $params);
		} catch(Exception $e) {
		}
	}

	
}