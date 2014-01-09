<?php
/*
Plugin Name: Facebook Auto Poster
Author: Sujan Dhakal
Plugin URL: http://sujandhakal.com.np/
*/
@session_start();
include_once 'autoload.php';

/** FB Autoposter Settings Global **/
$settings_from_wp = get_settings_for_fbautoposter();

$facebook_account = array('access_token'=>$settings_from_wp['wpsujan_facebook_appaccesstoken'],
						  'app_id'=>$settings_from_wp['wpsujan_facebook_appid'],
	                      'app_secret'=>$settings_from_wp['wpsujan_facebook_appsecret']);

$default_settings = array('post_types'=>array('post','page','any_type'),
						  'things_to_post'=>array('title','url','excerpt'),
						  'shorten_url'=>false);

$_SESSION['wpsujan']['fb_settings'] = array_merge($facebook_account,array('redirect_url'=>admin_url().'options-general.php?page=wpsujan-facebook-autoposter-settings','plugin_url'=>plugins_url('facebook-autopost')));


/*** WP Hooks */
$settingsObject = new Core_Settings($settings_from_wp);
add_action('admin_init',function() use ($settingsObject){
	$settingsObject->init();
});
add_action('admin_menu',function() use ($settingsObject){
	$settingsObject->addMenuPage();
});
if(!empty($facebook_account['access_token']) && !empty($facebook_account['app_id']) && !empty($facebook_account['app_secret']) ){
		//hook in to the before post save and try to post to the facebook profile/ Page;
		
		$pageToPost = $settings_from_wp['wpsujan_facebook_pagetopost'];
		if(!empty($pageToPost)){
			$segments = explode('|', $pageToPost);
			$facebook_account['profile_id'] = $segments[1];
			$facebook_account['access_token'] = $segments[2];
		}
		$fbPoster = new Core_FacebookPoster($facebook_account);
		add_action('publish_post',function($id) use ($fbPoster){
			$post = get_post($id);
			$data = array( "message" => mb_substr($post->post_content, 0,150)."...",
				  "link" => get_permalink($id),
		//		  "picture" => "http://i.imgur.com/lHkOsiH.png",
				  //"name" => $post->post_title,
				  "caption" => $post->post_title,
				  "description" => mb_substr($post->post_content, 0,300)."..."
				  );
			$fbPoster->postToFacebook($data);
		});
}else{
	//print "";
	$html ='<div id="message" class="error"><p><strong>Facebook Autoposter</strong> Plugin Temporarily Disabled void of {causes}, Go to <a href="options-general.php?page=wpsujan-facebook-autoposter-settings">Settings Page</a> to authorize the Facebook AutoPoster and get the token.</p></div>';
	$causes[] = 'access token';
	$html = str_replace('{causes}', join(' and ',$causes), $html);
	add_action('admin_notices', function() use ($html){
		echo $html;
	});
}


