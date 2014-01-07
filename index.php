<?php 
  @session_start();
  if(isset($_SESSION['wpsujan']['fb_settings'])){
    $fb_settings = $_SESSION['wpsujan']['fb_settings']; 
  }else{
    $fb_settings = false;
  }
  if(!is_array($fb_settings)){
    header('Location: /');
    exit;
  }
  include_once 'autoload.php';

$fb_tokens = $_GET;
$token = '';
if(isset($fb_tokens['code'])){ 
    $code = $fb_tokens['code'];
    $curlObj = new MicroblogPoster_Curl();
    $url_for_getting_token = "https://graph.facebook.com/oauth/access_token?client_id=".$fb_settings['app_id']."&redirect_uri=http://wordpress.dev:88/content/plugins/facebook-autopost/&client_secret=".$fb_settings['app_secret']."&code=".$code;
    $result = $curlObj->fetch_url($url_for_getting_token);
    preg_match_all('/access_token=(.*)&/', $result,$segments);
    if(isset($segments[1][0]) && !empty($segments[1][0]) )
    { 
      $token = $segments[1][0];
      $_SESSION['wpsujan']['token'] = $token;
    }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Facebook Autoposter Access Token Generator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap-theme.min.css">



    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    
    <div class="container">

      <div class="col-md-12">
          <h1>Facebook Access Token</h1>
          <form class="form-horizontal" role="form">
  <div class="form-group">
    <div class="col-lg-12">
      <textarea class="form-control" id="facebook_access_token" rows="5"><?php echo $token; ?></textarea> 
    </div>
  </div>
</form>
<?php if(!empty($token)): ?>
  <a class="btn btn-primary" href="<?php echo $fb_settings['redirect_uri']; ?>">Use this Token</a>
<?php endif; ?>
      </div>

    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery.js"></script>
    <!-- Latest compiled and minified JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
  </body>
</html>