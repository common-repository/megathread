<?php
/*
*
* Restrict direct access
*
*/
if ( !defined( 'ABSPATH' ) ) exit;

/*
*
* URI to send API calls.
*
*/

define('ES_SERVER_API_SCRIPT', 'SOAP/API');

/*
*
* action hooks
*
*/
add_action('comment_post', 'es_new_comment', 1, 2);
add_action('wp_set_comment_status', 'es_update_comment_status', 1, 2);
add_action('edit_comment', 'es_update_comment', 1, 1);
add_action('save_post', 'es_save_post', 1 , 2);
/*
*
* add filter if user have choosed from admin panel
*
*/
if(get_option("megathread_discussion_avator")=="yes") {
	add_filter('the_title', 'megathread_discussion_link', 10);
}
/*
*
* add filter if user have choosed from admin panel
*
*/
if(get_option("megathread_header_image")=="yes") {
	add_action('wp_head', 'megathread_community_link', 10, 1);
}
/*
*
* add filter if user have choosed from admin panel
*
*/
if(get_option("megathread_image_avator")=="yes") {

	add_filter('bp_core_fetch_avatar','megathread_avatar', 10, 5);
	add_filter('get_avatar','megathread_avatar', 10, 5);
	
}
/*
*
* add filter if user have choosed from admin panel
*
*/
if(get_option("megathread_register_api")=="yes") {

	add_action( 'user_register', 'megathread_user_register' );
	
}
/*
*
* include file for buddypress
*
*/
require_once ES_PLUGIN_DIR.'megathread_buddypress_data_handler.php';

/**
* get megathread user URL for prvided ID
*
* @since  	1.0.0
*
* @param	String		ID of user
*
* @return 	String   	URL of user widget ; false if user not found
*/
if ( !function_exists ( 'get_megathread_url' )) {
	function get_megathread_url($user_id) {
		$megathread_user_id 	= es_get_user_id ( $user_id );
		
		if($megathread_user_id != '' && $megathread_user_id != NULL)
		{
			$server_url 		= get_option('es_megathread_server_url');
			return $server_url.'user_sm/'.$megathread_user_id.'/'.get_option('es_megathread_unique_code');
		}
		else
			return false;
	}
}

/**
* function to show megathread widget for discussion
*
* @since  	1.0.0
*
* @param	String		Title
*
* @return 	String   	Title
*/
if ( !function_exists ( 'megathread_discussion_link' ) ) {
	function megathread_discussion_link($title) {
	
		$post 				= get_post( get_the_ID() );
		if( !empty($post->ID) && strcasecmp(wptexturize(trim(convert_chars($title))), 
					wptexturize(trim(convert_chars($post->post_title)))) == 0 ){
						
			global $wpdb;
			$result 		= $wpdb->get_var( $wpdb->prepare( 
				'SELECT `megathread_post_id` FROM '.$wpdb->posts.' 
					WHERE ID = %d', $post->ID
			));
			
			$local_gloabl 	= get_option("megathread_local_global_discussion");
			
			if( is_single() && $result != '' && $result != NULL)
			{
				$title 		= megathread_make_discussion_title ($title, false, $result,$local_gloabl );
				
			}
			else if(!have_comments() && $result != '' && $result != NULL)
			{
				$title 		= megathread_make_discussion_title($title, true, $result,$local_gloabl );
			}
		}
		return $title;
	}
}
/**
* megathread make discussion title
*
* @since  	1.0.0
*
* @param	String		Title
*
* @param	Boolean		
*
* @param	String		Result
*
* @param	Boolean		It is local discussion or global?
*
* @return 	String   	Title
*/
if ( !function_exists ( 'megathread_make_discussion_title' ) ) {
	function megathread_make_discussion_title($title, $boolean, $result,$local_gloabl )
	{
		$id 			= uniqid();
		
		$find   		= '</a>';
		$pos 			= strpos($title, $find);
		
		if ($pos === false) {
			
			$link 		= get_permalink();
			$url 		= get_option('es_megathread_server_url').'thread_sm/'.$result.'/'.$boolean.'/'.$local_gloabl;
			$title 		= ' <a href="'.$link.'">'.$title;
			$title	   .= ' <span class="tooltip" isrc="'.$url.'"><span></span>';
			$title     .= ' <iframe src="'.$url.'" width="280px" height="320px" id="'.$id.'"  frameborder="0" scrolling="no"> ';
			$title     .= ' </iframe></span></a>';
			
		} else {
			
			$title 		= str_replace("</a>","",$title);
			$url 		= get_option('es_megathread_server_url').'thread_sm/'.$result.'/'.$boolean.'/'.$local_gloabl;
			$title	   .= ' <span class="tooltip" isrc="'.$url.'"><span></span> ';
			$title     .= ' <iframe src="'.$url.'" width="280px" height="320px" id="'.$id.'"  frameborder="0" scrolling="no"> ';
			$title     .= ' </iframe></span></a>';
		}
		
		return $title;
	}
}


/**
* function to show commuity link in application header section
*
* @since  	1.0.0
*
* @param	NULL		
*
* @return 	NULL   	Prints out data
*/
if ( !function_exists ( 'megathread_community_link' ) ) {
	
	function megathread_community_link() {
		
		$forum_ID 		= get_option('es_megathread_unique_code');
		$css			= get_option('megathread_widget_css');
		$position		= get_option('megathread_widget_position');
		if($forum_ID != '') {
			$url = get_option('es_megathread_server_url').'community_sm/'.$forum_ID;
			$local_gloabl = get_option("megathread_local_global_community");
			$url.="/".$local_gloabl;
			printf('<div id="megathread_link"  id="megathread_url" style="%s position:%s">
						<a href="javaScript:void(null)"><span class="tooltiph meghead"><span></span>
						<iframe src="%s" width="280px" height="320px" frameborder="0" scrolling="0" 
						id="megathread_head_frame" ></iframe></span><img src="%s" class="avatar" />
						</a></div>',$css, $position, $url, plugins_url().'/megathread/images/megathread_com.png');
		}
	}
}

/**
* Process new posted comment
*
* @since  	1.0.0
*
* @param	String	Comment ID
*
* @param	String 	Is comment approved?		
*
* @return 	NULL   	Another fucntion call to send data to megathread server
*/
if ( !function_exists ('es_new_comment') ) {
	function es_new_comment ( $comment_ID, $comment_approved ) {
		
		if ( $comment_approved == 1 && !es_is_sended($comment_ID, 'comment')) {
			$commentArr 	= get_comment($comment_ID, ARRAY_A);
			$data 			= es_prepare_data($commentArr, 'comment');
			$success		= array ('type'=>'comment','id'=>$data['message_id']);
			es_send_data_to_server($data, true, $success);
		}
	}
}

/**
* Process comment status update
*
* @since  	1.0.0
*
* @param	String	Comment ID
*
* @param	String 	Is comment approved?		
*
* @return 	NULL   	Another fucntion call to send data to megathread server
*/
if ( !function_exists ( 'es_update_comment_status' ) ) {
	function es_update_comment_status ( $comment_ID, $comment_status ) {
	
		$commentArr 		= get_comment($comment_ID, ARRAY_A);
	
		if ( $comment_status == 'approve' && !es_is_sended($comment_ID, 'comment')) {
			$data 			= es_prepare_data($commentArr, 'comment');
			$success		= array ('type'=>'comment','id'=>$data['message_id']);
			es_send_data_to_server($data, true, $success);
		}
	}
}

/**
* Process comment update
*
* @since  	1.0.0
*
* @param	String	Comment ID
*
* @param	String 	Is comment approved?		
*
* @return 	NULL   	Another fucntion call to send data to megathread server
*/
if ( !function_exists ( 'es_update_comment' ) ) {
	function es_update_comment ($comment_ID) {
	
		$commentArr 	= get_comment($comment_ID, ARRAY_A);
	
		if ( $commentArr['comment_approved'] && !es_is_sended($comment_ID, 'comment')) {
			$data 			= es_prepare_data($commentArr, 'comment');
			$success		= array ('type'=>'comment','id'=>$data['message_id']);
			es_send_data_to_server($data, true, $success);
		}
	}
}

/**
* Process new post
*
* @since  	1.0.0
*
* @param	String	Comment ID
*
* @param	String 	Is comment approved?		
*
* @return 	NULL   	Another fucntion call to send data to megathread server
*/
if ( !function_exists ( 'es_save_post' ) ) {
	function es_save_post($post_id, $post) {
	
		$available_post_type_arr 	= array('post', 'topic', 'reply');
		
		if ( $post->post_status == 'publish' && in_array($post->post_type, $available_post_type_arr) 
					&& !es_is_sended($post_id, 'post') ) {
						
			$postArr 				= get_post($post_id, ARRAY_A);
			$tag_names 				= wp_get_post_tags( $post_id, array( 'fields' => 'names' ) );
			$tagline 				= false;
			if(count($tag_names)>0)
				$tagline 			= " #".implode("# #", $tag_names)."#";
			$data 					= es_prepare_data($postArr, $post->post_type, $tagline);
			$success				= array ('type'=>'post','id'=>$data['thread_id']);
			es_send_data_to_server($data, true, $success);
			
		}
	}
}

/**
* update flag in database
*
* @since  	1.0.0
*
* @param	String	ID
*
* @param	String 	Type of Data		
*
* @return 	Int 	1 for yes; NULL for NO
*/
if ( !function_exists ( 'es_is_sended' ) ) {
	function es_is_sended($ID, $type) {
		
		global $wpdb;
	
		switch ($type) {
			case 'comment':
				$result = $wpdb->get_var( $wpdb->prepare( 
					'SELECT `megathread_comment_sended` FROM '.$wpdb->comments.' 
						WHERE comment_ID = %d', $ID
				));
				break;
	
			case 'post':
				$result = $wpdb->get_var( $wpdb->prepare( 
					'SELECT `megathread_post_sended` FROM '.$wpdb->posts.' 
						WHERE ID = %d', $ID
				));
				break;
		}
	
		return (int) $result;
	}
}
/**
* Prepare data for SOAP API call
*
* @since  	1.0.0
*
* @param	Array	data array
*
* @param	String 	Type of Data	
*
* @param	Bool	Is tagline?	
*
* @return 	String 	ID returned from Megathread
*/
if (  !function_exists ( 'es_prepare_data' )) {
	function es_prepare_data($data, $type, $tagline = false) {
	
		global $wpdb;
		$result 						= array(
			'thread_id' 				=> 0,
			'message_id' 				=> 0,
			'parent_id' 				=> 0,
			'thread_url' 				=> '',
			'subject' 					=> '',
			'message' 					=> '',
			'user_id' 					=> ''
		);
	
		switch ($type) {
			case 'comment':
				$result['thread_id'] 	= (int) $data['comment_post_ID'];
				$result['message_id'] 	= (int) $data['comment_ID'];
				$commentParent 			= (int) $data['comment_parent'] ? (int) $data['comment_parent'] : (int) $data['comment_post_ID'];
				$result['parent_id'] 	= $commentParent;
				$result['thread_url'] 	= get_permalink($result['thread_id']);
				$result['message'] 		= $data['comment_content'];
				$result['user_id'] 		= es_get_user_id($data['user_id']);
				$author_email 			= get_comment_author_email((int) $data['comment_ID']);
				$result['user_email'] 	= $author_email;
	
				break;
	
			case 'post':
			case 'topic':
				$result['thread_id'] 	= (int) $data['ID'];
				$result['message_id'] 	= (int) $data['ID'];
				$result['thread_url'] 	= get_permalink($result['thread_id']);
				$result['subject'] 		= $data['post_title'];
				$result['message'] 		= $data['post_content'];
				if($tagline)
					$result['message'] .= $tagline;
				$result['user_id'] 		= es_get_user_id($data['post_author']);
				$user_info 				= get_userdata(intval($data['post_author']));
				$result['user_email'] 	= $user_info->user_email;
	
				
				break;
			case 'reply':
				$result['thread_id'] 	= (int) $data['post_parent'];
				$result['message_id'] 	= (int) $data['ID'];
				$result['parent_id'] 	= (int) $data['post_parent'];
				$result['thread_url'] 	= get_permalink($result['thread_id']);
				$result['subject'] 		= $data['post_title'];
				$result['message'] 		= $data['post_content'];
				if($tagline)
					$result['message'] .= $tagline;
				$result['user_id'] 		= es_get_user_id($data['post_author']);
				$user_info = get_userdata(intval($data['post_author']));
				$result['user_email'] 	= $user_info->user_email;
	
				
				break;
		}
	
		return $result;
	}
}

/**
* get megathread user id for provided user 
*
* @since  	1.0.0
*
* @param	String		WP ID of user
*
* @return 	String   	Megathread user ID
*/
if (  !function_exists ( 'es_get_user_id' ) ) {
	function es_get_user_id($user_id) {
		global $wpdb;
		return $wpdb->get_var(
				$wpdb->prepare('SELECT `megathread_user_id` 
							FROM '.$wpdb->users.' WHERE `ID` = %d', 
					 intval($user_id))
		);
	}
}
/**
* Send data to megathread server for processing
*
* @since  	1.0.0
*
* @param	Array	data array
*
* @param	Bool	Is post?	
*
* @return 	NULL 	
*/
if ( !function_exists ( 'es_send_data_to_server' ) ) { 
	function es_send_data_to_server($data, $is_post=true, $success = array ()) {
	
		$param 					= array(
		'location' 				=> get_option('es_megathread_server_url').ES_SERVER_API_SCRIPT, 
		'uri' 					=> '',
		'encoding' 				=> 'utf-8',
		'exceptions' 			=> 0, 
		'trace' 				=> 1);
	
		$client 				= new SoapClient(null, $param);
		$data 					= array(
			'login' 			=> get_option('es_megathread_login'),
			'password' 			=> get_option('es_megathread_password'),
			'forum_id' 			=> get_option('es_megathread_unique_code'),
			'thread_id' 		=> $data['thread_id'],
			'message_id' 		=> $data['message_id'],
			'parent_id' 		=> $data['parent_id'],
			'thread_url' 		=> $data['thread_url'],
			'subject' 			=> $data['subject'],
			'message' 			=> $data['message'],
			'user_id' 			=> $data['user_id'],
			'user_email' 		=> $data['user_email']
		);
		
		try {
			$data 				= serialize ($data);
			$client->AddMessage($data);
	
			$thread_id 			= trim(strip_tags($client->__getLastResponse()));
			$data 				= unserialize ($data);
			if($thread_id != '' && $thread_id != 'looks like we got no XML document')
			{
				global $wpdb;
				if($is_post) {
					$query 		= 'UPDATE '.$wpdb->posts.' SET `megathread_post_id`=\''.$thread_id.'\' WHERE ID='.$data['thread_id'];
				} else {
					global $bp;
					$ID 		= preg_replace("/[^0-9]/","",$data['thread_id']);
					$query 		= 'UPDATE '.$bp->activity->table_name.' SET `megathread_activity_id`=\''.$thread_id.'\' WHERE ID='.$ID;
				}
	
				$wpdb->query($query);
				if( !empty ($success)) {
					update_table_flag($success);
				}
			}
		} catch (Exception $e) {
			//no xml returned
		}
	
		// Uncomment on debuging
		/*ob_start();
		var_dump($param);
		$data_print = ob_get_contents();
		ob_end_clean();
	
		$f = fopen(ES_PLUGIN_DIR.'test.html', 'a+');
		fwrite($f, $data_print);
		fclose($f);*/
		
	}
}
/**
* Function set flags once data sent to server  
*
* @since  	1.0.0
*
* @param	Array		Array containing neccessary data
*
* @return 	NULL   		
*/
function update_table_flag ( $success ) {
	global $wpdb;
	global $bp;
	
	switch ($success['type']) {
		case 'comment':
		
			$wpdb->query( $wpdb->prepare( 
					'UPDATE '.$wpdb->comments.' SET megathread_comment_sended = \'1\' 
						WHERE comment_ID = %d', 
					$success['id']
				));
		break;
		
		case 'post':
		
			$wpdb->query( $wpdb->prepare( 
					'UPDATE '.$wpdb->posts.' SET megathread_post_sended = \'1\' 
						WHERE ID = %d', 
					$success['id']
				));
		
		break;
		
		case 'bp_activity':
		
			$query 	= "UPDATE ".$bp->activity->table_name." SET `megathread_post_sended`='1' WHERE ID=".$success['id'];
			$wpdb->query($query);
			
		break;
		
		default:
		//do nothing
	}
}
/**
* Function to show tooltip/megathread widget over user avator  
*
* @since  	1.0.0
*
* @param	String		Current avator data
*
* @param	String		User ID or use email ID
*
* @param	String		Size of Avator
*
* @param	String		Default Value
*
* @param	String		Alt value
*
* @return 	String   	Megathread user ID
*/
if ( !function_exists ( 'megathread_avatar' ) ) {
	
	function megathread_avatar($avatar, $id_or_email='', $size='', $default='', $alt=''){
			
			$url 				= FALSE;
			$my_avatar 			= '';
			$data 				= array();
			
			if ( is_string($id_or_email) && email_exists($id_or_email) ) {
				$userId 		= email_exists($id_or_email);
				$url 			=  get_megathread_url($userId);
			}  else if( is_int($id_or_email)) {
				$url 			=  get_megathread_url($id_or_email);
			} else {
				preg_match_all('/(class)=("[^"]*")/i',$avatar, $data);
				if(is_array($data)) {
					$data 		= (implode(" ",$data[0]));
					$user_id 	=  ereg_replace("[^0-9]", "",$data);
					$url 		=  get_megathread_url($user_id);
				}
			}
			
			if($url) {
				$id 			= uniqid();
				$local_gloabl 	= get_option("megathread_local_global_user");
				$url		   .= "/".$local_gloabl;
				$my_avatar 		= '<div class="meagav_link" isrc="'.$url.'">'.$avatar.'<span class="tooltip megathread_av" > ';
				$my_avatar     .= ' <span></span><iframe src="" width="280px" height="320px" frameborder="0" scrolling="no" ';
				$my_avatar     .= ' id="'.$id.'" >Loading...</iframe></span></span></div>';
			}
			else {
				$my_avatar = $avatar;
			}
			return $my_avatar;
	}
}

/**
* Send data to megathread server for User
*
* @since  	1.0.0
*
* @param	String	User ID	
*
* @return 	NULL 	
*/
if ( !function_exists ( 'megathread_user_register' ) ) {
	function megathread_user_register($user_id)
	{
		global $wpdb;
		$user_info 		= get_userdata($user_id);
		$param 			= array('location' => get_option('es_megathread_server_url').ES_SERVER_API_SCRIPT,
					   'uri' => '', 'encoding' => 'utf-8', 'exceptions' => 0, 'trace' => 1);
	
		$client 		= new SoapClient(null, $param);
		$forumId 		= get_option('es_megathread_unique_code');
		$data 			= array(
			'real_name' => $user_info->user_login,
			'email' 	=> $user_info->user_email,
			'password' 	=> 'changeme',
			'forum_id' 	=> $forumId
		);
		
		try {
			$client->AddUser(serialize( $data ));
	
			$mt_user_id = trim(strip_tags($client->__getLastResponse()));
			
			if($mt_user_id != '' && $mt_user_id != 'looks like we got no XML document')
			{
				global $wpdb;
	
				$wpdb->query( "UPDATE ".$wpdb->users." SET megathread_user_id = '$mt_user_id'  WHERE ID = $user_id");
	
				
			}
		} catch (Exception $e) {
			//no xml returned
		}
		
		
	}
}
?>
