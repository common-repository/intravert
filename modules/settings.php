<?php 

if( !class_exists('vooSettingsClassMainAds') ){
class vooSettingsClassMainAds{
	
	var $setttings_parameters;
	var $settings_prefix;
	var $message;
	
	function __construct( $prefix ){
		$this->setttings_prefix = $prefix;	
		
		if(  wp_verify_nonce($_POST['save_settings_field'], 'save_settings_action') ){
			$options = array();
			foreach( $_POST as $key=>$value ){
				$key = sanitize_text_field( $key );
				$value = sanitize_text_field( $value );
				$options[$key] = $value ;
			}
			update_option( $this->setttings_prefix.'_options', $options );
			
			$call = new adsAPI();
			$call->initiate_ads_array();
			
			if( $call->is_error == 1 ){
				$extra_string = '<br/><div class="alert alert-danger">Sorry, seems your Auth Token is invalid</div>';
			}
			
			$this->message = '<div class="alert alert-success">Settings saved</div>'.$extra_string;
			
		}
	}
	
	function get_setting( $setting_name ){
		$inner_option = get_option( $this->setttings_prefix.'_options');
		return $inner_option[$setting_name];
	}
	
	function create_menu( $parameters ){
		$this->setttings_parameters = $parameters;		
			
		add_action('admin_menu', array( $this, 'add_menu_item') );
		
	}
	
	 
	
	
	function add_menu_item(){
		
		foreach( $this->setttings_parameters as $single_option ){
			if( $single_option['type'] == 'menu' ){
				add_menu_page(  			 
				$single_option['page_title'], 
				$single_option['menu_title'], 
				$single_option['capability'], 
				$single_option['menu_slug'], 
				array( $this, 'show_settings' ) 
				);
			}
			if( $single_option['type'] == 'submenu' ){
				add_submenu_page(  
				$single_option['parent_slug'],  
				$single_option['page_title'], 
				$single_option['menu_title'], 
				$single_option['capability'], 
				$single_option['menu_slug'], 
				array( $this, 'show_settings' ) 
				);
			}
			if( $single_option['type'] == 'option' ){
				add_options_page(  				  
				$single_option['page_title'], 
				$single_option['menu_title'], 
				$single_option['capability'], 
				$single_option['menu_slug'], 
				array( $this, 'show_settings' ) 
				);
			}
		}
		 
	}
	
	function show_settings(){
		?>
		<div class="wrap tw-bs4">
		
		
		
		<h1><?php _e('Intravert Ad Spaces', 'sc'); ?></h1>

		<?php 
			echo $this->message;
		?>
		
		<?php
		if( $_GET['refresh'] == '1' ){
		echo '<br/><div class="alert alert-success">Ad Blocks Updated</div>';;
			}
		?>
		
		<form class="form-horizontal" method="post" action="">
		<?php 
		wp_nonce_field( 'save_settings_action', 'save_settings_field'  );  
		$config = get_option( $this->setttings_prefix.'_options'); 
		?>  
		<fieldset>

			<?php 
		foreach( $this->setttings_parameters as $single_page ){	
			foreach( $single_page['parameters'] as $key=>$value ){
				switch( $value['type'] ){
					case "separator":
						$out .= '
						<div class="lead">'.$value['title'].'</div> 
						';
					break;
					case "ads_block_1":
						$out .= '
						<div class="ads_block_gray">
							<p>Intravert is a simple, easy and ethical way to monetize your content, with native and
directly purchasable ad spaces. For more info, visit our website (link to https://intravert.co/)</p>
							<p>Intravert for Wordpress offers an easy way to generate embeddable shortcodes for
your personal ad spaces and display ads on every post in your blog.</p>

						<a href="https://intravert.co/" target="_blank">Learn More</a>
						
						</div>
						';
					break;
					
					
					case "text":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
							
							  <input type="text"  class="form-control '.$value['class'].'"  name="'.$value['name'].'" id="'.$value['id'].'" placeholder="'.$value['placeholder'].'" value="'.esc_html( stripslashes( $config[$value['name']] ) ).'">  
							  <p class="help-block">'.$value['sub_text'].'</p>  
							
						  </div> 
						';
					break;
					case "button":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="">&nbsp;</label>  
							
							  <a class="btn btn-success" href="'.$value['href'].'"   >'.$value['title'].'</a>  
							  
							
						</div> 
						';
					break;
					case "select":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
							 
							  <select  style="'.$value['style'].'" class="form-control '.$value['class'].'" name="'.$value['name'].'" id="'.$value['id'].'">' ; 
							  if( count( $value['value'] ) > 0 )
							  foreach( $value['value'] as $k => $v ){
								  $out .= '<option value="'.$k.'" '.( $config[$value['name']]  == $k ? ' selected ' : ' ' ).' >'.$v.'</option> ';
							  }
						$out .= '		
							  </select>  
							  <p class="help-block">'.$value['sub_text'].'</p> 
							</div>  
						 
						';
					break;
					case "shortcode_list":
					
						if( !$config['auth_token'] || $config['auth_token'] == '' ){
							$out .= '<div class="alert alert-warning">It looks like you either don\'t have any ad blocks available, or didn\'t enter an Auth Code yet (see above)</div>';
							break;
						}
					
						$out .= '
						<div class="form-group parent_short_picker">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label> ';
							
							$all_settings = get_option( 'user_ads_blocks' );
							  
							  if( count( (array)$all_settings ) > 0  ){
								$out .= '<table class="table">
										  <thead>
											<tr>
											  <th scope="col">Site URL</th>
											  <th scope="col">ID</th>
											  <th scope="col">Description</th>
											  <th scope="col">Shortcode</th>
										 
											</tr>
										  </thead>';
								  foreach( (array)$all_settings as $k => $v ){
							 
										if( count( (array)$v['values'] ) > 0 ) 
										foreach( (array)$v['values'] as $single_inner ){
										$out .= '
										<tr>
										  <td>'.$v['url'].'</td>
										  <td>'.$single_inner['id'].'</td>
										  <td>'.$single_inner['description'].'</td>
										  <td>[ads_block hash=\''.$v['hash_1'].'\' id=\''.$single_inner['id'].'\']</td>
										</tr>';
										
									 
										}
									}
									$out .= '
									</tbody>
									</table>
									';
							  }else{
								 $out .= '<div class="alert alert-warning">It looks like you either don\'t have any ad blocks available, or didn\'t enter an Auth Code yet (see above)</div>';
							 }
							
							$out .= ' 
							<!--
							  <select  style="'.$value['style'].'" class="form-control shortcode_changer '.$value['class'].'" name="'.$value['name'].'" id="'.$value['id'].'">' ; 
							  
							  $out .= '<option value="" >Select Ads Block</option> 
							  
					 
							  ';
							  
							  $all_settings = get_option( 'user_ads_blocks' );
							  
							  if( count( $all_settings ) > 0 ){
								  foreach( (array)$all_settings as $k => $v ){
									  
									$out .= '<optgroup value="0" label="'.$v['url'].'">';	
									if( count( $v['values'] ) > 0 ) 
									foreach( $v['values'] as $single_inner ){
								  
										
								  
										  $out .= '<option value="[ads_block hash=\''.$v['hash_1'].'\' id=\''.$single_inner['id'].'\']" '.( $config[$value['name']]  == $k ? ' selected ' : ' ' ).' >ID: '.$single_inner['id'].' ('.$single_inner['description'].') </option> ';
									}  
									$out .= '</optgroup>';
								 }
							 }else{
								 $out .= '<div class="alert alert-warning">It looks like you either don\'t have any ad blocks available, or didn\'t enter an Auth Code yet (see above)</div>';
							 }
						$out .= '		
							  </select>  
							 
							  <div class="shortcode_preview"></div> -->
							  <p class="help-block">'.$value['sub_text'].'</p> 
							</div>  
						 
						';
					break;
					case "ads_select":
					
						if( !$config['auth_token'] || $config['auth_token'] == '' ){
							$out .= '<div class="alert alert-warning">It looks like you either don\'t have any ad blocks available, or didn\'t enter an Auth Code yet (see above)</div>';
							break;
						}
					
						$out .= '
						<div class="form-group ">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
							 
							  <select  style="'.$value['style'].'" class="form-control shortcode_changer '.$value['class'].'" name="'.$value['name'].'" id="'.$value['id'].'">' ; 
							  
							  $out .= '<option value="" >Select Ads Block</option> 
							  
					 
							  ';
							  
							  $all_settings = get_option( 'user_ads_blocks' );
							  
							  if( count( $all_settings ) > 0 ){
								  foreach( (array)$all_settings as $k => $v ){
									  
									$out .= '<optgroup value="0" label="'.$v['url'].'">';	
									if( count( $v['values'] ) > 0 ) 
									foreach( $v['values'] as $single_inner ){
								  
										
								  
										  $out .= '<option value="'.$v['hash_1'].'.'.$single_inner['id'].'" '.( $config[$value['name']]  == $v['hash_1'].'.'.$single_inner['id'] ? ' selected ' : ' ' ).' >ID: '.$single_inner['id'].' ('.$single_inner['description'].') </option> ';
									}  
									$out .= '</optgroup>';
								 }
							 }else{
								 $out .= '<div class="alert alert-warning">It looks like you either don\'t have any ad blocks available, or didn\'t enter an Auth Code yet (see above)</div>';
							 }
						$out .= '		
							  </select>  
						
							  <p class="help-block">'.$value['sub_text'].'</p> 
							</div>  
						 
						';
					break;
					case "checkbox":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
						
							  <label class="checkbox">  
								<input  class="'.$value['class'].'" type="checkbox" name="'.$value['name'].'" id="'.$value['id'].'" value="on" '.( $config[$value['name']] == 'on' ? ' checked ' : '' ).' > &nbsp; 
								'.$value['text'].'  
								<p class="help-block">'.$value['sub_text'].'</p> 
							  </label>  
							 
						  </div>  
						';
					break;
					case "radio":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>';
								foreach( $value['value'] as $k => $v ){
									$out .= '
									<label class="radio">  
										<input  class="'.$value['class'].'" type="radio" name="'.$value['name'].'" id="'.$value['id'].'" value="'.$k.'" '.( $config[$value['name']] == $k ? ' checked ' : '' ).' >&nbsp;  
										'.$v.'  
										<p class="help-block">'.$value['sub_text'].'</p> 
									  </label> ';
								}
							$out .= '
							
						  </div>  
						';
					break;
					case "textarea":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
						
							  <textarea style="'.$value['style'].'" class="form-control '.$value['class'].'" name="'.$value['name'].'" id="'.$value['id'].'" rows="'.$value['rows'].'">'.esc_html( stripslashes( $config[$value['name']] ) ).'</textarea>  
							  <p class="help-block">'.$value['sub_text'].'</p> 
						 
						  </div> 
						';
					break;
					case "multiselect":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
							 
							  <select  multiple="multiple" style="'.$value['style'].'" class="form-control '.$value['class'].'" name="'.$value['name'].'[]" id="'.$value['id'].'">' ; 
							  foreach( $value['value'] as $k => $v ){
								  $out .= '<option value="'.$k.'" '.( @in_array( $k, $config[$value['name']] )   ? ' selected ' : ' ' ).' >'.$v.'</option> ';
							  }
						$out .= '		
							  </select>  
							  <p class="help-block">'.$value['sub_text'].'</p> 
							 
						  </div>  
						';
					break;
					case "wide_editor":
					$out .= '<div class="form-group">  
						<label class="control-label" for="input01">'.$value['title'].'</label>
						<div class="form-control1">
						';  
						 
						ob_start();
						wp_editor( $config[$value['name']], $value['name'] );
						$editor_contents = ob_get_clean();	
					 
						$out .= $editor_contents;  
					$out .= '
						</div>
					  </div> ';	 
					 
					break;
					case "file":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
				 
							<input type="file" class="form-control-file '.$value['class'].'" name="'.$value['name'].''.( $value['multi'] ? '[]' : '' ).'" id="'.$value['id'].'" '.( $value['multi'] ? ' multiple ' : '' ).' >
							  
							  <p class="help-block">'.$value['sub_text'].'</p> 
						 
						  </div> 
						';
					break;
					case "text_section":
						$out .= '
						<div class="form-group mt-4">  
							<h3>'.$value['text'].'</h3>  
							<p>'.$value['subtext'].'</p>  
						 
						 
						  </div> 
						';
					break;
					case "saveblock":
						$out .= '
						<div class="form-actions">  
							<button type="submit" class="btn btn-primary">Save Settings</button>  
							<a href="<?php echo admin_url(); ?>options-general.php?page=wap_settings&refresh=1" class="btn btn-success">Refresh Ads</a>  
						  </div>  
						  <br/>
						';
					break;
				}
			}
		}
			echo $out;
			?>

				
				   
				</fieldset>  

		</form>

		</div>
		<?php
	}
}	
}	
 
	
	
add_Action('init',  function (){
	$config_big = 
	array(

		array(
			'type' => 'option',
		 
			'page_title' => __('Intravert', $locale_taro),
			'menu_title' => __('Intravert', $locale_taro),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'wap_settings',

			'parameters' => array(
				array(
					'type' => 'ads_block_1',
 
				), 
				array(
					'type' => 'text',
					'title' => 'Intravert API Key',
					'name' => 'auth_token',
					'sub_text' => 'To claim your free API key, sign up at https://intravert.co and claim your API key <a href="https://intravert.co/dashboard/integrations/" target="_blank">here</a>'
				),
	 
				array(
					'type' => 'saveblock',
			 
				),
				
				array(
					'type' => 'text_section',
					'text' => 'Posts',
					'subtext' => 'Our Integration makes it possible to display an ad space in every of your blog posts.'
				),
				
				array(
					'type' => 'select',
					'title' => 'Select Placement in Posts',
					'name' => 'where_to_show_ads',
					'value' => array( 
						'' => 'Select placement',
						'top' => 'Display at the beginning of the post',
						'middle' => 'Display in the middle of the post',
						'bottom' => 'Display at the end of the post',
					
					)
				),
				
				array(
					'type' => 'ads_select',
					'title' => 'Select Ad Space to insert in Posts',
					'name' => 'ads_insertion',
				),
				
				array(
					'type' => 'text_section',
					'text' => 'Shortcodes',
					'subtext' => 'Shortcodes make it possible to easily integrate our ad spaces in your content, pages and
wherever you want. Just copy the shortcode and place it where you want an ad space to
appear.'
				),
				
				array(
					'type' => 'shortcode_list',
					'title' => 'Select your ads to generate shortcode',
					'name' => 'auth_token11',
				),
				
				array(
					'type' => 'saveblock',
			 
				),
				
				
				array(
					'type' => 'text_section',
					'text' => 'Widgets',
					'subtext' => 'Widgets can be placed in multiple areas on your site, depending on your theme (sidebar,
footer). Visit <a href="'.admin_url().'widgets.php">Appearence > Widgets</a> to claim and place your widgets.'
				),
				
				array(
					'type' => 'text_section',
					'text' => 'Need Help?',
					'subtext' => 'More info can be found on our main page https://intravert.co or by contacting us
at <a href="mailto:info@intravert.co">info@intravert.co</a>.'
				),
				 
				 
			)
		)
	); 
	global $settings;

	$settings = new vooSettingsClassMainAds( 'wap' ); 
	$settings->create_menu(  $config_big   );
	
} );
	
 

?>