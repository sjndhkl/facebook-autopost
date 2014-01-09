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

	private function canIHideEditButton(){
		$hideEditButton = false;
		if(empty($this->settings_from_wp['wpsujan_facebook_appid'])){
			$hideEditButton = true;
		}
		return $hideEditButton;
	}

	function displaySettingsPage(){
    $hideEditButton = $this->canIHideEditButton();
    $editButton = '';
?>
	<div class="wrap">
		<script src="//cdnjs.cloudflare.com/ajax/libs/chosen/1.0/chosen.jquery.min.js" type="text/javascript"></script>
		<link rel="stylesheet" href="http://harvesthq.github.io/chosen/chosen.css" />
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
				<?php if(!$hideEditButton): ?>
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
				<?php 
					$editButton ='&nbsp;<a href="#" class="edit_fbappid button button-primary">Edit</a>';
					endif; 
				?>
				jQuery('#wpsujan_facebook_profileselection').on('change',function(){
					var selectInput = jQuery(this);
					var currentSelection = selectInput.val();
					if(currentSelection=='page'){
						//ajax it
						jQuery.post('<?php echo plugins_url('facebook-autopost'); ?>/index.php',{'t':'<?php echo $this->settings_from_wp['wpsujan_facebook_appaccesstoken'] ?>'},function(response){
							selectInput.parent().append('&nbsp;<select style="width:270px" name="page-selections" id="page-selections"></select>');
							jQuery("#page-selections").append(response.optionsText).trigger('change');
						})
					}else{
						//do some thing else
						jQuery("#wpsujan_facebook_pagetopost").val('');
						jQuery("#profile_selection_text").text('');
						jQuery("#page-selections").remove();
					}
					return false;
				});

				jQuery(document).on('change',"#page-selections",function(){
					jQuery("#wpsujan_facebook_pagetopost").val(jQuery(this).val());
					return false;
				});

				jQuery("#posttypes_selection").on('change',function(){
					jQuery("#wpsujan_facebook_posttypestoautopost").val(jQuery(this).val());
					return false;
				});

				jQuery("#posttypes_selection").chosen();
		});
		</script>
			<h2>FB Autoposter Settings<?php echo $editButton; ?>
			<?php //if we have both options echo the button to Authorize
					if(  !empty($this->settings_from_wp['wpsujan_facebook_appid']) && !empty( $this->settings_from_wp['wpsujan_facebook_appsecret'] )  && empty( $this->settings_from_wp['wpsujan_facebook_appaccesstoken'] ) ){
						$url_to_authorize_app ="https://www.facebook.com/dialog/oauth?client_id=".$this->settings_from_wp['wpsujan_facebook_appid']."&redirect_uri=".plugins_url('facebook-autopost')."/&scope=manage_pages,publish_stream";
						echo '&nbsp;<a href="'.$url_to_authorize_app.'" class="button button-primary">Authorize Autoposter</a>';
					}
				 ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields('wpsujan_fbap_options'); ?>
				<?php do_settings_sections(__FILE__); ?>
				<p class="submit">
				<input type="submit" class="button button-primary" value="Save Changes" /></p>
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
		add_settings_field('wpsujan_facebook_pagetopost','Autopost In: ',array($this,'fbAppPageToPostInput'),__FILE__,'wpsujan_fbap_section');
		add_settings_field('wpsujan_facebook_posttypestoautopost','Post Types to Autopost: ',array($this,'fbAppPostTypesToAutoPostInput'),__FILE__,'wpsujan_fbap_section');		
	}

	/*
	* Section callback
	*/
	public function wpsujanFbapCallback(){

	}

	/*
	* Inputs
	*/
	private function ifReadonly($name,$dont_bypass=true){
		if(!$dont_bypass)
			return;
		if(!empty($this->settings_from_wp[$name])){
			$readonly = ' readonly="true"';
		}else{
			$readonly = '';
		}
		return $readonly;
	}
	private function textInput($name,$description='',$check_readonly=true){

		echo '<input name="wpsujan_fbap_options['.$name.']" type="text" id="'.$name.'" value="'.$this->settings_from_wp[$name].'" class="regular-text"'.$this->ifReadonly($name,$check_readonly).' />
		<p class="description">'.$description.'</p>';
	}
	private function hiddenInput($name){
		echo '<input name="wpsujan_fbap_options['.$name.']" type="hidden" id="'.$name.'" value="'.$this->settings_from_wp[$name].'" />';
	}
	private function textArea($name,$description,$cols=40,$rows=10){
echo '<textarea rows="'.$rows.'" cols="'.$cols.'" name="wpsujan_fbap_options['.$name.']" id="'.$name.'" class="regular-text"'.$this->ifReadonly($name).'>'.$this->settings_from_wp[$name].'</textarea>
		<p class="description">'.$description.'</p>';

	}

	private function choiceInputForPostTypesToPost($name){

		$optionsToDisplay = '';
		$selections = explode(',', $this->settings_from_wp[$name]);
		$post_types = get_post_types(array('public'=>true),'objects');
		foreach ($post_types as $post_type) {
			if($post_type->name=='attachment'){
				continue;
			}
			if(is_array($selections) && in_array($post_type->name, $selections)){
				$selected = " selected='selected'";
			}else{
				$selected = "";
			}
			$optionsToDisplay .= "<option value='{$post_type->name}'$selected>".ucfirst($post_type->name)."</option>";
		}
		echo str_replace("{options}", $optionsToDisplay,'<select style="width:350px;" multiple="true" id="posttypes_selection" name="posttypes_selection">
			{options}
</select>');
	}

	private function choiceInputForPageToPost($name,$default_value='',$description=''){
		$options = array('page'=>'Page','profile'=>'Profile');
		if(!empty($this->settings_from_wp[$name])){
			$default_value = $this->settings_from_wp[$name];
		}
		$optionsToDisplay = '';
		foreach ($options as $key => $value) {
			if($default_value==$key){
				$selected = ' selected="selected"';
			}else{
				$selected = '';
			}
			$optionsToDisplay .= "<option value='$key'$selected>$value</option>";
		}
		echo str_replace("{options}", $optionsToDisplay,'<span id="profile_selection_text">'.$description.'</span>&nbsp;<select id="'.$name.'" name="'.$name.'">
			{options}
</select>');
	}

	private function pageToPostInput($name){
		$profile_selection = $this->settings_from_wp['wpsujan_facebook_pagetopost'];
		if(!empty($profile_selection) ){
			$segments = explode('|', $profile_selection);
			$profile_selection = 'page';//'Page - '.;
			$profile_selection_text  = $segments[0];
		}else{
			$profile_selection = 'profile';
			$profile_selection_text = '';

		}
		$this->choiceInputForPageToPost('wpsujan_facebook_profileselection',$profile_selection,$profile_selection_text);
		$this->hiddenInput($name);
		
	}
	
	
	public function fbAppProfileSelectionInput(){
		$this->choiceInput('wpsujan_facebook_profileselection','profile');
	}
	public function fbAppPageToPostInput(){
		$this->pageToPostInput('wpsujan_facebook_pagetopost');
	}

	public function fbAppPostTypesToAutoPostInput(){
		$this->choiceInputForPostTypesToPost('wpsujan_facebook_posttypestoautopost');
		$this->hiddenInput('wpsujan_facebook_posttypestoautopost');
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
