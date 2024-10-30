<?php
/*
Plugin Name: Megathread plugin
Plugin URI: http://megathread.com/
Description: Megathread plugin. Plugin supports BuddyPress and bbPress
Version: 1.0
Author: Megathread
Author URI: http://megathread.com/
*/

//restrict direct access
if ( !defined( 'ABSPATH' ) ) exit;
/*
*
* Constants
*
*/
define('ES_PLUGIN_DIR', WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.dirname(plugin_basename(__FILE__)).DIRECTORY_SEPARATOR);

/*
*
* hooks
*
*/
register_activation_hook(__FILE__, 'es_set_options'); 
register_deactivation_hook(__FILE__, 'es_unset_options');
//wordpress action hooks
add_action('wp_print_scripts', 'megathread_addscript');
add_action('wp_print_styles', 'megathread_addstyle');
add_action( 'admin_print_scripts', 'megathread_retroactivation_javascript', 100 );
add_action('wp_ajax_megathread_retroactivation', 'megathread_retroactivation_callback');

/**
* plugin activation
*
* @since  	1.0.0
*
* @param	NULL		
*
* @return 	NULL   	
*/
if ( !function_exists ( 'es_set_options' ) ) {
	
	function es_set_options() {
		
		global $wpdb;
		/*
		*
		* Set default options
		*
		*/
		add_option ( 'es_megathread_login' , '' );
		add_option ( 'es_megathread_unique_code', '' );
		add_option ( 'es_megathread_password', '' );
		add_option ( 'megathread_header_image','no' );
		add_option ( 'megathread_image_avator','no' );
		add_option ( 'megathread_discussion_avator','no' );
		add_option ( 'megathread_local_global_user',			'no' );
		add_option ( 'megathread_local_global_community',	'no' );
		add_option ( 'megathread_local_global_discussion',	'no' );
		add_option ( 'megathread_register_api',				'no' );
		add_option ( 'es_megathread_server_url', 'http://app.megathread.com/' );
		add_option ( 'megathread_widget_position','fixed' );
		add_option ( 'megathread_widget_css','clear: both;float: right;margin-left: 82%;margin-top: 20px;position: absolute;text-align: right;z-index: 1100;' );
		
		/*
		*
		* make necessary changes in database tables
		*
		*/
		$query = 'ALTER TABLE '.$wpdb->users.' 
					ADD `megathread_user_id` VARCHAR( 255 ) NULL DEFAULT NULL';
	
		$wpdb->query($query);
	
		$query = 'ALTER TABLE '.$wpdb->comments.'
					ADD COLUMN `megathread_comment_sended` ENUM(\'1\',\'0\') NOT NULL DEFAULT \'0\'';
	
		$wpdb->query($query);
	
		$query = 'ALTER TABLE '.$wpdb->posts.'
					ADD COLUMN `megathread_post_sended` ENUM(\'1\',\'0\') NOT NULL DEFAULT \'0\'';
	
		$wpdb->query($query);
	
		$query = 'ALTER TABLE '.$wpdb->posts.' ADD COLUMN `megathread_post_id` VARCHAR( 255 ) NULL DEFAULT NULL';
	
		$wpdb->query($query);
		/*
		*
		* Plugin activation check
		*
		*/
		$plugin_name = 'buddypress/bp-loader.php';
		if( is_plugin_active($plugin_name) ) {
			
			global $bp;
			$query = 'ALTER TABLE '.$bp->activity->table_name.' ADD COLUMN `megathread_activity_id` VARCHAR( 255 ) NULL DEFAULT NULL';
			$wpdb->query($query);
			
			$query = 'ALTER TABLE '.$bp->activity->table_name.'
				ADD COLUMN `megathread_post_sended` ENUM(\'1\',\'0\') NOT NULL DEFAULT \'0\'';
	
			$wpdb->query($query);
		}
		
	}
}

/**
* plugin deactivation
*
* @since  	1.0.0
*
* @param	NULL		
*
* @return 	NULL   	
*/
if ( !function_exists ( 'es_unset_options' ) ) {
	function es_unset_options() {
		//do nothing
	}
}

/**
* add stylesheet
*
* @since  	1.0.0
*
* @param	NULL		
*
* @return 	NULL   	
*/
if ( !function_exists ( 'megathread_addstyle' ) ) {
	function megathread_addstyle () {
			$megathread_plugin_url	 = plugins_url();
			wp_enqueue_style ( 'myscript', $megathread_plugin_url. '/megathread/css/tooltips.css' );
	}
}

/**
* add scripts
*
* @since  	1.0.0
*
* @param	NULL		
*
* @return 	NULL   	
*/
if ( !function_exists ( 'megathread_addscript' ) ) {
	function megathread_addscript () {
		
			$megathread_plugin_url = plugins_url();
			
			wp_enqueue_script( 'jquery' );
			
			wp_enqueue_script( 'myscript', $megathread_plugin_url. '/megathread/js/megathread.js');
	}
}



/**
* admin footer script
*
* @since  	1.0.0
*
* @param	NULL		
*
* @return 	NULL   	
*/
if ( !function_exists ( 'megathread_retroactivation_javascript' ) ) {
	function megathread_retroactivation_javascript() {
	?>
	<script type="text/javascript" >
	function StartSynchronization()
	{
		var data = {
			action: 'megathread_retroactivation'
		};
		jQuery('#syn_msg').html(' Synchronization in process...');
		jQuery.post(ajaxurl, data, function(response) {
			jQuery('#syn_msg').html(response);
		});
		
	}
	
	</script>
	<?php
	}
}


/**
* megathread retroactivation callback
*
* @since  	1.0.0
*
* @param	NULL		
*
* @return 	NULL   	
*/
if ( !function_exists ('megathread_retroactivation_callback') ) {
	function megathread_retroactivation_callback () {
		require_once ES_PLUGIN_DIR.'megathread_retroactivation.php';
	}
}



//if admin logged in. Add settings page
if (is_admin())
	require_once ES_PLUGIN_DIR.'megathread_settings.php';

//include megathread core file. 
require_once ES_PLUGIN_DIR.'megathread_data_handler.php';

?>
