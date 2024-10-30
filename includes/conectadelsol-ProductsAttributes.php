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
 * Check if CDS_ProductAttribute_Extend class exist
 **/
if (! class_exists( 'CDS_ProductAttribute_Extend' )) {	
	class CDS_ProductAttribute_Extend{

		//const REST_TYPE = 'product_cat'; //Type used for REST API
		const FSOL_FIELD_PCAT_MAXLENGTH = 3;

		public function __construct() {
			/*
			add_action( 'init', array( &$this, 'cds_register_meta')  );
			add_action( 'product_cat_add_form_fields', array( &$this, 'cds_add_feature_group_field'), 10, 2  );
			add_action( 'manage_edit-product_cat_columns', array( &$this, 'cds_add_feature_group_column')  );
			add_action( 'created_product_cat', array( &$this, 'cds_save_feature_meta'), 10, 2  );
			add_action( 'product_cat_edit_form_fields', array( &$this, 'cds_edit_feature_group_field'), 10, 2  );
			add_action( 'edited_product_cat', array( &$this, 'cds_update_feature_meta'), 10, 2  );
			add_action( 'manage_product_cat_custom_column', array( &$this, 'cds_add_feature_group_column_content'), 10, 3  );
			add_action( 'rest_api_init', array( &$this, 'cds_product_cat_add_rest_data')  );
			*/
			add_action( 'woocommerce_after_add_attribute_fields', array( &$this, 'cds_product_attr_add')   );
		} 
 
		function cds_register_meta() {
	    	//register_meta( self::REST_TYPE, CDS_FSOL_FIELD, null);
		}


		function cds_product_attr_add(){
			woocommerce_wp_text_input( array(
				'id' => CDS_FSOL_FIELD,
				'label' =>  __(CDS_FSOL_FIELD, CDS_TEXT_DOMAIN),
				'description' =>  __('This is a custom field, you can write here anything you want.', CDS_TEXT_DOMAIN),
				'desc_tip' => 'true',
				'placeholder' =>  __('FactuSol Code', CDS_TEXT_DOMAIN)
			) );
		}


		#region UI_CUSTOM_FIELD

			/**
			 * Add new form: Create input field for custom data. 
			 **/
			function cds_add_feature_group_field($taxonomy) {	
				//THIS CODE WORKS, BUT LOG HAS ERRORS
				/*
				woocommerce_wp_text_input( array(
					'id' => CDS_FSOL_FIELD,
					'label' =>  __(CDS_FSOL_FIELD, CDS_TEXT_DOMAIN),
					'description' =>  __('This is a custom field, you can write here anything you want.', CDS_TEXT_DOMAIN),
					'desc_tip' => 'true',
					'placeholder' =>  __('FactuSol Code', CDS_TEXT_DOMAIN)
				) );
				*/

				 // this will add the custom meta field to the add new term page
				 ?>
				 <div class="form-field">
				 	<label for="<?php _e(CDS_FSOL_FIELD, CDS_TEXT_DOMAIN ); ?>"><?php _e(CDS_FSOL_FIELD, CDS_TEXT_DOMAIN ); ?></label>
				 	<input name="<?php _e(CDS_FSOL_FIELD, CDS_TEXT_DOMAIN ); ?>" id="<?php _e(CDS_FSOL_FIELD, CDS_TEXT_DOMAIN ); ?>" type="text" value="" size="40" maxlength="<?php echo self::FSOL_FIELD_PCAT_MAXLENGTH ?>]">
				    <p class="description"><?php _e( 'FactuSol code for relation.', CDS_TEXT_DOMAIN ); ?></p>
				 </div>
				 <?php
			}

			/**
			 *  Add new form: Save custom fields data.
			 **/
			function cds_save_feature_meta( $term_id, $tt_id ){
				if( isset( $_POST[CDS_FSOL_FIELD] ) && '' !== $_POST[CDS_FSOL_FIELD] ){
					$group = sanitize_title( $_POST[CDS_FSOL_FIELD] );
					add_term_meta( $term_id, CDS_FSOL_FIELD, $group, true );
				}
			}



			/**
			 *  Modify form: Create input field for custom data. 
			 **/
			function cds_edit_feature_group_field( $term, $taxonomy ){
				$term_value = get_term_meta( $term->term_id, CDS_FSOL_FIELD, true );	           

				?>
				<tr class="form-field term-group-wrap">
					<th scope="row"><label for="<?php echo(CDS_FSOL_FIELD); ?>"><?php _e(CDS_FSOL_FIELD, CDS_TEXT_DOMAIN); ?></label></th>
					<td>
					   <?php
							//THIS CODE WORKS, BUT LOG HAS ERRORS
							/*

							woocommerce_wp_text_input( array(
								'id' => CDS_FSOL_FIELD,
								'label' =>  '',
								'description' =>  __('This is a custom field, you can write here anything you want.', CDS_TEXT_DOMAIN),
								'desc_tip' => 'true',
								'placeholder' =>  __('Custom text', CDS_TEXT_DOMAIN),
								'value' =>  $term_value
							) );

							*/
					   ?>
					   <input type="text" class="postform" id="<?php _e(CDS_FSOL_FIELD, CDS_TEXT_DOMAIN ); ?>" name="<?php _e(CDS_FSOL_FIELD, CDS_TEXT_DOMAIN ); ?>" value="<?php echo esc_attr( $term_value ); ?>" size="40" maxlength="<?php echo self::FSOL_FIELD_PCAT_MAXLENGTH ?>]">
					   <p class="description"><?php _e( 'FactuSol code for relation.', CDS_TEXT_DOMAIN ); ?></p>
			   	   </td>
				</tr>
			   <?php
			}

			/**
			 * Modify form: Update custom fields data.
			 **/
			function cds_update_feature_meta( $term_id, $tt_id ){
				if( isset( $_POST[CDS_FSOL_FIELD] ) && '' !== $_POST[CDS_FSOL_FIELD] ){
					$group = sanitize_title( $_POST[CDS_FSOL_FIELD] );
					update_term_meta( $term_id, CDS_FSOL_FIELD, $group );
				}
			}

		#endregion UI_CUSTOM_FIELD


		#region COLUMN_LIST

			/**
			 * Create the column
			 **/
			function cds_add_feature_group_column( $columns ){
				$columns[CDS_FSOL_FIELD] = __(CDS_FSOL_FIELD, CDS_TEXT_DOMAIN);
				return $columns;
			}


			/*		
			// Makes the column sortable
			add_filter( 'manage_edit-product_cat_sortable_columns', 'cds_add_feature_group_column_sortable' );
			function cds_add_feature_group_column_sortable( $sortable ){
				$sortable[ CDS_FSOL_FIELD ] = CDS_FSOL_FIELD;
				return $sortable;
			}
			*/

			
			/**
			 * Set the column row data
			 **/
			function cds_add_feature_group_column_content( $content, $column_name, $term_id ){
				if( $column_name == CDS_FSOL_FIELD ){	
					$term_id = absint( $term_id );
					$term_value = get_term_meta( $term_id, CDS_FSOL_FIELD, true );

					if( !empty( $term_value) || $term_value === '0' ){
						$content .= esc_attr( $term_value );
					}
				} 

				return $content;
			}

	 	#endregion COLUMN_LIST

	 	#region REST_API

			/**
			 * Add product custom fields to the REST API. 
			 */
			function cds_product_cat_add_rest_data() {
				register_rest_field(self::REST_TYPE,
					CDS_FSOL_FIELD,
					array(
						'get_callback' => array($this, 'cds_product_cat_get_field'),
						'update_callback' => array($this, 'cds_product_cat_update_field'),
						'schema' => array(
							'description'   => __('FactuSol code', CDS_TEXT_DOMAIN), // 'ID de factusol'
							'type'          => 'string',
							'context'       => array( 'view', 'edit' ),
						)
					)
				);
			}


			/**
			 * get_callback for the REST API. 
			 */
			function cds_product_cat_get_field($object, $field_name, $request) {
				/* EXAMPLE JSON OBJ:
				    {"id":615,"name":"CACHI PISTACHI 51 - 51","slug":"seccion-004","parent":0,"description":"Seccion 001","display":"default","image":[],"menu_order":0,"count":0}
				*/ 

				return get_term_meta($object[ 'id' ], $field_name, true);
			} 



			/**
			 * update_callback for the REST API. 
			 */
			function cds_product_cat_update_field($value, $object, $field_name) {
				/* EXAMPLE JSON OBJ:
				    {"term_id":615,"name":"CACHI PISTACHI 51 - 51","slug":"seccion-004","term_group":0,"term_taxonomy_id":615,"taxonomy":"product_cat","description":"Seccion 001","parent":0,"count":0,"filter":"raw"}
				*/

				if(!isset($value) || !is_string($value)) {
					error_log( 'cds_product_cat_update_field ERROR: Propiedad no establecida');
					return;
				}

				return update_woocommerce_term_meta($object->term_id, $field_name, strip_tags($value)); 
			}

	  	#endregion REST_API

	}// END:CDS_ProductAttribute_Extend class.

}// END: Check if CDS_ProductAttribute_Extend class exist.

new CDS_ProductAttribute_Extend();

}// END WooCommerce validation