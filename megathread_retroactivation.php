<?php
// set the PHP timelimit to 100 minutes
ini_set('max_execution_time',6000);
/*
*
* Constants
*
*/
define('PATH_TO_ROOT', realpath(dirname(__FILE__).'/../../../').DIRECTORY_SEPARATOR);
/*
*
* Plugin activation check
*
*/
if ( !is_plugin_active('megathread/megathread.php') ) exit();
/*
*
* Include necessary files
*
*/
require_once ES_PLUGIN_DIR.'megathread_data_handler.php';
require_once ES_PLUGIN_DIR.'megathread_buddypress_data_handler.php';


$response = NULL;
/*
*
* Synchronize users first
*
*/
if( megathread_synchornize_users() ) {
	$response.= " <br>Users have been synchornized successfully.";
} else {
	$response.=" <br>Error: Could not synchronize users, please try again. ";
}
/*
*
* Then Wordpress posts/pages
*
*/
if(megathread_synchornize_posts() ) {
	$response.= " <br>Posts have been synchornized successfully.";
} else {
	$response.=" <br>Error: Could not synchronize posts, please try again. ";
}
/*
*
* Then comments on these posts/pages
*
*/
if( megathread_synchornize_comments() ) {
	$response.= " <br>Comments have been synchornized successfully.";
} else {
	$response.=" <br>Error: Could not synchronize comments, please try again. ";
}
/*
*
* Lets synchronize BP as well
*
*/
$plugin_name = 'buddypress/bp-loader.php';
/*
*
* Plugin activation check
*
*/
if( is_plugin_active( $plugin_name ) ) {
	
	if( megathread_synchornize_buddypress () ) {
		$response.= " <br>Buddypress data have been synchornized successfully.";
	} else {
		$response.=" <br>Error: Could not synchronize buddypress data, please try again. ";
	}
}


echo $response;

die(); // this is required to return a proper result


/*******************************************************************************
*
*								FUNCTIONS
*
********************************************************************************/

/**
* Synchronize users of this application with megathread
*
* @since  	1.0.0
*
* @param	NULL
*
* @return 	Boolean   	True for success
*/
function megathread_synchornize_users() {
		
		global $wpdb;
		$users = $wpdb->get_results("SELECT ID from ".$wpdb->prefix."users WHERE megathread_user_id is NULL", ARRAY_A);
		
		foreach ( $users as $user ) {
			
			megathread_user_register( $user['ID'] );
		}
		return true;
}
/**
* Synchronize posts of this application with megathread
*
* @since  	1.0.0
*
* @param	NULL
*
* @return 	Boolean   	True for success
*/
	
	function megathread_synchornize_posts() {
		global $wpdb;
		$posts = $wpdb->get_results("SELECT ID from ".$wpdb->prefix."posts WHERE megathread_post_id is NULL", ARRAY_A);
		
		foreach ( $posts as $post ) {
			$wp_post_obj = get_post($post['ID']);
			es_save_post ( $post['ID'], $wp_post_obj );
		}

		return true;
	}

/**
* Synchronize comments of this application with megathread
*
* @since  	1.0.0
*
* @param	NULL
*
* @return 	Boolean   	True for success
*/

	function megathread_synchornize_comments() {
		global $wpdb;
		$comments = $wpdb->get_results("SELECT comment_ID from ".$wpdb->prefix."comments WHERE comment_approved = '1'
									AND megathread_comment_sended != '1'", ARRAY_A);

		foreach($comments as $comment) {
				es_new_comment ( $comment['comment_ID'], 1 );
		}
		return true;
	}

/**
* Synchronize BP of this application with megathread
*
* @since  	1.0.0
*
* @param	NULL
*
* @return 	Boolean   	True for success
*/

	function megathread_synchornize_buddypress()
	{
		global $wpdb, $bp;
		$activities = $wpdb->get_results( 'SELECT * FROM '.$bp->activity->table_name.' WHERE `megathread_activity_id`
		is NULL' );
		foreach($activities as $activity) {
			es_activity_data_handler($activity);
		}
		return true;
		
	}
	

?>
