<?php
/**
  * Plugin Name: Direct Mailer
  *Plugin URI: adiplugins.epizy.com
  *Description: Make email delivery easy from WordPress to any mailing website. It is easy to configure and supports many hosts. 
  *Text Domain: direct-mailer
  *Tested up to: 6.2
  *Tags: direct, direct mailer, easy smpt, smpt, easy
  *Requires PHP: 7.0
  *Stable tag: 4.3
  *Requires at least:5.8
  *Version: 1.0.0
  *Author: Aditya Pandey
  *Author URI: adiblogs.epizy.com
  *License: GPLv2 or later
**/


define( 'DIRECT_VERSION', '1.0.0' );
define( 'DIRECT__MINIMUM_WP_VERSION', '5.0' );
define( 'DIRECT__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once plugin_dir_path( __FILE__).'encryption.class.php';

//Plugin activation 
function direct_plugin_activate() {

	  $dir_path   = plugin_dir_path( __FILE__ );
    
    add_option( 'DIRECT_mail_data','' , '', 'yes' );
    add_option( 'direct_mailer_install_date', date('Y-m-d G:i:s'), '', 'yes');

}
register_activation_hook( __FILE__, 'direct_plugin_activate' );

//Plugin deactivation 
function direct_plugin_deactivation() {
	
	delete_option( 'DIRECT_mail_data' );
	delete_option( 'DIRECT_mail_flag' );

}
register_deactivation_hook( __FILE__, 'direct_plugin_deactivation' );



// Add settings link on plugin page
function direct_settings_link($links) { 
  $settings_link = "<a href='options-general.php?page=direct-mailer'>".__('Settings','direct-mailer')."</a>"; 
  array_unshift($links, $settings_link); 
  return $links; 
}

$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'direct_settings_link' );

//plugin load
function direct_plugin_load(){

	require_once plugin_dir_path(__FILE__).'settings.class.php';
	
	$dir_path  		 = plugin_dir_path( __FILE__ );

	DIRECT_settings::get_instance($dir_path);
	
}
add_action('plugins_loaded', 'direct_plugin_load');




add_action( 'phpmailer_init', 'direct_php_mailer' );
function direct_php_mailer( $phpmailer ) {

  global $direct_option;
  $option = $direct_option;

	// if( empty( $direct_option ) ) $option = get_option('direct_mail_data','');

	if ($option['encrypt'] == '1'){

		$option['host'] 	  = DIRECT_encryption::decrypt( $option['host'] );
		$option['username'] = DIRECT_encryption::decrypt($option['username'], SECURE_AUTH_KEY);
		$option['password']	= DIRECT_encryption::decrypt($option['password'], SECURE_AUTH_KEY);
	}

    $phpmailer->isSMTP();     
    $phpmailer->Host = $option['host'];
    $phpmailer->SMTPAuth = true;  
    $phpmailer->Port = $option['port'];
    $phpmailer->Username = $option['username'];
    $phpmailer->Password = $option['password'];

    // Additional settings…
    if( $option['SMTPSecure'] != 'none' )
      $phpmailer->SMTPSecure = $option['SMTPSecure']; 
    

    if( $option['From'] != '' )
      $phpmailer->From = $option['From'];
    
    if( $option['FromName'] != '' )
      $phpmailer->FromName = $option['FromName'];

      if( $option['Subject'] != '' )
      $phpmailer->Subject = $option['Subject'];

    unset( $direct_option );
  
}

add_filter('wp_mail_from', 'direct_mail_form');

function direct_mail_form( $from ){

  global $direct_option;
  
  $direct_option = get_option('DIRECT_mail_data','');

  if( $direct_option['From'] != '' ) 
    $from = $direct_option['From'];

  return $from;
}