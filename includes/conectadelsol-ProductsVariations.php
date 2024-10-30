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
if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

/**
 * Check if CDS_Product_Variation_Extend class exist
 **/
if (! class_exists( 'CDS_Product_Variation_Extend' )) {	
	class CDS_Product_Variation_Extend{
		const REST_TYPE = 'product_variation'; //Type used for REST API
		const FSOL_FIELD_PRODUCT_MAXLENGTH = 13; //Max number of character for the FSolCode


		public function __construct() {
			// Add Variation Settings
			add_action( 'woocommerce_product_after_variable_attributes', array( &$this, 'cds_add_custom_fields'), 10, 3 );
			// Save Variation Settings
			add_action( 'woocommerce_save_product_variation', array( &$this, 'cds_save_custom_fields'), 10, 2 );

			add_action( 'rest_api_init', array( &$this, 'cds_product_add_rest_data') );
		}


		#region UI_CUSTOM_FIELD

			/**
			 * Create input field for custom data.
			 */
			function cds_add_custom_fields($loop, $variation_data, $variation) {
				// Print a custom text field
				woocommerce_wp_text_input( array(
					'id' => CDS_FSOL_FIELD . $loop,
					'name' => CDS_FSOL_FIELD . "[{$loop}]",
					'label' => __('FactuSol Id', CDS_TEXT_DOMAIN),
					'description' => __('FactuSol product code', CDS_TEXT_DOMAIN), //Id del producto en FactuSol
					'desc_tip' => 'true',
					'placeholder' => __('Introduce FactuSol product code', CDS_TEXT_DOMAIN), //Introduzca ID FactuSol
					'custom_attributes' => array(
						"maxlength" => self::FSOL_FIELD_PRODUCT_MAXLENGTH
					),
					'value' => get_post_meta( $variation->ID, CDS_FSOL_FIELD, true ),
				) );
			}

			/**
			 * Save custom fields data.
			 */
			function cds_save_custom_fields( $variation_id, $i ) {
				$FSolCode = $_POST[CDS_FSOL_FIELD];
				if ( isset( $FSolCode[$i] ) ) {
					update_post_meta( $variation_id, CDS_FSOL_FIELD, wc_clean( $FSolCode[$i] ) );
				}
			}

		#endregion UI_CUSTOM_FIELD

		#region REST_API 	

			/**
			 * Add product variation custom fields to the REST API. 
			 */
			function cds_product_add_rest_data() {
				register_rest_field(self::REST_TYPE,
					CDS_FSOL_FIELD,
					array(
						'get_callback' => array($this, 'cds_get_product_variation_field'),
						'update_callback' => array($this, 'cds_update_product_variation_field'),
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
			function cds_get_product_variation_field($object, $field_name, $request) {
			  return get_post_meta($object[ 'id' ], $field_name, true);
			} 

			/**
			 * update_callback for the REST API. 
			 */
			function cds_update_product_variation_field($value, $object, $field_name) {
				if (!$value || !is_string($value)) {
					return;
				} 

				return update_post_meta($object->ID, $field_name, strip_tags($value));
			}

		#endregion REST_API

	} // END: Class CDS_Product_Variation_Extend
}// END: Check if CDS_Product_Variation_Extend class exist.

new CDS_Product_Variation_Extend();

} // END: WooCommerce validation

