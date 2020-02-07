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

### Function: Get IP Address (http://stackoverflow.com/a/2031935)
function get_ip() {
	foreach ( array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {
		if ( array_key_exists( $key, $_SERVER ) === true ) {
			foreach ( explode( ',', $_SERVER[$key] ) as $ip ) {
				$ip = trim( $ip );
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false ) {
					return esc_attr( $ip );
				}
				return esc_attr($ip);
			}
		}
	}
}

//check both black and white lists
function check_lists($ip){
	$list = get_option('fs_bw_list');
	if(is_array($list) and !empty($list)){
		return $list[$ip]["list_type"];
	}
	return false;
}

function add_to_list($ip, $list_type){
	$list = get_option('fs_bw_list');
	$list[$ip] = array("list_type" => $list_type, "expire" => date("m/d/Y h:i:s a", time() + 600)); //setting expiration time for 10 mins into future
	update_option('fs_bw_list',$list);
}

//user agent string validation method:
function check_ua(){
	$uas = $_SERVER['HTTP_USER_AGENT'];
	if($uas){
		if(strpos($uas, "curl") !== false){
			return true;
		}
	}
	return false;
}

//route based on list check results; if not listed, subject to checks
function validate() {
	$ip = get_ip();
	if($ip == 'unknown' or is_null($ip)) {
		return;
	}
	$result = check_lists($ip);	
	if($result == "black"){
		wp_die();	
	}elseif($result == "white"){
		return;	
	}else{ //do validations
		if(check_ua()){
			add_to_list($ip, "black");
			wp_die();
		}
	}
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

function uninstall(){
	delete_option('fs_bw_list');
}

function activate(){
	add_option('fs_bw_list');
	update_option('fs_bw_list',array());
	register_uninstall_hook( __FILE__, 'uninstall' );
}

function deactivate(){
//	delete_option('fs_bw_list');
}

add_action( 'wp_enqueue_scripts', 'load_javascript' ); 
add_action( 'rest_api_init', 'fs_register_floodspark_routes' );
add_action( 'init', 'validate' );
register_activation_hook( __FILE__, 'activate' );
register_deactivation_hook( __FILE__, 'deactivate' );
?>
