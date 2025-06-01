<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSLW_General' ) ) :
	/**
	 * Class WPSSLW_General.
	 */
	class WPSSLW_General extends WPSSLW_Settings {
		/**
		 * Initialization
		 */
		public function __construct() {
		}
		/**
		 * Save Settings of General settings tab.
		 */
		public static function wpsslw_update_general_settings() {

			if ( ! isset( $_POST['wpsslw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpsslw_general_settings'] ) ), 'save_general_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}

			if ( isset( $_POST['freeze_header'] ) ) {
				$wpsslw_freeze = 1;
				parent::wpsslw_update_option( 'freeze_header', 'yes' );
			} else {
				$wpsslw_freeze = 0;
				parent::wpsslw_update_option( 'freeze_header', 'no' );
			}

			$wpsslw_freeze_header = 1;
			$wpsslw_color         = 1;

			$wpsslw_settings          = array(
				'wpssw_product_spreadsheet_setting'  => array(
					'spreadsheet' => 'wpssw_product_spreadsheet_id',
					'settings'    => 'wpssw_product_override',
					'sheetname'   => 'All Products',
				),
				'wpssw_order_spreadsheet_setting'    => array(
					'spreadsheet' => 'wpssw_woocommerce_spreadsheet',
					'settings'    => 'wpssw_order_override',
					'sheetname'   => 'wpssw_sheets',
				),
				'wpssw_coupon_spreadsheet_setting'   => array(
					'spreadsheet' => 'wpssw_coupon_spreadsheet_id',
					'settings'    => 'wpssw_coupon_override',
					'sheetname'   => 'All Coupons',
				),
				'wpssw_customer_spreadsheet_setting' => array(
					'spreadsheet' => 'wpssw_customer_spreadsheet_id',
					'settings'    => 'wpssw_customer_override',
					'sheetname'   => 'All Customers',
				),
			);
			$wpsslw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			foreach ( $wpsslw_settings as $key => $values ) {
				$is_active = parent::wpsslw_option( $key );
				$settings  = $values['settings'];

				if ( 'wpssw_order_spreadsheet_setting' === (string) $key ) {
					$is_active = WPSSLW_Order::wpsslw_check_order_spreadsheet_setting();
				}
				$wpsslw_spreadsheetid = parent::wpsslw_option( $values['spreadsheet'] );
				if ( 'yes' !== (string) $is_active || 'yes' === (string) $settings || '' === (string) $wpsslw_spreadsheetid || 'new' === $wpsslw_spreadsheetid || ! array_key_exists( $wpsslw_spreadsheetid, $wpsslw_spreadsheets_list ) ) {
					continue;
				}

				$sheetname = $values['sheetname'];
				if ( 'wpssw_sheets' === (string) $sheetname ) {
					$wpsslw_sheets = array_filter( (array) parent::wpsslw_option( $sheetname ) );
					if ( 'wpssw_sheets' === (string) $sheetname && ! $wpsslw_sheets ) {
						$wpsslw_sheets = WPSSLW_Order::wpsslw_prepare_sheets();
					}
				} else {
					$wpsslw_sheets = array( $sheetname );
				}
				// Freeze headers.
				parent::wpsslw_freeze_header( $wpsslw_spreadsheetid, $wpsslw_freeze, '', '', 0, $wpsslw_freeze_header, true, $wpsslw_sheets );
			}

			// Row Input Format Option.
			if ( isset( $_POST['inputoption'] ) ) {
				$wpsslw_inputoption = sanitize_text_field( wp_unslash( $_POST['inputoption'] ) );
				parent::wpsslw_update_option( 'wpssw_inputoption', $wpsslw_inputoption );
			}
		}
	}
	new WPSSLW_General();
endif;
