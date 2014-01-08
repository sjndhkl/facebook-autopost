<?php
class Core_Settings{

	private $settings_from_wp;

	function __construct($settings_from_wp){
		$this->settings_from_wp = $settings_from_wp;
	}

	function init(){
		$this->registerSettingsAndFields();
	}

	function addMenuPage(){
		add_options_page('FB Autoposter','FB Autoposter','manage_options','wpsujan-facebook-autoposter-settings',array($this,'displaySettingsPage'));
	}

	function displaySettingsPage(){
?>
	<div class="wrap">
			<?php screen_icon(); ?>
			<script type="text/javascript">
			function updateFields(a,s,t,r){
				var appId = a || '';
				var secretId = s || '';
				var token = t || '';
				var readonly = r || false;
				jQuery('#wpsujan_facebook_appid').val(appId).attr("readonly",readonly);
				jQuery('#wpsujan_facebook_appsecret').val(secretId).attr("readonly",readonly);
				jQuery('#wpsujan_facebook_appaccesstoken').text(token);
			}
			jQuery(function(){
					var appId = '',secretId = '',token = '';
					jQuery('.edit_fbappid').toggle(function(){
						appId = jQuery('#wpsujan_facebook_appid').val();
						secretId = jQuery('#wpsujan_facebook_appsecret').val();
						token  = jQuery('#wpsujan_facebook_appaccesstoken').text();
						updateFields();
						jQuery('#wpsujan_facebook_appid').focus();
					    jQuery(this).text('Cancel');
						return false;
					},function(){
						jQuery(this).text('Edit App Id');
						updateFields(appId,secretId,token,true);
					});

			});
			</script>
			<h2>FB Autoposter Settings <a href="#" class="edit_fbappid button button-primary">Edit App Id</a></h2>
			<form method="post" action="options.php">
				<?php settings_fields('wpsujan_fbap_options'); ?>
				<?php do_settings_sections(__FILE__); ?>
				<p class="submit">
				<?php //if we have both options echo the button to Authorize
					if(  !empty($this->settings_from_wp['wpsujan_facebook_appid']) && !empty( $this->settings_from_wp['wpsujan_facebook_appsecret'] )  && empty( $this->settings_from_wp['wpsujan_facebook_appaccesstoken'] ) ){
						$url_to_authorize_app ="https://www.facebook.com/dialog/oauth?client_id=".$this->settings_from_wp['wpsujan_facebook_appid']."&redirect_uri=".plugins_url('facebook-autopost')."/&scope=manage_pages,publish_stream";
						echo '<a href="'.$url_to_authorize_app.'" class="button button-primary">Authorize Autoposter</a>';
					}
				 ?>
				<input type="submit" class="button button-secondary" value="Save Changes" /></p>
			</form>
	</div>
<?php
	}

	function registerSettingsAndFields(){
		register_setting('wpsujan_fbap_options','wpsujan_fbap_options'); //3rd param is optional
		add_settings_section('wpsujan_fbap_section','',array($this,'wpsujanFbapCallback'),__FILE__);
		add_settings_field('wpsujan_facebook_appid','Facebook App ID: ',array($this,'fbAppIdInput'),__FILE__,'wpsujan_fbap_section');
		add_settings_field('wpsujan_facebook_appsecret','Facebook App Secret: ',array($this,'fbAppSecretInput'),__FILE__,'wpsujan_fbap_section');
		add_settings_field('wpsujan_facebook_appaccesstoken','Access Token: ',array($this,'fbAppAccessTokenInput'),__FILE__,'wpsujan_fbap_section');
		add_settings_field('wpsujan_facebook_username','Facebook Username/ID: ',array($this,'fbUsernameInput'),__FILE__,'wpsujan_fbap_section');
		
	}

	/*
	* Section callback
	*/
	public function wpsujanFbapCallback(){

	}

	/*
	* Inputs
	*/
	private function ifReadonly($name){
		if(!empty($this->settings_from_wp[$name])){
			$readonly = ' readonly="true"';
		}else{
			$readonly = '';
		}
		return $readonly;
	}
	private function textInput($name,$description){

		echo '<input name="wpsujan_fbap_options['.$name.']" type="text" id="'.$name.'" value="'.$this->settings_from_wp[$name].'" class="regular-text"'.$this->ifReadonly($name).' />
		<p class="description">'.$description.'</p>';
	}
	private function textArea($name,$description,$cols=40,$rows=10){
echo '<textarea rows="'.$rows.'" cols="'.$cols.'" name="wpsujan_fbap_options['.$name.']" id="'.$name.'" class="regular-text"'.$this->ifReadonly($name).'>'.$this->settings_from_wp[$name].'</textarea>
		<p class="description">'.$description.'</p>';

	}

	public function fbUsernameInput(){
		$this->textInput('wpsujan_facebook_username','Write your User id here.');
	}
	
	public function fbAppIdInput(){
		$this->textInput('wpsujan_facebook_appid','Get the App Id and Paste in here.');
	}

	public function fbAppSecretInput(){
		$this->textInput('wpsujan_facebook_appsecret','Get the App Secret and Paste in here.');
	}

	public function fbAppAccessTokenInput(){
		$this->textArea('wpsujan_facebook_appaccesstoken','Authorize The app ID and Secret and get the Access token.',40,7);
	}

}
