<?php
/**
 * Helper Functions
 */

defined( 'ABSPATH' ) || exit;


if ( ! function_exists( 'instawp_helper' ) ) {
	/**
	 * @return INSTAWP_HELPER_Functions
	 */
	function instawp_helper() {
		global $instawp_helper;

		if ( empty( $instawp_helper ) ) {
			$instawp_helper = new INSTAWP_HELPER_Functions();
		}

		return $instawp_helper;
	}
}


if ( ! function_exists( 'instawp_get_time_left' ) ) {
	/**
	 * Return formatted array for time left
	 *
	 * @param $minutes
	 *
	 * @return array
	 */
	function instawp_get_time_left( $minutes = 0 ) {

		$minutes = (int) $minutes;

		if ( empty( $minutes ) ) {
			return array( 'days' => 0, 'hours' => 0, 'minutes' => 0, );
		}

		$days    = 0;
		$hours   = str_pad( floor( $minutes / 60 ), 2, "0", STR_PAD_LEFT );
		$minutes = str_pad( $minutes % 60, 2, "0", STR_PAD_LEFT );

		if ( (int) $hours > 24 ) {
			$days  = str_pad( floor( $hours / 24 ), 2, "0", STR_PAD_LEFT );
			$hours = str_pad( $hours % 24, 2, "0", STR_PAD_LEFT );
		}

		return array(
			'days'    => $days,
			'hours'   => $hours,
			'minutes' => $minutes,
		);
	}
}