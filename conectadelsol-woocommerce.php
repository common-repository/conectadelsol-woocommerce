<?php

/**
 * Plugin Name: ConectaDelSol WooCommerce
 * Plugin URI: http://conectadelsol.es/
 * Description: Tools to integrate WooCommerce with FactuSol
 * Version: 1.0.2
 * Author: ConectaDelSol
 * Author URI: http://conectadelsol.es/
 * Developer: gguerra
 * License: GPL2
 * Text Domain: conectadelsol-woocommerce
 * Domain Path: /i18n/languages
 */ 


/**
 * Try to prevent direct access data leaks.
 **/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once( ABSPATH . 'wp-content/plugins/woocommerce/includes/api/class-wc-rest-authentication.php');
new WC_REST_Authentication();


/**
 * Check if WooCommerce is active
 **/
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins') ) ) ) {
	add_action( 'admin_notices', 'cds_woocoommerce_not_installed' );
}
else
{	
	if (! class_exists( 'ConectaDelSol' )) {	

		final class ConectaDelSol {

			/**
			 * ConectaDelSol version.
			 *
			 * @var string
			 */
			public $version = '1.0.2';

			/**
			 * The single instance of the class.
			 *
			 * @var ConectaDelSol
			 * @since 1.0.0
			 */
			protected static $_instance = null;

			/**
			 * Main ConectaDelSol Instance.
			 *
			 * Ensures only one instance of ConectaDelSol is loaded or can be loaded.
			 *
			 * @since 1.0.0
			 * @static
			 * @see WC()
			 * @return ConectaDelSol - Main instance.
			 */
			public static function instance() {
				if ( is_null( self::$_instance ) ) {
					self::$_instance = new self();
				}
				return self::$_instance;
			}
			
			public function __construct() {
				$this->define_constants();
				$this->includes();
				$this->init_hooks();

				/*
				// called just before the woocommerce template functions are included
				add_action( 'init', array( $this, 'include_template_functions' ), 20 );

				// called only after woocommerce has finished loading
				add_action( 'woocommerce_init', array( $this, 'woocommerce_loaded' ) );

				// called after all plugins have loaded
				add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

				// indicates we are running the admin
				if ( is_admin() ) {
				  // ...
				}

				// indicates we are being served over ssl
				if ( is_ssl() ) {
				  // ...
				}

				// take care of anything else that needs to be done immediately upon plugin instantiation, here in the constructor

				*/
			}


			/**
			 * Define constant if not already set.
			 *
			 * @param  string $name
			 * @param  string|bool $value
			 */
			private function define( $name, $value ) {
				if ( ! defined( $name ) ) {
					define( $name, $value );
				}
			}

			/**
			 * Define WC Constants.
			 */
			private function define_constants() {
				$upload_dir = wp_upload_dir();

				$this->define( 'CDS_PLUGIN_FILE', __FILE__ );
				$this->define( 'CDS_ABSPATH', dirname( __FILE__ ) . '/' );
				$this->define( 'CDS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
				$this->define( 'CDS_VERSION', $this->version );
				$this->define( 'CDS_LOG_DIR', $upload_dir['basedir'] . '/cds-logs/' );
				$this->define( 'CDS_TEXT_DOMAIN', "conectadelsol-woocommerce" );

				//Nombre del campo para codigos de factusol
				$this->define("CDS_FSOL_FIELD", "FSolCode");
			}

			/**
			 * Include required core files used in admin and on the frontend.
			 */
			public function includes() {
				require_once('includes/conectadelsol-Statuses.php');
				require_once('includes/conectadelsol-Countries.php');

				require_once('includes/conectadelsol-Products.php');
				require_once('includes/conectadelsol-ProductsCategories.php');
				require_once('includes/conectadelsol-ProductsVariations.php');
				require_once('includes/conectadelsol-ProductsAttributes.php');
				require_once('includes/conectadelsol-ProductsAttributeTerms.php');

				require_once('includes/conectadelsol-Customers.php');
				require_once('includes/conectadelsol-Orders.php');

				require_once('includes/conectadelsol-media-functions.php');
			}

			/**
			 * Init ConectaDelSol when WordPress Initialises.
			 */
			public function init() {
				// Before init action.
				do_action( 'before_conectadelsol_init' );

				// Set up localisation.
				$this->load_plugin_textdomain();

				// Init action.
				do_action( 'conectadelsol_init' );
			}


			/**
			 * Load Localisation files.
			 */
			public function load_plugin_textdomain() {
				$locale = apply_filters( 'plugin_locale', get_locale(), CDS_TEXT_DOMAIN );
				$plugin_locale_dir = plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages';

				//load_textdomain( self::CDS_TEXT_DOMAIN, WP_LANG_DIR . '/conectadelsol/conectadelsol-woocommerce-' . $locale . '.mo' );
				load_plugin_textdomain( CDS_TEXT_DOMAIN, false, $plugin_locale_dir );
			}


			/**
			 * Get the plugin url.
			 * @return string
			 */
			public function plugin_url() {
				return untrailingslashit( plugins_url( '/', __FILE__ ) );
			}

			/**
			 * Get the plugin path.
			 * @return string
			 */
			public function plugin_path() {
				return untrailingslashit( plugin_dir_path( __FILE__ ) );
			}


			/**
			 * Hook into actions and filters.
			 * @since  1.0.0
			 */
			private function init_hooks() {
				//register_activation_hook( __FILE__, array( 'WC_Install', 'install' ) );
				add_action( 'init', array( $this, 'init' ), 0 );

				add_action( 'rest_api_init', array( $this, 'cds_register_api_hooks') );
			}



			function cds_register_api_hooks() { 
				$version = '1';
			    $namespace = 'wc-conectadelsol/v' . $version;

				register_rest_route( $namespace, '/upload-file/', array(
					'methods' => WP_REST_Server::CREATABLE,
					'callback' => 'cds_upload',
					/*
					'permission_callback' => function(){
							return current_user_can('upload_files');
						},
					*/
				) );

				
				


				
				register_rest_route( $namespace, '/server-time/', array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => function(){
							return time(); 
						},
					'permission_callback' => function(){
							return current_user_can('administrator');
						},
				) );

				register_rest_route( $namespace, '/get-nonce/', array(
				

					'methods' => WP_REST_Server::READABLE,
					'callback' => function(){
							error_log(json_encode('endpoint /get-nonce/' . wp_get_current_user()->user_login));
							return wp_create_nonce( 'wp_rest' ); 
							

						},
					'permission_callback' => function(){
							return current_user_can('administrator');
						},
				) );


				register_rest_route( $namespace, '/get-user/', array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => function(){
							return wp_get_current_user(); 
						},
					'permission_callback' => function(){
							return current_user_can('administrator');
						},
				) );
			}


		}//END: ConectaDelSol class. 
	} // END: Comprueba si existe la clase ConectaDelSol


	/**
	 * Main instance of ConectaDelSol.
	 *
	 * Returns the main instance of ConectaDelSol to prevent the need to use globals.
	 *
	 * @since  1.0.0
	 * @return ConectaDelSol
	 */
	function CDS() {
		return ConectaDelSol::instance();
	}

	CDS();

} // END WooCommerce validation




/**
* WooCommerce Not Installed Notice
**/
if ( ! function_exists( 'cds_woocoommerce_not_installed' ) ) {

	function cds_woocoommerce_not_installed() {

		echo '<div class="error"><p>' . sprintf( __( 'ConectaDelSol WooCommerce requires %s to be installed and active.', CDS_TEXT_DOMAIN ), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a>' ) . '</p></div>';

	}

}