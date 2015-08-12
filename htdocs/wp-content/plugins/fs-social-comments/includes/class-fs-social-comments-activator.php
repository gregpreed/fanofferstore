<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.lifeisfood.it/
 * @since      1.0.0
 *
 * @package    Fs_Social_Comments
 * @subpackage Fs_Social_Comments/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Fs_Social_Comments
 * @subpackage Fs_Social_Comments/includes
 * @author     Fabio Sirchia <fabio.sirchia@gmail.com>
 */
class Fs_Social_Comments_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		Fs_Social_Comments_Activator::init_table();
	}
	
	public static function init_table(){
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE IF NOT EXISTS fs_social_comments_relation (
  				id_fb varchar(500) NOT NULL,
  				id_wp int(255) NOT NULL
				) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		$sql = " ALTER TABLE fs_social_comments_relation
		ADD PRIMARY KEY (id_fb), ADD UNIQUE KEY id_wp (id_wp);";
		dbDelta( $sql );
	}
}
