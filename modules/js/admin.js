jQuery(document).ready(function($){

 $('.shortcode_changer').change(function(){
	 var parent_pnt = $(this).parents('.parent_short_picker');
	 $('.shortcode_preview', parent_pnt).html( $(this).val() )
 })
	
});