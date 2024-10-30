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
 * Check if CDS_OrderStatus_Extend class exist
 **/
if (! class_exists( 'CDS_OrderStatus_Extend' )) {	
	class CDS_OrderStatus_Extend{

		public function __construct() {
			add_action( 'init', array( &$this, 'cds_register_my_new_order_statuses') );
			add_action( 'wc_order_statuses', array( &$this, 'cds_my_new_wc_order_statuses') );
			add_action( 'wp_print_scripts', array( &$this, 'cds_skyverge_add_custom_order_status_icon') );

			add_action( 'rest_api_init', array( $this, 'cds_register_status_api_hooks') );
		} 


		#region REST_API
			function cds_register_status_api_hooks() { 
				$version = '1';
			    $namespace = 'wc-conectadelsol/v' . $version;

				register_rest_route( $namespace, '/get-statuses/', array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => function () {
						return wc_get_order_statuses();
					}
				) );

			}

		#endregion REST_API

		// CREATE CUSTOM STATUS--------------------------------------------- 
		function cds_register_my_new_order_statuses() {
			register_post_status( 'wc-invoiced', array(
				'label'                     => _x( 'Invoiced', 'Order status', CDS_TEXT_DOMAIN ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Invoiced <span class="count">(%s)</span>', 'Invoiced<span class="count">(%s)</span>', CDS_TEXT_DOMAIN )
			) );
		}

		// Register in wc_order_statuses.
		function cds_my_new_wc_order_statuses( $order_statuses ) {
			$order_statuses['wc-invoiced'] = _x( 'Invoiced', 'Order status', CDS_TEXT_DOMAIN );
			return $order_statuses;
		}


		//         IMAGEN DEL ESTADO
		function cds_skyverge_add_custom_order_status_icon() {
			if( ! is_admin() ) { 
				return; 
			}

			?>
			<style>
				/* Add custom status order icons */
				.column-order_status mark.invoiced,
				.column-order_status mark.building {
					content: url(<?php echo ConectaDelSol::instance()->plugin_url() ?>/assets/images/icons/order-status/CustomOrderStatus1.png);

				}
				/* Repeat for each different icon; tie to the correct status */
			</style> 
			<?php
		}

	}// END: CDS_OrderStatus_Extend class
}// END: Check if CDS_OrderStatus_Extend class exist.

new CDS_OrderStatus_Extend();

}// END WooCommerce validation