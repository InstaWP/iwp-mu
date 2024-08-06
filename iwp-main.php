<?php
/**
 * IWP MU Plugin main file
 */

use InstaWP\Connect\Helpers\Helper;
use InstaWP\Connect\Helpers\Installer;

defined( 'IWP_MU_PLUGIN_DIR' ) || define( 'IWP_MU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
defined( 'IWP_MU_PLUGIN_URL' ) || define( 'IWP_MU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
defined( 'IWP_MU_CACHE_TIMEOUT' ) || define( 'IWP_MU_CACHE_TIMEOUT', 1800 );
defined( 'IWP_MU_PLUGIN_VERSION' ) || define( 'IWP_MU_PLUGIN_VERSION', '1.0.0' );

class IWP_MU_Main {

	protected static $_instance = null;
	protected static $_script_version = null;


	public function __construct() {
		self::$_script_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? current_time( 'U' ) : IWP_MU_PLUGIN_VERSION;

		remove_action( 'welcome_panel', 'wp_welcome_panel', 10 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
//		add_action( 'admin_notices', array( $this, 'display_dashboard' ) );
		add_action( 'welcome_panel', array( $this, 'display_dashboard' ), 10 );
		add_action( 'admin_init', array( $this, 'handle_dismissible_action' ) );
		add_action( 'wp_ajax_iwp_install_plugin', array( $this, 'ajax_install_plugin' ) );
	}

	public function ajax_install_plugin() {

		$plugin_slug    = isset( $_POST['plugin_slug'] ) ? sanitize_text_field( $_POST['plugin_slug'] ) : '';
		$plugin_zip_url = isset( $_POST['plugin_zip_url'] ) ? sanitize_text_field( $_POST['plugin_zip_url'] ) : '';
		$plugin_action  = isset( $_POST['plugin_action'] ) ? sanitize_text_field( $_POST['plugin_action'] ) : '';
		$plugin_file    = isset( $_POST['plugin_file'] ) ? sanitize_text_field( $_POST['plugin_file'] ) : '';
		$install_nonce  = isset( $_POST['install_nonce'] ) ? sanitize_text_field( $_POST['install_nonce'] ) : '';

		if ( ! wp_verify_nonce( $install_nonce, 'iwp_install_plugin' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Nonce verification failed!', 'iwp-mu' ) ] );
		}

		if ( $plugin_action === 'activate' ) {

			$activated = activate_plugin( $plugin_file );

			if ( is_wp_error( $activated ) ) {
				wp_send_json_error( [ 'message' => $activated->get_error_message() ] );
			}

			wp_send_json_success( [ 'message' => esc_html__( 'Successfully activated the plugin.', 'iwp-mu' ) ] );
		}

		$plugin_to_install = array(
			'type'     => 'plugin',
			'activate' => true,
		);

		if ( ! empty( $plugin_slug ) ) {
			$plugin_to_install['slug']   = $plugin_slug;
			$plugin_to_install['source'] = 'wp.org';
		} else if ( ! empty( $plugin_zip_url ) ) {
			$plugin_to_install['slug']   = $plugin_zip_url;
			$plugin_to_install['source'] = 'url';
		} else {
			wp_send_json_error( [ 'message' => esc_html__( 'Missing plugin information.', 'iwp-mu' ) ] );
		}

		$installer = new Installer( [ $plugin_to_install ] );
		$response  = $installer->start();

		wp_send_json_success( [ 'message' => esc_html__( 'Successfully installed the plugin.', 'iwp-mu' ), 'response' => $response ] );
	}

	public function handle_dismissible_action() {
		$iwp_hide_welcome_panel = isset( $_GET['iwp_hide_welcome_panel'] ) ? sanitize_text_field( $_GET['iwp_hide_welcome_panel'] ) : '';
		$iwp_nonce              = isset( $_GET['iwp_nonce'] ) ? sanitize_key( $_GET['iwp_nonce'] ) : '';

		if ( $iwp_hide_welcome_panel == 'yes' && wp_verify_nonce( $iwp_nonce, 'iwp_welcome_nonce' ) ) {
			update_user_meta( get_current_user_id(), 'iwp_welcome_panel_dismissed', true );
		}

		if ( isset( $_GET['iwp_clean'] ) && sanitize_text_field( $_GET['iwp_clean'] ) === 'yes' ) {
			delete_user_meta( get_current_user_id(), 'iwp_welcome_panel_dismissed' );
		}
	}

	public function display_dashboard() {

		wp_enqueue_script( 'iwp-mu-main' );

		wp_enqueue_style( 'iwp-mu-tailwind' );
		wp_enqueue_style( 'iwp-mu-style' );

		require_once IWP_MU_PLUGIN_DIR . 'templates/dashboard.php';
	}

	public function admin_scripts() {

		$localize_data = array(
			'ajax_url'          => admin_url( 'admin-ajax.php' ),
			'copy_text'         => esc_html__( 'Copied.', 'iwp-hosting-mig' ),
			'text_transferring' => esc_html__( 'Transferring...', 'iwp-hosting-mig' ),
		);

		wp_register_script( 'iwp-mu-main', plugins_url( '/assets/js/scripts.js', __FILE__ ), array( 'jquery' ), self::$_script_version );
		wp_localize_script( 'iwp-mu-main', 'iwp_mu_main', $localize_data );

		wp_register_style( 'iwp-mu-tailwind', IWP_MU_PLUGIN_URL . 'assets/css/tailwind.min.css', [], self::$_script_version );
		wp_register_style( 'iwp-mu-style', IWP_MU_PLUGIN_URL . 'assets/css/style.min.css', [ 'iwp-mu-tailwind' ], self::$_script_version );
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-hooks.php';

IWP_MU_Main::instance();