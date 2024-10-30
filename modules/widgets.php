<?php 

class ads_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'ads_widget', // Base ID
			'Intravert Ad Space', // Name
			array( 'description' => __( 'Display any of your intravert ads.', 'text_domain' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$ads_block = apply_filters( 'widget_title', $instance['ads_block'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		
		$ads_arr = explode('.', $ads_block);
		$out .= do_shortcode( '[ads_block hash="'.$ads_arr[0].'" id="'.$ads_arr[1].'"]' );	

		
		
		echo $out;
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['ads_block'] = strip_tags( $new_instance['ads_block'] );

		return $instance;
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
			$ads_block = $instance[ 'ads_block' ];
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Select Ad to display:' ); ?></label> 
		
		
		
		
		<select class="widefat" id="<?php echo $this->get_field_id( 'ads_block' ); ?>" name="<?php echo $this->get_field_name( 'ads_block' ); ?>">
		<?php 
		$all_settings = get_option( 'user_ads_blocks' );
		
		if( count( $all_settings ) > 0 ){
			foreach( $all_settings as $k => $v ){
				$out .= '<optgroup value="0" label="'.$v['url'].'">';	
				foreach( $v['values'] as $single_inner ){
					$out .= '<option value="'.$v['hash_1'].'.'.$single_inner['id'].'" '.( $ads_block  == $v['hash_1'].'.'.$single_inner['id'] ? ' selected ' : ' ' ).' >ID: '.$single_inner['id'].' ('.$single_inner['description'].') </option> ';
					}  
				$out .= '</optgroup>';
			}
		}else{
			 $out .= '<div class="alert alert-warning">You don\'t have ads blocks or you enter wront Auth Token </div>';
		}
		echo $out;
		?>
		</select>
		</p>
		<?php 
	}

} // class Foo_Widget
// register Foo_Widget widget
add_action( 'widgets_init', create_function( '', 'register_widget( "ads_widget" );' ) );

?>