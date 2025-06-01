<?php
/**
 * Main WPSyncSheetsWooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-woocommerce
 */

/**
 * WPSSLW Dependency Checker
 */
class WPSSLW_Dependencies {
	/**
	 * Array of active plugins.
	 *
	 * @var $active_plugins .
	 */
	private static $active_plugins;
	/**
	 * Initialization.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		if ( is_multisite() ) {
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			// Check plugin active at the network site.
			if ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
				self::$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
			} else { // Check plugin active at the network individual site.
				self::$active_plugins = (array) get_option( 'active_plugins', array() );
			}
		} else {
			self::$active_plugins = (array) get_option( 'active_plugins', array() );
		}
	}
	/**
	 * Check woocommerce exist
	 *
	 * @return Boolean
	 */
	public static function wpsslw_woocommerce_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}
		return in_array( 'woocommerce/woocommerce.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );
	}
	/**
	 * Check if woocommerce active
	 *
	 * @return Boolean
	 */
	public static function wpsslw_is_woocommerce_active() {
		return self::wpsslw_woocommerce_active_check();
	}
}
