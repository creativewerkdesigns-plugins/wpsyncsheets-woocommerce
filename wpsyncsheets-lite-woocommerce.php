<?php

/**

 * Plugin Name: WPSyncSheets Lite For WooCommerce

 * Plugin URI: https://www.wpsyncsheets.com/wpsyncsheets-for-woocommerce/

 * Description: An automated, run-time solution for WooCommerce orders, products, customers and coupons. Users can export WooCommerce orders, products, customers and coupons into a single Google Spreadsheet.

 * Author: Creative Werk Designs

 * Author URI: https://www.creativewerkdesigns.com/

 * Text Domain: wpssw

 * Domain Path: /languages

 * Version: 1.9.7

 * WC tested up to: 6.8.1

 *

 * @package     wpsyncsheets-woocommerce

 * @author      Creative Werk Designs

 * @Category    Plugin

 * @copyright   Copyright (c) 2025 Creative Werk Designs

 */



if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly.

}

add_action(

	'before_woocommerce_init',

	function() {

		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {

			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );

		}

	}

);



if (!function_exists('is_plugin_active')) {

    include_once(ABSPATH . 'wp-admin/includes/plugin.php');

}



if ( ! get_option( 'active_wpssw' ) && ! is_plugin_active( 'wpsyncsheets-for-woocommerce/wpsyncsheets-for-woocommerce.php' )) {

	define( 'WPSSLW_PLUGIN_SECURITY', 1 );

	define( 'WPSSLW_VERSION', '1.9.7' );

	define( 'WPSSLW_URL', plugin_dir_url( __FILE__ ) );

	define( 'WPSSLW_DIR', plugin_dir_path( __FILE__ ) );

	define( 'WPSSLW_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

	define( 'WPSSLW_DIRECTORY', dirname( plugin_basename( __FILE__ ) ) );

	define( 'WPSSLW_PLUGIN_SLUG', WPSSLW_DIRECTORY . '/' . basename( __FILE__ ) );

	define( 'WPSSLW_BASE_FILE', basename( dirname( __FILE__ ) ) . '/wpsyncsheets-lite-woocommerce.php' );

	define( 'WPSSLW_DOC_MENU_URL', 'https://docs.wpsyncsheets.com' );

	define( 'WPSSLW_SUPPORT_MENU_URL', 'https://wordpress.org/support/plugin/wpsyncsheets-woocommerce/' );



	if ( ! class_exists( 'WPSSLW_Dependencies' ) ) {

		require_once trailingslashit( dirname( __FILE__ ) ) . 'includes/class-wpsslw-dependencies.php';

	}



	// Check WCGS Dependency Class and WooCommerce Activation.

	if ( WPSSLW_Dependencies::wpsslw_is_woocommerce_active() ) {



		/**

		 * Remove capability.

		 */

		function wpsslw_remove_custom_capability_from_all_roles() {

			// Get all roles.

			global $wp_roles;



			// Ensure $wp_roles is properly initialized.

			if ( ! $wp_roles instanceof WP_Roles ) {

				return;

			}



			// Iterate through each role.

			foreach ( $wp_roles->roles as $role_name => $role_info ) {

				$role = get_role( $role_name );

				if ( $role ) {

					if ( $role->has_cap( 'edit_wpsyncsheets_woocommerce_lite_main_settings' ) ) {

						$role->remove_cap( 'edit_wpsyncsheets_woocommerce_lite_main_settings' );

					}

				}

			}

		}

		register_deactivation_hook( __FILE__, 'wpsslw_remove_custom_capability_from_all_roles' );



		/**

		 * Add capability.

		 */

		function wpsslw_add_custom_capability_to_specific_roles() {

			$specific_roles = array( 'administrator' );



			foreach ( $specific_roles as $role_name ) {

				$role = get_role( $role_name );

				if ( $role ) {

					if ( ! $role->has_cap( 'edit_wpsyncsheets_woocommerce_lite_main_settings' ) ) {

						$role->add_cap( 'edit_wpsyncsheets_woocommerce_lite_main_settings' );

					}

				}

			}

		}

		register_activation_hook( __FILE__, 'wpsslw_add_custom_capability_to_specific_roles' );

		add_action( 'init', 'wpsslw_add_custom_capability_to_specific_roles' );



		// Add methods if WooCommerce is active.

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wpsslw_add_action_links' );

		/**

		 * Add Settings Link.

		 *

		 * @param array $links Links array.

		 */

		function wpsslw_add_action_links( $links ) {

			$mylinks = array(

				'<a href="' . admin_url( 'admin.php?page=wpsyncsheets-woocommerce' ) . '">Settings</a>',

			);

			return array_merge( $mylinks, $links );

		}

		require_once dirname( __FILE__ ) . '/src/class-wpsyncsheetswoocommerce.php';

		wpsslw();

	} else {

		add_action( 'admin_notices', 'wpsslw_wc_admin_notice' );

		if ( ! function_exists( 'wpsslw_wc_admin_notice' ) ) {

			/**

			 * Add plugin missing notice.

			 */

			function wpsslw_wc_admin_notice() {

				global $pagenow;

				// phpcs:ignore

				if ( 'plugins.php' === (string) $pagenow || ( isset( $_GET['page'] ) && ( 'wpsyncsheets-woocommerce' === (string) sanitize_text_field( $_GET['page'] ) ) ) ) {

					echo '<div class="notice error wpsslw-error">

					<div>

						<p>WPSyncSheets Lite for WooCommerce plugin requires <a href=' . esc_url( 'http://wordpress.org/extend/plugins/woocommerce/' ) . '>WooCommerce</a> plugin to be active!</p>

					</div>

				</div>';

				}

			}

		}

	}

}

