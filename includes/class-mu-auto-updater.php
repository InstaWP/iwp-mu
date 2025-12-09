<?php
/**
 * MU Plugin Auto Updater
 *
 * Handles automatic updates for MU plugins from GitHub.
 * Hooks into WordPress core update system (update-core.php?force-check=1)
 * and checks once per day for efficiency.
 */

use InstaWP\Connect\Helpers\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IWP_MU_Auto_Updater {

	/**
	 * Current plugin version
	 *
	 * @var string
	 */
	private $current_version;

	/**
	 * GitHub repository URL
	 *
	 * @var string
	 */
	private $repo_url;

	/**
	 * Plugin directory path
	 *
	 * @var string
	 */
	private $plugin_dir;

	/**
	 * Plugin slug/folder name
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Main plugin file name
	 *
	 * @var string
	 */
	private $main_file;

	/**
	 * Option key for update data (like WordPress uses site_transient)
	 *
	 * @var string
	 */
	private $option_key = 'iwp_mu_update_data';

	/**
	 * Lock key for preventing concurrent updates
	 *
	 * @var string
	 */
	private $lock_key = 'iwp_mu_update_lock';

	/**
	 * Update check interval in seconds (24 hours)
	 *
	 * @var int
	 */
	private $check_interval = 86400;

	/**
	 * Single instance
	 *
	 * @var IWP_MU_Auto_Updater
	 */
	private static $instance = null;

	/**
	 * Initialize the updater
	 *
	 * @param string $current_version Current plugin version.
	 * @param string $repo_url        GitHub repository URL.
	 * @param string $plugin_dir      Plugin directory path.
	 * @param string $main_file       Main plugin file name.
	 */
	public function __construct( $current_version, $repo_url, $plugin_dir, $main_file = 'iwp-main.php' ) {
		$this->current_version = $current_version;
		$this->repo_url        = untrailingslashit( esc_url( $repo_url ) );
		$this->plugin_dir      = trailingslashit( $plugin_dir );
		$this->main_file       = $main_file;
		$this->plugin_slug     = basename( $plugin_dir );

		$this->init_hooks();
	}

	/**
	 * Get single instance
	 *
	 * @param string $current_version Current plugin version.
	 * @param string $repo_url        GitHub repository URL.
	 * @param string $plugin_dir      Plugin directory path.
	 * @param string $main_file       Main plugin file name.
	 *
	 * @return IWP_MU_Auto_Updater
	 */
	public static function instance( $current_version = '', $repo_url = '', $plugin_dir = '', $main_file = 'iwp-main.php' ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $current_version, $repo_url, $plugin_dir, $main_file );
		}
		return self::$instance;
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Hook into WordPress core update check (update-core.php?force-check=1)
		add_action( 'core_upgrade_preamble', array( $this, 'check_for_updates_on_core_page' ) );

		// Hook into wp_version_check which runs on force-check=1
		add_action( 'wp_version_check', array( $this, 'maybe_check_for_updates' ) );

		// Check on tools.php?page=instawp (respects 24-hour interval)
		add_action( 'load-tools_page_instawp', array( $this, 'maybe_check_for_updates' ) );

		// Force check when instawp-connect plugin is installed or updated
		add_action( 'upgrader_process_complete', array( $this, 'on_plugin_upgrade' ), 10, 2 );

		// Force check when instawp-connect plugin is activated
		add_action( 'activated_plugin', array( $this, 'on_plugin_activated' ), 10, 1 );
	}

	/**
	 * Handle plugin upgrade - force check if instawp-connect was updated/installed
	 *
	 * @param WP_Upgrader $upgrader WP_Upgrader instance.
	 * @param array       $options  Array of bulk item update data.
	 */
	public function on_plugin_upgrade( $upgrader, $options ) {
		if ( ! isset( $options['type'] ) || $options['type'] !== 'plugin' ) {
			return;
		}

		$instawp_plugin = 'instawp-connect/instawp-connect.php';

		// Check for single plugin update/install
		if ( ! empty( $options['plugin'] ) && $options['plugin'] === $instawp_plugin ) {
			return $this->force_check_for_updates();
		}

		// Check for bulk update
		if ( ! empty( $options['plugins'] ) && is_array( $options['plugins'] ) && in_array( $instawp_plugin, $options['plugins'], true ) ) {
			return $this->force_check_for_updates();
		}
	}

	/**
	 * Handle plugin activation - force check if instawp-connect was activated
	 *
	 * @param string $plugin Path to the plugin file relative to the plugins directory.
	 */
	public function on_plugin_activated( $plugin ) {
		if ( $plugin === 'instawp-connect/instawp-connect.php' ) {
			$this->force_check_for_updates();
		}
	}

	/**
	 * Check for updates when visiting update-core.php
	 * This fires on the Updates page before content is displayed
	 */
	public function check_for_updates_on_core_page() {
		// Check if force-check=1 is set (user clicked "Check again")
		if ( ! empty( $_GET['force-check'] ) ) {
			$this->force_check_for_updates();
		} else {
			$this->maybe_check_for_updates();
		}
	}

	/**
	 * Check for updates only if interval has passed (once per day)
	 */
	public function maybe_check_for_updates() {
		$update_data  = get_option( $this->option_key, array() );
		$last_checked = isset( $update_data['last_checked'] ) ? $update_data['last_checked'] : 0;

		// Skip if checked within the interval
		if ( ( time() - $last_checked ) < $this->check_interval ) {
			return;
		}

		$this->check_for_updates();
	}

	/**
	 * Force check for updates (bypass interval)
	 */
	public function force_check_for_updates() {
		$this->check_for_updates();
	}

	/**
	 * Check for updates from GitHub
	 *
	 * @return bool True if update is available
	 */
	private function check_for_updates() {
		$remote_version = $this->get_remote_version();

		$update_data = array(
			'last_checked'     => time(),
			'current_version'  => $this->current_version,
			'remote_version'   => $remote_version ? $remote_version : $this->current_version,
			'update_available' => false,
		);

		if ( $remote_version && version_compare( $this->current_version, $remote_version, '<' ) ) {
			$update_data['update_available'] = true;

			// Auto-apply update immediately when found
			$this->perform_update();
		}

		update_option( $this->option_key, $update_data, false );

		return $update_data['update_available'];
	}

	/**
	 * Get remote version from GitHub.
	 *
	 * @return string|false Remote plugin version or false on failure.
	 */
	public function get_remote_version() {
		$request = wp_remote_get(
			$this->repo_url . '/raw/main/' . $this->main_file,
			array(
				'timeout'   => 15,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $request );

		// Check if this file contains the Version header
		if ( preg_match( '/Version:\s*(\S+)/i', $body, $matches ) ) {
			return $matches[1];
		}

		return false;
	}

	/**
	 * Download and install update
	 *
	 * @return bool True on success, false on failure.
	 */
	private function perform_update() {
		// Prevent concurrent updates using a lock
		if ( get_transient( $this->lock_key ) ) {
			return false;
		}

		// Set lock for 5 minutes max
		set_transient( $this->lock_key, time(), 300 );

		// Initialize filesystem
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			$this->log( 'Could not initialize WordPress filesystem.' );
			delete_transient( $this->lock_key );
			return false;
		}

		// Validate plugin directory
		if ( ! $wp_filesystem->exists( $this->plugin_dir ) || ! $wp_filesystem->is_writable( $this->plugin_dir ) ) {
			$this->log( 'Plugin directory is not writable: ' . $this->plugin_dir );
			delete_transient( $this->lock_key );
			return false;
		}

		// Download zip from GitHub
		$download_url = $this->repo_url . '/archive/refs/heads/main.zip';
		$temp_file    = download_url( $download_url, 60 );

		if ( is_wp_error( $temp_file ) ) {
			$this->log( 'Download failed: ' . $temp_file->get_error_message() );
			delete_transient( $this->lock_key );
			return false;
		}

		// Validate downloaded file
		if ( ! $wp_filesystem->exists( $temp_file ) || $wp_filesystem->size( $temp_file ) < 1000 ) {
			$wp_filesystem->delete( $temp_file );
			$this->log( 'Downloaded file is invalid or too small.' );
			delete_transient( $this->lock_key );
			return false;
		}

		// Create temp directory for extraction
		$temp_dir = trailingslashit( get_temp_dir() ) . 'iwp-mu-update-' . time();
		if ( ! $wp_filesystem->mkdir( $temp_dir ) ) {
			$wp_filesystem->delete( $temp_file );
			$this->log( 'Could not create temp directory.' );
			delete_transient( $this->lock_key );
			return false;
		}

		// Unzip
		$unzip_result = unzip_file( $temp_file, $temp_dir );
		$wp_filesystem->delete( $temp_file );

		if ( is_wp_error( $unzip_result ) ) {
			$wp_filesystem->delete( $temp_dir, true );
			$this->log( 'Unzip failed: ' . $unzip_result->get_error_message() );
			delete_transient( $this->lock_key );
			return false;
		}

		// Find extracted folder (GitHub adds -main or -master suffix)
		$extracted_dir = null;
		foreach ( array( '-main', '-master' ) as $suffix ) {
			$check_path = trailingslashit( $temp_dir ) . $this->plugin_slug . $suffix;
			if ( $wp_filesystem->exists( $check_path ) ) {
				$extracted_dir = $check_path;
				break;
			}
		}

		if ( ! $extracted_dir ) {
			$wp_filesystem->delete( $temp_dir, true );
			$this->log( 'Could not find extracted plugin folder.' );
			delete_transient( $this->lock_key );
			return false;
		}

		// Validate extracted content has main plugin file
		$main_file_path = trailingslashit( $extracted_dir ) . $this->main_file;
		if ( ! $wp_filesystem->exists( $main_file_path ) ) {
			$wp_filesystem->delete( $temp_dir, true );
			$this->log( 'Main plugin file not found in update package.' );
			delete_transient( $this->lock_key );
			return false;
		}

		// Create backup
		$backup_dir = trailingslashit( get_temp_dir() ) . 'iwp-mu-backup-' . time();
		if ( ! $wp_filesystem->mkdir( $backup_dir ) ) {
			$wp_filesystem->delete( $temp_dir, true );
			$this->log( 'Could not create backup directory.' );
			delete_transient( $this->lock_key );
			return false;
		}

		$backup_result = copy_dir( $this->plugin_dir, $backup_dir );
		if ( is_wp_error( $backup_result ) ) {
			$wp_filesystem->delete( $temp_dir, true );
			$wp_filesystem->delete( $backup_dir, true );
			$this->log( 'Backup failed: ' . $backup_result->get_error_message() );
			delete_transient( $this->lock_key );
			return false;
		}

		// Clear and copy new files
		$this->clear_plugin_directory( $wp_filesystem );
		$copy_result = $this->copy_directory( $extracted_dir, $this->plugin_dir, $wp_filesystem );

		if ( is_wp_error( $copy_result ) ) {
			// Restore backup on failure
			$this->clear_plugin_directory( $wp_filesystem );
			copy_dir( $backup_dir, $this->plugin_dir );
			$wp_filesystem->delete( $backup_dir, true );
			$wp_filesystem->delete( $temp_dir, true );
			$this->log( 'Copy failed: ' . $copy_result->get_error_message() );
			delete_transient( $this->lock_key );
			return false;
		}

		// Verify main file exists after copy
		if ( ! $wp_filesystem->exists( $this->plugin_dir . $this->main_file ) ) {
			// Restore backup
			$this->clear_plugin_directory( $wp_filesystem );
			copy_dir( $backup_dir, $this->plugin_dir );
			$wp_filesystem->delete( $backup_dir, true );
			$wp_filesystem->delete( $temp_dir, true );
			$this->log( 'Main file missing after copy.' );
			delete_transient( $this->lock_key );
			return false;
		}

		// Cleanup temp files
		$wp_filesystem->delete( $backup_dir, true );
		$wp_filesystem->delete( $temp_dir, true );

		// Release lock
		delete_transient( $this->lock_key );

		// Update stored data
		$update_data                     = get_option( $this->option_key, array() );
		$update_data['last_updated']     = time();
		$update_data['update_available'] = false;
		update_option( $this->option_key, $update_data, false );

		do_action( 'iwp_mu_plugin_updated', $this->plugin_slug );

		return true;
	}

	/**
	 * Clear plugin directory before update
	 *
	 * @param WP_Filesystem_Base $wp_filesystem Filesystem object.
	 */
	private function clear_plugin_directory( $wp_filesystem ) {
		$files = $wp_filesystem->dirlist( $this->plugin_dir );

		if ( ! $files ) {
			return;
		}

		foreach ( $files as $file => $info ) {
			// Skip excluded files/folders
			if ( $this->should_exclude( $file ) ) {
				continue;
			}

			$path = $this->plugin_dir . $file;
			$wp_filesystem->delete( $path, ( $info['type'] === 'd' ) );
		}
	}

	/**
	 * Copy directory with exclusions
	 *
	 * @param string             $from          Source directory.
	 * @param string             $to            Destination directory.
	 * @param WP_Filesystem_Base $wp_filesystem Filesystem object.
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	private function copy_directory( $from, $to, $wp_filesystem ) {
		$from = trailingslashit( $from );
		$to   = trailingslashit( $to );

		$files = $wp_filesystem->dirlist( $from );

		if ( ! $files ) {
			return true;
		}

		foreach ( $files as $file => $info ) {
			// Skip excluded files/folders
			if ( $this->should_exclude( $file ) ) {
				continue;
			}

			$source      = $from . $file;
			$destination = $to . $file;

			if ( $info['type'] === 'd' ) {
				// Create directory and copy contents recursively
				if ( ! $wp_filesystem->exists( $destination ) ) {
					$wp_filesystem->mkdir( $destination );
				}
				$result = $this->copy_directory( $source, $destination, $wp_filesystem );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
			} else {
				// Copy file
				if ( ! $wp_filesystem->copy( $source, $destination, true ) ) {
					return new WP_Error( 'copy_failed', 'Could not copy file: ' . $file );
				}
			}
		}

		return true;
	}

	/**
	 * Check if file/folder should be excluded from update
	 *
	 * @param string $name File or folder name.
	 * @return bool True if should be excluded.
	 */
	private function should_exclude( $name ) {
		// Exclude files/folders starting with dot (hidden files, .git, .gitignore, etc.)
		if ( strpos( $name, '.' ) === 0 ) {
			return true;
		}

		// Exclude node files/folders
		$node_excludes = array(
			'node_modules',
			'package.json',
			'package-lock.json',
			'yarn.lock',
		);

		if ( in_array( $name, $node_excludes, true ) ) {
			return true;
		}

		// Exclude Claude files
		$claude_excludes = array(
			'CLAUDE.md',
			'CLAUDE.local.md',
		);

		if ( in_array( $name, $claude_excludes, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Log critical error using Helper::add_error_log
	 *
	 * @param string $message Error message.
	 */
	private function log( $message ) {
		if ( class_exists( 'InstaWP\Connect\Helpers\Helper' ) ) {
			Helper::add_error_log(
				array(
					'source'  => 'IWP_MU_Auto_Updater',
					'message' => $message,
					'version' => $this->current_version,
				)
			);
		}
	}

	/**
	 * Get update data
	 *
	 * @return array
	 */
	public function get_update_data() {
		return get_option(
			$this->option_key,
			array(
				'last_checked'     => 0,
				'current_version'  => $this->current_version,
				'remote_version'   => $this->current_version,
				'update_available' => false,
			)
		);
	}
}
