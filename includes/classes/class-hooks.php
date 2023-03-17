<?php
/**
 * Class Hooks
 *
 * @author Pluginbazar
 */

use INSTAWP_HELPERS\Utils;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'INSTAWP_HELPER_Hooks' ) ) {
	/**
	 * Class INSTAWP_HELPER_Hooks
	 */
	class INSTAWP_HELPER_Hooks {

		protected static $_instance = null;


		/**
		 * INSTAWP_HELPER_Hooks constructor.
		 */
		function __construct() {
			add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_timer' ), 999 );
		}


		/**
		 * Return timer box content
		 *
		 * @return false|string
		 */
		function get_timer_content() {

			ob_start();
			include INSTAWP_HELPER_PLUGIN_DIR . 'templates/timer-content.php';

			return ob_get_clean();
		}


		/**
		 * Add timer in the admin bar
		 *
		 * @param WP_Admin_Bar $admin_bar
		 *
		 * @return void
		 */
		function add_admin_bar_timer( WP_Admin_Bar $admin_bar ) {

			$site_status        = instawp_helper()::get_site_status();
			$remaining_mins     = (int) Utils::get_args_option( 'remaining_mins', $site_status );
			$remaining_mins_arr = instawp_get_time_left( $remaining_mins );

			$remaining_days    = (int) Utils::get_args_option( 'days', $remaining_mins_arr );
			$remaining_hours   = (int) Utils::get_args_option( 'hours', $remaining_mins_arr );
			$remaining_minutes = (int) Utils::get_args_option( 'minutes', $remaining_mins_arr );

			$admin_bar->add_node(
				array(
					'id'     => 'instawp-helper-timer',
					'parent' => 'top-secondary',
					'title'  => sprintf( '<span class="clock"></span><span class="distance" data-distance="%s"></span><span class="days">%sd</span>&nbsp;<span class="hours">%s:</span><span class="minutes">%s:</span><span class="seconds">00</span>',
						( $remaining_mins * 60 ), $remaining_days, $remaining_hours, $remaining_minutes
					),
					'href'   => '#',
					'meta'   => array(
						'class' => 'instawp-helper-timer',
						'html'  => $this->get_timer_content(),
					)
				)
			);
		}


		/**
		 * @return INSTAWP_HELPER_Hooks
		 */
		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

INSTAWP_HELPER_Hooks::instance();