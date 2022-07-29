<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       floodspark.com
 * @since      1.0.0
 *
 * @package    Counterespionage_Firewall
 * @subpackage Counterespionage_Firewall/admin/partials
 */
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
		        <div id="icon-themes" class="icon32"></div>  
		        <h2>Counterespionage Firewall Settings</h2>  
				<?php settings_errors(); ?>  
		        <form method="POST" action="options.php">  
		            <?php 
		                settings_fields( 'fs_cef_settings_page_general_settings' );
		                do_settings_sections( 'fs_cef_settings_page_general_settings' ); 
		            ?>             
		            <p style="font:italic;max-width: 700px;-">What is this? Some additional, optional insights require access to our backend. This is because some operations would consume a large amount of your server's resources (e.g. contextual checks on large databases, intensive computations, etc.) and slow your site down. We collect basic contextual data about your WordPress installation and detected malicious activity. We do not collect sensitive information such as passwords.</p>
		            <?php submit_button(); ?>  
		        </form> 
</div>