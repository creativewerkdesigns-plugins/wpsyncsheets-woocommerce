<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
/**
 * Class WPSSLW_Include_Action.
 */
class WPSSLW_Include_Action {
	/**
	 * Include Order compatibility files.
	 */
	public function wpsslw_include_order_compatibility_files() {
		require_once WPSSLW_PLUGIN_PATH . '/includes/order/class-wpsslw-order-utils.php';
		foreach ( glob( WPSSLW_PLUGIN_PATH . '/includes/order/compatibility/*.php' ) as $filename ) {
			include $filename;
		}
	}
	/**
	 * Include Product compatibility files.
	 */
	public function wpsslw_include_product_compatibility_files() {
		require_once WPSSLW_PLUGIN_PATH . '/includes/product/class-wpsslw-product-utils.php';
		foreach ( glob( WPSSLW_PLUGIN_PATH . '/includes/product/compatibility/*.php' ) as $filename ) {
			include $filename;
		}
	}
	/**
	 * Include Customer compatibility files.
	 */
	public function wpsslw_include_customer_compatibility_files() {
		require_once WPSSLW_PLUGIN_PATH . '/includes/customer/class-wpsslw-customer-utils.php';
		foreach ( glob( WPSSLW_PLUGIN_PATH . '/includes/customer/compatibility/*.php' ) as $filename ) {
			include $filename;
		}
	}
	/**
	 * Include Coupon compatibility files.
	 */
	public function wpsslw_include_coupon_compatibility_files() {
		require_once WPSSLW_PLUGIN_PATH . '/includes/coupon/class-wpsslw-coupon-utils.php';
		foreach ( glob( WPSSLW_PLUGIN_PATH . '/includes/coupon/compatibility/*.php' ) as $filename ) {
			include $filename;
		}
	}
	/**
	 * Include Product hooks.
	 */
	public function wpsslw_include_product_hook() {
		add_action( 'woocommerce_update_product', 'WPSSLW_Product::wpsslw_woocommerce_update_product', 99, 2 );
	}
	/**
	 * Include Product ajax hooks.
	 */
	public function wpsslw_include_product_ajax_hook() {
		add_action( 'wp_ajax_wpsslw_get_product_count', 'WPSSLW_Product::wpsslw_get_product_count' );
		add_action( 'wp_ajax_wpsslw_sync_products', 'WPSSLW_Product::wpsslw_sync_products' );
		add_action( 'wp_ajax_wpsslw_clear_productsheet', 'WPSSLW_Product::wpsslw_clear_productsheet' );
		add_action( 'wp_ajax_wpsslw_sync_single_product_data', 'WPSSLW_Product::wpsslw_sync_single_product_data' );
	}

	/**
	 * Include Order hooks.
	 */
	public function wpsslw_include_order_hook() {
		add_action( 'woocommerce_order_status_changed', 'WPSSLW_Order::wpsslw_woo_order_status_change_custom', 40, 3 );
		add_action( 'woocommerce_process_shop_order_meta', 'WPSSLW_Order::wpsslw_wc_woocommerce_process_post_meta', 60, 2 );
		add_action( 'woocommerce_update_options_google_sheet_settings', 'WPSSLW_Order::wpsslw_update_settings' );

		$hpos_order_enabled = WPSSLW_Settings::wpsslw_check_hpos_order_setting_enabled();
		if ( $hpos_order_enabled ) {
			add_action( 'woocommerce_trash_order', 'WPSSLW_Order::wpsslw_wcgs_trash_order' );
		} else {
			add_action( 'transition_post_status', 'WPSSLW_Order::wpsslw_wcgs_restore', 10, 3 );
		}
	}
	/**
	 * Include Order fields hooks.
	 */
	public function wpsslw_include_orderfield_hook() {
		add_action( 'woocommerce_admin_field_set_headers', 'WPSSLW_Order::wpsslw_woocommerce_admin_field_set_headers', 10, 0 );
		add_action( 'woocommerce_admin_field_set_sheets', 'WPSSLW_Order::wpsslw_woocommerce_admin_field_set_sheets', 10, 0 );
		add_action( 'woocommerce_admin_field_set_custom_sheets', 'WPSSLW_Order::wpsslw_woocommerce_admin_field_set_custom_sheets', 10, 0 );
		add_action( 'woocommerce_admin_field_manage_row_field', 'WPSSLW_Order::wpsslw_woocommerce_admin_field_manage_row_field', 10, 0 );
		add_action( 'woocommerce_admin_field_select_spreadsheet', 'WPSSLW_Order::wpsslw_woocommerce_admin_field_select_spreadsheet' );

		add_action( 'woocommerce_admin_field_sync_button', 'WPSSLW_Order::wpsslw_woocommerce_admin_field_sync_button', 10, 0 );
	}
	/**
	 * Include Order ajax hooks.
	 */
	public function wpsslw_include_order_ajax_hook() {
		add_action( 'wp_ajax_wpsslw_clear_all_sheet', 'WPSSLW_Order::wpsslw_clear_all_sheet' );
		add_action( 'wp_ajax_wpsslw_check_existing_sheet', 'WPSSLW_Order::wpsslw_check_existing_sheet' );

		add_action( 'wp_ajax_wpsslw_get_orders_count', 'WPSSLW_Order::wpsslw_get_orders_count' );
		add_action( 'wp_ajax_wpsslw_sync_sheetswise', 'WPSSLW_Order::wpsslw_sync_sheetswise' );
	}
	/**
	 * Include Coupon hooks.
	 */
	public function wpsslw_include_coupon_hook() {
		add_action( 'woocommerce_coupon_object_updated_props', 'WPSSLW_Coupon::wpsslw_coupon_object_updated_props' );
	}
	/**
	 * Include Coupon ajax hooks.
	 */
	public function wpsslw_include_coupon_ajax_hook() {
		add_action( 'wp_ajax_wpsslw_clear_couponsheet', 'WPSSLW_Coupon::wpsslw_clear_couponsheet' );
		add_action( 'wp_ajax_wpsslw_get_coupon_count', 'WPSSLW_Coupon::wpsslw_get_coupon_count' );
		add_action( 'wp_ajax_wpsslw_sync_coupons', 'WPSSLW_Coupon::wpsslw_sync_coupons' );
	}
	/**
	 * Include Customer hooks.
	 */
	public function wpsslw_include_customer_hook() {
		add_action( 'edit_user_profile_update', 'WPSSLW_Customer::edit_user_profile_update', 10, 1 );
		add_action( 'delete_user', 'WPSSLW_Customer::wpsslw_delete_user' );
		add_action( 'user_register', 'WPSSLW_Customer::wpsslw_user_registration_save' );
		add_action( 'woocommerce_save_account_details', 'WPSSLW_Customer::edit_user_profile_update' );
		add_action( 'woocommerce_checkout_update_user_meta', 'WPSSLW_Customer::action_woocommerce_checkout_update_customer', 10, 2 );
	}
	/**
	 * Include Customer ajax hooks.
	 */
	public function wpsslw_include_customer_ajax_hook() {
		add_action( 'wp_ajax_wpsslw_clear_customersheet', 'WPSSLW_Customer::wpsslw_clear_customersheet' );
		add_action( 'wp_ajax_wpsslw_get_customer_count', 'WPSSLW_Customer::wpsslw_get_customer_count' );
		add_action( 'wp_ajax_wpsslw_sync_customers', 'WPSSLW_Customer::wpsslw_sync_customers' );
	}
	/**
	 * Include Plugin hooks.
	 */
	public function wpsslw_include_plugin_hook() {
		add_action( 'admin_menu', 'WPSSLW_Settings::wpsslw_menu_page', 10 );
		add_action( 'wp_trash_post', 'WPSSLW_Settings::wpsslw_wcgs_trash' );
		add_action( 'untrashed_post', 'WPSSLW_Settings::wpsslw_wcgs_untrash', 10 );
		add_action( 'admin_enqueue_scripts', 'WPSSLW_Settings::wpsslw_load_custom_wp_admin_style', 30 );
		add_filter( 'plugin_row_meta', 'WPSSLW_Settings::wpsslw_plugin_row_meta', 10, 2 );
		add_action( 'plugins_loaded', 'WPSSLW_Settings::wpsslw_load_textdomain', 10 );
		add_action( 'wp_ajax_wpsslw_reset_settings', 'WPSSLW_Settings::wpsslw_reset_settings' );
		add_action( 'admin_enqueue_scripts', 'WPSSLW_Settings::wpsslw_selectively_enqueue_admin_script' );
	}
}
