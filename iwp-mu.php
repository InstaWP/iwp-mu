<?php
/**
 * Plugin Name: InstaWP MU
 * Description: Welcome dashboard and site status for InstaWP sites
 * Version: 2.0.0
 * Author: InstaWP
 * License: GPL-2.0+
 */

defined( 'ABSPATH' ) || exit;

// Constants
defined( 'IWP_MU_CACHE_TIMEOUT' ) || define( 'IWP_MU_CACHE_TIMEOUT', 1800 ); // 30 minutes
defined( 'IWP_MU_PLUGIN_VERSION' ) || define( 'IWP_MU_PLUGIN_VERSION', '2.0.0' );

// ============================================================================
// Helper Functions
// ============================================================================

if ( ! function_exists( 'iwp_get_current_admin_url' ) ) {
	/**
	 * Return current admin URL with optional query params
	 */
	function iwp_get_current_admin_url( $query_param = [] ) {
		$base_url = admin_url();
		$query    = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( $_SERVER['QUERY_STRING'] ) : '';

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
	 * Return if a plugin is activated
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
	 * Return if a plugin is installed
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

if ( ! function_exists( 'iwp_get_time_left' ) ) {
	/**
	 * Return formatted array for time left from seconds
	 */
	function iwp_get_time_left( $seconds = 0 ) {
		if ( ! is_int( $seconds ) || $seconds < 0 ) {
			return [ 'days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0 ];
		}

		return [
			'days'    => floor( $seconds / ( 60 * 60 * 24 ) ),
			'hours'   => floor( ( $seconds % ( 60 * 60 * 24 ) ) / ( 60 * 60 ) ),
			'minutes' => floor( ( $seconds % ( 60 * 60 ) ) / 60 ),
			'seconds' => $seconds % 60
		];
	}
}

if ( ! function_exists( 'iwp_api_request' ) ) {
	/**
	 * Make API request to InstaWP backend (replaces Curl::do_curl)
	 */
	function iwp_api_request( $endpoint, $method = 'GET', $body = [] ) {
		$api_url = defined( 'JETRAILS_API_URL' ) ? JETRAILS_API_URL : 'https://app.instawp.io/api/v2/';

		$args = [
			'timeout' => 30,
			'headers' => [ 'Accept' => 'application/json' ],
		];

		if ( $method === 'POST' ) {
			$args['body'] = $body;
			$response = wp_remote_post( $api_url . $endpoint, $args );
		} else {
			$response = wp_remote_get( $api_url . $endpoint, $args );
		}

		if ( is_wp_error( $response ) ) {
			return [ 'success' => false, 'data' => null ];
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}
}

// ============================================================================
// Main Plugin Class
// ============================================================================

if ( ! class_exists( 'IWP_MU' ) ) {

	class IWP_MU {

		protected static $_instance = null;
		protected static $_site_status = null;
		protected static $_transient_key = 'instawp_site_status';

		/**
		 * Constructor - register all hooks
		 */
		public function __construct() {
			// Initialize site status cache
			$this->set_site_status_data();

			// Remove default WordPress welcome panel
			remove_action( 'welcome_panel', 'wp_welcome_panel', 10 );

			// Register hooks
			add_action( 'welcome_panel', [ $this, 'display_dashboard' ], 10 );
			add_action( 'admin_init', [ $this, 'handle_dismissible_action' ] );
			add_action( 'wp_ajax_iwp_install_plugin', [ $this, 'ajax_install_plugin' ] );
		}

		/**
		 * Check and set site status data from cache or API
		 */
		private function set_site_status_data() {
			self::$_site_status = get_option( self::$_transient_key );
			$update_time = self::$_site_status['update_time'] ?? 0;

			if ( empty( self::$_site_status ) ) {
				$this->fetch_site_status();
				return;
			}

			if ( is_numeric( $update_time ) && ( current_time( 'U' ) - $update_time ) > IWP_MU_CACHE_TIMEOUT ) {
				$this->fetch_site_status();
			} else {
				// Calculate actual remaining seconds
				$last_update_time = (int) ( self::$_site_status['update_time'] ?? 0 );
				$remaining_secs = (int) ( self::$_site_status['data']['remaining_secs'] ?? 0 );
				$actual_remaining_sec = round( $remaining_secs - ( current_time( 'U' ) - $last_update_time ) );
				self::$_site_status['data']['remaining_secs'] = max( 0, $actual_remaining_sec );
			}
		}

		/**
		 * Fetch site status from API
		 */
		private function fetch_site_status() {
			$domain_name = str_replace( [ 'https://', 'http://' ], '', site_url() );
			$site_status = iwp_api_request( 'sites/get-basic-details?domain=' . $domain_name );

			$site_status_data = $site_status['data'] ?? [
				'type'           => '',
				'remaining_mins' => 0,
				'current_status' => '',
			];

			$site_status_data['remaining_secs'] = (int) ( $site_status_data['remaining_mins'] ?? 0 ) * 60;

			$transient_data = [
				'data'        => $site_status_data,
				'update_time' => current_time( 'U' ),
			];

			self::$_site_status = $transient_data;
			update_option( self::$_transient_key, $transient_data );
		}

		/**
		 * Get site status data
		 */
		public function get_site_status() {
			return self::$_site_status['data'] ?? [];
		}

		/**
		 * Handle dismissible action for welcome panel
		 */
		public function handle_dismissible_action() {
			$iwp_hide_welcome_panel = isset( $_GET['iwp_hide_welcome_panel'] ) ? sanitize_text_field( $_GET['iwp_hide_welcome_panel'] ) : '';
			$iwp_nonce = isset( $_GET['iwp_nonce'] ) ? sanitize_key( $_GET['iwp_nonce'] ) : '';

			if ( $iwp_hide_welcome_panel === 'yes' && wp_verify_nonce( $iwp_nonce, 'iwp_welcome_nonce' ) ) {
				update_user_meta( get_current_user_id(), 'iwp_welcome_panel_dismissed', true );
			}

			if ( isset( $_GET['iwp_clean'] ) && sanitize_text_field( $_GET['iwp_clean'] ) === 'yes' ) {
				delete_user_meta( get_current_user_id(), 'iwp_welcome_panel_dismissed' );
			}
		}

		/**
		 * AJAX handler for plugin installation
		 */
		public function ajax_install_plugin() {
			$plugin_slug = isset( $_POST['plugin_slug'] ) ? sanitize_text_field( $_POST['plugin_slug'] ) : '';
			$plugin_zip_url = isset( $_POST['plugin_zip_url'] ) ? sanitize_text_field( $_POST['plugin_zip_url'] ) : '';
			$plugin_action = isset( $_POST['plugin_action'] ) ? sanitize_text_field( $_POST['plugin_action'] ) : '';
			$plugin_file = isset( $_POST['plugin_file'] ) ? sanitize_text_field( $_POST['plugin_file'] ) : '';
			$install_nonce = isset( $_POST['install_nonce'] ) ? sanitize_text_field( $_POST['install_nonce'] ) : '';

			if ( ! wp_verify_nonce( $install_nonce, 'iwp_install_plugin' ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Nonce verification failed!', 'iwp-mu' ) ] );
			}

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'iwp-mu' ) ] );
			}

			// Handle activation only
			if ( $plugin_action === 'activate' ) {
				$activated = activate_plugin( $plugin_file );
				if ( is_wp_error( $activated ) ) {
					wp_send_json_error( [ 'message' => $activated->get_error_message() ] );
				}
				wp_send_json_success( [ 'message' => esc_html__( 'Successfully activated the plugin.', 'iwp-mu' ) ] );
			}

			// Handle installation
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

			$skin = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );

			if ( ! empty( $plugin_zip_url ) ) {
				$result = $upgrader->install( esc_url_raw( $plugin_zip_url ) );
			} elseif ( ! empty( $plugin_slug ) ) {
				$api = plugins_api( 'plugin_information', [ 'slug' => $plugin_slug, 'fields' => [ 'sections' => false ] ] );
				if ( is_wp_error( $api ) ) {
					wp_send_json_error( [ 'message' => $api->get_error_message() ] );
				}
				$result = $upgrader->install( $api->download_link );
			} else {
				wp_send_json_error( [ 'message' => esc_html__( 'Missing plugin information.', 'iwp-mu' ) ] );
			}

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [ 'message' => $result->get_error_message() ] );
			}

			if ( ! $result ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Installation failed.', 'iwp-mu' ) ] );
			}

			// Activate the installed plugin
			$plugin_info = $upgrader->plugin_info();
			if ( $plugin_info ) {
				activate_plugin( $plugin_info );
			}

			wp_send_json_success( [ 'message' => esc_html__( 'Successfully installed the plugin.', 'iwp-mu' ) ] );
		}

		/**
		 * Display the welcome dashboard
		 */
		public function display_dashboard() {
			$welcome_panel_dismissed = (bool) get_user_meta( get_current_user_id(), 'iwp_welcome_panel_dismissed', true );
			if ( $welcome_panel_dismissed ) {
				return;
			}

			$iwp_welcome_details = get_option( 'iwp_welcome_details' );
			if ( empty( $iwp_welcome_details ) ) {
				return;
			}

			$partners = $iwp_welcome_details['partners'] ?? [];
			$site = $iwp_welcome_details['site'] ?? [];
			$site_username = $site['username'] ?? '';
			$site_password = $site['password'] ?? '';
			$manage_site_url = $site['manage_site_url'] ?? '';

			$dismissible_url = wp_nonce_url(
				iwp_get_current_admin_url( [ 'iwp_hide_welcome_panel' => 'yes' ] ),
				'iwp_welcome_nonce',
				'iwp_nonce'
			);

			// Output inline styles
			echo $this->get_inline_styles();
			?>

			<div class="iwp-dashboard">
				<a href="<?php echo esc_url( $dismissible_url ); ?>" class="iwp-dashboard-close">
					<span><?php esc_html_e( 'Dismiss', 'iwp-mu' ); ?></span>
					<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1.5 10.5L10.5 1.5M1.5 1.5L10.5 10.5" stroke="#EBF9F5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>

				<div class="iwp-dashboard-content">
					<div class="iwp-welcome-title"><?php esc_html_e( 'Welcome to WordPress', 'iwp-mu' ); ?></div>

					<div class="iwp-credentials">
						<div class="iwp-credential-row">
							<span class="iwp-credential-label"><?php esc_html_e( 'Username', 'iwp-mu' ); ?></span>
							<span class="iwp-credential-sep">:</span>
							<span class="iwp-credential-value"><?php echo esc_html( $site_username ); ?></span>
							<span class="iwp-copy-content" data-content="<?php echo esc_attr( $site_username ); ?>" data-text-copied="<?php echo esc_attr__( 'Copied!', 'iwp-mu' ); ?>">
								<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M4 10H2.5C1.67157 10 1 9.32843 1 8.5V2.5C1 1.67157 1.67157 1 2.5 1H8.5C9.32843 1 10 1.67157 10 2.5V4M5.5 13H11.5C12.3284 13 13 12.3284 13 11.5V5.5C13 4.67157 12.3284 4 11.5 4H5.5C4.67157 4 4 4.67157 4 5.5V11.5C4 12.3284 4.67157 13 5.5 13Z" stroke="#D4D4D8" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
						</div>
						<div class="iwp-credential-row">
							<span class="iwp-credential-label"><?php esc_html_e( 'Password', 'iwp-mu' ); ?></span>
							<span class="iwp-credential-sep">:</span>
							<span class="iwp-credential-value"><?php echo esc_html( $site_password ); ?></span>
							<span class="iwp-copy-content" data-content="<?php echo esc_attr( $site_password ); ?>" data-text-copied="<?php echo esc_attr__( 'Copied!', 'iwp-mu' ); ?>">
								<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M4 10H2.5C1.67157 10 1 9.32843 1 8.5V2.5C1 1.67157 1.67157 1 2.5 1H8.5C9.32843 1 10 1.67157 10 2.5V4M5.5 13H11.5C12.3284 13 13 12.3284 13 11.5V5.5C13 4.67157 12.3284 4 11.5 4H5.5C4.67157 4 4 4.67157 4 5.5V11.5C4 12.3284 4.67157 13 5.5 13Z" stroke="#D4D4D8" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
						</div>
					</div>

					<div class="iwp-external-links">
						<?php if ( ! empty( $manage_site_url ) ) : ?>
						<a target="_blank" href="<?php echo esc_url( $manage_site_url ); ?>" class="iwp-manage-link">
							<span><?php esc_html_e( 'Manage Site', 'iwp-mu' ); ?></span>
							<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M5.5 2.5H2.5C1.67157 2.5 1 3.17157 1 4V11.5C1 12.3284 1.67157 13 2.5 13H10C10.8284 13 11.5 12.3284 11.5 11.5V8.5M8.5 1H13M13 1V5.5M13 1L5.5 8.5" stroke="#F3E98D" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</a>
						<?php endif; ?>
						<div class="iwp-logo">
							<svg width="50" height="44" viewBox="0 0 50 44" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M41.4225 16.8945H48.365C48.4823 16.8942 48.5982 16.9266 48.7004 16.9885C48.8027 17.0505 48.8883 17.1399 48.9473 17.2476C49.007 17.3554 49.038 17.4778 49.0387 17.6026C49.0394 17.7275 49.0092 17.8503 48.9516 17.9588L36.4584 41.4547C36.2684 41.8127 35.992 42.1106 35.6574 42.3181C35.3228 42.5254 34.9428 42.635 34.5549 42.6353H29.2403C28.7976 42.6353 28.3652 42.4925 28.001 42.2259C27.6361 41.9593 27.357 41.5815 27.2 41.1425L25.8989 37.5185L25.9904 37.5838C26.4062 37.8692 26.8726 38.0608 27.3613 38.1471C27.8506 38.2333 28.3508 38.2121 28.8316 38.0849C29.3122 37.9577 29.7627 37.7271 30.1564 37.4076C30.5493 37.088 30.8767 36.6863 31.1171 36.2271L38.6137 21.9972H32.6327C32.5047 22 32.3786 21.9638 32.2692 21.8931C32.1598 21.8224 32.0713 21.72 32.0152 21.598C31.9591 21.476 31.9368 21.3395 31.9512 21.2045C31.9656 21.0697 32.0166 20.942 32.0973 20.8365L47.128 1.02141C47.2042 0.934338 47.3064 0.878104 47.4173 0.862371C47.5288 0.846637 47.6411 0.872386 47.7361 0.935191C47.831 0.997996 47.903 1.09393 47.9383 1.20652C47.9735 1.31911 47.9714 1.44131 47.9311 1.55212L41.4225 16.8945Z" fill="white"/>
								<path d="M17.0128 41.2134C17.0128 41.3998 16.9783 41.5842 16.9107 41.7564C16.8437 41.9286 16.7451 42.085 16.6207 42.2169C16.4968 42.3486 16.3486 42.4531 16.1866 42.5244C16.0241 42.5957 15.8498 42.6324 15.6742 42.6324H9.97389C9.69755 42.6324 9.42839 42.542 9.20243 42.3732C8.97645 42.2046 8.8059 41.966 8.71306 41.6902L8.03587 39.6497L1.08258 18.81C1.0099 18.5934 0.987585 18.3615 1.01709 18.1338C1.04732 17.9061 1.12792 17.6893 1.25314 17.5017C1.37836 17.314 1.54388 17.1609 1.73675 17.0552C1.92889 16.9495 2.14263 16.8943 2.35924 16.8944H7.56737C7.90849 16.8941 8.24169 17.0055 8.52164 17.2132C8.80158 17.4209 9.01387 17.7148 9.13045 18.0551L16.5601 39.6241L16.9409 40.7252C16.9913 40.8822 17.0157 41.0474 17.0128 41.2134Z" fill="white"/>
								<path d="M22.7929 28.891L18.5094 41.7246C18.4195 41.9925 18.2532 42.2243 18.0344 42.3884C17.8157 42.5524 17.5537 42.6408 17.286 42.6413H9.97363C9.69655 42.6407 9.42668 42.5489 9.20072 42.3786C8.97474 42.2083 8.80419 41.9679 8.7128 41.6905L8.03271 39.65C8.16657 39.8827 9.28275 41.699 10.6724 39.6244L17.8157 18.8841C17.8157 18.8841 18.6728 18.3335 19.1974 18.8841L22.7929 28.891Z" fill="white"/>
								<path d="M32.4531 33.704L29.2219 39.8256C29.0894 40.076 28.893 40.2814 28.6548 40.4172C28.4158 40.5532 28.146 40.6141 27.8761 40.5931C27.6063 40.5718 27.3472 40.4694 27.1305 40.2978C26.914 40.1261 26.7477 39.8924 26.6512 39.6242L22.7932 28.8907L19.3288 19.2414C19.2539 19.0388 19.1143 18.8708 18.9337 18.7669C18.7538 18.6631 18.5443 18.63 18.3436 18.6738C18.1558 18.7155 17.9744 18.7881 17.8082 18.8896C17.9852 18.3644 18.299 17.903 18.7127 17.5584C19.218 17.1292 19.8462 16.8951 20.4932 16.8944H25.2703C25.7661 16.8932 26.2497 17.0542 26.6564 17.3554C27.0622 17.6565 27.371 18.0833 27.5401 18.5773L32.5013 32.9775C32.5437 33.095 32.561 33.221 32.5532 33.3463C32.5446 33.4718 32.5106 33.5938 32.4531 33.704Z" fill="white"/>
							</svg>
						</div>
					</div>
				</div>

				<?php if ( ! empty( $partners ) ) : ?>
				<div class="iwp-dashboard-footer">
					<h6 class="iwp-footer-title"><?php esc_html_e( 'In partnership with', 'iwp-mu' ); ?></h6>
					<div class="iwp-partners-grid">
						<?php foreach ( $partners as $index => $partner ) :
							$name = $partner['name'] ?? '';
							$logo_url = $partner['logo_url'] ?? '';
							$description = $partner['description'] ?? '';
							$slug = $partner['slug'] ?? '';
							$plugin_file = $partner['plugin_file'] ?? '';
							$zip_url = $partner['zip_url'] ?? '';
							$cta_text = $partner['cta_text'] ?? '';
							$cta_link = $partner['cta_link'] ?? '';

							$btn_classes = 'iwp-mu-install-plugin iwp-btn ';
							if ( iwp_is_plugin_active( $plugin_file ) ) {
								$btn_classes .= 'activated';
								$plugin_action = '';
								$btn_text = esc_html__( 'Activated', 'iwp-mu' );
							} elseif ( iwp_is_plugin_installed( $plugin_file ) ) {
								$btn_classes .= 'installed';
								$plugin_action = 'activate';
								$btn_text = esc_html__( 'Activate Plugin', 'iwp-mu' );
							} else {
								$btn_classes .= 'install';
								$plugin_action = 'install_activate';
								$btn_text = esc_html__( 'Install Plugin', 'iwp-mu' );
							}
							?>
							<div class="iwp-partner-card">
								<div class="iwp-partner-logo">
									<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $name ); ?>">
								</div>
								<div class="iwp-partner-info">
									<div class="iwp-partner-name"><?php echo esc_html( $name ); ?></div>
									<div class="iwp-partner-desc"><?php echo esc_html( $description ); ?></div>
									<div class="iwp-partner-actions">
										<button class="<?php echo esc_attr( $btn_classes ); ?>"
											data-text-installing="<?php echo esc_attr__( 'Installing...', 'iwp-mu' ); ?>"
											data-text-installed="<?php echo esc_attr__( 'Installed', 'iwp-mu' ); ?>"
											data-text-activating="<?php echo esc_attr__( 'Activating...', 'iwp-mu' ); ?>"
											data-text-activated="<?php echo esc_attr__( 'Activated', 'iwp-mu' ); ?>"
											data-install-nonce="<?php echo esc_attr( wp_create_nonce( 'iwp_install_plugin' ) ); ?>"
											data-slug="<?php echo esc_attr( $slug ); ?>"
											data-zip-url="<?php echo esc_url( $zip_url ); ?>"
											data-plugin-action="<?php echo esc_attr( $plugin_action ); ?>"
											data-plugin-file="<?php echo esc_attr( $plugin_file ); ?>">
											<svg class="icon-install" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M1 10L1 10.75C1 11.9926 2.00736 13 3.25 13L10.75 13C11.9926 13 13 11.9926 13 10.75L13 10M10 7L7 10M7 10L4 7M7 10L7 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<svg class="icon-installed" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59"/>
											</svg>
											<svg class="icon-activated" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
											</svg>
											<span><?php echo esc_html( $btn_text ); ?></span>
										</button>
										<?php if ( ! empty( $cta_link ) ) : ?>
										<a href="<?php echo esc_url( $cta_link ); ?>" class="iwp-cta-link" target="_blank">
											<span><?php echo esc_html( $cta_text ); ?></span>
											<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M5.5 2.5H2.5C1.67157 2.5 1 3.17157 1 4V11.5C1 12.3284 1.67157 13 2.5 13H10C10.8284 13 11.5 12.3284 11.5 11.5V8.5M8.5 1H13M13 1V5.5M13 1L5.5 8.5" stroke="#005E54" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</a>
										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>
			</div>

			<?php
			// Output inline JavaScript
			echo $this->get_inline_script();
		}

		/**
		 * Get inline CSS styles
		 */
		private function get_inline_styles() {
			return '<style>
/* Welcome Panel Override */
.welcome-panel { position: relative; overflow: auto; margin: 0; background-color: transparent; font-size: 14px; line-height: 1.3; clear: both; }
.welcome-panel .welcome-panel-close, .welcome-panel .welcome-panel-content { display: none; }

/* Dashboard Container */
.iwp-dashboard { position: relative; display: inline-block; margin: 24px 0; width: 100%; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
.iwp-dashboard img { max-width: 100%; height: auto; }

/* Close Button */
.iwp-dashboard-close { position: absolute; top: 16px; right: 32px; z-index: 99; cursor: pointer; display: flex; align-items: center; gap: 6px; color: #fff; text-decoration: none; font-size: 14px; }
.iwp-dashboard-close:hover, .iwp-dashboard-close:focus { color: #fff; }

/* Content Area */
.iwp-dashboard-content { position: relative; z-index: 9; background: linear-gradient(135deg, #005E54 0%, #00231F 100%); border-radius: 8px 8px 0 0; padding: 32px; color: #fff; }
.iwp-welcome-title { font-size: 24px; line-height: 36px; font-weight: 500; margin-bottom: 16px; }

/* Credentials */
.iwp-credentials { margin-bottom: 16px; }
.iwp-credential-row { display: flex; align-items: center; gap: 4px; margin-bottom: 8px; font-size: 14px; color: #E5E7EB; }
.iwp-credential-label { min-width: 70px; }
.iwp-copy-content { cursor: pointer; opacity: 0.7; display: inline-flex; align-items: center; }
.iwp-copy-content:hover { opacity: 1; }
.iwp-copy-content .copied { color: #10B981; font-size: 12px; margin-left: 8px; }

/* External Links */
.iwp-external-links { display: flex; justify-content: space-between; align-items: flex-end; }
.iwp-manage-link { display: flex; align-items: center; gap: 8px; color: #F3E98D; text-decoration: none; font-size: 14px; font-weight: 500; }
.iwp-manage-link:hover, .iwp-manage-link:focus { color: #F3E98D; }
.iwp-logo { cursor: pointer; z-index: 9; }

/* Footer */
.iwp-dashboard-footer { background: #fff; border: 1px solid #E4E4E7; border-top: none; border-radius: 0 0 8px 8px; padding: 32px; }
.iwp-footer-title { color: #71717A; text-transform: uppercase; font-size: 12px; font-weight: 600; margin: 0 0 24px 0; }

/* Partners Grid */
.iwp-partners-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 32px; }
.iwp-partner-card { display: flex; gap: 12px; }
.iwp-partner-logo { flex-shrink: 0; width: 32px; height: 32px; overflow: hidden; border-radius: 8px; }
.iwp-partner-logo img { width: 100%; height: 100%; object-fit: cover; }
.iwp-partner-info { flex: 1; min-width: 0; }
.iwp-partner-name { font-size: 18px; font-weight: 500; color: #27272A; margin-bottom: 8px; }
.iwp-partner-desc { font-size: 14px; color: #71717A; line-height: 1.5; margin-bottom: 16px; }
.iwp-partner-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

/* Buttons */
.iwp-btn { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; border: none; cursor: pointer; text-decoration: none; min-height: 36px; }
.iwp-mu-install-plugin { background: #11BF85; color: #fff; }
.iwp-mu-install-plugin:hover { background: #0ea874; }
.iwp-mu-install-plugin.activated { background: #ccc; cursor: default; }
.iwp-mu-install-plugin svg { display: none; }
.iwp-mu-install-plugin.install:not(.loading) .icon-install { display: inline-block; }
.iwp-mu-install-plugin.installed:not(.loading) .icon-installed { display: inline-block; }
.iwp-mu-install-plugin.activated:not(.loading) .icon-activated { display: inline-block; }
.iwp-mu-install-plugin.loading { position: relative; }
.iwp-mu-install-plugin.loading > span { margin-left: 20px; }
.iwp-mu-install-plugin.loading::after { content: ""; position: absolute; height: 16px; width: 16px; top: 50%; left: 20px; transform: translate(-50%, -50%); border-radius: 50%; border: 3px solid rgba(255,255,255,0.2); border-right-color: #fff; animation: iwp-spin 1s linear infinite; }

/* CTA Link */
.iwp-cta-link { display: inline-flex; align-items: center; gap: 8px; color: #005E54; text-decoration: none; font-size: 14px; }
.iwp-cta-link:hover { color: #004940; }
.iwp-cta-link span { border-bottom: 1px dashed #005E54; }

/* Animation */
@keyframes iwp-spin { 0% { transform: translate(-50%, -50%) rotate(0deg); } 100% { transform: translate(-50%, -50%) rotate(360deg); } }

/* Responsive */
@media (max-width: 782px) {
	.iwp-partners-grid { grid-template-columns: 1fr; }
	.iwp-dashboard-content { padding: 24px; }
	.iwp-dashboard-footer { padding: 24px; }
}
</style>';
		}

		/**
		 * Get inline JavaScript
		 */
		private function get_inline_script() {
			return '<script>
(function($) {
	"use strict";

	// Copy to clipboard
	function iwp_copy_to_clipboard(string) {
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(string);
		} else {
			var el = document.createElement("input");
			document.body.appendChild(el);
			el.value = string;
			el.select();
			document.execCommand("copy", false);
			el.remove();
		}
	}

	// Logo click
	$(document).on("click", ".iwp-dashboard .iwp-logo", function() {
		window.open("https://instawp.com?utm_source=wa_welcome_msg", "_blank");
	});

	// Plugin installation
	$(document).on("click", ".iwp-mu-install-plugin", function() {
		var btn = $(this),
			nonce = btn.data("install-nonce"),
			slug = btn.data("slug"),
			zipUrl = btn.data("zip-url"),
			action = btn.data("plugin-action"),
			file = btn.data("plugin-file"),
			textInstalling = btn.data("text-installing"),
			textInstalled = btn.data("text-installed"),
			textActivating = btn.data("text-activating"),
			textActivated = btn.data("text-activated");

		if (btn.hasClass("loading") || btn.hasClass("activated")) return;

		$.ajax({
			type: "POST",
			url: ajaxurl,
			beforeSend: function() {
				btn.addClass("loading").find("span").html(action === "activate" ? textActivating : textInstalling);
				btn.find("svg").addClass("hidden");
			},
			data: {
				action: "iwp_install_plugin",
				plugin_action: action,
				plugin_slug: slug,
				plugin_file: file,
				plugin_zip_url: zipUrl,
				install_nonce: nonce
			},
			success: function(response) {
				if (response.success) {
					btn.removeClass("loading").addClass("activated").find("span").html(action === "activate" ? textActivated : textInstalled);
				} else {
					btn.removeClass("loading").find("span").html("Failed");
				}
			},
			error: function() {
				btn.removeClass("loading").find("span").html("Error");
			}
		});
	});

	// Copy content
	$(document).on("click", ".iwp-copy-content", function() {
		var el = $(this),
			content = el.data("content"),
			textCopied = el.data("text-copied");

		iwp_copy_to_clipboard(content);
		el.find(".copied").remove();
		el.append("<span class=\"copied\">" + textCopied + "</span>");

		setTimeout(function() {
			el.find(".copied").fadeOut(300, function() { $(this).remove(); });
		}, 2000);
	});
})(jQuery);
</script>';
		}

		/**
		 * Singleton instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
	}
}

// Initialize the plugin
IWP_MU::instance();
