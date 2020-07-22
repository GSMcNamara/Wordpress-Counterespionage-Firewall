<?php
/**
 * @package Counterespionage_Firewall
 * @version 1.2.0
 */
/*
Plugin Name: Counterespionage Firewall
Plugin URI: http://wordpress.org/extend/plugins/counterespionage-firewall
Description: CEF protects against reconnaissance by hackers and otherwise illegitimate traffic such as bots and scrapers. Increase performance, reduce fraud, thwart attacks, and serve your real customers. Note: WP-Cron needs to be enabled or the deny and allow lists may grow indefinitely.
Author: Floodspark
Version: 1.2.0
Author URI: http://floodspark.com
*/

### Function: Get IP Address (http://stackoverflow.com/a/2031935)
function fs_cef_get_ip() {
	foreach ( array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {
		if ( array_key_exists( $key, $_SERVER ) === true ) {
			foreach ( explode( ',', $_SERVER[$key] ) as $ip ) {
				$ip = trim( $ip );
//comment the following filter_var code when testing locally / not on the Internet
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false ) {
					return esc_attr( $ip );
				}
//uncomment below if testing locally / not on Internet
//				return esc_attr($ip);
			}
		}
	}
}

function fs_die (){
	wp_die($message = 'Unauthorized device or behavior. Please revisit in ten minutes with a valid browser.', $title = 'Unauthorized', $args = ($response = 403));
}

//check both black and white lists
function fs_cef_check_lists($ip){
	$list = get_option('fs_bw_list');
	if(is_array($list) and !empty($list)){
		if (array_key_exists($ip,$list)){
			return $list[$ip]["list_type"];
		}
	}
	return false;
}

function fs_cef_add_to_list($ip, $list_type){
	$list = get_option('fs_bw_list');
	$list[$ip] = array("list_type" => $list_type, "expire" => time() + 600); //setting expiration time for 10 mins into future
	update_option('fs_bw_list',$list);
}

//user agent string validation method:
function fs_cef_check_ua(){
	$uas = $_SERVER['HTTP_USER_AGENT'];
	if($uas){
		if(preg_match('~(curl|wget)~i', $uas)) {
			return true;
		}
	}
	if ($uas == ''){
		return true;
	}
	return false;
}

//checking if request method is allowed (get,post,head)
function fs_cef_check_request_method(){
	$allowed_methods = array("get","post","head");
	$rm = strtolower($_SERVER['REQUEST_METHOD']);
	if(!in_array($rm, $allowed_methods)){
		return true;
	}
	return false;
}

function fs_cef_blacklist_and_die($ip){
	fs_cef_add_to_list($ip, "black");
	fs_die();
}

//route based on list check results; if not listed, subject to checks
function fs_cef_validate() {
	$ip = fs_cef_get_ip();
	if($ip == 'unknown' or is_null($ip)) {
		return;
	}
	$result = fs_cef_check_lists($ip);	
	if($result == "black"){
		fs_die();	
	}elseif($result == "white"){
		return;	
	}else{ //do validations
		if(fs_cef_check_ua()){
			fs_cef_blacklist_and_die($ip);
		}
		if(fs_cef_check_request_method()){
			fs_cef_blacklist_and_die($ip);
		}
	}
}

function fs_cef_receive_values($request) {
	$ip = fs_cef_get_ip();
	if($ip == 'unknown' or is_null($ip)) {
		return;
	}
	$result = fs_cef_check_lists($ip);	
	if($result == "black"){
		fs_die();	
	}elseif($result == "white"){
		return;	
	}else{ //do validations
		$json = file_get_contents('php://input', FALSE, NULL, 0, 500); //limiting input to first 500 bytes to limit any attacks with huge values
		if ($json) {
			$input_json = json_decode($json, TRUE, 3);

			//tor check
			if (array_key_exists("screen.height", $input_json) and array_key_exists("window.innerHeight", $input_json)){
				if ($input_json["screen.height"] == $input_json["window.innerHeight"]){
					fs_cef_blacklist_and_die($ip);
				}
			}
	
			//Chrome incognito check
			if (array_key_exists("storage", $input_json)){
				if ($input_json["storage"] < 120000000){
					fs_cef_blacklist_and_die($ip);
				}
			}

			//Firefox private browsing check
			$ffp_key = "browser.firefox.private";
			if (array_key_exists($ffp_key, $input_json)){
				if ($input_json[$ffp_key] == true){
					fs_cef_blacklist_and_die($ip);
				}
			}

			//Chrome Selenium check
			if (array_key_exists("navigator.webdriver", $input_json)){
				if ($input_json["navigator.webdriver"] == true){
					fs_cef_blacklist_and_die($ip);
				}
			}
		}
	}
}

 
function fs_cef_register_floodspark_routes() {
    // register_rest_route() handles more arguments but we are going to stick to the basics for now.
    register_rest_route( 'floodspark/v1/cef', '/validate', array(
            // By using this constant we ensure that when the WP_REST_Server changes, our create endpoints will work as intended.
            'methods'  => WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => 'fs_cef_receive_values',
    ) );
}

function fs_cef_load_javascript () {
	wp_enqueue_script( 'fs-js', plugin_dir_url( __FILE__ ) . 'js/fs.js');
}

function fs_cef_activate(){
	add_option('fs_bw_list');
	update_option('fs_bw_list',array());
	register_uninstall_hook( __FILE__, 'uninstall' );
}

function fs_cef_deactivate(){
	delete_option('fs_bw_list');
}

function fs_cef_list_purge_cron_exec() {
        $list = get_option('fs_bw_list');
        if(is_array($list) and !empty($list)){
		foreach ($list as $ip => $meta_data){
			$expire_time = $meta_data["expire"];
			if (time() >= $expire_time){
				unset($list[$ip]);
				update_option('fs_bw_list',$list);
			}
		}
        }
}

function fs_cef_add_cron_interval( $schedules ) {
	$schedules['ten_minutes'] = array(
		'interval' => 600,
		'display'  => esc_html__( 'Every Ten Minutes' ),
	);

    return $schedules;
}

function fs_filter_wp_headers( $headers ) {
	//here we replace the PHP header with the most current version
	if (function_exists('header_remove')) {
	    header_remove('X-Powered-By'); // PHP 5.3+
	} else {
	    @ini_set('expose_php', 'off');
	}
	$headers['X-Powered-By'] = 'PHP/7.4.8';

    return $headers;
}

add_action( 'wp_enqueue_scripts', 'fs_cef_load_javascript' ); 
add_action( 'login_enqueue_scripts', 'fs_cef_load_javascript');
add_action( 'admin_enqueue_scripts', 'fs_cef_load_javascript');

add_action( 'rest_api_init', 'fs_cef_register_floodspark_routes' );

add_action( 'init', 'fs_cef_validate' );

register_activation_hook( __FILE__, 'fs_cef_activate' );
register_deactivation_hook( __FILE__, 'fs_cef_deactivate' );

add_action( 'fs_cef_list_purge_cron_hook', 'fs_cef_list_purge_cron_exec' );
add_filter( 'cron_schedules', 'fs_cef_add_cron_interval' );
if ( ! wp_next_scheduled( 'fs_cef_list_purge_cron_hook' ) ) {
	wp_schedule_event( time(), 'ten_minutes', 'fs_cef_list_purge_cron_hook' );
}

add_filter( 'wp_headers', 'fs_filter_wp_headers' );

?>
