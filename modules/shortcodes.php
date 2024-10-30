<?php  
add_shortcode( 'ads_block', 'wap_ads_block' );
function wap_ads_block( $atts, $content = null ){
	
	$hash = $atts['hash'];
 
	$id = $atts['id'];
	
	$out .= '
	<div class="intravert-space" id="space-'.$hash.$id.'"></div>
	<script defer src="https://intravert.co/serve/'.$hash.'.'.$id.'.js"></script>
	';
	
	return $out;	
}

  
 
?>