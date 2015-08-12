<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.lifeisfood.it/
 * @since             1.0.0
 * @package           Fs_Social_Comments
 *
 * @wordpress-plugin
 * Plugin Name:       Fs Social Comments
 * Plugin URI:        http://www.lifeisfood.it/
 * Description:       this plugin will help you to synchronize facebook comments with wordpress integrated comments .We are currently in beta, so functions like delete and spam at the moment are not available. 
 * Version:           1.1.1
 * Author:            Fabio Sirchia
 * Author URI:        http://www.lifeisfood.it/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fs-social-comments
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('FACEBOOK_SDK_V4_SRC_DIR', __DIR__ . '/facebook-php/src/Facebook/');
require __DIR__ . '/facebook-php/autoload.php';


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fs-social-comments-activator.php
 */
function activate_fs_social_comments() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fs-social-comments-activator.php';
	Fs_Social_Comments_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fs-social-comments-deactivator.php
 */
function deactivate_fs_social_comments() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fs-social-comments-deactivator.php';
	Fs_Social_Comments_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_fs_social_comments' );
register_deactivation_hook( __FILE__, 'deactivate_fs_social_comments' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-fs-social-comments.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_fs_social_comments() {

	$plugin = new Fs_Social_Comments();
	$plugin->run();

}
run_fs_social_comments();
