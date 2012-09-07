<?php

/**
	Air Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 2.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2012 WPBandit
	Jermaine Maree

		@package Air
		@version 1.1
**/

//! Base structure
class AirBase {

	//@{ Framework details
	const
		TEXT_Name = 'Air Framework',
		TEXT_Version = '1.1';
	//@}
	
	//@{ Global variables
	protected static
		//! Framework variables
		$vars,
		//! Theme Configuration
		$config,
		//! Theme options
		$options,
		//! Theme options menu
		$options_menu,
		//! Theme modules
		$modules;
	//@}

}

//! Framework controller
class AirControl extends AirBase {

	/**
		Get configuration option
			@public
	**/
	static function get($key,$default=FALSE) {
		return isset(self::$config[$key])?self::$config[$key]:$default;
	}

	/**
		Set configuration option
			@public
	**/
	static function set($key,$value) {
		self::$config[$key] = $value;
	}

	/**
		Set multiple configuration options
			@public
	**/
	static function mset(array $keys) {
		if ( isset(self::$config) ) {
			array_merge(self::$config, $keys);
		} else {
			foreach ( $keys as $key=>$value ) {
				self::$config[$key] = $value;
			}
		}
	}

	/**
		Get theme option
			@public
	**/
	static function get_option($key,$default=FALSE) {
		if ( isset(self::$options[$key]) && self::$options[$key] )
			return self::$options[$key];
		else
			return $default;
	}

	/**
		Load framework file
			@public
	**/
	static function load_file($file) {
		if ( is_file($file) )
			require ( $file );
	}

	/**
		Add theme options menu item
			@public
	**/
	static function add_options_menu_item($slug,$title) {
		self::$options_menu[$slug] = $title;
	}

	/**
		Get theme option menu
			@public
	**/
	static function get_options_menu() {
		return isset(self::$options_menu)?self::$options_menu:FALSE;
	}

	/**
		Add module
			@public
	**/
	static function add_module($slug,$title) {
		self::$modules[$slug] = $title;
	}

	/**
		Get modules
			@public
	**/
	static function get_modules() {
		return isset(self::$modules)?self::$modules:FALSE;
	}

	/**
		Set default options
			@public
	**/
	static function set_default_options() {
		// Check if theme options are set
		if ( isset(self::$options) && self::$options )
			return;
		// Load settings library
		if ( !class_exists('AirSettings') )
			require ( AIR_PATH . '/lib/air-settings.php' );
		// Set default options
		AirSettings::set_default_options();
	}
}

//! Air Framework
class Air extends AirBase {
	
	/**
		Initialize framework
			@public
	**/
	function init() {

		// Load framework files
		require ( AIR_PATH . '/lib/air-actions.php' );
		require ( AIR_PATH . '/lib/air-functions.php' );

		// Global $pagenow variable
		global $pagenow;

		// Permalinks structure
		$permalinks = get_option('permalink_structure');

		// Hydrate framework variables
		self::$vars = array(
			// Global $pagenow variable
			'PAGENOW' => $pagenow,
			// Permalinks
			'PERMALINKS' => ($permalinks && ($permalinks != ''))?TRUE:FALSE,
			// Static front page
			'STATIC' => ('page' === get_option('show_on_front'))?TRUE:FALSE,
		);

		// Theme options name
		if ( !isset(self::$config['theme-options']) ) {
			self::$config['theme-options'] = 'air-options';
		}

		// Admin library
		if ( is_admin() ) {
			$air_admin = require ( AIR_PATH . '/lib/air-admin.php' ); 
		}

		// Admin bar menu
		add_action('admin_bar_menu', 'air_admin_bar_menu', 100);

		// Load modules
		if ( is_array(self::$modules) ) {
			foreach ( self::$modules as $key=>$module ) {
				require ( AIR_MODULES . '/' . $key . '/' . $key.'.php' );
			}
		}

		// Get theme options
		self::$options = get_option(self::$config['theme-options']);

		// Add metadata (custom fields)
		if ( is_admin() && AirControl::get('meta-files') ) {
			$this->metadata();
		}
		
		// Register sidebars
		$this->register_sidebars();

		// Widgets
		$this->widgets();

		// Theme actions
		$this->actions();

		// Load theme-specific functions
		AirControl::load_file( AIR_PATH . '/theme/theme.php' );

		// Load custom-functions.php
		if ( is_file(get_template_directory().'/custom-functions.php') ) {
			require ( get_template_directory().'/custom-functions.php' );
		}
	}

	/**
		Add metadata
			@private
	**/
	private function metadata() {
		// Pages to apply custom fields
		$pages = array('post.php','post-new.php');
		// Check page
		if( !in_array(self::$vars['PAGENOW'],$pages) )
			return;
		// Load form and libraries
		require ( AIR_PATH . '/lib/air-form.php' );
		require ( AIR_PATH . '/lib/air-meta.php' );
		
	}

	/**
		Register sidebars
			@private
	**/
	private function register_sidebars() {
		$sidebars = AirControl::get('sidebars');
		if ( $sidebars ) {
			foreach($sidebars as $sidebar) {
				// Single Sidebar
				if ( !isset($sidebar['count']) ) {
					register_sidebar($sidebar);
				}
				// Multiple Sidebars
				if ( isset($sidebar['count']) ) {
					$count = $sidebar['count'];
					unset($sidebar['count']);
					register_sidebars($count,$sidebar);
				}
			}
		}
	}

	/**
		Widgets
			@private
	**/
	private function widgets() {
		$widgets = AirControl::get('widgets');
		$widget_path = get_template_directory().'/widgets/';
		if ( $widgets ) {
			foreach ( $widgets as $name=>$class ) {
				if ( is_file($widget_path.$name.'.php') ) {
					require ( $widget_path.$name.'.php' );
					$function = 'return register_widget("'. $class .'");';
					add_action('widgets_init',create_function('',$function));
				}
			}
		}
	}

	/**
		Theme actions
			@private
	**/
	private function actions() {
		// Setup theme : Text Domain, Features, Image Sizes
		add_action('after_setup_theme', array($this, 'action_setup_theme'));
		// Register styles and scripts
		add_action('wp_enqueue_scripts',
			array($this, 'action_register_styles_and_scripts'));
	}

	/**
		Setup theme
			@public
	**/
	function action_setup_theme() {
		// Text domain
		if ( isset(self::$config['text-domain']) )
			air_text_domain(self::$config['text-domain']);
		// Register nav menus
		if ( isset(self::$config['nav-menus']) )
			air_register_nav_menus(self::$config['nav-menus']);
		// Theme features
		if ( isset(self::$config['features']) )
			air_theme_features(self::$config['features']);
		// Image sizes
		if ( isset(self::$config['features']['post-thumbnails']) &&
				isset(self::$config['image-sizes']) )
			air_image_sizes(self::$config['image-sizes']);
	}

	/**
		Register styles and scripts
			@public
	**/
	function action_register_styles_and_scripts() {
		// Styles
		if ( isset(self::$config['styles']) )
			air_register_styles(self::$config['styles']);
		// Scripts
		if ( isset(self::$config['scripts']) )
			air_register_scripts(self::$config['scripts']);
	}

	/**
		Class constructor
			@param $boot bool
			@public
	**/
	function __construct($boot=FALSE) {
		if ( !$boot ) { return; }

		// Define Air Framework constants
		define ( 'AIR_PATH', get_template_directory() . '/air' );
		define ( 'AIR_URL', get_template_directory_uri() . '/air' );
		define ( 'AIR_ASSETS', AIR_URL . '/assets' );
		define ( 'AIR_MODULES', AIR_PATH . '/modules' );
	}

}

//! Bootstrap framework
return new Air(TRUE);
