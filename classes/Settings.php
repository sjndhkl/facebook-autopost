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
		add_options_page('Autoposter Settings','Autoposter Settings','manage_options','wpsujan-facebook-autoposter-settings',array($this,'displaySettingsPage'));
	}

	function displaySettingsPage(){
?>
	<div class="wrap">
			<?php screen_icon(); ?>
			<h2>FB Autoposter Settings</h2>
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
	}

	/*
	* Section callback
	*/
	public function wpsujanFbapCallback(){

	}

	/*
	* Inputs
	*/
	public function fbAppIdInput(){
		echo '<input name="wpsujan_fbap_options[wpsujan_facebook_appid]" type="text" id="wpsujan_facebook_appid" value="'.$this->settings_from_wp['wpsujan_facebook_appid'].'" class="regular-text" />
		<p class="description">Get the App Id and Paste in here.</p>';
	}

	public function fbAppSecretInput(){
		echo '<input name="wpsujan_fbap_options[wpsujan_facebook_appsecret]" type="text" id="wpsujan_facebook_appsecret" value="'.$this->settings_from_wp['wpsujan_facebook_appsecret'].'" class="regular-text" />
		<p class="description">Get the App Secret and Paste in here.</p>';
	}

	public function fbAppAccessTokenInput(){
		echo '<textarea rows="10" cols="40" name="wpsujan_fbap_options[wpsujan_facebook_appaccesstoken]" id="wpsujan_facebook_appaccesstoken" class="regular-text">'.$this->settings_from_wp['wpsujan_facebook_appaccesstoken'].'</textarea>
		<p class="description">Authorize The app ID and Secret and get the Access token and Paste in here.</p>';
	}

}
