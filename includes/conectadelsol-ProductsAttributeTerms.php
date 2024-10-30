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
 * Check if CDS_ProductAttributeTerm_Extend class exist
 **/
if (! class_exists( 'CDS_ProductAttributeTerm_Extend' )) {	
	class CDS_ProductAttributeTerm_Extend{

		//const REST_TYPE = 'product_cat'; //Type used for REST API
		const FSOL_FIELD_PCAT_MAXLENGTH = 3;


		private $meta_key = 'cds_fsol';

		private $fsol_type_key = 'cds_fsol_type';
		private $fsol_value_key = 'cds_fsol_code';


		public function __construct() {
			add_action( 'admin_init', array($this, 'on_admin_init') );
		} 
 
		function cds_register_meta() {
	    	//register_meta( self::REST_TYPE, CDS_FSOL_FIELD, null);
		}

		public function on_admin_init() {

			if (isset($_REQUEST['taxonomy'])){
				$this->taxonomy = $_REQUEST['taxonomy'];
			}

			$attribute_taxonomies = wc_get_attribute_taxonomies();
			if ( $attribute_taxonomies ) {
				foreach ( $attribute_taxonomies as $tax ) {
					if ( is_admin() ) {
						add_action( 'created_term', array(&$this, 'woocommerce_attribute_thumbnail_field_save'), 10, 3 );
						add_action( 'edit_term', array(&$this, 'woocommerce_attribute_thumbnail_field_save'), 10, 3 );
					}

					add_action( 'pa_' . $tax->attribute_name . '_add_form_fields', array(&$this, 'woocommerce_add_attribute_thumbnail_field') );
					add_action( 'pa_' . $tax->attribute_name . '_edit_form_fields', array(&$this, 'woocommerce_edit_attributre_thumbnail_field'), 10, 2 );

					/*
					add_filter( 'manage_edit-pa_' . $tax->attribute_name . '_columns', array(&$this, 'woocommerce_product_attribute_columns') );
					add_filter( 'manage_pa_' . $tax->attribute_name . '_custom_column', array(&$this, 'woocommerce_product_attribute_column'), 10, 3 );
					*/

					//Hook for when the actual product attribute itself is modified. (for example slug change)
					add_action('woocommerce_attribute_updated', array($this, 'on_woocommerce_attribute_updated'), 10, 3);
				}
			}
		}

		//The field used when adding a new term to an attribute taxonomy
		public function woocommerce_add_attribute_thumbnail_field() {
			?>
			<div class="form-field ">
				<label for="product_attribute_cds_type<?php echo $this->meta_key; ?>"><?php _e( 'FSolType', CDS_TEXT_DOMAIN ); ?></label>
				<select name="product_attribute_meta[<?php echo $this->meta_key; ?>][FSolType]" id="product_attribute_cds_type<?php echo $this->meta_key; ?>" class="postform">
					<option value="-1"><?php _e( 'None', CDS_TEXT_DOMAIN ); ?></option>
					<option value="color"><?php _e( 'Color', CDS_TEXT_DOMAIN ); ?></option>
					<option value="talla"><?php _e( 'Size', CDS_TEXT_DOMAIN ); ?></option>
				</select>
			</div>

			<div class="form-field ">
				<label for="product_attribute_cds_code_<?php echo $this->meta_key; ?>"><?php _e( CDS_FSOL_FIELD, CDS_TEXT_DOMAIN ); ?></label>
				<input type="text" class="text"
						   id="product_attribute_cds_code_<?php echo $this->meta_key; ?>"
						   name="product_attribute_meta[<?php echo $this->meta_key; ?>][FSolCode]"
						   value="<?php echo $fsol_value; ?>" />
			</div>

			<?php
		}

		//The field used when editing an existing proeuct attribute taxonomy term
		public function woocommerce_edit_attributre_thumbnail_field( $term, $taxonomy ) {
			$type = get_woocommerce_term_meta( $term->term_id, $taxonomy . '_' . $this->fsol_type_key, true );
			$fsol_value = get_woocommerce_term_meta( $term->term_id, $taxonomy . '_' . $this->fsol_value_key, true );
			?>

			<tr class="form-field ">
				<th scope="row" valign="top"><label for="product_attribute_cds_type<?php echo $this->meta_key; ?>"><?php _e( 'FSolType', CDS_TEXT_DOMAIN  ); ?></label></th>
				<td>
					<select name="product_attribute_meta[<?php echo $this->meta_key; ?>][FSolType]" id="product_attribute_cds_type<?php echo $this->meta_key; ?>" class="postform">
						<option <?php selected( 'none', $type ); ?> value="-1"><?php _e( 'None', CDS_TEXT_DOMAIN ); ?></option>
						<option <?php selected( 'color', $type ); ?> value="color"><?php _e( 'Color', CDS_TEXT_DOMAIN ); ?></option>
						<option <?php selected( 'talla', $type ); ?> value="talla"><?php _e( 'Size', CDS_TEXT_DOMAIN ); ?></option>
					</select>
				</td>
			</tr>

			<tr class="form-field">
				<th scope="row" valign="top"><label for="product_attribute_cds_code_<?php echo $this->meta_key; ?>"><?php _e( CDS_FSOL_FIELD, CDS_TEXT_DOMAIN ); ?></label></th>
				<td>
					<input type="text" class="text"
						   id="product_attribute_cds_code_<?php echo $this->meta_key; ?>"
						   name="product_attribute_meta[<?php echo $this->meta_key; ?>][FSolCode]"
						   value="<?php echo $fsol_value; ?>" />
				</td>
			</tr>

			<?php
		}


		//Saves the product attribute taxonomy term data
		public function woocommerce_attribute_thumbnail_field_save( $term_id, $tt_id, $taxonomy ) {
			error_log('woocommerce_attribute_thumbnail_field_save-------------------');
			if ( isset( $_POST['product_attribute_meta'] ) ) {

				$metas = $_POST['product_attribute_meta'];
				if ( isset( $metas[$this->meta_key] ) ) {

					error_log('isset( $metas[$this->meta_key]');
					$data = $metas[$this->meta_key];

					$type = isset( $data['FSolType'] ) ? $data['FSolType'] : '';
					$code = isset( $data['FSolCode'] ) ? $data['FSolCode'] : '';


					update_woocommerce_term_meta( $term_id, $taxonomy . '_' . $this->fsol_type_key, $type );
					update_woocommerce_term_meta( $term_id, $taxonomy . '_' . $this->fsol_value_key, $code );
				}
			}

			error_log('END woocommerce_attribute_thumbnail_field_save-------------------');
		}

		/**
		 * When someone updates the actual product attribute itself.  Need to rename our hashes.
		 * @param $attribute_id
		 * @param $attribute
		 * @param $old_attribute_name
		 */
		public function on_woocommerce_attribute_updated($attribute_id, $attribute, $old_attribute_name){
			global $wpdb;
			error_log('on_woocommerce_attribute_updated------------------');

			$old_key =  md5( sanitize_title( 'pa_' . $old_attribute_name ) );
			$new_key = md5( sanitize_title( 'pa_' . $attribute['attribute_name'] ) );

			if ($old_key == $new_key){
				return;
			}

			//Update term meta next:
			$old_meta_key = 'pa_' . $old_attribute_name . '_';
			$new_meta_key = 'pa_' . $attribute['attribute_name'] . '_';

			error_log('on_woocommerce_attribute_updated: ' . $old_meta_key . ' | ' . $new_meta_key);
			error_log('on_woocommerce_attribute_updated: ' . $new_meta_key . $this->fsol_type_key . ' | ' . $old_meta_key . $this->fsol_type_key);

			$sql = $wpdb->prepare("UPDATE $wpdb->termmeta SET meta_key = %s WHERE meta_key = %s", $new_meta_key . $this->fsol_type_key, $old_meta_key . $this->fsol_type_key);
			$wpdb->query($sql);
			$wpdb->query($wpdb->prepare("UPDATE $wpdb->termmeta SET meta_key = %s WHERE meta_key = %s", $new_meta_key . $this->fsol_value_key, $old_meta_key . $this->fsol_value_key));

			error_log('END on_woocommerce_attribute_updated------------------');

			return;
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

	}// END:CDS_ProductAttributeTerm_Extend class.

}// END: Check if CDS_ProductAttributeTerm_Extend class exist.

new CDS_ProductAttributeTerm_Extend();

}// END WooCommerce validation