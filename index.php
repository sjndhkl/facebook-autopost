<?php
/*
Plugin Name: Facebook Auto Poster
Author: Sujan Dhakal
Plugin URL: http://sujandhakal.com.np/
*/
//type can be profile

include_once 'autoload.php';
$facebook_account = array('access_token'=>'CAAGXKDzYop4BAG4S7qAmi74dQ1ao0KmISvdsnQCSTgQGrQqiHTnSXhqZBR981ZACZC3sOZAbygIKUinoe0mjWsKVGsmwaYuVZBpPehRrpNwYiu9PRiKZBrnjHMgZBZBBZAGfKQNPx8ZCihMVzaghspHr3ZATjIbng8wSRoXAACldZAHvs7WutZB5ZACjH5',
						  'app_id'=>'447674052027038',
	                      'app_secret'=>'550232464d78c3673decaacc545b4640');

$default_settings = array('post_types'=>array('post','page','any_type'),
						  'things_to_post'=>array('title','url','excerpt'),
						  'shorten_url'=>false);

$fb_tokens = $_GET;

if(isset($fb_tokens['code'])){
	$code = $fb_tokens['code'];
	$curlObj = new MicroblogPoster_Curl();
	$url_for_getting_token = "https://graph.facebook.com/oauth/access_token?client_id=".$facebook_account['app_id']."&redirect_uri=http://wordpress.dev:88/content/plugins/facebook-autopost/&client_secret=".$facebook_account['app_secret']."&code=".$code;
	//print $url_for_getting_token;
	$result = $curlObj->fetch_url($url_for_getting_token);
	preg_match_all('/access_token=(.*)&/', $result,$segments);
	print "Access Token : <br/><textarea rows=\"10\" cols=\"100\">".$segments[1][0]."</textarea>";


}
else{


$url_to_authorize_app ="https://www.facebook.com/dialog/oauth?client_id=".$facebook_account['app_id']."&redirect_uri=".plugins_url('facebook-autopost')."/&scope=manage_pages,publish_stream";

//print '<a href="'.$url_to_authorize_app.'">Authorize</a>';
//print $url_to_request_code;

//$access_token = "AQAykLWekb0A8exHSms3HIBDLTMpGz3jajj9ZYKrUzXLUZYe2W67fHXGcYxr1BBxmvBjgPgfFRMYxgSW-NlAhfLTeFOyeEQX8i-1pk55HRuLTOJnqtAnRzTpvzzXozi41I3DKW4gIlMbI1LV4nGmWtMbY21beOLHxbS2LViHufz3DaO4WgSVX64Lh91ZtoxiYfBQ4Jr6RGxiJ2mAaphtEf-U_k0t3U350OAa2dbLHTXub9xZnEn6Sc9u1bwDbzmvqNQoZISZ9ElxYRLpBgP64gmY65gNAF-44MQYRKVGzYrufdxYOKYpQnwf1Qd7ocdRkOg";
//access_token=CAAGXKDzYop4BAHVetJ0c3D0TGlZCCi9Apqgm3iioa3BFPEC9fOfVr8HquhwCSOEELcfyqe0QJQKAj9xv8Wzm8GUGZAAZCIzZCkqP050lFYprhuXejdaHlTNEuFL2LZCRk6wLB9KljnKujZAzyxkUkWVzDQuFdUw0gxKzXROGIYcNaR5XvHAFvQ&expires=5183807
/*
{
   "data": [
      {
         "category": "Attractions/things to do",
         "name": "Mulpani",
         "access_token": "CAAGXKDzYop4BAGOwTltx7Y4ayhS6TGhYtC5ExDcwdf17LLH6dsaSKRxyV2srKkgd0FAF9lZBhzbDuo0IZAOY4E1Xk3ft4I1JiJTDSQYwx5eA1ZBHtmoiit8nz3L6Vfipr2tEZAdxsDToAKMcFgTWJj1EwTBWjNbnlFvIemrGKZCUkcZCYB6429",
         "perms": [
            "ADMINISTER",
            "EDIT_PROFILE",
            "CREATE_CONTENT",
            "MODERATE_CONTENT",
            "CREATE_ADS",
            "BASIC_ADMIN"
         ],
         "id": "10150114912100501"
      }
   ],
   "paging": {
      "next": "https://graph.facebook.com/767888675/accounts?access_token=CAAGXKDzYop4BAHVetJ0c3D0TGlZCCi9Apqgm3iioa3BFPEC9fOfVr8HquhwCSOEELcfyqe0QJQKAj9xv8Wzm8GUGZAAZCIzZCkqP050lFYprhuXejdaHlTNEuFL2LZCRk6wLB9KljnKujZAzyxkUkWVzDQuFdUw0gxKzXROGIYcNaR5XvHAFvQ&limit=5000&offset=5000&__after_id=10150114912100501"
   }
}

*/
//hook in to the before post save and try to post to the facebook profile/ Page;
$fbPoster = new Core_FacebookPoster($facebook_account);

add_action('publish_post',function($id){
	global $fbPoster;
	$post = get_post($id);
	$data = array( "message" => mb_substr($post->post_content, 0,100)."...",
		  "link" => get_permalink($id),
//		  "picture" => "http://i.imgur.com/lHkOsiH.png",
		  //"name" => $post->post_title,
		  "caption" => $post->post_title,
		  //"description" => "wordpress.dev"
		  );

	$fbPoster->postToFacebook($data);
});

}
