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
	function instawp_get_time_left( $seconds = 0 ) {

		$days    = floor( $seconds / ( 60 * 60 * 24 ) );
		$hours   = floor( ( $seconds % ( 60 * 60 * 24 ) ) / ( 60 * 60 ) );
		$minutes = floor( ( $seconds % ( 60 * 60 ) ) / 60 );
		$seconds = $seconds % 60;

		return array(
			'days'    => $days,
			'hours'   => $hours,
			'minutes' => $minutes,
			'seconds' => $seconds
		);
	}
}