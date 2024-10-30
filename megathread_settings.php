<?php
//restrict direct access
if ( !defined( 'ABSPATH' ) ) exit;
/*
*
* Constants
*
*/
define('PARENT_ADMIN_PAGE', 'options-general.php');
define('PLUGIN_ADMIN_PAGE', 'megathread-admin');
define('PLUGIN_TITLE', 'Megathread');
/*
*
* perform this action on initialization
*
*/
add_action('init', 'es_init_locale', 98);

/**
* 1-2-3 initialized
*
* @since  	1.0.0
*
* @param	NULL		
*
* @return 	NULL   	
*/
if ( !function_exists ( 'es_init_locale' ) ) {
	
	function es_init_locale() {
		$locale 	= get_locale();
		$mo_file 	= ES_PLUGIN_DIR.'locale'.DIRECTORY_SEPARATOR.$locale.'.mo';
	
		load_textdomain( 'es_locale', $mo_file);
	}
}
/*
*
*  save setting in wordpress options
*
*/
if (isset ($_POST['action']) && $_POST['action'] == 'savedata') {
	
	$es_errors = array();

	$megathread_login 			= (string) $_POST['megathread_login'];
	$megathread_forum_id 		= (string) $_POST['megathread_unique_code'];
	$megathread_server_url 		= (string) $_POST['megathread_server_url'];
	$megathread_password1 		= (string) $_POST['pass1'];
	$megathread_password2 		= (string) $_POST['pass2'];
	/*
	*
	*  Validation
	*
	*/
	if ( !strlen ( trim ( $megathread_login ) ) )
		$es_errors[] 			= __('Empty login', 'es_locale');
		
	if ( !strlen ( trim ( $megathread_forum_id ) ) )
		$es_errors[] 			= __('Empty forum ID', 'es_locale');
		
	if (! strlen ( trim ( $megathread_server_url ) ) )
		$es_errors[] 			= __('Empty server API URL', 'es_locale');
		
	if ( !strlen ( trim ( $megathread_password1 ) ) || !strlen ( trim ( $megathread_password2 ) ) )
		$es_errors[] 			= __('Empty password', 'es_locale');
		
	if ( $megathread_password1 !== $megathread_password2 )
		$es_errors[] 			= __('Passwords do not match', 'es_locale');
	/*
	*
	*  No errors
	*
	*/
	if ( !count($es_errors) ) {
		update_option( 'es_megathread_login', $megathread_login );
		update_option( 'es_megathread_unique_code', $megathread_forum_id) ;
		update_option( 'es_megathread_password', $megathread_password1 );
		update_option( 'es_megathread_server_url', $megathread_server_url );
		header('Location: '.admin_url(PARENT_ADMIN_PAGE.'?page='.PLUGIN_ADMIN_PAGE.'&update=1'));
		exit();
	}
	
} else if (isset ($_POST['action']) && $_POST['action'] == 'updateoptions') {
		megathreatd_update_options( 'megathread_header_image',				$_POST['megathread_header_image'] );
		megathreatd_update_options( 'megathread_image_avator',				$_POST['megathread_image_avator'] );
		megathreatd_update_options( 'megathread_discussion_avator',			$_POST['megathread_discussion_avator'] );
		megathreatd_update_options( 'megathread_local_global_user',			$_POST['megathread_local_global_user'] );
		megathreatd_update_options( 'megathread_local_global_community',	$_POST['megathread_local_global_community'] );
		megathreatd_update_options( 'megathread_local_global_discussion',	$_POST['megathread_local_global_discussion'] );
		megathreatd_update_options( 'megathread_register_api',				$_POST['megathread_register_api'] );
		megathreatd_update_options( 'megathread_widget_position',			$_POST['megathread_widget_position'] );
		megathreatd_update_options( 'megathread_widget_css',				$_POST['megathread_widget_css'] );
		
		
		$es_messages[] = __('Options have been updated successfully', 'es_locale');
}

if (isset ($_GET['update']) && $_GET['update'] == 1) {
	
	$es_messages 	= array();
	$es_messages[] 	= __('Data has been updated', 'es_locale');
	
}

/**
* fucntion to update option value
*
* @since  	1.0.0
*
* @param	String		Name of option
*
* @param	String 		New value of option
*
* @return 	NULL   	
*/

	function megathreatd_update_options ( $option_name, $newvalue ) {
		
		if ( get_option( $option_name ) != $newvalue ) {
			update_option ( $option_name, $newvalue );
		} else {
			$deprecated 	= ' ';
			$autoload 		= 'no';
			add_option ( $option_name, $newvalue, $deprecated, $autoload );
		}
	}

/*
*
* Add action
*
*/
add_action('admin_menu', 'es_add_admin_menu');
/**
* add megathread link to admin menu
*
* @since  	1.0.0
*
* @param	NULL		
*
* @return 	NULL   	
*/
if ( !function_exists ( 'es_add_admin_menu' ) ) {
	function es_add_admin_menu() {
		add_submenu_page( PARENT_ADMIN_PAGE, PLUGIN_TITLE , PLUGIN_TITLE, 10, PLUGIN_ADMIN_PAGE , 'es_manage_menu');
	}
}
/**
* function to handle wp-admin side functionality of megathread
*
* @since  	1.0.0
*
* @param	NULL		
*
* @return 	NULL   	
*/
if ( !function_exists ( 'es_manage_menu' ) ) {
	function es_manage_menu() {
		global $es_errors, $es_messages;
	
		if ( isset ( $es_errors ) ) {
			
			$login 			= isset($_POST['megathread_login']) ? $_POST['megathread_login'] : get_option('es_megathread_login');
			
			$unique_code 	= isset($_POST['megathread_unique_code']) ? $_POST['megathread_unique_code'] 
								: get_option('es_megathread_unique_code');
								
			$server_url 	= isset($_POST['megathread_server_url']) ? $_POST['megathread_server_url'] 
								: get_option('es_megathread_server_url');
		}
		else {
			
			$login 			= get_option('es_megathread_login');
			$unique_code 	= get_option('es_megathread_unique_code');
			$server_url 	= get_option('es_megathread_server_url');
		}
		
		wp_enqueue_script( 'jquery' );
		require_once ES_PLUGIN_DIR.'megathread_settings_template.html';
	}
}
?>
