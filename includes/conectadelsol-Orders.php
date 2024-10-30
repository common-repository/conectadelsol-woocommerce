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

/**
 * Check if CDS_Order_Extend class exist
 **/
if (! class_exists( 'CDS_Order_Extend' )) {	
	class CDS_Order_Extend{
		
		const REST_TYPE = 'shop_order'; //Type used for REST API

		public function __construct() {
			add_action( 'woocommerce_admin_order_data_after_billing_address', array( &$this, 'cds_my_custom_checkout_field_display_admin_order_meta'), 10, 1 );

			//Register special fields in REST API
			add_action( 'rest_api_init', array( &$this, 'cds_order_add_rest_data') );
		} 

		/**
		 * Display field value on the order edit page
		 */
		function cds_my_custom_checkout_field_display_admin_order_meta($order){
			$wc_pre_30 = version_compare( WC_VERSION, '3.0.0', '<' ); 
			$order_id  = $wc_pre_30 ? $order->id : $order->get_id();
		    echo '<p><strong>'.__(CDS_FSOL_FIELD, CDS_TEXT_DOMAIN).':</strong> ' . get_post_meta( $order_id, CDS_FSOL_FIELD, true ) . '</p>';
		}


		#region REST_API 	

			/**
			 * Add order custom fields to the REST API. 
			 */
			function cds_order_add_rest_data() {
				register_rest_field(self::REST_TYPE,
					CDS_FSOL_FIELD,
					array(
						'get_callback' => array($this, 'cds_get_order_field'),
						'update_callback' => array($this, 'cds_update_order_field'),
						'schema' => array(
							'description'   => __('FactuSol ID', CDS_TEXT_DOMAIN),
							'type'          => 'string',
							'context'       => array( 'view', 'edit' ),
						)
					)
				);
			}

			/**
			 * get_callback for the REST API. 
			 */
			function cds_get_order_field($object, $field_name, $request) {
				return get_post_meta($object[ 'id' ], $field_name, true);
			} 

			/**
			 * update_callback for the REST API. 
			 */
			function cds_update_order_field($value, $object, $field_name) {
				if (!$value || !is_string($value)) {
					return;
				} 

				return update_post_meta($object->ID, $field_name, strip_tags($value));
			}

		#endregion REST_API

	} // END: CDS_Order_Extend class
}// END: Check if CDS_Order_Extend class exist.

new CDS_Order_Extend();

} // END WooCommerce validation