<?php
/*
Plugin Name: WP-Cron-Mail
Plugin URI: http://www.skippy.net/blog/plugins/
Description: Does blog-by-email stuff, in conjunction with WP-Cron
Version: 1.2
Author: Scott Merrill
Author URI: http://www.skippy.net/

based on core wp-mail.php
released under the terms of the GNU GPL
*/

add_action('wp_cron_15', 'wp_cron_mail');

function wp_cron_mail() {
// change this to 1 if you want to always get a notification
// even if nothing happened.
$always_notify = 0;

global $wpdb;

$logfile = '';
require_once(ABSPATH.WPINC.'/class-pop3.php');

// I don't think we want to display errors, so let's use whatever
// the webserver uses by default.  Remove the slashes below to override.
// error_reporting(2037);

$time_difference = get_settings('gmt_offset') * 3600;

$phone_delim = '::';

$pop3 = new POP3();

if (!$pop3->connect(get_settings('mailserver_url'), get_settings('mailserver_port'))) :
	$admin = get_userdata(1);
	mail ($admin->user_email, 'wp-mail error', "Ooops $pop3->ERROR \r\n");
	return;
endif;

$count = $pop3->login(get_settings('mailserver_login'), get_settings('mailserver_pass'));
if (0 == $count) {
	if (1 == $always_notify) {
		$admin = get_userdata(1);
		mail ($admin->user_email, 'wp-mail status', __('There doesn&#8217;t seem to be any new mail.'));
	}
	return;
}

for ($i=1; $i <= $count; $i++) :

	$message = $pop3->get($i);

	$content = '';
	$content_type = '';
	$boundary = '';
	$bodysignal = 0;
	$dmonths = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
					 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	foreach ($message as $line) :
		if (strlen($line) < 3) $bodysignal = 1;

		if ($bodysignal) {
			$content .= $line;
		} else {
			if (preg_match('/Content-Type: /i', $line)) {
				$content_type = trim($line);
				$content_type = substr($content_type, 14, strlen($content_type)-14);
				$content_type = explode(';', $content_type);
				$content_type = $content_type[0];
			}
			if (($content_type == 'multipart/alternative') && (preg_match('/boundary="/', $line)) && ($boundary == '')) {
				$boundary = trim($line);
				$boundary = explode('"', $boundary);
				$boundary = $boundary[1];
			}
			if (preg_match('/Subject: /i', $line)) {
				$subject = trim($line);
				$subject = substr($subject, 9, strlen($subject)-9);
				if (!preg_match('#\=\?(.+)\?Q\?(.+)\?\=#i', $subject)) {
				  $subject = wp_iso_descrambler($subject);
				}
				// Captures any text in the subject before $phone_delim as the subject
				$subject = explode($phone_delim, $subject);
				$subject = $subject[0];
			}

			// Set the author using the email address (To or Reply-To, the last used)
			// otherwise use the site admin
			// SDM: added slash to beginning to 2nd preg_match
			if (preg_match('/From: /', $line) | preg_match('/Reply-To: /', $line))  {
				$author=trim($line);
			if ( ereg("([a-zA-Z0-9\_\-\.]+@[\a-zA-z0-9\_\-\.]+)", $author , $regs) ) {
				$logfile .= "Author = {$regs[1]} \r\n";
				$result = $wpdb->get_row("SELECT ID FROM $wpdb->users WHERE user_email='$regs[1]' ORDER BY ID DESC LIMIT 1");
				if (!$result)
					$post_author = 1;
				else
					$post_author = $result->ID;
			} else
				$post_author = 1;
			}

			if (preg_match('/Date: /i', $line)) { // of the form '20 Mar 2002 20:32:37'
				$ddate = trim($line);
				$ddate = str_replace('Date: ', '', $ddate);
				if (strpos($ddate, ',')) {
					$ddate = trim(substr($ddate, strpos($ddate, ',')+1, strlen($ddate)));
				}
				$date_arr = explode(' ', $ddate);
				$date_time = explode(':', $date_arr[3]);
				
				$ddate_H = $date_time[0];
				$ddate_i = $date_time[1];
				$ddate_s = $date_time[2];
				
				$ddate_m = $date_arr[1];
				$ddate_d = $date_arr[0];
				$ddate_Y = $date_arr[2];
				for ($j=0; $j<12; $j++) {
					if ($ddate_m == $dmonths[$j]) {
						$ddate_m = $j+1;
					}
				}

				$time_zn = intval($date_arr[4]) * 36;
				$ddate_U = gmmktime($ddate_H, $ddate_i, $ddate_s, $ddate_m, $ddate_d, $ddate_Y);
				$ddate_U = $ddate_U - $time_zn;
				$post_date = gmdate('Y-m-d H:i:s', $ddate_U + $time_difference);
				$post_date_gmt = gmdate('Y-m-d H:i:s', $ddate_U);
			}
		}
	endforeach;

	$subject = addslashes(trim(str_replace(get_settings('subjectprefix'), '', $subject)));

	if ($content_type == 'multipart/alternative') {
		$content = explode('--'.$boundary, $content);
		$content = $content[2];
		$content = explode('Content-Transfer-Encoding: quoted-printable', $content);
		$content = strip_tags($content[1], '<img><p><br><i><b><u><em><strong><strike><font><span><div>');
	}
	$content = trim($content);
	// Captures any text in the body after $phone_delim as the body
	$content = explode($phone_delim, $content);
	$content[1] ? $content = $content[1] : $content = $content[0];

	$logfile .= "Content-type: $content_type, boundary: $boundary\r\n";
	$logfile .= "Raw content:\r\n$content\r\n";

	$content = trim(addslashes($content));

	$post_content = apply_filters('phone_content', $content);

	$post_title = xmlrpc_getposttitle($content);

	if ($post_title == '') $post_title = $subject;

	if (empty($post_categories)) $post_categories[] = get_settings('default_email_category');

	$post_category = $post_categories;

	// or maybe we should leave the choice to email drafts? propose a way
	$post_status = 'publish';

	$post_data = compact('post_content','post_title','post_date','post_date_gmt','post_author','post_category', 'post_status');

	$post_ID = wp_insert_post($post_data);

	if (!$post_ID) {
		// we couldn't post, for whatever reason. better move forward to the next email
		continue;
	}

	do_action('publish_phone', $post_ID);

	$logfile .=  "Author: $post_author\r\n";
	$logfile .=  "Posted title: $post_title\r\n";
	$logfile .=  "Posted content:\r\n$content\r\n";

	if (!$post_categories) $post_categories[] = 1;
	foreach ($post_categories as $post_category) :
		$post_category = intval($post_category);

		// Double check it's not there already
		$exists = $wpdb->get_row("SELECT * FROM $wpdb->post2cat WHERE post_id = $post_ID AND category_id = $post_category");

		if (!$exists && $result) { 
			$wpdb->query("
			INSERT INTO $wpdb->post2cat
			(post_id, category_id)
			VALUES
			($post_ID, $post_category)
			");
		}
	endforeach;

	if(!$pop3->delete($i)) {
		$logfile .= 'Oops '.$pop3->ERROR."\r\n";
		$pop3->reset();
		$admin = get_userdata(1);
		mail($admin->user_email, 'wp-mail error', $logfile);
		return;
	} else {
		$logfile .= "Mission complete, message <strong>$i</strong> deleted.\r\n";
	}

endfor;

$admin = get_userdata(1);
mail($admin->user_email, 'wp-mail complete', $logfile);
$pop3->quit();
}
?>
