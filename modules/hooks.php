<?php 
 
class adsAPI{
	var $token;
	var $api_url;
	
	var $is_error;
	
	var $responce_url;
	var $responce_hash;
	
	function __construct(){
		
		$config = get_option( 'wap_options'); 
		
		$this->token = $config['auth_token'];
		$this->api_url = 'http://intravert.co/api/';
	}
	
	function make_call( $hash = false, $id = false ){
		
		$headers = array(
			'Authorization' => 'Token ' . $this->token 
		);
		
		
		$args = array(
			'headers'     => $headers
		); 
		
		if( count($args) > 0 ){
			$args['body'] = $args_inner;
		}
		
		$url = $this->api_url;
		
		if( $hash ){
			$url = $url.$hash.'/';
		}
		if( $id ){
			$url = $url.$id.'/';
		}
 
 
		$response = wp_remote_get( $url, $args );
	 
		if( !is_wp_error( $response ) ){
			
			if( wp_remote_retrieve_response_code( $response ) == 200 ){
				$this->is_error = 0;
				return  wp_remote_retrieve_body( $response ) ;
			}else{
				$this->is_error = 	1;
				$this->error_text =   'Server code1 '.wp_remote_retrieve_response_code( $response );
			}
			
		}else{
			$this->is_error = 1;
 
			$this->error_text =  'Server code2 '.wp_remote_retrieve_response_code( $response );
		}
	}
	
	
	function initiate_ads_array(){
		$responce = $this->make_call();
		if( $this->is_error != 1 ){
			
			$out_array_ads = array();
			
			$lis_of_items = json_Decode( $responce, true );
			if( count($lis_of_items) > 0 ){
				foreach( $lis_of_items as $s_item ){
	
					$current_item_hash = md5( $s_item['url'].$s_item['hash_1'] );
					$out_array_ads[$current_item_hash] = $s_item;
					
					$response_entyty = $this->make_call( $s_item['hash_1'] );
					if( $this->is_error != 1 ){
						$list_of_spaces = json_Decode( $response_entyty, true );
						foreach( $list_of_spaces as $s_item_inner ){
					 
							$out_array_ads[$current_item_hash]['values'][] = $s_item_inner;
						}
					}
				}
				
				update_option( 'user_ads_blocks', $out_array_ads );
				
			}
		}else{
				delete_option( 'user_ads_blocks'  );
		}
	}
	
}
 
add_action('init', 'wap_init');
function wap_init(){
	if( $_GET['adsapi'] ){
		$call = new adsAPI();
		$call->initiate_ads_array();
	 
	}
	if( $_GET['refresh'] == '1' ){
		$call = new adsAPI();
		$call->initiate_ads_array();	 
	}
}


add_Action('the_content', 'waa_filter_content');
function waa_filter_content( $content ){
	global $post;
	
	$config = get_option( 'wap_options'); 
	
	if( is_single()  ){
		if( $config['ads_insertion']  != '' ){
			$ads_arr = explode('.',$config['ads_insertion'] );
			
			if( $config['where_to_show_ads'] == 'top' ){
				$content = '<div class="post_ads_insertion">'.do_shortcode( '[ads_block hash="'.$ads_arr[0].'" id="'.$ads_arr[1].'"]' ).'</div>'.$content;
			}
			if( $config['where_to_show_ads'] == 'middle' ){
			
				$content_len = strlen( $content );
				$half = $content_len / 2;
				$rest = substr( $content, 0, $half );
				$last_position = strrpos( $rest, '</p>' );
			 
				$ads = '<div class="post_ads_insertion">'.do_shortcode( '[ads_block hash="'.$ads_arr[0].'" id="'.$ads_arr[1].'"]' ).'</div>';
				
				$content = substr_replace( $content, $ads, $last_position+4, 0);
			
				
				}
			if( $config['where_to_show_ads'] == 'bottom' ){
				$content = $content.'<div class="post_ads_insertion">'.do_shortcode( '[ads_block hash="'.$ads_arr[0].'" id="'.$ads_arr[1].'"]' ).'</div>';
				}
			
		}
	}
	return $content;
}

?>