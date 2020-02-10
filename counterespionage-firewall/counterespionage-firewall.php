<?php
/**
 * @package Counterespionage_Firewall
 * @version 1.0
 */
/*
Plugin Name: Counterespionage Firewall
Plugin URI: http://wordpress.org/extend/plugins/counterespionage-firewall
Description: CEF protects against reconnaissance by hackers and otherwise illegitimate traffic such as bots and scrapers. Increase performance, reduce fraud, thwart attacks, and serve your real customers. Tested on PHP 7.3.14. Note: WP-Cron needs to be enabled or the black and whitelist may grow indefinitely.
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

//check both black and white lists
function check_lists($ip){
	$list = get_option('fs_bw_list');
	if(is_array($list) and !empty($list)){
		if (array_key_exists($ip,$list)){
			return $list[$ip]["list_type"];
		}
	}
	return false;
}

function add_to_list($ip, $list_type){
	$list = get_option('fs_bw_list');
	$list[$ip] = array("list_type" => $list_type, "expire" => time() + 600); //setting expiration time for 10 mins into future
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

function blacklist_and_die($ip){
	add_to_list($ip, "black");
	wp_die();

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
			blacklist_and_die($ip);
		}
	}
}

function fs_receive_values($request) {
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
		$json = file_get_contents('php://input', FALSE, NULL, 0, 500); //limiting input to first 500 bytes to limit any attacks with huge values
		if ($json) {
			$input_json = json_decode($json, TRUE, 3);

	
			//Firefox private browsing check
			$ffp_key = "browser.firefox.private";
			if (array_key_exists($ffp_key, $input_json)){
				if ($input_json[$ffp_key] == true){
					blacklist_and_die($ip);
				}
			}
		}
	}
}

 
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

function activate(){
	add_option('fs_bw_list');
	update_option('fs_bw_list',array());
	register_uninstall_hook( __FILE__, 'uninstall' );
}

function deactivate(){
	delete_option('fs_bw_list');
}

function list_purge_cron_exec() {
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

function add_cron_interval( $schedules ) {
	$schedules['ten_minutes'] = array(
		'interval' => 600,
		'display'  => esc_html__( 'Every Ten Minutes' ),
	);

    return $schedules;
}

add_action( 'wp_enqueue_scripts', 'load_javascript' ); 
add_action( 'login_enqueue_scripts', 'load_javascript');
add_action( 'admin_enqueue_scripts', 'load_javascript');

add_action( 'rest_api_init', 'fs_register_floodspark_routes' );

add_action( 'init', 'validate' );

register_activation_hook( __FILE__, 'activate' );
register_deactivation_hook( __FILE__, 'deactivate' );

add_action( 'list_purge_cron_hook', 'list_purge_cron_exec' );
add_filter( 'cron_schedules', 'add_cron_interval' );
if ( ! wp_next_scheduled( 'list_purge_cron_hook' ) ) {
	wp_schedule_event( time(), 'ten_minutes', 'list_purge_cron_hook' );
}
?>
