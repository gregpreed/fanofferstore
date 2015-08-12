<?php
/*
Plugin Name:  Tfingi Megamenu Plugin
Plugin URI:   http://themeforest.net/user/Tfingi
Description:  Adds a mega menu functionality to your website
Version:      1.4.1
Author:       Tfingi
Author URI:   http://themeforest.net/user/Tfingi
Author Email:
Contributors: Marcin Krysiak and Vladimir Mitkovsky
License:
License URI:

Copyright 2015 Tfingi
 */
defined( 'ABSPATH' ) OR exit;
require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
require_once 'class-tfingi-megamenu-walker.php';

// Load at the default priority of 10
add_action( 'plugins_loaded', array( 'Tfingi_Megamenu', 'init' ) );

class Tfingi_Megamenu {
    /**
     * Instance of the class
     * @static
     * @access protected
     * @var object
     */

    protected static $instance;

    /**
     * ID of the custom metabox
     * @var string
     */
    public $metabox_id = 'megamenu_sectionid';

    public static function init() {
        is_null( self::$instance ) AND self::$instance = new self;
        return self::$instance;
    }


    /**
     * Constructor.
     * @return \Tfingi_Megamenu
     */
    public function __construct() {

        //Adds new menu to menus in admin section
        add_action( 'admin_menu', array( $this, 'add_metabox' ) );

        //Saves megamenu specific fields
        add_action('wp_update_nav_menu_item', array( $this, 'save_megamenu_fields' ), 10, 3  );

        //Reads megamenu fields from post and set it up to menu item
        add_filter( 'wp_setup_nav_menu_item', array( $this, 'read_megamenu_fields' ) );

        //Adds specific classes to megamenu items in frontend side
        add_filter( 'wp_nav_menu_objects', array( $this, 'add_classes_to_megamenu_items_frontend' ) );

        //Adds specific attributes to megamenu items in frontend side
        add_filter( 'wp_nav_menu_items', array( $this, 'add_attributes_to_megamenu_items_frontend' ) );

        //Replace original nav_edit_walker class
        add_filter( 'wp_edit_nav_menu_walker', array( $this, 'replace_nav_edit_walker' ) );

        // Allows HTML tags and shortcodes in megamenu-content menu item
        add_filter( 'walker_nav_menu_start_el', array( $this, 'nav_menu_with_description' ), 10, 4 );

        //Switches off striping HTML and short code tags for megamenu-content item
        add_filter('wp_setup_nav_menu_item', array( $this, 'strip_tags_but_content_item' ) );

    }

//
//Adds metabox to admin menus area
//
    function add_metabox() {

        add_meta_box(
            $this->metabox_id,
            'Mega Menu Elements',
            array( $this, 'add_megamenu_metabox' ),
            'nav-menus', 'side', 'high'
        );

    }

    function add_megamenu_metabox( $post ) {

        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'add_megamenu_metabox', 'add_megamenu_metabox_nonce' );

        global $_nav_menu_placeholder, $nav_menu_selected_id;

        $_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;

    ?>
        <div class="customlink-add-item" id="customlink-add-item">
            <input type="submit" <?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e('Add Column'); ?>" name="add-column" id="submit-megamenu-column" onclick="jQuery('#custom-menu-item-name-megamenu').val('Mega Menu Column') "/><br><br>
            <input type="submit" <?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e('Add Heading Item'); ?>" name="add-column" id="submit-megamenu-heading-item" onclick="jQuery('#custom-menu-item-name-megamenu').val('Mega Menu Heading') "/><br><br>
            <input type="submit" <?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e('Add Content Item'); ?>" name="add-column" id="submit-megamenu-content-item" onclick="jQuery('#custom-menu-item-name-megamenu').val('Mega Menu Content') "/><br><br>


            <label for="myplugin_new_field">After you click on the button, menu item will appear on the bottom of current menu.<br>
                Rearrange it to the right position<br>
                <br>
                See our documentation on ....
            </label>
        </div>
    <?php
        wp_register_style( 'tfingi-megamenu-backend', plugins_url('tfingi-megamenu-backend.css', __FILE__) );
        wp_enqueue_style('tfingi-megamenu-backend');

        wp_register_script(
            'tfingi-megamenu-js',
            plugins_url( 'tfingi-megamenu.js', __FILE__ ),
            array( 'jquery' ),
            filemtime( plugin_dir_path( __FILE__ ).'tfingi-megamenu.js' ),
            true
        );
        wp_enqueue_script( 'tfingi-megamenu-js' );



    }

//
// Saves new fields to postmeta for navigation
//
    function save_megamenu_fields($menu_id, $menu_item_db_id, $args ) {
        if ( isset( $_REQUEST['menu-item-tfingi-megamenu-type'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-tfingi-megamenu-type'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_tfingi_megamenu_type', $custom_value );
        }
        if ( isset( $_REQUEST['menu-item-tfingi-megamenu'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-tfingi-megamenu'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_tfingi_megamenu', $custom_value );
        }
        if ( isset( $_REQUEST['menu-item-tfingi-megamenu-description'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-tfingi-megamenu-description'][$menu_item_db_id];
            if (strrpos($custom_value, "full") === false) {
                $custom_value = preg_replace('/\D/', '', $custom_value);
            }
            else $custom_value = 'full';
            update_post_meta( $menu_item_db_id, '_menu_item_tfingi_megamenu_description', $custom_value );
        }
    }

//
// Reads values of new fields to $item object that will be passed to Walker_Nav_Menu_Edit_Custom
//
    function read_megamenu_fields($menu_item) {
        $menu_item->megamenuActive = get_post_meta( $menu_item->ID, '_menu_item_tfingi_megamenu', true );
        $menu_item->megamenuDescription = get_post_meta( $menu_item->ID, '_menu_item_tfingi_megamenu_description', true );
        $menu_item->megamenuType = get_post_meta( $menu_item->ID, '_menu_item_tfingi_megamenu_type', true );
        return $menu_item;
    }

//
//adds frontend megamenu items classes
//
    function add_classes_to_megamenu_items_frontend($menu_items) {

        foreach ($menu_items as $menu_item) {
            array_push($menu_item->classes, $menu_item->megamenuType);
            if ($menu_item->megamenuActive == "active") {
                array_push($menu_item->classes, 'megamenu-parent');
                array_push($menu_item->classes, 'data-width="'.$menu_item->megamenuDescription.'"');
            }
        }
        return $menu_items;
    }

//
//adds frontend megamenu items attributes
//
    function add_attributes_to_megamenu_items_frontend($menu) {
        wp_register_script(
            'jquery_hoverIntent_minified-js',
            plugins_url( 'jquery.hoverIntent.minified.js', __FILE__ ),
            array( 'jquery' ),
            filemtime( plugin_dir_path( __FILE__ ).'jquery.hoverIntent.minified.js' ),
            true
        );
        wp_enqueue_script( 'jquery_hoverIntent_minified-js' );

        wp_register_script(
            'tfingi-megamenu-frontend-js',
            plugins_url( 'tfingi-megamenu-frontend.js', __FILE__ ),
            array( 'jquery' ),
            filemtime( plugin_dir_path( __FILE__ ).'tfingi-megamenu-frontend.js' ),
            true
        );
        wp_enqueue_script( 'tfingi-megamenu-frontend-js' );

        wp_register_style( 'tfingi-megamenu-frontend', plugins_url('tfingi-megamenu-frontend.css', __FILE__) );
        wp_enqueue_style('tfingi-megamenu-frontend');

        // Icon font
        wp_register_style( 'tfingi-megamenu-iconfont', plugins_url('/iconfont/style.css', __FILE__) );
        wp_enqueue_style('tfingi-megamenu-iconfont');

        $matches = explode('"', $menu);
        foreach ($matches as $match) {
            $data_width_value = strstr($match, 'data-width=');
            if ($data_width_value != false) {
                $just_value = explode('&quot;', $data_width_value);
                $new_match = str_replace("data-width=&quot;".$just_value[1]."&quot;", "", $match);
                $new_match = $new_match.'" data-width="'.$just_value[1];
                $menu = str_replace($match, $new_match, $menu);
            }
        }
        return $menu;
    }

//
//Replace original nav_edit_walker class
//
    function replace_nav_edit_walker() {
        return 'Tfingi_Megamenu_Walker';
    }

//
// Changes the default HTML output for 'megamenu-content' and 'megamenu-column' menu items
//
    function nav_menu_with_description ( $item_output, $item, $depth, $args ) {
        if ( isset( $item->megamenuType ) )
        	// Removes the <a>Title</a> output + Allows HTML tags and shortcodes in megamenu-content menu item
            if ('megamenu-content' == $item->megamenuType) {
                $item_output = do_shortcode( $item->post_content );
            }
            // Removes the <a>Title</a> output for megamenu-column
            if ('megamenu-column' == $item->megamenuType) {
            	$item_output = $item->post_content;
            }
        return $item_output;
    }


//
//Switches off striping HTML and short code tags for megamenu-content item
//
    function strip_tags_but_content_item ( $menuitem ) {
        if ( 'megamenu-content' == $menuitem->megamenuType ) {
            $allowed_tags = wp_kses_allowed_html( 'post' );
            $menuitem->post_content = wp_kses( $menuitem->post_content, $allowed_tags );
        }
        return $menuitem;
    }

}





?>