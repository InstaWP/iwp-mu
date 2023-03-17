<?php
/**
 * WPDK SDK Client
 *
 * @version 1.0.9
 * @author WPDK
 */

namespace INSTAWP_HELPERS;


/**
 * Class Client
 *
 * @package INSTAWP_HELPERS
 */
class Client {

	public $plugin_name = null;
	public $text_domain = null;
	public $plugin_version = null;

	/**
	 * @var Utils
	 */
	private static $utils;


	/**
	 * Client constructor.
	 *
	 * @param $__file__
	 */
	function __construct( $__file__ ) {

		$this::utils();

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$plugin_data       = get_plugin_data( $__file__ );
		$this->plugin_name = Utils::get_args_option( 'Name', $plugin_data );
		$this->text_domain = Utils::get_args_option( 'TextDomain', $plugin_data );
	}


	/**
	 * Return Utils class
	 *
	 * @return Utils
	 */
	public function utils() {

		if ( ! class_exists( __NAMESPACE__ . '\Utils' ) ) {
			require_once __DIR__ . '/class-utils.php';
		}

		if ( ! self::$utils ) {
			self::$utils = new Utils( $this );
		}

		return self::$utils;
	}


	/**
	 * Print notices
	 *
	 * @param string $message
	 * @param string $type
	 * @param bool $is_dismissible
	 * @param bool $permanent_dismiss
	 */
	public function print_notice( $message = '', $type = 'success', $is_dismissible = true, $permanent_dismiss = false ) {

		$is_dismissible = $is_dismissible ? 'is-dismissible' : '';
		$pb_dismissible = '';

		if ( ! empty( $message ) ) {
			printf( '<div class="notice notice-%s %s">%s%s</div>', $type, $is_dismissible, $message, $pb_dismissible );
			?>
            <style>
                .pb-is-dismissible {
                    position: relative;
                }

                .notice-dismiss, .notice-dismiss:active, .notice-dismiss:focus {
                    top: 50%;
                    transform: translateY(-50%);
                    text-decoration: none;
                    outline: none;
                    box-shadow: none;
                }
            </style>
			<?php
		}
	}


	/**
	 * Translate function __()
	 */
	public function __trans( $text ) {
		return call_user_func( '__', $text, $this->text_domain );
	}


	/**
	 * Return Plugin Basename
	 *
	 * @return string
	 */
	public function basename() {
		return sprintf( '%1$s/%1$s.php', $this->text_domain );
	}
}
