<?php

/**
 * Try to prevent direct access data leaks.
 **/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

/*
//Allow to override the error function
if ( ! function_exists( 'wp_handle_upload_error' ) ) {
    function wp_handle_upload_error( &$file, $message ) {
        return array( 'error'=> 'wp_handle_upload_error: '  . $message );
    }
}
*/

/**
 * Check if cds_upload function exist
 **/
if (! function_exists('cds_upload' )) {	

	function cds_upload(WP_REST_Request  $request) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		//On success it returns attachment ID
		$attachment_id = media_handle_upload('file', 0);

		//Check for errors
		if ( is_wp_error( $attachment_id ) ) 
		{
			// There was an error uploading the image.
			$error_string = $attachment_id->get_error_message();
			error_log('cds_upload: ' . $error_string);
			return new WP_Error( __('There was an error uploading the image: ', CDS_TEXT_DOMAIN) . $error_string  );
		} 
		else 
		{
			// The image was uploaded successfully!
			// $attachment_url = wp_get_attachment_url($attachment_id);
			return $attachment_id;
		}
	}

}// END: Check if cds_upload function exist.

}// END WooCommerce validation