<div class="row fs-sc-container form-group">
<div class="col-md-7">
<h2>Fs Social Comments (Beta)</h2>
<p>
	<?php _e("intro", 'fs-social-comments')?>
</p>
<form method="post" action="options.php" class="input-group">
    <?php settings_fields( 'fs-comments-opt-group' ); ?>
    <h2><?php _e("General Settings", 'fs-social-comments')?></h2>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e("Hide default comments", 'fs-social-comments')?></th>
        <td><input class="form-control" type="checkbox" name="fs_social_comments_hide_default" <?php if(get_option('fs_social_comments_hide_default')):?>checked<?php endif;?> /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Wordpress Label</th>
        <td><input class="form-control" type="text" name="fs_social_comments_wordpress_label" value="<?php echo get_option('fs_social_comments_wordpress_label'); ?>" /></td>
        </tr>
    </table>
    <hr>
    <h2><?php _e("Facebook Settings", 'fs-social-comments')?></h2>
    <table class="form-table">
    	<tr valign="top">
        <th scope="row"><?php _e("Enable Facebook comments", 'fs-social-comments')?></th>
        <td><input class="form-control" type="checkbox" name="fs_social_comments_enable_facebook" <?php if(get_option('fs_social_comments_enable_facebook')):?>checked<?php endif;?> /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Facebook Label</th>
        <td><input class="form-control" type="text" name="fs_social_comments_facebook_label" value="<?php echo get_option('fs_social_comments_facebook_label'); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Facebook app id</th>
        <td><input class="form-control" type="text" name="fs_social_comments_facebook_app_id" value="<?php echo get_option('fs_social_comments_facebook_app_id'); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Facebook app secret</th>
        <td><input type="text" name="fs_social_comments_facebook_app_secret" value="<?php echo get_option('fs_social_comments_facebook_app_secret'); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e("language code", 'fs-social-comments');?></th>
        <td><input type="text" name="fs_social_comments_lang_code" value="<?php echo get_option('fs_social_comments_lang_code'); ?>" /> <p>en_EN or it_IT or ...</p></td>
        </tr>
    </table>
    <hr>
    
    <p class="submit">
    	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
<?php /*
<script>
  // This is called with the results from from FB.getLoginStatus().
  function statusChangeCallback(response) {
    console.log('statusChangeCallback');
    console.log(response);
    // The response object is returned with a status field that lets the
    // app know the current login status of the person.
    // Full docs on the response object can be found in the documentation
    // for FB.getLoginStatus().
    if (response.status === 'connected') {
      // Logged into your app and Facebook.
      testAPI();
    } else if (response.status === 'not_authorized') {
      // The person is logged into Facebook, but not your app.
      document.getElementById('status').innerHTML = 'Please log ' +
        'into this app.';
    } else {
      // The person is not logged into Facebook, so we're not sure if
      // they are logged into this app or not.
      document.getElementById('status').innerHTML = 'Please log ' +
        'into Facebook.';
    }
  }

  // This function is called when someone finishes with the Login
  // Button.  See the onlogin handler attached to it in the sample
  // code below.
  function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
  }

  window.fbAsyncInit = function() {
  FB.init({
    appId      : '<?php echo get_option('fs_social_comments_facebook_app_id'); ?>',
    cookie     : true,  // enable cookies to allow the server to access 
                        // the session
    xfbml      : true,  // parse social plugins on this page
    version    : 'v2.3' // use version 2.2
  });

  // Now that we've initialized the JavaScript SDK, we call 
  // FB.getLoginStatus().  This function gets the state of the
  // person visiting this page and can return one of three states to
  // the callback you provide.  They can be:
  //
  // 1. Logged into your app ('connected')
  // 2. Logged into Facebook, but not your app ('not_authorized')
  // 3. Not logged into Facebook and can't tell if they are logged into
  //    your app or not.
  //
  // These three cases are handled in the callback function.

  FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
  });

  };

  // Load the SDK asynchronously
  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/it_IT/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));

  // Here we run a very simple test of the Graph API after login is
  // successful.  See statusChangeCallback() for when this call is made.
  function testAPI() {
    console.log('Welcome!  Fetching your information.... ');
    FB.api('/me', function(response) {
      console.log('Successful login for: ' + response.name);
      document.getElementById('status').innerHTML =
        'Thanks for logging in, ' + response.name + '!';
    });
  }
</script>

<!--
  Below we include the Login Button social plugin. This button uses
  the JavaScript SDK to present a graphical Login button that triggers
  the FB.login() function when clicked.
-->

<fb:login-button scope="public_profile,email,manage_pages,publish_pages,publish_actions" onlogin="checkLoginState();">
</fb:login-button>

<div id="status">
</div>
*/?>
</div>
<div class="col-md-4">
	<h3> <?php _e("sidebar title", 'fs-social-comments')?> </h3>
	<p>
		<?php _e("sidebar content", 'fs-social-comments')?>
	</p>
	<hr>
	<h3> <?php _e("sidebar title donate", 'fs-social-comments')?> </h3>
	<p>
		<?php _e("sidebar content donate", 'fs-social-comments')?>
	</p>
	<div style="text-align:center;">
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="QBBU9HMDYSQ7N">
		<input type="image" src="https://www.paypalobjects.com/it_IT/IT/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - Il metodo rapido, affidabile e innovativo per pagare e farsi pagare.">
		<img alt="" border="0" src="https://www.paypalobjects.com/it_IT/i/scr/pixel.gif" width="1" height="1">
	</form>
	</div>
</div>
</div>
