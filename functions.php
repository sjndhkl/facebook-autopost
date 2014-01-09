<?php

function post_to_facebook($id,$fbPoster){
	$post = get_post($id);
		/** little sanitization **/
	$post->post_content = strip_tags(str_replace('&nbsp;',' ',$post->post_content));
	$post->post_content = trim($post->post_content);
	
	$data = array( "message" => mb_substr($post->post_content, 0,150)."...",
		  "link" => get_permalink($id),
		  "caption" => $post->post_title,
		  "description" => mb_substr($post->post_content, 0,300)."..."
		  );
	$image_url = wp_get_attachment_image_src( get_post_thumbnail_id($id), 'medium');
	if($image_url){
		$data = array_merge($data,array('picture'=>$image_url[0]));
	}
	$fbPoster->postToFacebook($data);
}

function getPagesJson($access_token){
	$url = 'https://graph.facebook.com/me/accounts?access_token='.$access_token;
    $optionsText  = '';
    try{
      $curlObj = new MicroblogPoster_Curl();
      $result = $curlObj->fetch_url($url);
      $result = json_decode($result);
      $pages = $result->data;
     
      foreach ($pages as $page) {
          $optionsText .="<option value='{$page->name}|{$page->id}|{$page->access_token}'>{$page->name} - {$page->category}</option>";
      }
    }catch(Exception $e){

    }
    echo json_encode(array('error'=>false,'optionsText'=>$optionsText) );
}

function set_fbsettings_session($facebook_account){
	$access_token = $facebook_account['access_token'];
	if(empty($access_token)){
		/** Save FB settings to Session **/
		$_SESSION['wpsujan']['fb_settings'] = array_merge($facebook_account,
														  array('redirect_url'=>admin_url().'options-general.php?page=wpsujan-facebook-autoposter-settings',
														  	    'plugin_url'=>plugins_url('facebook-autopost'))
														 );
	}
}

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
	    $result = explode('&', $result);
	    preg_match_all('/access_token=(.*)/', $result[0],$segments);
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
				  'wpsujan_facebook_pagetopost'=>'',
				  'wpsujan_facebook_posttypestoautopost'=>'');
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