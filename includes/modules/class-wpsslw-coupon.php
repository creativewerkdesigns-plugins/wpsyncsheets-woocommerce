<?php
/**
 * Main WPSyncSheetsWooCommerce namespace.
 *
 * @package wpsyncsheets-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSLW_Coupon' ) ) :
	/**
	 * Class WPSSLW_Coupon.
	 */
	class WPSSLW_Coupon extends WPSSLW_Settings {
		/**
		 * Initialization
		 */
		public function __construct() {
			$wpsslw_include = new WPSSLW_Include_Action();
			$wpsslw_include->wpsslw_include_coupon_hook();
			$wpsslw_include->wpsslw_include_coupon_ajax_hook();

		}
		/**
		 * Save Settings of Coupon settings tab.
		 */
		public static function wpsslw_update_coupon_settings() {
			if ( ! isset( $_POST['wpssw_coupon_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_coupon_settings'] ) ), 'save_coupon_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( isset( $_POST['woocoupon_header_list'] ) && isset( $_POST['woocoupon_custom'] ) ) {
				$wpsslw_woo_coupon_headers        = array_map( 'sanitize_text_field', wp_unslash( $_POST['woocoupon_header_list'] ) );
				$wpsslw_woo_coupon_headers_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['woocoupon_custom'] ) );
				if ( isset( $_POST['coupon_settings_checkbox'] ) ) {

					if ( isset( $_POST['couponsheetselection'] ) && 'new' === (string) sanitize_text_field( wp_unslash( $_POST['couponsheetselection'] ) ) ) {
						$wpsslw_newsheetname = isset( $_POST['coupon_spreadsheet_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['coupon_spreadsheet_name'] ) ) ) : '';

						/*
						*Create new spreadsheet
						*/
						$wpsslw_requestbody   = self::$instance_api->createspreadsheetobject( $wpsslw_newsheetname );
						$wpsslw_response      = self::$instance_api->createspreadsheet( $wpsslw_requestbody );
						$wpsslw_spreadsheetid = $wpsslw_response['spreadsheetId'];
					} else {
						$wpsslw_spreadsheetid = isset( $_POST['coupon_spreadsheet'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_spreadsheet'] ) ) : '';
					}
					parent::wpsslw_update_option( 'wpssw_coupon_spreadsheet_id', $wpsslw_spreadsheetid );
					parent::wpsslw_update_option( 'wpssw_coupon_spreadsheet_setting', 'yes' );
				} else {
					parent::wpsslw_update_option( 'wpssw_coupon_spreadsheet_setting', 'no' );
					parent::wpsslw_update_option( 'wpssw_coupon_spreadsheet_id', '' );
					return;
				}
				$wpsslw_sheetname           = 'All Coupons';
				$requestarray               = array();
				$deleterequestarray         = array();
				$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
				$wpsslw_existingsheets      = array_flip( $wpsslw_existingsheetsnames );
				$wpsslw_inputoption         = parent::wpsslw_option( 'wpssw_inputoption' );
				if ( ! $wpsslw_inputoption ) {
					$wpsslw_inputoption = 'USER_ENTERED';
				}
				if ( count( $wpsslw_woo_coupon_headers ) > 0 ) {
					array_unshift( $wpsslw_woo_coupon_headers, 'Coupon Id' );
				}
				if ( count( $wpsslw_woo_coupon_headers_custom ) > 0 ) {
					array_unshift( $wpsslw_woo_coupon_headers_custom, 'Coupon Id' );
				}
				$wpsslw_old_header_coupon = parent::wpsslw_option( 'wpssw_woo_coupon_headers' );
				if ( empty( $wpsslw_old_header_coupon ) ) {
					$wpsslw_old_header_coupon = array();
				}
				if ( count( $wpsslw_old_header_coupon ) > 0 ) {
					array_unshift( $wpsslw_old_header_coupon, 'Coupon Id' );
				}
				if ( ! in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpsslw_spreadsheetid;
					$param['sheetname']     = $wpsslw_sheetname;
					$wpsslw_response        = self::$instance_api->newsheetobject( $param );
					$wpsslw_range           = trim( $wpsslw_sheetname ) . '!A1';
					$wpsslw_requestbody     = self::$instance_api->valuerangeobject( array( $wpsslw_woo_coupon_headers_custom ) );
					$wpsslw_params          = array( 'valueInputOption' => $wpsslw_inputoption );
					$param                  = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_range, $wpsslw_requestbody, $wpsslw_params );
					$wpsslw_response        = self::$instance_api->appendentry( $param );
				}
				if ( 'new' === (string) sanitize_text_field( wp_unslash( $_POST['couponsheetselection'] ) ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpsslw_spreadsheetid;
					$wpsslw_response        = self::$instance_api->deletesheetobject( $param );
				}
				if ( $wpsslw_old_header_coupon !== $wpsslw_woo_coupon_headers && in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
					$wpsslw_existingsheets      = array();
					$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
					$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
					$wpsslw_existingsheets      = array_flip( $wpsslw_existingsheetsnames );
					// Delete deactivate column from sheet.
					$wpsslw_column = array_diff( $wpsslw_old_header_coupon, $wpsslw_woo_coupon_headers );
					if ( ! empty( $wpsslw_column ) ) {
						$wpsslw_column = array_reverse( $wpsslw_column, true );
						foreach ( $wpsslw_column as $columnindex => $columnval ) {
							unset( $wpsslw_old_header_coupon[ $columnindex ] );
							$wpsslw_old_header_coupon = array_values( $wpsslw_old_header_coupon );
							if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
								$wpsslw_sheetid = array_search( $wpsslw_sheetname, $wpsslw_existingsheets, true );
								if ( $wpsslw_sheetid ) {
									$param                = array();
									$startindex           = $columnindex;
									$endindex             = $columnindex + 1;
									$param                = self::$instance_api->prepare_param( $wpsslw_sheetid, $startindex, $endindex );
									$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param );
								}
							}
						}
					}
					try {
						if ( ! empty( $deleterequestarray ) ) {
							$param                  = array();
							$param['spreadsheetid'] = $wpsslw_spreadsheetid;
							$param['requestarray']  = $deleterequestarray;
							$wpsslw_response        = self::$instance_api->updatebachrequests( $param );
						}
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
				if ( $wpsslw_old_header_coupon !== $wpsslw_woo_coupon_headers ) {
					foreach ( $wpsslw_woo_coupon_headers as $key => $hname ) {
						if ( 'Coupon Id' === (string) $hname ) {
							continue;
						}
						$wpsslw_startindex = array_search( (string) $hname, parent::wpsslw_convert_string( $wpsslw_old_header_coupon ), true );

						if ( false !== $wpsslw_startindex && ( isset( $wpsslw_old_header_coupon[ $key ] ) && $wpsslw_old_header_coupon[ $key ] !== $hname ) ) {
							unset( $wpsslw_old_header_coupon[ $wpsslw_startindex ] );
							$wpsslw_old_header_coupon = array_merge( array_slice( $wpsslw_old_header_coupon, 0, $key ), array( 0 => $hname ), array_slice( $wpsslw_old_header_coupon, $key, count( $wpsslw_old_header_coupon ) - $key ) );
							$wpsslw_endindex          = $wpsslw_startindex + 1;
							$wpsslw_destindex         = $key;
							if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
								$wpsslw_sheetid = array_search( (string) $wpsslw_sheetname, parent::wpsslw_convert_string( $wpsslw_existingsheets ), true );
								if ( $wpsslw_sheetid ) {
									$param              = array();
									$param              = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
									$param['destindex'] = $wpsslw_destindex;
									$requestarray[]     = self::$instance_api->moveDimensionrequests( $param );
								}
							}
						} elseif ( false === (bool) $wpsslw_startindex ) {
							$wpsslw_old_header_coupon = array_merge( array_slice( $wpsslw_old_header_coupon, 0, $key ), array( 0 => $hname ), array_slice( $wpsslw_old_header_coupon, $key, count( $wpsslw_old_header_coupon ) - $key ) );
							if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
								$wpsslw_sheetid = array_search( (string) $wpsslw_sheetname, parent::wpsslw_convert_string( $wpsslw_existingsheets ), true );
								if ( $wpsslw_sheetid ) {
									$param                = array();
									$wpsslw_startindex    = $key;
									$wpsslw_endindex      = $key + 1;
									$param                = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
									$coupon_inherit_style = self::wpsslw_option( 'wpssw_coupon_inherit_style' );
									if ( 'no' === (string) $coupon_inherit_style ) {
										$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'COLUMNS', false );
									} else {
										$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'COLUMNS', true );
									}
								}
							}
						}
					}

					if ( ! empty( $requestarray ) ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpsslw_spreadsheetid;
						$param['requestarray']  = $requestarray;
						$wpsslw_response        = self::$instance_api->updatebachrequests( $param );
					}
				}
				$freeze_header              = parent::wpsslw_option( 'freeze_header' );
				$wpsslw_existingsheets      = array();
				$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
				$wpsslw_existingsheets      = array_flip( $wpsslw_existingsheetsnames );
				if ( 'yes' === (string) $freeze_header ) {
					$wpsslw_freeze = 1;
				} else {
					$wpsslw_freeze = 0;
				}
				if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
					$wpsslw_sheetid = array_search( $wpsslw_sheetname, $wpsslw_existingsheets, true );
					// freeze coupon headers.
					$wpsslw_requestbody = self::$instance_api->freezeobject( $wpsslw_sheetid, $wpsslw_freeze );
					try {
						$requestbody                    = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
							array( 'requests' => $wpsslw_requestbody )
						);
						$requestobject                  = array();
						$requestobject['spreadsheetid'] = $wpsslw_spreadsheetid;
						$requestobject['requestbody']   = $requestbody;
						$wpsslw_response                = self::$instance_api->formatsheet( $requestobject );
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
				if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
					$wpsslw_range       = trim( $wpsslw_sheetname ) . '!A1';
					$wpsslw_requestbody = self::$instance_api->valuerangeobject( array( $wpsslw_woo_coupon_headers_custom ) );
					$wpsslw_params      = array( 'valueInputOption' => $wpsslw_inputoption );
					$param              = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_range, $wpsslw_requestbody, $wpsslw_params );
					$wpsslw_response    = self::$instance_api->updateentry( $param );
				}
				parent::wpsslw_update_option( 'wpssw_woo_coupon_headers', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocoupon_header_list'] ) ) );
				parent::wpsslw_update_option( 'wpssw_woo_coupon_headers_custom', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocoupon_custom'] ) ) );
			}
		}
		/**
		 * Coupon headers
		 *
		 * @retun array $headers
		 */
		public static function wpsslw_woo_coupon_headers() {
			$wpsslw_include = new WPSSLW_Include_Action();
			$wpsslw_include->wpsslw_include_coupon_compatibility_files();
			$headers = WPSSLW_Coupon_Headers::get_header_list( array() );
			return $headers['WPSSLW_Coupon_Headers'];
		}
		/**
		 * Insert / Update coupon data into sheet on coupon update
		 *
		 * @param object $coupon .
		 */
		public static function wpsslw_coupon_object_updated_props( $coupon ) {

			$wpsslw_coupon_spreadsheet_setting = parent::wpsslw_option( 'wpssw_coupon_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpsslw_coupon_spreadsheet_setting ) {
				return;
			}

			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) || ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) ) {
				if ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) {
					$changed_posts = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['ids'] ) ) );
				} else {
					$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				}
				
				// @codingStandardsIgnoreEnd.
				if ( (int) $coupon->get_id() === (int) $changed_posts[ count( $changed_posts ) - 1 ] ) {
					$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_coupon_spreadsheet_id' );
					$wpsslw_sheetname     = 'All Coupons';

					$settings = array(
						'setting'        => 'coupon',
						'setting_enable' => $wpsslw_coupon_spreadsheet_setting,
						'spreadsheet_id' => $wpsslw_spreadsheetid,
						'sheetname'      => $wpsslw_sheetname,
					);
					WPSSLW_Settings::wpsslw_multiple_update_data( $changed_posts, $settings, false, 'update' );

				}
				return;
			}

			self::wpsslw_insert_coupon_data_into_sheet( $coupon );
		}
		/**
		 * Clear Coupon sheet
		 */
		public static function wpsslw_clear_couponsheet() {

			$wpsslw_coupon_spreadsheet_setting = parent::wpsslw_option( 'wpssw_coupon_spreadsheet_setting' );
			$wpsslw_spreadsheetid              = parent::wpsslw_option( 'wpssw_coupon_spreadsheet_id' );

			$wpsslw_sheetname = 'All Coupons';
			if ( 'yes' !== (string) $wpsslw_coupon_spreadsheet_setting || ! parent::wpsslw_check_sheet_exist( $wpsslw_spreadsheetid, $wpsslw_sheetname ) ) {
				echo esc_html__( 'Please save settings.', 'wpssw' );
				die();
			}

			$requestbody                = self::$instance_api->clearobject();
			$total_headers              = count( parent::wpsslw_option( 'wpssw_woo_coupon_headers' ) ) + 1;
			$last_column                = parent::wpsslw_get_column_index( $total_headers );
			$wpsslw_existingsheetsnames = array();
			$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
			$wpsslw_existingsheetsnames = array_flip( $wpsslw_existingsheetsnames );

			if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheetsnames, true ) ) {
				try {
					$range                  = $wpsslw_sheetname . '!A2:' . $last_column . '100000';
					$param                  = array();
					$param['spreadsheetid'] = $wpsslw_spreadsheetid;
					$param['sheetname']     = $range;
					$param['requestbody']   = $requestbody;
					$response               = self::$instance_api->clear( $param );
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die();
		}
		/**
		 * Get coupons count for syncronization
		 */
		public static function wpsslw_get_coupon_count() {

			if ( ! isset( $_POST['wpssw_coupon_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_coupon_settings'] ) ), 'save_coupon_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpsslw_sheetname = 'All Coupons';

			$wpsslw_syncall = isset( $_POST['coupon_sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_sync_all'] ) ) : '';

			$wpsslw_query_args                 = array(
				'post_type'      => 'shop_coupon',
				'posts_per_page' => -1,
				'order'          => 'ASC',
			);
			$wpsslw_query_args['fields']       = 'ids'; // Fetch only ids.
			$wpsslw_coupon_spreadsheet_setting = parent::wpsslw_option( 'wpssw_coupon_spreadsheet_setting' );
			$wpsslw_spreadsheetid              = parent::wpsslw_option( 'wpssw_coupon_spreadsheet_id' );

			if ( 'yes' !== (string) $wpsslw_coupon_spreadsheet_setting || ! parent::wpsslw_check_sheet_exist( $wpsslw_spreadsheetid, $wpsslw_sheetname ) ) {
				echo esc_html__( 'Please save settings.', 'wpssw' );
				die();
			}
			$wpsslw_sheet    = "'" . $wpsslw_sheetname . "'!A:A";
			$wpsslw_allentry = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheet );
			$wpsslw_data     = $wpsslw_allentry->getValues();
			$wpsslw_allentry = null;
			$wpsslw_data     = array_map(
				function( $wpsslw_element ) {
					if ( isset( $wpsslw_element['0'] ) ) {
						return $wpsslw_element['0'];
					} else {
						return '';
					}
				},
				$wpsslw_data
			);
			if ( is_array( $wpsslw_data ) && ! empty( $wpsslw_data ) ) {
				$wpsslw_query_args['post__not_in'] = array_values( array_filter( array_unique( $wpsslw_data ) ) );
				$wpsslw_data                       = null;
			}
			$wpsslw_all_coupons = new WP_Query( $wpsslw_query_args );
			$couponcount        = 0;
			$couponlimit        = apply_filters( 'wpssw_coupon_sync_limit', 500 );
			$couponcount        = $wpsslw_all_coupons->found_posts;
			echo wp_json_encode(
				array(
					'totalcoupons' => $couponcount,
					'couponlimit'  => $couponlimit,
				)
			);
			die;
		}
		/**
		 * Sync coupon data to spreadsheet
		 */
		public static function wpsslw_sync_coupons() {

			if ( ! isset( $_POST['couponnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['couponnonce'] ) ), 'save_coupon_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				return;
			}
			$wpsslw_coupon_spreadsheet_setting = parent::wpsslw_option( 'wpssw_coupon_spreadsheet_setting' );
			$wpsslw_spreadsheetid              = parent::wpsslw_option( 'wpssw_coupon_spreadsheet_id' );

			if ( 'yes' !== (string) $wpsslw_coupon_spreadsheet_setting ) {
				return;
			}
			$wpsslw_inputoption = parent::wpsslw_option( 'wpssw_inputoption' );
			if ( ! $wpsslw_inputoption ) {
				$wpsslw_inputoption = 'USER_ENTERED';
			}
			$wpsslw_sheetname   = 'All Coupons';
			$wpsslw_couponcount = isset( $_POST['couponcount'] ) ? sanitize_text_field( wp_unslash( $_POST['couponcount'] ) ) : '';
			$wpsslw_couponlimit = isset( $_POST['couponlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['couponlimit'] ) ) : '';

			$wpsslw_syncall = isset( $_POST['coupon_sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_sync_all'] ) ) : '';

			$wpsslw_query_args                   = array(
				'post_type'      => 'shop_coupon',
				'posts_per_page' => -1,
				'order'          => 'ASC',
			);
			$wpsslw_query_args['fields']         = 'ids'; // Fetch only ids.
			$wpsslw_query_args['posts_per_page'] = $wpsslw_couponlimit;

			$wpsslw_sheet = "'" . $wpsslw_sheetname . "'!A:Z";

			$wpsslw_allentry   = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheet );
			$wpsslw_data       = $wpsslw_allentry->getValues();
			$wpsslw_allentry   = null;
			$wpsslw_data       = array_map(
				function( $wpsslw_element ) {
					if ( isset( $wpsslw_element['0'] ) ) {
						return $wpsslw_element['0'];
					} else {
						return '';
					}
				},
				$wpsslw_data
			);
			$wpsslw_data_count = count( $wpsslw_data );
			if ( is_array( $wpsslw_data ) && ! empty( $wpsslw_data ) ) {
				$wpsslw_query_args['post__not_in'] = array_values( array_filter( array_unique( $wpsslw_data ) ) );
				$wpsslw_data                       = null;
			}
			$wpsslw_all_coupons    = new WP_Query( $wpsslw_query_args );
			$wpsslw_all_coupon_ids = $wpsslw_all_coupons->posts;
			$wpsslw_all_coupons    = null;
			if ( empty( $wpsslw_all_coupon_ids ) ) {
				die();
			}
			$rangetofind         = $wpsslw_sheetname . '!A' . ( $wpsslw_data_count + 1 );
			$wpsslw_values_array = array();
			$newcoupon           = 0;
			foreach ( $wpsslw_all_coupon_ids as $wpsslw_coupon_id ) {
				if ( ! empty( $wpsslw_coupon_id ) && $newcoupon < $wpsslw_couponlimit ) {
					set_time_limit( 999 );
					$wpsslw_value        = self::wpsslw_make_coupon_value_array( 'insert', $wpsslw_coupon_id );
					$wpsslw_values_array = array_merge( $wpsslw_values_array, $wpsslw_value );
					$newcoupon++;
				}
			}
			$wpsslw_sheet = "'" . $wpsslw_sheetname . "'!A:A2";
			if ( ! empty( $wpsslw_values_array ) ) {
				try {
					$wpsslw_requestbody = self::$instance_api->valuerangeobject( $wpsslw_values_array );
					$wpsslw_params      = array( 'valueInputOption' => $wpsslw_inputoption );
					$param              = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $rangetofind, $wpsslw_requestbody, $wpsslw_params );
					$wpsslw_response    = self::$instance_api->appendentry( $param );

				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die;
		}
		/**
		 *  Prepare array value of coupon data to insert into sheet.
		 *
		 * @param string $wpsslw_operation operation to perfom on sheet.
		 * @param string $wpsslw_coupon_code Coupon Code.
		 * @return array $coupon_value_array
		 */
		public static function wpsslw_make_coupon_value_array( $wpsslw_operation = 'insert', $wpsslw_coupon_code = '' ) {
			if ( ! $wpsslw_coupon_code ) {
				return array();
			}
			$wpsslw_include = new WPSSLW_Include_Action();
			$wpsslw_include->wpsslw_include_coupon_compatibility_files();
			$wpsslw_headers              = apply_filters( 'wpsyncsheets_coupon_headers', array() );
			$wpsslw_coupon               = new WC_Coupon( $wpsslw_coupon_code );
			$wpsslw_coupon_row           = array();
			$wpsslw_coupon_row[]         = $wpsslw_coupon->get_id();
			$wpsslw_woo_selections       = stripslashes_deep( parent::wpsslw_option( 'wpssw_woo_coupon_headers' ) );
			$wpsslw_classarray           = array();
			$wpsslw_woo_selections_count = count( $wpsslw_woo_selections );
			for ( $i = 0; $i < $wpsslw_woo_selections_count; $i++ ) {
				$wpsslw_classarray[ $wpsslw_woo_selections[ $i ] ] = parent::wpsslw_find_class( $wpsslw_headers, $wpsslw_woo_selections[ $i ] );
			}

			foreach ( $wpsslw_classarray as $headername => $classname ) {
				if ( ! empty( $classname ) ) {
					$wpsslw_coupon_row[] = $classname::get_value( $headername, $wpsslw_coupon );
				} else {
					$wpsslw_coupon_row[] = '';
				}
			}
			$wpsslw_coupon_row = self::wpsslw_couponcleanArray( $wpsslw_coupon_row );
			return array( $wpsslw_coupon_row );
		}
		/**
		 *  Insert coupon data into sheet
		 *
		 * @param object $wpsslw_coupon .
		 */
		public static function wpsslw_insert_coupon_data_into_sheet( $wpsslw_coupon ) {
			try {
				if ( ! self::$instance_api->checkcredenatials() ) {
					return;
				}
				if ( ! $wpsslw_coupon ) {
					return;
				}
				$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_coupon_spreadsheet_id' );
				$wpsslw_sheetname     = 'All Coupons';
				if ( ! parent::wpsslw_check_sheet_exist( $wpsslw_spreadsheetid, $wpsslw_sheetname ) ) {
					return;
				}
				$wpsslw_inputoption = parent::wpsslw_option( 'wpssw_inputoption' );
				if ( ! $wpsslw_inputoption ) {
					$wpsslw_inputoption = 'USER_ENTERED';
				}
				$wpsslw_headers_name        = parent::wpsslw_option( 'wpssw_woo_coupon_headers' );
				$wpsslw_sheet               = "'" . $wpsslw_sheetname . "'!A:A";
				$wpsslw_allentry            = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheet );
				$wpsslw_data                = $wpsslw_allentry->getValues();
				$wpsslw_data                = array_map(
					function( $wpsslw_element ) {
						if ( isset( $wpsslw_element['0'] ) ) {
							return $wpsslw_element['0'];
						} else {
							return '';
						}
					},
					$wpsslw_data
				);
				$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
				$wpsslw_sheetid             = $wpsslw_existingsheetsnames[ $wpsslw_sheetname ];
				$is_exists                  = array_search(
					(int) $wpsslw_coupon->get_id(),
					parent::wpsslw_convert_int( $wpsslw_data ),
					true
				);
				$wpsslw_values_array        = self::wpsslw_make_coupon_value_array( 'update', $wpsslw_coupon->get_id() );
				$wpsslw_append              = 0;
				if ( $is_exists > 0 ) {
					if ( 0 === (int) $wpsslw_append ) {
						$wpsslw_append   = 1;
						$rownum          = $is_exists + 1;
						$rangetoupdate   = $wpsslw_sheetname . '!A' . $rownum;
						$params          = array( 'valueInputOption' => 'USER_ENTERED' );
						$requestbody     = self::$instance_api->valuerangeobject( $wpsslw_values_array );
						$param           = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $rangetoupdate, $requestbody, $params );
						$wpsslw_response = self::$instance_api->updateentry( $param );
					}
				} else {
					$coupon_inherit_style = self::wpsslw_option( 'wpssw_coupon_inherit_style' );
					foreach ( $wpsslw_data as $wpsslw_key => $wpsslw_value ) {
						if ( ! empty( $wpsslw_value ) ) {
							if ( ( (int) $wpsslw_coupon->get_id() < (int) $wpsslw_value ) ) {
								$wpsslw_append     = 1;
								$wpsslw_startindex = $wpsslw_key;
								$wpsslw_endindex   = $wpsslw_key + 1;
								$param             = array();
								$param             = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
								if ( 'no' === (string) $coupon_inherit_style ) {
									$wpsslw_batchupdaterequest = self::$instance_api->insertdimensionobject( $param, false );
								} else {
									$wpsslw_batchupdaterequest = self::$instance_api->insertdimensionobject( $param, true );
								}
								$requestobject                  = array();
								$requestobject['spreadsheetid'] = $wpsslw_spreadsheetid;
								$requestobject['requestbody']   = $wpsslw_batchupdaterequest;
								$wpsslw_response                = self::$instance_api->formatsheet( $requestobject );
								$wpsslw_start_index             = $wpsslw_startindex + 1;
								$wpsslw_rangetoupdate           = $wpsslw_sheetname . '!A' . $wpsslw_start_index;
								$wpsslw_params                  = array( 'valueInputOption' => $wpsslw_inputoption );
								$wpsslw_requestbody             = self::$instance_api->valuerangeobject( $wpsslw_values_array );
								$param                          = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_rangetoupdate, $wpsslw_requestbody, $wpsslw_params );
								$wpsslw_response                = self::$instance_api->updateentry( $param );
								break;
							}
						}
					}
				}
				if ( 0 === (int) $wpsslw_append ) {
					$wpsslw_isupdated   = 0;
					$wpsslw_requestbody = self::$instance_api->valuerangeobject( $wpsslw_values_array );
					$wpsslw_params      = array( 'valueInputOption' => $wpsslw_inputoption );
					$rangetofind        = $wpsslw_sheetname . '!A' . ( count( $wpsslw_data ) + 1 );
					$param              = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $rangetofind, $wpsslw_requestbody, $wpsslw_params );
					$wpsslw_response    = self::$instance_api->appendentry( $param );
				}
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
		/**
		 * Clean coupon data array.
		 *
		 * @param array $wpsslw_array coupon data array.
		 * @return array $wpsslw_array
		 */
		public static function wpsslw_couponcleanArray( $wpsslw_array ) {
			$wpsslw_max   = count( parent::wpsslw_option( 'wpssw_woo_coupon_headers' ) ) + 1;
			$wpsslw_array = parent::wpsslw_cleanarray( $wpsslw_array, $wpsslw_max );
			return $wpsslw_array;
		}
		/**
		 * Get all product categories.
		 *
		 * @return array $wpsslw_categories
		 */
		public static function wpsslw_get_all_product_categories() {
			global $wpdb;
			// @codingStandardsIgnoreStart.
			$query            = "SELECT t.term_id AS ID, t.name AS title  
				FROM {$wpdb->prefix}terms AS t
				LEFT JOIN {$wpdb->prefix}term_taxonomy AS ta ON ta.term_id = t.term_id
				WHERE ta.taxonomy='product_cat'
				ORDER BY t.name ASC"; // db call ok.
			$cats             = $wpdb->get_results( $query );
			// @codingStandardsIgnoreEnd.
			$wpsslw_categories = array();
			foreach ( $cats as $cat ) {
				$wpsslw_categories[ $cat->ID ] = $cat->title;
			}
			return $wpsslw_categories;
		}
	}
	new WPSSLW_Coupon();
endif;
