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
 * Check if CDS_Product_Extend class exist
 **/
if (! class_exists( 'CDS_Product_Extend' )) {	
	class CDS_Product_Extend{

		const REST_TYPE = 'product'; //Type used for REST API
		const FSOL_FIELD_PRODUCT_MAXLENGTH = 13; //Max number of character for the FSolCode


		public function __construct() {
			add_action( 'woocommerce_product_options_general_product_data', array( &$this, 'cds_add_custom_fields')  );
			add_action( 'woocommerce_process_product_meta', array( &$this, 'cds_save_custom_fields') );
			add_filter( 'manage_edit-product_columns', array( &$this, 'cds_add_product_column' ) );
			add_filter( 'manage_product_posts_custom_column', array( &$this, 'cds_add_product_column_content') , 10, 3 );
			add_action( 'rest_api_init', array( &$this, 'cds_product_add_rest_data') );
		}


		#region UI_CUSTOM_FIELD

			/**
			 * Create input field for custom data.
			 */
			function cds_add_custom_fields() {
				// Print a custom text field
				woocommerce_wp_text_input( array(
					'id' => CDS_FSOL_FIELD,
					'label' => __('FactuSol Id', CDS_TEXT_DOMAIN),
					'description' => __('FactuSol product code', CDS_TEXT_DOMAIN), //Id del producto en FactuSol
					'desc_tip' => 'true',
					'placeholder' => __('Introduce FactuSol product code', CDS_TEXT_DOMAIN), //Introduzca ID FactuSol
					'custom_attributes' => array(
						"maxlength" => self::FSOL_FIELD_PRODUCT_MAXLENGTH
					)
				) );
			}

			/**
			 * Save custom fields data.
			 */
			function cds_save_custom_fields( $post_id ) {
				if ( ! empty( $_POST[CDS_FSOL_FIELD] ) ) {
					update_post_meta( $post_id, CDS_FSOL_FIELD, esc_attr( $_POST[CDS_FSOL_FIELD] ) );
				}
			}

		#endregion UI_CUSTOM_FIELD

	 	#region COLUMN_AT_LIST

			/**
			 * Create column at products list
			 */
			function cds_add_product_column( $columns ){
				$columns[CDS_FSOL_FIELD] = __( CDS_FSOL_FIELD, CDS_TEXT_DOMAIN);
				return $columns;
			}


			/*
			// Makes column sortable
			add_filter( 'manage_edit-product_sortable_columns', 'add_product_column_sortable' );
			function add_product_column_sortable( $sortable ){
				$sortable[CDS_FSOL_FIELD ] = CDS_FSOL_FIELD;
				return $sortable;
			}
			*/

			/**
			 * Set the column row data
			 */
			function cds_add_product_column_content( $column, $postid ) {
				if (CDS_FSOL_FIELD ==  $column) {
					$return_data = get_post_meta( $postid, CDS_FSOL_FIELD, true );	
					echo $return_data;
				}
			}

		#endregion COLUMN_AT_LIST

		#region REST_API 	

			/**
			 * Add product custom fields to the REST API. 
			 */
			function cds_product_add_rest_data() {
				register_rest_field(self::REST_TYPE,
					CDS_FSOL_FIELD,
					array(
						'get_callback' => array($this, 'slug_get_field'),
						'update_callback' => array($this, 'slug_update_field'),
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
			function slug_get_field($object, $field_name, $request) {
			  return get_post_meta($object[ 'id' ], $field_name, true);
			} 

			/**
			 * update_callback for the REST API. 
			 */
			function slug_update_field($value, $object, $field_name) {
				if (!$value || !is_string($value)) {
					return;
				} 

				return update_post_meta($object->ID, $field_name, strip_tags($value));
			}

		#endregion REST_API

	} // END: Class CDS_Product_Extend
}// END: Check if CDS_Product_Extend class exist.

new CDS_Product_Extend();

} // END: WooCommerce validation

