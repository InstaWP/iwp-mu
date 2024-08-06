<?php
/**
 * All the functions goes here
 */


use InstaWP\Connect\Helpers\Helper;

if ( ! function_exists( 'iwp_get_current_admin_url' ) ) {
	/**
	 * Return current admin URL
	 *
	 * @param $query_param
	 *
	 * @return string|null
	 */
	function iwp_get_current_admin_url( $query_param = [] ) {

//		$screen = get_current_screen();
//		$base_url = admin_url( $screen->base . '.php' );
		$base_url = admin_url();
		$query    = $_SERVER['QUERY_STRING'];

		if ( ! empty( $query ) ) {
			$base_url .= '?' . $query;
		}

		if ( is_array( $query_param ) && ! empty( $query_param ) ) {
			$base_url = add_query_arg( $query_param, $base_url );
		}

		return $base_url;
	}
}

if ( ! function_exists( 'iwp_is_plugin_active' ) ) {
	/**
	 * Return if a plugin is activated or not
	 *
	 * @param $plugin_file
	 *
	 * @return bool
	 */
	function iwp_is_plugin_active( $plugin_file = '' ) {

		if ( empty( $plugin_file ) ) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin_file );
	}
}

if ( ! function_exists( 'iwp_is_plugin_installed' ) ) {
	/**
	 * Return if a plugin is installed or not
	 *
	 * @param $plugin_file
	 *
	 * @return bool
	 */
	function iwp_is_plugin_installed( $plugin_file = '' ) {

		if ( empty( $plugin_file ) ) {
			return false;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return in_array( $plugin_file, array_keys( get_plugins() ) );
	}
}

if ( ! function_exists( 'iwp_mu' ) ) {
	/**
	 * @return INSTAWP_MU_Functions
	 */
	function iwp_mu() {
		global $iwp_mu;

		if ( empty( $iwp_mu ) ) {
			$iwp_mu = new INSTAWP_MU_Functions();
		}

		return $iwp_mu;
	}
}

if ( ! function_exists( 'iwp_get_time_left' ) ) {
	/**
	 * Return formatted array for time left
	 *
	 * @param $minutes
	 *
	 * @return array
	 */
	function iwp_get_time_left( $seconds = 0 ) {

		if ( ! is_int( $seconds ) || $seconds < 0 ) {
			return array(
				'days'    => 0,
				'hours'   => 0,
				'minutes' => 0,
				'seconds' => 0
			);
		}

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