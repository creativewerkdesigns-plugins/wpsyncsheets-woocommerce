<?php
	/**
	 * Main WPSyncSheetsWooCommerce namespace.
	 *
	 * @since 1.0.0
	 * @package wpsyncsheets-woocommerce
	 */

namespace WPSyncSheetsWooCommerce{
	/**
	 * Main WPSyncSheetsWooCommerce class.
	 *
	 * @since 1.0.0
	 * @package wpsyncsheets-woocommerce
	 */
	final class WPSyncSheetsWooCommerce {
		/**
		 * Instance of this class.
		 *
		 * @since 1.0.0
		 *
		 * @var \WPSyncSheetsWooCommerce\WPSyncSheetsWooCommerce
		 */
		private static $instance;
		/**
		 * Plugin version for enqueueing, etc.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $version = '';
		/**
		 * Main WPSyncSheetsWooCommerce Instance.
		 * Only one instance of WPSyncSheetsWooCommerce exists in memory at any one time.
		 * Also prevent the need to define globals all over the place.
		 *
		 * @since 1.0.0
		 *
		 * @return WPSyncSheetsWooCommerce
		 */
		public static function instance() {
			if ( null === self::$instance || ! self::$instance instanceof self ) {
				self::$instance = new self();
				self::$instance->constants();
				self::$instance->includes();
				add_action( 'init', array( self::$instance, 'load_textdomain' ), 10 );
			}
			return self::$instance;
		}

		/**
		 * Setup plugin constants.
		 * All the path/URL related constants are defined in main plugin file.
		 *
		 * @since 1.0.0
		 */
		private function constants() {
			$this->version = WPSSLW_VERSION;
		}
		/**
		 * Load the plugin language files.
		 *
		 * @since 1.0.0
		 */
		public function load_textdomain() {
			// If the user is logged in, unset the current text-domains before loading our text domain.
			// This feels hacky, but this way a user's set language in their profile will be used,
			// rather than the site-specific language.
			if ( is_user_logged_in() ) {
				unload_textdomain( 'wpssw' );
			}
			load_plugin_textdomain( 'wpssw', false, WPSSLW_DIRECTORY . '/languages/' );
		}
		/**
		 * Include files.
		 *
		 * @since 1.0.0
		 */
		private function includes() {
			// Global Includes.
			require_once WPSSLW_PLUGIN_PATH . '/includes/class-wpsslw-include-action.php';
			require_once WPSSLW_PLUGIN_PATH . '/includes/class-wpsslw-google-api.php';
			require_once WPSSLW_PLUGIN_PATH . '/includes/class-wpsslw-google-api-functions.php';
			require_once WPSSLW_PLUGIN_PATH . '/includes/class-wpsslw-settings.php';
			$this->wpsslw_include_module_files();
		}
		/**
		 * Include Module files.
		 *
		 * @since 1.0.0
		 */
		private function wpsslw_include_module_files() {
			foreach ( glob( WPSSLW_PLUGIN_PATH . '/includes/modules/*.php' ) as $filename ) {
				include $filename;
			}

		}
	}
}

namespace {
	/**
	 * The function which returns the one WPSSLW instance.
	 *
	 * @since 1.0.0
	 *
	 * @return WPSSLW\wpsslw
	 */
	function wpsslw() {
		return WPSyncSheetsWooCommerce\WPSyncSheetsWooCommerce::instance();
	}
	class_alias( 'WPSyncSheetsWooCommerce\WPSyncSheetsWooCommerce', 'WPSyncSheetsWooCommerce' );
}
