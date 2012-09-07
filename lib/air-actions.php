<?php

/**
	Admin bar menu
	- Adds items to admin bar menu
**/
function air_admin_bar_menu($admin_bar) {
	if (!is_super_admin() || !is_admin_bar_showing())  
        return;

	// Theme name menu item
	$admin_bar->add_node(
		array(
			'id'	=> 'wpbandit',
			'title'	=> '<span class="ab-icon"></span>'.
				'<span class="ab-label">'.AirControl::get('theme-name').'</span>',
			'href'	=> admin_url('/admin.php?page=theme-options')
		)
	);

	// Theme options submenu item
	$admin_bar->add_node(
		array(
			'id'		=> 'wpbandit-options',
			'title'		=> 'Theme Options',
			'href'		=> admin_url('/admin.php?page=theme-options'),
			'parent'	=> 'wpbandit'
		)
	);

	// Theme modules submenu item
	$admin_bar->add_node(
		array(
			'id'		=> 'wpbandit-modules',
			'title'		=> 'Theme Modules',
			'href'		=> admin_url('/admin.php?page=theme-modules'),
			'parent'	=> 'wpbandit'
		)
	);
}

/**
	Admin head
	- Adds CSS to support icon in admin bar
	- Adds CSS to move admin bar to bottom of page
**/
function air_admin_bar() {
	// Do not display if admin bar not showing
	if ( !is_admin_bar_showing() )
		return;

	// Start style output
	$output = '<style>';
 
	// Move admin bar to bottom
	if ( AirControl::get('admin-bar-bottom') && !is_admin() )
		$output .= '
* html body { margin-top: 0 !important; }
body.admin-bar { margin-top: -28px; padding-bottom: 28px; }
body.wp-admin #footer { padding-bottom: 28px; }
#wpadminbar { top: auto !important; bottom: 0; }
#wpadminbar .quicklinks .ab-sub-wrapper { bottom: 28px; }
#wpadminbar .quicklinks .ab-sub-wrapper ul .ab-sub-wrapper { bottom: -7px; }
';

	// Set admin bar icon style
	$output .= '
#wpadminbar #wp-admin-bar-wpbandit .ab-icon { background: url(' . AIR_ASSETS . '/img/adminbar-icon.png); }
#wpadminbar #wp-admin-bar-wpbandit.menupop.hover .ab-icon { background-position: 0 -16px; }
';
 
	// End style output
	$output .= '</style>'."\n";
 
	// Print styles
	echo $output;
}

/**
	Image sizes
	- Add custom image sizes
	- action : after_setup_theme | air->action_setup_theme
**/
function air_image_sizes($image_sizes) {
	foreach ( $image_sizes as $size ) {
		if ( !isset($size['crop']) ) { $size['crop'] = FALSE; }
		extract($size);
		add_image_size($name,$width,$height,$crop);
	}
}

/**
	Register nav menus
	- action : after_setup_theme | air->action_setup_theme
**/
function air_register_nav_menus($menus) {
	register_nav_menus($menus);
}

/**
	Register styles
	- Register styles for use in theme
	- action : wp_enqueue_scripts | air->action_register_styles_and_scripts
**/
function air_register_styles($styles) {
	// Style Defaults
	$defaults = array(
		'deps'		=> FALSE,
		'ver'		=> '1.0',
		'media'		=> 'all'
	);

	// Loop through styles and register
	foreach($styles as $style) {
		// Parse $style and merge with $defaults
		extract(wp_parse_args($style,$defaults));
		// Register style
		wp_register_style($handle,$src,$deps,$ver,$media);
	}
}

/**
	Register scripts
	- Register scripts for use in theme
	- action : wp_enqueue_scripts | air->action_register_styles_and_scripts
**/
function air_register_scripts($scripts) {
	// Script Defaults
	$defaults = array(
		'deps'		=> FALSE,
		'ver'		=> '1.0',
		'footer'	=> FALSE
	);

	// Loop through scripts and register
	foreach($scripts as $script) {
		// Parse $script and merge with $defaults
		extract(wp_parse_args($script,$defaults));
		// Register script
		wp_register_script($handle,$src,$deps,$ver,$footer);
	}
}

/**
	Text domain
	- Load theme's text domain
	- action : after_setup_theme | air->action_setup_theme
**/
function air_text_domain($domain) {
	load_theme_textdomain($domain, get_template_directory().'/languages');
}

/**
	Theme features
	- Enable configured theme features
	- action : after_setup_theme | air->action_setup_theme
**/
function air_theme_features($features) {
	foreach($features as $key=>$value) {
		if ( $value && is_bool($value) ) {
			add_theme_support($key);
		} elseif ( $value && is_array($value) ) {
			add_theme_support($key, $value);
		}
	}
}
