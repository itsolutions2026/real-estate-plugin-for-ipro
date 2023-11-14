<?php

/**
 * Plugin Name:       Ipro
 * Plugin URI:        https://www.iprodemo.co.uk/
 * Description:       A simple version of Ipro Real Estate plugin for Wordpress.
 * Version:           1.0.0
 * Author:            Jasen
 * Author URI:        https://www.iprodemo.co.uk/
 * Text Domain:       es
 * License:           GPL2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'DS' ) ) {
	define( 'DS', DIRECTORY_SEPARATOR );
}

define( 'ES_FILE', __FILE__ );
define( 'ES_PLUGIN_PATH', dirname( ES_FILE ) );
define( 'ES_PLUGIN_URL', plugin_dir_url( ES_FILE ) );
define( 'ES_PLUGIN_INCLUDES', dirname( ES_FILE ) . DS . 'includes' . DS );
define( 'ES_PLUGIN_CLASSES', dirname( ES_FILE ) . DS . 'includes' . DS . 'classes' . DS );
define( 'ES_PLUGIN_BASENAME', plugin_basename( __FILE__) );
define( 'ESTATIK4', true );


require_once ES_PLUGIN_CLASSES . 'class-ipro.php';

Ipro::get_instance();
