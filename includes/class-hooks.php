<?php
/**
 * All Hooks
 */

use InstaWP\Connect\Helpers\Helper;

class IWP_MU_Hooks {

	protected static $_instance = null;

	public function __construct() {
//		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_timer' ), 999 );
	}

	/**
	 * Return timer box content
	 *
	 * @return false|string
	 */
	function get_timer_content() {

		ob_start();
		include IWP_MU_PLUGIN_DIR . 'templates/timer-content.php';

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

		$site_status        = iwp_mu()::get_site_status();
		$remaining_secs     = (int) Helper::get_args_option( 'remaining_secs', $site_status );
		$remaining_secs_arr = iwp_get_time_left( $remaining_secs );

		$remaining_days    = (int) Helper::get_args_option( 'days', $remaining_secs_arr );
		$remaining_hours   = (int) Helper::get_args_option( 'hours', $remaining_secs_arr );
		$remaining_minutes = (int) Helper::get_args_option( 'minutes', $remaining_secs_arr );

		$admin_bar->add_node(
			array(
				'id'     => 'instawp-helper-timer',
				'parent' => 'top-secondary',
				'title'  => sprintf( '<span class="clock"></span><span class="distance" data-distance="%s"></span><span class="days">%sd</span>&nbsp;<span class="hours">%s:</span><span class="minutes">%s:</span><span class="seconds">00</span>',
					$remaining_secs, $remaining_days, $remaining_hours, $remaining_minutes
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
	 * @return IWP_MU_Hooks
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}

IWP_MU_Hooks::instance();