<?php
/*
Plugin Name: Intravert
 
Description: This plugin allows you to integrate, display and sell native ad spaces
on your blog. Don’t rely on ad networks - sell your own ads and stay independent!
Version: 1.1
Author: Intravert
 
Stable tag: 1.1
*/

//error_reporting(E_ALL);
//ini_set('display_errors', 'On');


// core initiation
if( !class_Exists('vooMainStart') ){
	class vooMainStart{
		var $locale;
		function __construct( $locale, $includes, $path ){
			$this->locale = $locale;
			
			// include files
			foreach( $includes as $single_path ){
				include( $path.$single_path );				
			}
			// calling localization
			add_action('plugins_loaded', array( $this, 'myplugin_init' ) );
		}
		function myplugin_init() {
		 $plugin_dir = basename(dirname(__FILE__));
		 load_plugin_textdomain( $this->locale , false, $plugin_dir );
		}
	}
	
	
}


// initiate main class
new vooMainStart('wws', array(
	'modules/scripts.php',
	'modules/hooks.php',
	'modules/settings.php',
	'modules/shortcodes.php',
	'modules/widgets.php',
	
), dirname(__FILE__).'/' );

 
register_activation_hook(__FILE__, 'waa_activation');
function waa_activation() {
    if (! wp_next_scheduled ( 'waa_hourly_event' )) {
	wp_schedule_event(time(), 'hourly', 'my_hourly_event');
    }
}

add_action('waa_hourly_event', 'waa_do_this_hourly');
function waa_do_this_hourly() {
	// do something every hour
	$call = new adsAPI();
	$call->initiate_ads_array();
}

 
?>