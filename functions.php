<?php

function get_fbsettings_from_session(){
  if(isset($_SESSION['wpsujan']['fb_settings'])){
    $fb_settings = $_SESSION['wpsujan']['fb_settings']; 
  }else{
    $fb_settings = false;
  }
  if(!is_array($fb_settings)){
    header('Location: /');
    exit;
  }
  else{
	  return $fb_settings;
  }
}

function get_facebook_access_token($fb_settings){
	$fb_tokens = $_GET;
	$token = '';
	if(isset($fb_tokens['code'])){ 
		$curlObj = new MicroblogPoster_Curl();
	    $url_for_getting_token = "https://graph.facebook.com/oauth/access_token?client_id=".$fb_settings['app_id']."&redirect_uri=".$fb_settings['plugin_url']."/&client_secret=".$fb_settings['app_secret']."&code=".$fb_tokens['code'];
	    $result = $curlObj->fetch_url($url_for_getting_token);
	    preg_match_all('/access_token=(.*)&/', $result,$segments);
	    if(isset($segments[1][0]) && !empty($segments[1][0]) )
	    { 
	      $token = $segments[1][0];
	      $_SESSION['wpsujan']['token'] = $token;
	    }
	}
	return $token;

}

function get_settings_for_fbautoposter(){
	$defaults = array('wpsujan_facebook_appid'=>'',
				  'wpsujan_facebook_appsecret'=>'',
				  'wpsujan_facebook_appaccesstoken'=>'',
				  'wpsujan_facebook_username'=>'');
	$settings_from_wp = try_update_token_from_session(get_option('wpsujan_fbap_options'));

	if(is_array($settings_from_wp)){
		$settings_from_wp = array_merge($defaults,$settings_from_wp);
	}else{
		$settings_from_wp = $defaults;
	}
	return $settings_from_wp;
}

function try_update_token_from_session($settings){
	$settings_from_wp = $settings;
	if(isset($_SESSION['wpsujan']['token'])){
		$new_options = array_merge( $settings, 
									array('wpsujan_facebook_appaccesstoken'=>$_SESSION['wpsujan']['token']) );
		update_option('wpsujan_fbap_options',$new_options);
		$settings_from_wp = $new_options;
		unset($_SESSION['wpsujan']);
	}
	return $settings_from_wp;
}