<?php
//restrict direct access
if ( !defined( 'ABSPATH' ) ) exit;
/*
*
* Add actions
*
*/
add_action( 'bp_activity_after_save' , 'es_activity_data_handler' , 1 , 1 );
add_action( 'groups_new_forum_topic', 'es_new_forum_topic', 1, 2 );
add_action( 'groups_new_forum_topic_post', 'es_new_forum_topic_post', 1, 2 );
add_filter( 'bp_get_activity_content_body' , 'es_bp_activity_content_body' );
add_filter( 'bp_get_group_name' , 'es_bp_get_group_name' );
/*
*
* Constants
*
*/
define( 'ACTIVITY_PREFIX' , 'a_' );
define( 'FORUM_PREFIX' , 'f_' );
/**
 * Get buddypress group name
 *
 * @since  	1.0.0
 *
 * @param	Array	Array of arguments
 *
 * @return String   String containing group name and megathread iframe
 */
if ( !function_exists ( 'es_bp_get_group_name' ) ) {
	function es_bp_get_group_name($args) {
		global $groups_template;
	
		if ( empty( $group ) )
			$group = &$groups_template->group;
	
		$content = "<span class='iframe' isrc='".get_option('es_megathread_server_url')."'>".$group->name."</span>";
		return $content;
	}
}
/**
 * Get buddypress activity content
 *
 * @since  	1.0.0
 *
 * @param	Array	Array of arguments
 *
 * @return String   String containing group name and megathread iframe
 */
if (!function_exists ( 'es_bp_activity_content_body' ) ) {
	function es_bp_activity_content_body($args) {
		
		global $activities_template;
		global $wpdb, $bp;
		
		$activity_id = $wpdb->get_var( "SELECT megathread_activity_id FROM 
				 ".$bp->activity->table_name. " WHERE id=".$activities_template->activity->id);
		if( !is_null( $activity_id ) ) {
			$content = 	 $activities_template->activity->content;
			$content.= ' <span isrc="'.get_option('es_megathread_server_url').'thread_sm/'.$activity_id.'/1/yes" class="tooltip">';
			$content.= ' <span></span>	<iframe width="280px" scrolling="no" height="320px" frameborder="0" ';
			$content.= ' src="'.get_option('es_megathread_server_url').'thread_sm/'.$activity_id.'/1/yes"></iframe></span>';
		} else {
			echo $activities_template->activity->content;
		}
		echo $content;
	
			
	}
}
/**
 * listener and performer for activities made in buddypress
 *
 * @since  	1.0.0
 *
 * @param	Array	Array of data
 *
 * @return 	NULL   	Returns nothing / another function call to send data to server
 */
//if( !function_exists ( 'es_activity_data_handler' ) ) {
	
	function es_activity_data_handler( $data ) {
		global $wpdb, $bp;
	
		$type 							= $data->type;

		switch ($type) {
			case 'activity_comment':
	
				$thread_activity_id 	= $data->item_id;
				$parent_id 				= $data->secondary_item_id;
	
				$query 						= $wpdb->prepare( 'SELECT type, secondary_item_id, 
											primary_link FROM '.$bp->activity->table_name.' WHERE id = %d LIMIT 1', $thread_activity_id
											);
	
				$thread_info 			= $wpdb->get_results ( $query, ARRAY_A) ;
				$thread_secondary_id 	= $thread_info[0]['secondary_item_id'];
				$thread_type 			= $thread_info[0]['type'];
				$primary_link 			= $thread_info[0]['primary_link'];
				
				if ($thread_type == 'activity_update')
					$thread_id 			= $thread_activity_id;
				else
					$thread_id 			= es_get_thread_id ( $thread_type, $thread_secondary_id );
	
				$thread_url 			= '';
				$thread_url 			= es_get_thread_url ( $thread_type, $thread_id, $primary_link );
	
				$id_prefix 				= es_get_id_prefix ( $thread_type );
				$thread_id 				= $id_prefix.$thread_id;
	
				if ($parent_id == $thread_activity_id ) {
					
					if ( $thread_type == 'new_forum_post' || $thread_type == 'new_blog_post' )
						$parent_id 		= $id_prefix.$thread_secondary_id;
					else
						$parent_id 		= $thread_id;
				}
				else
					$parent_id 			= ACTIVITY_PREFIX.$parent_id;
	
				if ( (int) $thread_id )
					$thread_id 			= (int) $thread_id;
					
				if ( (int) $parent_id )
					$parent_id 			= (int) $parent_id;
				$user_info = get_userdata(intval($data->user_id));
				$data_to_send 			= array(
					'thread_id' 		=> $thread_id,
					'message_id' 		=> ACTIVITY_PREFIX.$data->id,
					'parent_id' 		=> $parent_id,
					'thread_url' 		=> $thread_url,
					'subject' 			=> '',
					'message' 			=> $data->content,
					'user_id' 			=> es_get_user_id ( $data->user_id ),
					'user_email' 		=> $user_info ->user_email
				);
				
				break;
			case 'activity_update':
				$user_info 				= get_userdata (intval ( $data->user_id ) );
				$data_to_send 			= array(
					'thread_id' 		=> ACTIVITY_PREFIX.$data->id,
					'message_id' 		=> ACTIVITY_PREFIX.$data->id,
					'parent_id' 		=> 0,
					'thread_url' 		=> bp_activity_get_permalink ( $data->id ),
					'subject' 			=> substr( $data->content, 0, 30 ),
					'message' 			=> $data->content,
					'user_id' 			=> es_get_user_id( $data->user_id ),
					'user_email' 		=> $user_info->user_email
				);
				break;
		}
	
		if (!is_null($data_to_send)) {
			$is_sended 					= $wpdb->get_var( "SELECT megathread_post_sended FROM ".$bp->activity->table_name. " 
											WHERE id=".$data->id );
			if($is_sended!=1) {
				
				$success		= array ('type'=>'bp_activity','id'=>$data->id);
				
				es_send_data_to_server($data_to_send, false, $success);
				//es_send_data_to_server ( $data_to_send, false );
			}
		}
	}
//}

/**
* get constants we declared some time ago.
*
* @since  	1.0.0
*
* @param	String		Type of BP activity
*
* @return 	String   	ID of requested activity
*/
if ( !function_exists ( 'es_get_id_prefix' ) ) {
	function es_get_id_prefix ( $type ) {
		$id_prefix 			= '';
	
		switch ( $type ) {
			case 'activity_update':
				$id_prefix 	= ACTIVITY_PREFIX;
				break;
			case 'new_forum_topic':
			case 'new_forum_post':
				$id_prefix 	= FORUM_PREFIX;
				break;
		}
	
		return $id_prefix;
	}
}

/**
* get thread URL
*
* @since  	1.0.0
*
* @param	String		Type of BP activity
*
* @param	String		ID of thread
*
* @param	String		Primary Link
*
* @return 	String   	URL of requested thread
*/
if ( !function_exists ( 'es_get_thread_url' )  ) {
	function es_get_thread_url ( $type, $thread_id, $primary_link ) {
	
		$thread_url 		= '';
	
		switch ( $type ) {
			case 'new_forum_topic':
			case 'new_blog_post':
				$thread_url = $primary_link;
				break;
			case 'new_forum_post':
			case 'new_blog_comment':
				$link_pices = explode('#', $primary_link );
				$thread_url = $link_pices[0];
				break;
			case 'activity_update':
				$thread_url = bp_activity_get_permalink( $thread_id );
				break;
		}
	
		return $thread_url;
	}
}

/**
* get thread id by thread type
*
* @since  	1.0.0
*
* @param	String		Type of BP thread
*
* @param	String		ID of thread
*
* @return 	String   	ID of requested activity
*/
if ( !function_exists ( 'es_get_thread_id' ) ) {
	function es_get_thread_id($type, $id) {
	
		$thread_id 			= false;
	
		switch ($type) {
			case 'new_blog_post':
				$thread_id 	= $id;
				break;
			case 'new_blog_comment':
				$comment 	= get_comment ( $id, ARRAY_A );
				$thread_id 	= $comment['comment_post_ID'];
				break;
			case 'new_forum_topic':
				$thread_id 	= $id;
				break;
			case 'new_forum_post':
				$post 		= bp_forums_get_post ( $id );
				$thread_id 	= $post->topic_id;
				break;
		}
		return $thread_id;
	}
}

/**
* listener and performr for new topic posted on buddypress with topic
*
* @since  	1.0.0
*
* @param	String		ID of BP group
*
* @param	String		Topic of group
*
* @return 	NULL   		Another function call to send data to Megathread server
*/
if ( !function_exists ( 'es_new_forum_topic' ) ) {
	function es_new_forum_topic ( $group_id, $topic ) {
	
		$post 				= bp_forums_get_post ( $topic->topic_last_post_id );
	
		$topic_url 			= es_get_topic_url( $group_id, $topic->topic_id );
	
		$data = array(
			'thread_id' 	=> FORUM_PREFIX.$topic->topic_id,
			'message_id' 	=> FORUM_PREFIX.$topic->topic_last_post_id,
			'parent_id' 	=> 0,
			'thread_url' 	=> $topic_url,
			'subject' 		=> $topic->topic_title,
			'message' 		=> $post->post_text,
			'user_id' 		=> es_get_user_id ( $topic->topic_poster )
		);
	
		es_send_data_to_server($data);
	}
}

/**
* listener and performr for new topic posted on buddypress with post it
*
* @since  	1.0.0
*
* @param	String		ID of BP group
*
* @param	String		ID of post
*
* @return 	NULL   		Another function call to send data to Megathread server
*/
if ( !function_exists ( 'es_new_forum_topic_post' ) ) {
	function es_new_forum_topic_post ( $group_id, $post_id ) {
	
		$post 				= bp_forums_get_post ( $post_id );
	
		$topic_url 			= es_get_topic_url( $group_id, $post->topic_id );
	
		$data = array(
			'thread_id' 	=> FORUM_PREFIX.$post->topic_id,
			'message_id' 	=> FORUM_PREFIX.$post->post_id,
			'parent_id' 	=> FORUM_PREFIX.$post->topic_id,
			'thread_url' 	=> $topic_url,
			'subject' 		=> '',
			'message' 		=> $post->post_text,
			'user_id' 		=> es_get_user_id( $post->poster_id )
		);
	
		es_send_data_to_server( $data );
	}
}

/**
* get URL for  topic
*
* @since  	1.0.0
*
* @param	String		ID of BP group
*
* @param	String		ID of topic
*
* @return 	NULL   		Another function call to send data to Megathread server
*/
if ( !function_exists ( 'es_get_topic_url' )) {
	function es_get_topic_url ($group_id, $topic_id ) {
	
		global $bp, $wpdb;
	
		$query 			= $wpdb->prepare( 'SELECT primary_link FROM '.$bp->activity->table_name.'
			WHERE item_id = %d AND secondary_item_id = %d AND type = \'new_forum_topic\'', 
			$group_id, $topic_id);
	
		return $wpdb->get_var($query);
	}
}
?>