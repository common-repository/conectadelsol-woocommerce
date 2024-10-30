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
 * Check if CDS_Countries class exist
 **/
if (! class_exists( 'CDS_Countries' )) {	
	class CDS_Countries{

  		public function __construct() {
  			add_action( 'rest_api_init', array( $this, 'cds_register_countries_api_hooks') );
		}


		/**
		 * Register countries endpoints
		 */
		function cds_register_countries_api_hooks( $checkout ) {
			$version = '1';
			$namespace = 'wc-conectadelsol/v' . $version;

			//Get all the countries
			register_rest_route( $namespace, '/countries/', array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => function () {
						return WC()->countries->countries;
					},
			) );	

			//Get all the countries
			register_rest_route( $namespace, '/countries/(?P<country_code>\D{2})', array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => function ($data) {
						$country_code = $data['country_code']; 

						if (array_key_exists($country_code,WC()->countries->countries))
  						{
  							return WC()->countries->countries[ $country_code ];
  						}
  						else
  						{
  							return null;
  						}						
					},
				'args' => array(
					'country_code' => array(
							// description should be a human readable description of the argument.
					        'description' => esc_html__( 'The filter parameter is used to filter the collection of countries', CDS_TEXT_DOMAIN ),
					        // type specifies the type of data that the argument should be.
					        'type'        => 'string',
					    ),
				),
			) );		 
		}  



	}// END: CDS_Countries class
}// END: Check if CDS_Countries class exist.

new CDS_Countries();

} // END WooCommerce validation