<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://floodspark.com
 * @since      1.0.0
 *
 * @package    Counterespionage_Firewall
 * @subpackage Counterespionage_Firewall/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Counterespionage_Firewall
 * @subpackage Counterespionage_Firewall/admin
 * @author     GS McNamara <gs@floodspark.com>
 */
class Counterespionage_Firewall_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action('admin_menu', array( $this, 'fs_cef_addPluginAdminMenu' ), 9);   
		add_action('admin_init', array( $this, 'fs_cef_registerAndBuildFields' )); 

	}

	public function fs_cef_addPluginAdminMenu() {
		//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page(  'Counterespionage Firewall Settings', 'Firewall Settings', 'administrator', $this->plugin_name, array( $this, 'fs_cef_displayPluginAdminDashboard' ), 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDIwMDEwOTA0Ly9FTiIKICJodHRwOi8vd3d3LnczLm9yZy9UUi8yMDAxL1JFQy1TVkctMjAwMTA5MDQvRFREL3N2ZzEwLmR0ZCI+CjxzdmcgdmVyc2lvbj0iMS4wIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiB3aWR0aD0iMjU2LjAwMDAwMHB0IiBoZWlnaHQ9IjI1Ni4wMDAwMDBwdCIgdmlld0JveD0iMCAwIDI1Ni4wMDAwMDAgMjU2LjAwMDAwMCIKIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIG1lZXQiPgoKPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMC4wMDAwMDAsMjU2LjAwMDAwMCkgc2NhbGUoMC4xMDAwMDAsLTAuMTAwMDAwKSIKZmlsbD0iIzAwMDAwMCIgc3Ryb2tlPSJub25lIj4KPHBhdGggZD0iTTQ4MCAxMjkwIGwwIC03NDAgMTI1IDAgMTI1IDAgMCAzMzAgMCAzMzAgMjQwIDAgYzEzMiAwIDI0MCAyIDI0MCA0CjAgMiA3IDQwIDE1IDgzIDggNDMgMTUgODQgMTUgOTEgMCA5IC01OCAxMiAtMjU1IDEyIGwtMjU1IDAgMCAyMjAgMCAyMjAgMzEwCjAgMzEwIDAgMCA5NSAwIDk1IC00MzUgMCAtNDM1IDAgMCAtNzQweiIvPgo8cGF0aCBkPSJNMTYwNSAxNjQ3IGMtNzMgLTI1IC0xMjIgLTU2IC0xNjggLTEwNyAtNTcgLTY0IC04MSAtMTMyIC03NCAtMjA5CjExIC0xMjggNzkgLTIxNSAyMTcgLTI4MSAxNjUgLTc4IDE5NSAtOTYgMjI1IC0xMzMgMjUgLTMyIDMwIC00OCAzMCAtOTIgMAotNjkgLTI2IC05OSAtOTkgLTExNiAtNjQgLTE0IC0xNDIgMyAtMjU5IDU4IC00MiAyMCAtNzkgMzQgLTgxIDMyIC0zIC0zIC0yMQotNDIgLTQwIC04NyBsLTM2IC04MyA1OCAtMjYgYzMxIC0xNCAxMDAgLTM2IDE1MiAtNTAgNzkgLTIwIDExMiAtMjQgMTk3IC0yMAo5MCAzIDExMCA3IDE2NyAzNiA3NyAzOCAxNDcgMTExIDE3MCAxNzggMjQgNzEgMjEgMTc3IC04IDIzOCAtMjkgNjMgLTk2IDEyMwotMTc4IDE2MSAtMTYwIDc0IC0yMDIgOTkgLTIzNSAxNDIgLTI3IDM1IC0zMyA1MyAtMzMgOTEgMCAxMTMgMTE2IDEzNyAzMTIgNjQKMzggLTE0IDcwIC0yNCA3MiAtMjIgMTcgMjMgNjUgMTU0IDU5IDE2MCAtNCA0IC01MSAyMiAtMTAzIDQwIC03OCAyNiAtMTE0IDMzCi0yMDAgMzUgLTY5IDMgLTExOSAtMSAtMTQ1IC05eiIvPgo8L2c+Cjwvc3ZnPgo=', 26 );
		
		//add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		//add_submenu_page( $this->plugin_name, 'Counterespionage Firewall Settings', 'Settings', 'administrator', $this->plugin_name.'-settings', array( $this, 'fs_cef_displayPluginAdminSettings' ));
	}

	public function fs_cef_displayPluginAdminDashboard() {
		require_once 'partials/'.$this->plugin_name.'-admin-settings-display.php';
    }

	public function fs_cef_displayPluginAdminSettings() {
		// set this var to be used in the settings-display view
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
		if(isset($_GET['error_message'])){
				add_action('admin_notices', array($this,'fs_cef_settingsPageSettingsMessages'));
				do_action( 'admin_notices', $_GET['error_message'] );
		}
		require_once 'partials/'.$this->plugin_name.'-admin-settings-display.php';
	}

	public function fs_cef_settingsPageSettingsMessages($error_message){
		switch ($error_message) {
				case '1':
						$message = __( 'There was an error adding this setting. Please try again.  If this persists, shoot us an email.', 'gs@floodspark.com' );                 
						$err_code = esc_attr( 'fs_cef_settings' );                 
						$setting_field = 'fs_cef_settings';                 
						break;
		}
		$type = 'error';
		add_settings_error(
					$setting_field,
					$err_code,
					$message,
					$type
			);
	}

	public function fs_cef_registerAndBuildFields() {
			/**
		 * First, we add_settings_section. This is necessary since all future settings must belong to one.
		 * Second, add_settings_field
		 * Third, register_setting
		 */     
		add_settings_section(
			// ID used to identify this section and with which to register options
			'fs_cef_settings_page_general_section', 
			// Title to be displayed on the administration page
			'',  
			// Callback used to render the description of the section
				array( $this, 'fs_cef_settings_page_display_general_account' ),    
			// Page on which to add this section of options
			'fs_cef_settings_page_general_settings'                   
		);
		unset($args);
		$args = array (
							'type'      => '',
							'subtype'   => 'text',
							'id'    => 'fs_cef_settings',
							'name'      => 'fs_cef_settings',
							'required' => 'true',
							'get_options_list' => '',
							'value_type'=>'normal',
							'wp_data' => 'option'
					);
		add_settings_field(
			'fs_cef_settings',
			'Enhanced Protection',
			array( $this, 'fs_cef_settings_page_render_settings_field' ),
			'fs_cef_settings_page_general_settings',
			'fs_cef_settings_page_general_section',
			$args
		);


		register_setting(
						'fs_cef_settings_page_general_settings',
						'fs_cef_settings'
						);

	}

	public function fs_cef_settings_page_display_general_account() {
		echo '<p>Customize Floodspark Counterespionage Firewall Defenses.</p>';
	} 

	public function fs_cef_settings_page_render_settings_field($args) {
	$options = get_option( 'fs_cef_settings' );

    $html = '<input title="Check this box to get global intelligence from the Floodspark Counterespionage Firewall threat database" type="checkbox" id="fs_cef_settings_backend_access_permission_checkbox" name="fs_cef_settings[backend_access_permission]" value="1"' . checked( 1, $options['backend_access_permission'], false ) . '/>';
    $html .= '<label for="fs_cef_settings_backend_access_permission_checkbox">Use the Counterespionage Firewall threat database</label>';

    echo $html;
	}
}