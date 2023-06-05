<?php
/**
 * Class Functions
 */

use INSTAWP_HELPERS\Utils;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'INSTAWP_HELPER_Functions' ) ) {
	class INSTAWP_HELPER_Functions {

		protected static $_instance = null;

		protected static $_site_status = null;

		protected static $_transient_key = 'instawp_site_status';


		/**
		 * INSTAWP_HELPER_Functions constructor.
		 */
		function __construct() {
			self::set_site_status_data();
		}


		/**
		 * Check and set site status data
		 *
		 * @return void
		 */
		private static function set_site_status_data() {

			self::$_site_status = Utils::get_option( self::$_transient_key, array() );
			$update_time        = Utils::get_args_option( 'update_time', self::$_site_status, '0' );

			if ( empty( self::$_site_status ) ) {
				self::set_site_status();

				return;
			}

			if ( ( current_time( 'U' ) - $update_time ) > INSTAWP_HELPER_CACHE_TIMEOUT ) {
				self::set_site_status();
			} else {
				$last_update_time     = (int) Utils::get_args_option( 'update_time', self::$_site_status );
				$site_status_data     = Utils::get_args_option( 'data', self::$_site_status, array() );
				$remaining_secs       = (int) Utils::get_args_option( 'remaining_secs', $site_status_data, 0 );
				$actual_remaining_sec = round( ( $remaining_secs - ( current_time( 'U' ) - $last_update_time ) ) );

				self::$_site_status['data']['remaining_secs'] = $actual_remaining_sec;
			}
		}


		/**
		 * Set site status from API
		 *
		 * @return void
		 */
		private static function set_site_status() {

			$domain_name = str_replace( array( 'https://', 'http://' ), '', site_url() );
//			$domain_name      = 'eyed-bison-cayi.a.instawpsites.com';
			$site_status      = Utils::do_curl( 'sites/get-basic-details?domain=' . $domain_name, [], [], false );
			$site_status_data = Utils::get_args_option( 'data', $site_status, array() );

			if ( empty( $site_status_data ) ) {
				$site_status_data = array(
					'type'           => '',
					'remaining_mins' => 0,
					'current_status' => '',
				);
			}

			$site_status_data['remaining_secs'] = (int) ( $site_status_data['remaining_mins'] ?? 0 ) * 60;

			$transient_data = array(
				'data'        => $site_status_data,
				'update_time' => current_time( 'U' ),
			);

			self::$_site_status = $transient_data;

			update_option( self::$_transient_key, $transient_data );
		}


		/**
		 * Get site status
		 *
		 * @return mixed|null
		 */
		public static function get_site_status() {
			return Utils::get_args_option( 'data', self::$_site_status, array() );
		}


		/**
		 * @return INSTAWP_HELPER_Functions
		 */
		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

global $instawp_helper;

$instawp_helper = INSTAWP_HELPER_Functions::instance();