<?php
/*
Plugin Name: VR Advanced Widget Title
Plugin URL: http://blog.pixelthemes.com/
Description: Display widet titles with HTML tags in it.
Version: 1.0.0
Author: Rajesh Kannan MJ
Author URI: http://blog.pixelthemes.com/
*/

global $th_widgettitle_options;

if ( ( !$th_widgettitle_options = get_option( 'widget_custom_title' ) ) || !is_array( $th_widgettitle_options ) ) 
	$th_widgettitle_options = array();

if ( is_admin() ) {
	add_action( 'sidebar_admin_setup', 'th_widget_title_expand_control' );
	add_filter( 'widget_update_callback', 'th_widget_title_widget_update_callback', 10, 3 ); 
}

add_filter( 'dynamic_sidebar_params', 'th_filter_widget' );
add_filter( 'widget_title', 'th_filter_widget_title', 10, 3);

/**
 * Registered Callback function for the admin sidebar setup
 *
 * Augments the Widgets with extra control to specify a CSS class
 */
function th_widget_title_expand_control() {

	global $wp_registered_widgets, $wp_registered_widget_controls, $th_widgettitle_options;
	foreach ( $wp_registered_widgets as $id => $widget ) {
		if ( !$wp_registered_widget_controls[$id] ) {
			wp_register_widget_control($id,$widget['name'], 'widget_title_empty_control');
		}
		
		$wp_registered_widget_controls[$id]['callback_widget_title_redirect'] = $wp_registered_widget_controls[$id]['callback'];
		$wp_registered_widget_controls[$id]['callback'] = 'callback_widget_title_redirect';
		array_push( $wp_registered_widget_controls[$id]['params'], $id );	
	}	
	
}

/**
 * Callback function for the agumented widget control.
 *
 * Renders the text field box to the widget.
 */
function callback_widget_title_redirect() {
		
	global $wp_registered_widget_controls, $th_widgettitle_options;

	$params = func_get_args();
	$id = array_pop( $params );
	$callback = $wp_registered_widget_controls[$id]['callback_widget_title_redirect'];

	if( is_callable( $callback ) )
		call_user_func_array( $callback, $params );		
	
	$value = !empty( $th_widgettitle_options[$id ] ) ? htmlspecialchars( stripslashes( $th_widgettitle_options[$id ] ),ENT_QUOTES ) : '';


	if( isset( $params[0]['number']) )
		$number = $params[0]['number'];
		
	if( isset( $number ) && $number == -1 ) { 
		$number="%i%"; $value="";
	}
	
	$id_disp = $id;
	
	if( isset( $number ) ) {
		$id_disp = $wp_registered_widget_controls[$id]['id_base'].'-'.$number;
	}

	echo "<p><label for='".$id_disp."-widget_title'>".__('Html Title', 'langdirective')." </label>
	<textarea class='widefat' rows='16' cols='20' id='".$id_disp."-widget_title' name='".$id_disp."-widget_title'>".$value."</textarea>
	</p>";

}

/**
 * Callback Function for widget update.
 *
 * Saves the CSS class value that has been specified for the widget.
 */
function th_widget_title_widget_update_callback( $instance, $new_instance, $this_widget )
{	
	global $th_widgettitle_options;

	$widget_id = $this_widget->id;
	
	foreach( (array) $_POST['widget-id'] as $widget_number => $widget_id ) {
		
		if ( isset( $_POST[$widget_id.'-widget_title'] ) ) {
			//Filter input value
			if ( current_user_can('unfiltered_html') )
				$title_text =  $_POST[$widget_id.'-widget_title'];
			else
				$title_text = stripslashes( wp_filter_post_kses( addslashes($_POST[$widget_id.'-widget_title']) ) ); // wp_filter_post_kses() expects slashed
				
			$th_widgettitle_options[$widget_id] = $title_text;	
			update_option( 'widget_custom_title', $th_widgettitle_options );
			$instance['widget_id_title'] = $widget_id;
		}
		
	}
			
	return $instance;
}

/**
 * Callback function for the dynamic_sidebar_params
 *
 * Dynamically applies the specified CSS class to the widget when it is rendered at the front end.
 */
function th_filter_widget( $params ) {

	global $th_widgettitle_options;//echo "<pre>";print_r($params);echo "</pre>";
	
	if( isset( $th_widgettitle_options[$params[0]['widget_id']] ) && trim($th_widgettitle_options[$params[0]['widget_id']]) !='' ) {
		
		$params[0]['before_title']="";
		$params[0]['after_title']="";		
	}
	 
	return $params;
}

function th_filter_widget_title($title, $instance, $this_widget) 
 {
 
	global $th_widgettitle_options;
	if( isset( $instance['widget_id_title'] ) && trim( $instance['widget_id_title'] ) != '') {
		$title = stripslashes($th_widgettitle_options[$instance['widget_id_title']]);
	}
	return  $title;
	
}

?>