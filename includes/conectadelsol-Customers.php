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
 * Check if CDS_Customer_Extend class exist
 **/
if (! class_exists( 'CDS_Customer_Extend' )) {	
	class CDS_Customer_Extend{

  		public function __construct() {
			add_action( 'woocommerce_after_order_notes', array( &$this, 'cds_NIF_FIELD') );
			add_action( 'woocommerce_checkout_process', array( &$this, 'cds_check_nif_field') );
			add_action( 'woocommerce_checkout_update_order_meta', array( &$this, 'cds_actualizar_info_pedido_con_nuevo_campo') );
			add_action( 'woocommerce_admin_order_data_after_billing_address', array( &$this, 'cds_mostrar_campo_personalizado_en_admin_pedido'), 10, 1 );
			add_action( 'woocommerce_email_order_meta_keys', array( &$this, 'cds_muestra_campo_personalizado_email') );
		}


		/**
		 * Add NIF field to checkout page.
		 * Añade el campo NIF a la página de checkout de WooCommerce.
		 */
		function cds_NIF_FIELD( $checkout ) {
			echo '<div id="additional_checkout_field"><h2>' . __('Additional Information', CDS_TEXT_DOMAIN) . '</h2>';

			woocommerce_form_field( 'nif',
			  array(
			    'type'          => 'text',
			    'class'         => array('my-field-class form-row-wide'),
			    'label'         => __('NIF-DNI', CDS_TEXT_DOMAIN),
			    'required'      => true,
			    'placeholder'   => __('Enter the ID card number', CDS_TEXT_DOMAIN),
			  ),
			  $checkout->get_value('nif')
			);

			echo '</div>';
		}

		/**
		 * Check that NIF field is not empty.
		 * Comprueba que el campo NIF no esté vacío.
		 */
		function cds_check_nif_field() {		    
		    // Comprueba si se ha introducido un valor y si está vacío se muestra un error.
		    if ( ! $_POST['nif'] )
		        wc_add_notice( __( 'ID card number is a required field. You must enter your ID number to complete the purchase.', CDS_TEXT_DOMAIN ), 'error' );
		}

		/**
		 * Update order info with the new field data.
		 * Actualiza la información del pedido con el nuevo campo.
		 */		 
		function cds_actualizar_info_pedido_con_nuevo_campo( $order_id ) {
		    if ( ! empty( $_POST['nif'] ) ) {
		        update_post_meta( $order_id, 'NIF', sanitize_text_field( $_POST['nif'] ) );
		    }
		}

		/**
		 * Show new field (NIF) value at order edit page
		 * Muestra el valor del nuevo campo NIF en la página de edición del pedido
		 */
		function cds_mostrar_campo_personalizado_en_admin_pedido($order){			
			$wc_pre_30 = version_compare( WC_VERSION, '3.0.0', '<' ); 
			$order_id  = $wc_pre_30 ? $order->id : $order->get_id();
		    echo '<p><strong>'.__('NIF', CDS_TEXT_DOMAIN).':</strong> ' . get_post_meta( $order_id, 'NIF', true ) . '</p>';
		}

		/**
		 * Include NIF field at email notification.
		 * Incluye el campo NIF en el email de notificación del cliente.
		 */
		function cds_muestra_campo_personalizado_email( $keys ) {
		    $keys[] = 'NIF';
		    return $keys;
		}


		/**
		 * Include NIF on invoice (WooCommerce PDF Invoices & Packing Slips plugin needed)
		 * Incluir NIF en la factura (necesario el plugin WooCommerce PDF Invoices & Packing Slips)
		 */
		/*

		add_filter( 'wpo_wcpdf_billing_address', 'cds_incluir_nif_en_factura' );		 
		function cds_incluir_nif_en_factura( $address ){
		  global $wpo_wcpdf;
		 
		  echo $address . '<p>';
		  $wpo_wcpdf->custom_field( 'NIF', 'NIF: ' );
		  echo '</p>';
		}

		*/

	}// END: CDS_Customer_Extend class
}// END: Check if CDS_Customer_Extend class exist.

new CDS_Customer_Extend();

} // END WooCommerce validation