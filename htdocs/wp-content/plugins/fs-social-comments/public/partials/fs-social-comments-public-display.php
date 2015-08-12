<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://www.lifeisfood.it/
 * @since      1.0.0
 *
 * @package    Fs_Social_Comments
 * @subpackage Fs_Social_Comments/public/partials
 */
$file = '/comments.php';
$thecomments = false;
if(!get_option("fs_social_comments_hide_default"))
	$thecomments = true;
if(get_option("fs_social_comments_enable_facebook"))
	$thecomments = true;	
// fs_social_comments_hide_default
// fs_social_comments_enable_facebook
?>
<?php if($thecomments):?>
<input type="hidden" value="<?php echo get_the_ID();?>" id="commentPostId" />
<div role="tabpanel">
  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
  	<?php if(get_option("fs_social_comments_enable_facebook")):?>
    	<li role="presentation" class="active"><a href="#facebook" aria-controls="home" role="tab" data-toggle="tab"><?php echo get_option('fs_social_comments_facebook_label');?></a></li>
    <?php endif;?>
    <?php if(!get_option("fs_social_comments_hide_default")):?>
    	<li role="presentation" <?php if(!get_option("fs_social_comments_enable_facebook")): ?>class="active" aria-controls="home" <?php endif;?>><a href="#wordpress" aria-controls="profile" role="tab" data-toggle="tab"><?php echo get_option('fs_social_comments_wordpress_label');?></a></li>
    <?php endif;?>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
  	<?php if(get_option("fs_social_comments_enable_facebook")):?>
    <div role="tabpanel" class="tab-pane active" id="facebook">
		<script>
			(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/<?php echo get_option('fs_social_comments_lang_code');?>/sdk.js#xfbml=1&version=v2.3&cookie=true&appId=<?php echo get_option("fs_social_comments_facebook_app_id")?>";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
		<div id="fb-root"></div>
    	<div class="fb-comments" data-version="v2.3" data-href="<?php echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ?>" data-width="100%" data-numposts="5" data-colorscheme="light"></div>
    </div>
    <?php endif;?>
    <?php if(!get_option("fs_social_comments_hide_default")):?>
    	<div role="tabpanel" class="tab-pane <?php if(!get_option("fs_social_comments_enable_facebook")): ?>active<?php endif;?>" id="wordpress"><?php if ( file_exists( TEMPLATEPATH . $file ) )	require( TEMPLATEPATH . $file );?></div>
    <?php endif;?>
  </div>
</div>
<?php endif;?>