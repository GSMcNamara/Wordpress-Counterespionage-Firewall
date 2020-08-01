<?php
/**
 * @package Counterespionage_Firewall
 * @version 1.3.0
 */
/*
Plugin Name: Counterespionage Firewall
Plugin URI: http://wordpress.org/extend/plugins/counterespionage-firewall
Description: CEF protects against reconnaissance by hackers and otherwise illegitimate traffic such as bots and scrapers. Increase performance, reduce fraud, thwart attacks, and serve your real customers. Note: WP-Cron needs to be enabled or the deny and allow lists may grow indefinitely.
Author: Floodspark
Version: 1.3.0
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

	add_option('fs_username_aliases');
	#generate aliases here, and pad by a few more
	#TODO: figure out how to intercept 404 response to JSON API for non-existent user
	$username_aliases = array();
    $users = get_users();

    foreach($users as $user) {
    	$username_aliases[$user->ID] = ['username_alias' => fs_generate_username_alias()];
	}
	update_option('fs_username_aliases',$username_aliases);

	register_uninstall_hook( __FILE__, 'uninstall' );
}

function fs_cef_deactivate(){
	delete_option('fs_bw_list');
	delete_option('fs_username_aliases');
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

function fs_generate_username_alias(){
	return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 8);
}

function fs_mask_username_rest_prepare_user( WP_REST_Response $response, WP_User $user, WP_REST_Request $request ){

    $data = $response->get_data();

    #here need to check if a logged in user. If so, exist w/o masking.

    $original_slug = $data['slug']; 

    $username_aliases = get_option('fs_username_aliases');

    if(is_array($username_aliases) and !empty($username_aliases)){
		if (array_key_exists($data['id'],$username_aliases) and array_key_exists("username_alias", $username_aliases[$data['id']])){
			$username_alias = $username_aliases[$data['id']]["username_alias"];
		} else { 
			#something went wrong and set a default value for username_aliases for this iteration
			# or the probe was for a non-existent user
			# or parity was not maintained between alias list and real users, and the user ID does exist but not in our alias list (yet)
			$username_alias = fs_generate_username_alias();
		}
	}else { #TODO: should probably do a try-except here instead
			#something went wrong and set a default value for username_aliases for this iteration
			# or the probe was for a non-existent user
			# or parity was not maintained between alias list and real users, and the user ID does exist but not in our alias list (yet)
			$username_alias = fs_generate_username_alias();
	}

    $new_slug = $username_alias;
    $data['slug'] = $new_slug;
    $original_link = $data['link'];
    #https://stackoverflow.com/a/7791665
    $new_link = preg_replace('~' . $original_slug . '(?!.*' . $original_slug . ')~', $new_slug, $original_link);
    $data['link'] = $new_link;

    #check if the user's name is also the same as their slug. If so, also mask that.
    if ($data['name'] == $original_slug) {
    	$data['name'] = $new_slug;
    }

    $response->set_data( $data );

    return $response;
}

# https://wordpress.stackexchange.com/a/90516
function fs_get_user_id_by_display_name( $display_name ) {
    global $wpdb;

    if ( ! $user = $wpdb->get_row( $wpdb->prepare(
        "SELECT `ID` FROM $wpdb->users WHERE `display_name` = %s", $display_name
    ) ) )
        return false;

    return $user->ID;
}

function fs_filter_the_author( $display_name ) {

    // $display_name === string $authordata->display_name

	if (!is_user_logged_in()){
		$author_id = fs_get_user_id_by_display_name($display_name);
		$author_id = intval($author_id);
		$username_aliases = get_option('fs_username_aliases');
		$username_alias = $username_aliases[$author_id]["username_alias"];

	    return $username_alias;
	}else{
		return $display_name;
	}
}


function fs_filter_wp_redirect( $location, $status ) { 
    if ($status == 301){
	    if(preg_match('/\/author\//', $location)){ #then likely it's a response to a /?author=x request"
	    	$url_split_position = strpos($location, '/author/');
	    	$url_part_1 = substr($location, 0, $url_split_position + 8);
	    	$username = substr($location, $url_split_position + 8);
	    	$username = rtrim($username, '/');
	    	$user_id = get_user_by('login',$username);
	    	$user_id = $user_id->ID;
			$username_aliases = get_option('fs_username_aliases');
			$username_alias = $username_aliases[$user_id]["username_alias"];

			$location = $url_part_1 . $username_alias . "/";
	    }
	}
    return $location; 
}

add_filter( 'wp_redirect', 'fs_filter_wp_redirect', 10, 2 ); 

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


add_filter( 'rest_prepare_user', 'fs_mask_username_rest_prepare_user', 10, 3 );
add_filter( 'wp_headers', 'fs_filter_wp_headers' );

add_filter( 'the_author', 'fs_filter_the_author', 10, 1 );

?>
