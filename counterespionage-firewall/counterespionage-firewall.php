<?php
/**
 * @package Counterespionage_Firewall
 * @version 1.0
 */
/*
Plugin Name: Counterespionage Firewall
Plugin URI: http://wordpress.org/extend/plugins/counterespionage-firewall
Description: CEF protects against reconnaissance by hackers and otherwise illegitimate traffic such as bots and scrapers. Increase performance, reduce fraud, thwart attacks, and serve your real customers.
Author: Floodspark
Version: 1.0
Author URI: http://floodspark.com
*/

//user agent string validation method:
function check_ua(){
	$uas = $_SERVER['HTTP_USER_AGENT'];
	if(strpos($uas, "curl") !== false){
		wp_die();
	}
}

//perform all inline validations within:
function validate() {
	check_ua();
}

/**
 * This is our callback function to return a single product.
 *
 * @param WP_REST_Request $request This function accepts a rest request to process data.
 */
function fs_receive_values($request) {
    
	return rest_ensure_response("all ok");
}

 
/**
 * This function is where we register our routes for our endpoint.
 */
function fs_register_floodspark_routes() {
    // register_rest_route() handles more arguments but we are going to stick to the basics for now.
    register_rest_route( 'floodspark/v1/cef', '/validate', array(
            // By using this constant we ensure that when the WP_REST_Server changes, our create endpoints will work as intended.
            'methods'  => WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => 'fs_receive_values',
    ) );
}

function load_javascript () {
	wp_enqueue_script( 'fs-js', plugin_dir_url( __FILE__ ) . 'js/fs.js');
}

add_action( 'wp_enqueue_scripts', 'load_javascript' ); 
add_action( 'rest_api_init', 'fs_register_floodspark_routes' );
add_action( 'init', 'validate' );

?>
