<?php
/*
	Plugin Name: InstaWP Helper
	Plugin URI: https://instawp.com/
	Description: Helper plugin for InstaWP
	Version: 1.0.1
	Text Domain: instawp-helper
	Author: InstaWP Team
	Author URI: https://instawp.com/
	License: GPL-3.0+
	License URI: http://www.gnu.org/copyleft/gpl.html
*/

defined( 'ABSPATH' ) || exit;
defined( 'INSTAWP_HELPER_PLUGIN_URL' ) || define( 'INSTAWP_HELPER_PLUGIN_URL', str_replace( ABSPATH, site_url( '/' ), dirname( __FILE__ ) ) . '/' );
defined( 'INSTAWP_HELPER_PLUGIN_DIR' ) || define( 'INSTAWP_HELPER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
defined( 'INSTAWP_HELPER_PLUGIN_FILE' ) || define( 'INSTAWP_HELPER_PLUGIN_FILE', plugin_basename( __FILE__ ) );
defined( 'INSTAWP_HELPER_PLUGIN_VERSION' ) || define( 'INSTAWP_HELPER_PLUGIN_VERSION', '1.0.1' );
defined( 'INSTAWP_HELPER_API_BASE' ) || define( 'INSTAWP_HELPER_API_BASE', 'https://stage.instawp.io' );
defined( 'INSTAWP_HELPER_CACHE_TIMEOUT' ) || define( 'INSTAWP_HELPER_CACHE_TIMEOUT', 1800 );

if ( ! class_exists( 'INSTAWP_HELPER_Main' ) ) {
	/**
	 * Class INSTAWP_HELPER_Main
	 */
	class INSTAWP_HELPER_Main {

		protected static $_instance = null;

		protected static $_script_version = null;


		/**
		 * INSTAWP_HELPER_Main constructor.
		 */
		function __construct() {

			self::$_script_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? current_time( 'U' ) : INSTAWP_HELPER_PLUGIN_VERSION;

			$this->load_scripts();
			$this->load_helpers();
			$this->load_classes_functions();

			add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );
		}


		/**
		 * Load Text Domain
		 */
		function load_text_domain() {
			load_plugin_textdomain( 'instawp-helper', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
		}


		/**
		 * Include Classes and Functions
		 */
		function load_classes_functions() {
			require_once INSTAWP_HELPER_PLUGIN_DIR . 'includes/classes/class-functions.php';
			require_once INSTAWP_HELPER_PLUGIN_DIR . 'includes/functions.php';
			require_once INSTAWP_HELPER_PLUGIN_DIR . 'includes/classes/class-hooks.php';
		}


		/**
		 * Load helpers
		 */
		function load_helpers() {

			if ( ! class_exists( 'INSTAWP_HELPERS\Client' ) ) {
				require_once( plugin_dir_path( __FILE__ ) . 'includes/helpers/class-client.php' );
			}

			global $instawp_helpers;

			$instawp_helpers = new INSTAWP_HELPERS\Client( __FILE__ );
		}


		/**
		 * Localize Scripts
		 *
		 * @return mixed|void
		 */
		function localize_scripts() {
			return apply_filters( 'INSTAWP_HELPER/Filters/localize_scripts', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			) );
		}


		/**
		 * Load front Scripts
		 */
		function front_scripts() {

			wp_enqueue_script( 'instawp-helper', plugins_url( '/assets/both/js/scripts.js', __FILE__ ), array( 'jquery' ), self::$_script_version );
			wp_localize_script( 'instawp-helper', 'instawp_helper', $this->localize_scripts() );

			wp_enqueue_style( 'instawp-helper', INSTAWP_HELPER_PLUGIN_URL . 'assets/both/css/style.css', self::$_script_version );
		}


		/**
		 * Load Admin Scripts
		 */
		function admin_scripts() {

			wp_enqueue_script( 'instawp-helper', plugins_url( '/assets/both/js/scripts.js', __FILE__ ), array( 'jquery' ), self::$_script_version );
			wp_localize_script( 'instawp-helper', 'instawp_helper', $this->localize_scripts() );

			wp_enqueue_style( 'instawp-helper', INSTAWP_HELPER_PLUGIN_URL . 'assets/both/css/style.css', self::$_script_version );
		}


		/**
		 * Load Scripts
		 */
		function load_scripts() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
		}


		/**
		 * @return INSTAWP_HELPER_Main
		 */
		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

INSTAWP_HELPER_Main::instance();
