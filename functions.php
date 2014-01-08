<?php

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