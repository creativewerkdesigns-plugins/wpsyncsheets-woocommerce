<?php
/**
 * Main WPSyncSheetsWooCommerce namespace.
 *
 * @package wpsyncsheets-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSLW_Customer' ) ) :
	/**
	 * Class WPSSLW_Customer.
	 */
	class WPSSLW_Customer extends WPSSLW_Settings {
		/**
		 * Initialization
		 */
		public function __construct() {
			$wpsslw_include = new WPSSLW_Include_Action();
			$wpsslw_include->wpsslw_include_customer_hook();
			$wpsslw_include->wpsslw_include_customer_ajax_hook();
		}
		/**
		 *
		 * Save Customer settings tab's setting
		 */
		public static function wpsslw_update_customer_settings() {

			if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( isset( $_POST['woocustomer_header_list'] ) && isset( $_POST['woocustomer_custom'] ) ) {
				$wpsslw_woo_customer_headers        = array_map( 'sanitize_text_field', wp_unslash( $_POST['woocustomer_header_list'] ) );
				$wpsslw_woo_customer_headers_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['woocustomer_custom'] ) );
				if ( isset( $_POST['customer_settings_checkbox'] ) ) {

					if ( isset( $_POST['custsheetselection'] ) && 'new' === (string) sanitize_text_field( wp_unslash( $_POST['custsheetselection'] ) ) ) {
						$wpsslw_newsheetname = isset( $_POST['customer_spreadsheet_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['customer_spreadsheet_name'] ) ) ) : '';

						/*
						 *Create new spreadsheet
						 */
						$wpsslw_requestbody   = self::$instance_api->createspreadsheetobject( $wpsslw_newsheetname );
						$wpsslw_response      = self::$instance_api->createspreadsheet( $wpsslw_requestbody );
						$wpsslw_spreadsheetid = $wpsslw_response['spreadsheetId'];
					} else {
						$wpsslw_spreadsheetid = isset( $_POST['customer_spreadsheet'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_spreadsheet'] ) ) : '';
					}
					parent::wpsslw_update_option( 'wpssw_customer_spreadsheet_id', $wpsslw_spreadsheetid );
					parent::wpsslw_update_option( 'wpssw_customer_spreadsheet_setting', 'yes' );
				} else {
					parent::wpsslw_update_option( 'wpssw_customer_spreadsheet_setting', 'no' );
					parent::wpsslw_update_option( 'wpssw_customer_spreadsheet_id', '' );
					return;
				}

				$wpsslw_sheetname      = 'All Customers';
				$requestarray          = array();
				$deleterequestarray    = array();
				$response              = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpsslw_existingsheets = array_flip( $wpsslw_existingsheets );
				$wpsslw_inputoption    = parent::wpsslw_option( 'wpssw_inputoption' );
				if ( ! $wpsslw_inputoption ) {
					$wpsslw_inputoption = 'USER_ENTERED';
				}
				if ( count( $wpsslw_woo_customer_headers ) > 0 ) {
					array_unshift( $wpsslw_woo_customer_headers, 'Customer Id' );
				}
				if ( count( $wpsslw_woo_customer_headers_custom ) > 0 ) {
					array_unshift( $wpsslw_woo_customer_headers_custom, 'Customer Id' );
				}
				$wpsslw_old_header_customer = parent::wpsslw_option( 'wpssw_woo_customer_headers' );
				if ( empty( $wpsslw_old_header_customer ) ) {
					$wpsslw_old_header_customer = array();
				}
				if ( count( $wpsslw_old_header_customer ) > 0 ) {
					array_unshift( $wpsslw_old_header_customer, 'Customer Id' );
				}
				if ( ! in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpsslw_spreadsheetid;
					$param['sheetname']     = $wpsslw_sheetname;
					$wpsslw_response        = self::$instance_api->newsheetobject( $param );
					$wpsslw_range           = trim( $wpsslw_sheetname ) . '!A1';
					$wpsslw_requestbody     = self::$instance_api->valuerangeobject( array( $wpsslw_woo_customer_headers_custom ) );
					$wpsslw_params          = array( 'valueInputOption' => $wpsslw_inputoption );
					$param                  = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_range, $wpsslw_requestbody, $wpsslw_params );
					$wpsslw_response        = self::$instance_api->appendentry( $param );
				}
				if ( 'new' === (string) sanitize_text_field( wp_unslash( $_POST['custsheetselection'] ) ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpsslw_spreadsheetid;
					$wpsslw_response        = self::$instance_api->deletesheetobject( $param );
				}
				if ( $wpsslw_old_header_customer !== $wpsslw_woo_customer_headers && in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
					$wpsslw_existingsheets = array();
					$response              = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
					$wpsslw_existingsheets = self::$instance_api->get_sheet_list( $response );
					$wpsslw_existingsheets = array_flip( $wpsslw_existingsheets );
					// Delete deactivate column from sheet.
					$wpsslw_column = array_diff( $wpsslw_old_header_customer, $wpsslw_woo_customer_headers );
					if ( ! empty( $wpsslw_column ) ) {
						$wpsslw_column = array_reverse( $wpsslw_column, true );
						foreach ( $wpsslw_column as $columnindex => $columnval ) {
							unset( $wpsslw_old_header_customer[ $columnindex ] );
							$wpsslw_old_header_customer = array_values( $wpsslw_old_header_customer );
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
				if ( $wpsslw_old_header_customer !== $wpsslw_woo_customer_headers ) {
					foreach ( $wpsslw_woo_customer_headers as $key => $hname ) {
						if ( 'Customer Id' === (string) $hname ) {
							continue;
						}
						$wpsslw_startindex = array_search( $hname, $wpsslw_old_header_customer, true );
						if ( false !== $wpsslw_startindex && ( isset( $wpsslw_old_header_customer[ $key ] ) && $wpsslw_old_header_customer[ $key ] !== $hname ) ) {
							unset( $wpsslw_old_header_customer[ $wpsslw_startindex ] );
							$wpsslw_old_header_customer = array_merge( array_slice( $wpsslw_old_header_customer, 0, $key ), array( 0 => $hname ), array_slice( $wpsslw_old_header_customer, $key, count( $wpsslw_old_header_customer ) - $key ) );
							$wpsslw_endindex            = $wpsslw_startindex + 1;
							$wpsslw_destindex           = $key;
							if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
								$wpsslw_sheetid = array_search( $wpsslw_sheetname, $wpsslw_existingsheets, true );
								if ( $wpsslw_sheetid ) {
									$param              = array();
									$param              = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
									$param['destindex'] = $wpsslw_destindex;
									$requestarray[]     = self::$instance_api->moveDimensionrequests( $param );
								}
							}
						} elseif ( false === (bool) $wpsslw_startindex ) {
							$wpsslw_old_header_customer = array_merge( array_slice( $wpsslw_old_header_customer, 0, $key ), array( 0 => $hname ), array_slice( $wpsslw_old_header_customer, $key, count( $wpsslw_old_header_customer ) - $key ) );
							if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
								$wpsslw_sheetid = array_search( $wpsslw_sheetname, $wpsslw_existingsheets, true );
								if ( $wpsslw_sheetid ) {
									$param                  = array();
									$wpsslw_startindex      = $key;
									$wpsslw_endindex        = $key + 1;
									$param                  = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
									$customer_inherit_style = parent::wpsslw_option( 'wpssw_customer_inherit_style' );
									if ( 'no' === (string) $customer_inherit_style ) {
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
				$freeze_header         = parent::wpsslw_option( 'freeze_header' );
				$wpsslw_existingsheets = array();
				$response              = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpsslw_existingsheets = array_flip( $wpsslw_existingsheets );
				if ( 'yes' === (string) $freeze_header ) {
					$wpsslw_freeze = 1;
				} else {
					$wpsslw_freeze = 0;
				}
				if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
					$wpsslw_sheetid = array_search( $wpsslw_sheetname, $wpsslw_existingsheets, true );
					// freeze customer headers.
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
					$wpsslw_requestbody = self::$instance_api->valuerangeobject( array( $wpsslw_woo_customer_headers_custom ) );
					$wpsslw_params      = array( 'valueInputOption' => $wpsslw_inputoption );
					$param              = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_range, $wpsslw_requestbody, $wpsslw_params );
					$wpsslw_response    = self::$instance_api->updateentry( $param );
				}
				parent::wpsslw_update_option( 'wpssw_woo_customer_headers', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocustomer_header_list'] ) ) );
				parent::wpsslw_update_option( 'wpssw_woo_customer_headers_custom', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocustomer_custom'] ) ) );
			}
		}
		/**
		 * Add new user (created while checkout) data into sheet
		 *
		 * @param int   $customer_id .
		 * @param array $data contains user data.
		 */
		public static function action_woocommerce_checkout_update_customer( $customer_id, $data ) {
			self::wpsslw_insert_customer_data_into_sheet( $customer_id );
		}
		/**
		 * Insert / Update user data into sheet on user update
		 *
		 * @param int $user_id user id.
		 */
		public static function edit_user_profile_update( $user_id ) {

			$user = new WP_User( $user_id );
			// @codingStandardsIgnoreStart.
			$data = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
			// @codingStandardsIgnoreEnd.
			if ( isset( $data['action'] ) && 'update' === (string) $data['action'] ) {
				$role = $data['role'];
			}
			if ( isset( $data['action'] ) && 'save_account_details' === (string) $data['action'] ) {
				$user                  = new WP_User( $user_id );
				$wpsslw_customer_roles = array_values( $user->roles );
				$role                  = $wpsslw_customer_roles[0];
				$data['user_id']       = $user_id;
				$data['role']          = $role;
			}
			if ( isset( $data['action'] ) && in_array( $data['action'], array( 'update', 'save_account_details' ), true ) ) {
				self::wpsslw_insert_customer_data_into_sheet( $user_id, $data );
				return;
			}
			self::wpsslw_insert_customer_data_into_sheet( $user_id );
		}
		/**
		 * Add new user data into sheet
		 *
		 * @param int $user_id user id.
		 */
		public static function wpsslw_user_registration_save( $user_id ) {
			$data = array();
			// @codingStandardsIgnoreStart.
			$data = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
			// @codingStandardsIgnoreEnd.
			if ( isset( $data['action'] ) && 'createuser' === (string) $data['action'] ) {
				$role            = $data['role'];
				$data['user_id'] = $user_id;
				self::wpsslw_insert_customer_data_into_sheet( $user_id, $data );
				return;
			}
			self::wpsslw_insert_customer_data_into_sheet( $user_id );
		}
		/**
		 * Delete customer's data from sheet on trashing user
		 *
		 * @param int $user_id user id.
		 */
		public static function wpsslw_delete_user( $user_id ) {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			// @codingStandardsIgnoreStart.
			if ( isset( $_POST['users'] ) && is_array( $_POST['users'] ) && count( $_POST['users'] ) > 0 && isset( $_POST['action'] ) && 'dodelete' === sanitize_text_field( wp_unslash( $_POST['action'] )  ) ) {
				$changed_users = array_map( 'sanitize_text_field', wp_unslash( $_POST['users'] ) );
				// @codingStandardsIgnoreEnd.

				if ( (int) $user_id === (int) $changed_users[ count( $changed_users ) - 1 ] ) {
					$wpsslw_spreadsheetid                = parent::wpsslw_option( 'wpssw_customer_spreadsheet_id' );
					$wpsslw_customer_spreadsheet_setting = parent::wpsslw_option( 'wpssw_customer_spreadsheet_setting' );
					$wpsslw_sheetname                    = 'All Customers';

					$settings = array(
						'setting'        => 'customer',
						'setting_enable' => $wpsslw_customer_spreadsheet_setting,
						'spreadsheet_id' => $wpsslw_spreadsheetid,
						'sheetname'      => $wpsslw_sheetname,
					);
					parent::wpsslw_multiple_update_data( $changed_users, $settings, false, 'delete' );
				}
				return;
			}

			$wpsslw_customer_spreadsheet_setting = parent::wpsslw_option( 'wpssw_customer_spreadsheet_setting' );

			if ( 'yes' !== (string) $wpsslw_customer_spreadsheet_setting ) {
				return;
			}

			$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_customer_spreadsheet_id' );
			$wpsslw_sheetname     = 'All Customers';
			if ( ! self::wpsslw_check_sheet_exist( $wpsslw_spreadsheetid, $wpsslw_sheetname ) ) {
				return;
			}
			$customer              = get_userdata( $user_id );
			$wpsslw_customer_roles = array_values( $customer->roles );
			$role                  = $wpsslw_customer_roles[0];

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
			$wpsslw_num      = array_search( (int) $user_id, parent::wpsslw_convert_int( $wpsslw_data ), true );
			if ( $wpsslw_num > 0 ) {
				$response                   = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
				$wpsslw_sheetid             = $wpsslw_existingsheetsnames[ $wpsslw_sheetname ];
				$wpsslw_startindex          = $wpsslw_num;
				$wpsslw_endindex            = $wpsslw_num + 1;
				$param                      = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
				$deleterequest              = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				try {
					if ( ! empty( $deleterequest ) ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpsslw_spreadsheetid;
						$param['requestarray']  = $deleterequest;
						self::$instance_api->updatebachrequests( $param );
					}
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
		}
		/**
		 * Clean Customer data array.
		 *
		 * @param array $wpsslw_array customer data array.
		 */
		public static function wpsslw_customercleanArray( $wpsslw_array ) {
			$wpsslw_max = count( parent::wpsslw_option( 'wpssw_woo_customer_headers' ) ) + 1;
			for ( $i = 0; $i < $wpsslw_max; $i++ ) {
				if ( ! isset( $wpsslw_array[ $i ] ) || is_null( $wpsslw_array[ $i ] ) ) {
					$wpsslw_array[ $i ] = '';
				} else {
					$wpsslw_array[ $i ] = trim( $wpsslw_array[ $i ] );
				}
			}
			ksort( $wpsslw_array );
			return $wpsslw_array;
		}
		/**
		 *
		 * Get customers count for syncronization
		 */
		public static function wpsslw_get_customer_count() {

			if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
				echo 'error';
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				echo 'error';
				die();
			}
			$wpsslw_customer_spreadsheet_setting = parent::wpsslw_option( 'wpssw_customer_spreadsheet_setting' );
			$wpsslw_spreadsheetid                = parent::wpsslw_option( 'wpssw_customer_spreadsheet_id' );

			if ( 'yes' !== (string) $wpsslw_customer_spreadsheet_setting ) {
				return;
			}
			$wpsslw_sheetname = 'All Customers';
			$wpsslw_sheet     = "'" . $wpsslw_sheetname . "'!A:A";
			$wpsslw_allentry  = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheet );
			$wpsslw_data      = $wpsslw_allentry->getValues();
			$wpsslw_allentry  = null;
			$wpsslw_data      = array_map(
				function( $wpsslw_element ) {
					if ( isset( $wpsslw_element['0'] ) ) {
						return (int) $wpsslw_element['0'];
					} else {
						return '';
					}
				},
				$wpsslw_data
			);

			$wpsslw_syncall = isset( $_POST['cust_sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['cust_sync_all'] ) ) : '';

			$args            = array(
				'role'    => 'customer',
				'orderby' => 'ID',
				'order'   => 'ASC',
			);
			$args['fields']  = 'ID'; // Fetch only ids.
			$args['exclude'] = $wpsslw_data;
			$wpsslw_data     = null;
			$customers       = get_users( $args );

			if ( empty( $customers ) ) {
				echo 'notfound';
				die();
			}

			$total         = count( $customers );
			$customerlimit = apply_filters( 'wpssw_customer_sync_limit', 500 );
			echo wp_json_encode(
				array(
					'totalcustomers' => $total,
					'customerlimit'  => $customerlimit,
				)
			);
			die;
		}
		/**
		 *
		 * Sync Customers
		 */
		public static function wpsslw_sync_customers() {
			if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_customer_spreadsheet_id' );

			$wpsslw_sheetname = 'All Customers';
			$wpsslw_sheet     = "'" . $wpsslw_sheetname . "'!A:A";
			$wpsslw_allentry  = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheet );
			$wpsslw_data      = $wpsslw_allentry->getValues();
			$wpsslw_data      = array_map(
				function( $wpsslw_element ) {
					if ( isset( $wpsslw_element['0'] ) ) {
						return (int) $wpsslw_element['0'];
					} else {
						return '';
					}
				},
				$wpsslw_data
			);

			$wpsslw_customercount = isset( $_POST['customercount'] ) ? sanitize_text_field( wp_unslash( $_POST['customercount'] ) ) : '';
			$wpsslw_customerlimit = isset( $_POST['customerlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['customerlimit'] ) ) : '';

			$wpsslw_syncall = isset( $_POST['cust_sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['cust_sync_all'] ) ) : '';

			$args            = array(
				'role'    => 'customer',
				'orderby' => 'ID',
				'order'   => 'ASC',
			);
			$args['fields']  = 'ID'; // Fetch only ids.
			$args['exclude'] = $wpsslw_data;
			$args['number']  = $wpsslw_customerlimit;

			$wpsslw_data_count = count( $wpsslw_data );

			$wpsslw_allentry = null;
			$wpsslw_data     = null;

			$customers = get_users( $args );

			if ( empty( $customers ) ) {
				die();
			}

			$rangetofind         = $wpsslw_sheetname . '!A' . ( $wpsslw_data_count + 1 );
			$wpsslw_values_array = array();
			$newcustomer         = 0;
			foreach ( $customers as $customer_id ) {
				if ( ! empty( $customer_id ) && $newcustomer < $wpsslw_customerlimit ) {
					set_time_limit( 999 );
					$customer            = new WP_User( $customer_id );
					$wpsslw_value        = self::wpsslw_make_customer_value_array( 'insert', $customer );
					$wpsslw_values_array = array_merge( $wpsslw_values_array, $wpsslw_value );
					$newcustomer++;
				}
			}
			$wpsslw_sheet = "'" . $wpsslw_sheetname . "'!A:A2";
			if ( ! empty( $wpsslw_values_array ) ) {
				try {
					$wpsslw_inputoption = parent::wpsslw_option( 'wpssw_inputoption' );
					if ( ! $wpsslw_inputoption ) {
						$wpsslw_inputoption = 'USER_ENTERED';
					}
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
		 * Prepare array value of customer data to insert into sheet.
		 *
		 * @param string       $wpsslw_operation operation to perform on sheet.
		 * @param array|object $wpsslw_customer customer data.
		 * @return array $customer_value_array
		 */
		public static function wpsslw_make_customer_value_array( $wpsslw_operation = 'insert', $wpsslw_customer = '' ) {
			if ( ! $wpsslw_customer ) {
				return array();
			}
			$wpsslw_include = new WPSSLW_Include_Action();
			$wpsslw_include->wpsslw_include_customer_compatibility_files();
			$wpsslw_headers = apply_filters( 'wpsyncsheets_customer_headers', array() );

			$wpsslw_static_customer_header_values = stripslashes_deep( parent::wpsslw_option( 'wpssw_static_customer_header_values' ) );
			$wpsslw_custom_value                  = array();
			$wpsslw_static_header_name            = array();
			if ( ! empty( $wpsslw_static_customer_header_values ) ) {
				foreach ( $wpsslw_static_customer_header_values as $wpsslw_static_header_value ) {
					if ( strpos( $wpsslw_static_header_value, ',(static_header),' ) ) {
						$wpsslw_static_header_value = str_replace( ',(static_header),', ',', $wpsslw_static_header_value );
						$wpsslw_custom_value[]      = explode( ',', $wpsslw_static_header_value );
					}
				}
				if ( ! empty( $wpsslw_custom_value ) ) {
					$wpsslw_static_header_name = array_column( $wpsslw_custom_value, 0 );
				}
			}

			$wpsslw_profile_image = '';
			$customer_id          = '';
			if ( 'object' === gettype( $wpsslw_customer ) ) {
				$wpsslw_customer_row[]  = $wpsslw_customer->ID;
				$customer_id            = $wpsslw_customer->ID;
				$customers_metadata     = get_user_meta( $wpsslw_customer->ID );
				$wpsslw_customer_roles  = array_values( $wpsslw_customer->roles );
				$wpsslw_customer_values = $wpsslw_customer->data;
				$wpsslw_profile_image   = '=IMAGE("' . get_avatar_url( $wpsslw_customer->ID ) . '")';
			}
			if ( 'array' === gettype( $wpsslw_customer ) ) {
				$wpsslw_customer_row[]  = $wpsslw_customer['user_id'];
				$customer_id            = $wpsslw_customer['user_id'];
				$user                   = new WP_User( $wpsslw_customer['user_id'] );
				$customers_metadata     = get_user_meta( $wpsslw_customer['user_id'] );
				$wpsslw_customer_values = $user->data;
				$wpsslw_customer_roles  = array( $wpsslw_customer['role'] );
				$wpsslw_profile_image   = '=IMAGE("' . get_avatar_url( $wpsslw_customer['user_id'] ) . '")';
				if ( 'save_account_details' === (string) $wpsslw_customer['action'] ) {
					$customers_metadata['first_name'][0] = $wpsslw_customer['account_first_name'];
					$customers_metadata['last_name'][0]  = $wpsslw_customer['account_last_name'];
					$wpsslw_customer_values->user_email  = $wpsslw_customer['account_email'];
				} elseif ( 'update' === (string) $wpsslw_customer['action'] ) {
					$wpsslw_customer_values->user_email           = $wpsslw_customer['email'];
					$wpsslw_customer_values->user_url             = $wpsslw_customer['url'];
					$customers_metadata['first_name'][0]          = $wpsslw_customer['first_name'];
					$customers_metadata['last_name'][0]           = $wpsslw_customer['last_name'];
					$customers_metadata['nickname'][0]            = $wpsslw_customer['nickname'];
					$customers_metadata['description'][0]         = $wpsslw_customer['description'];
					$customers_metadata['billing_first_name'][0]  = $wpsslw_customer['billing_first_name'];
					$customers_metadata['billing_last_name'][0]   = $wpsslw_customer['billing_last_name'];
					$customers_metadata['billing_company'][0]     = $wpsslw_customer['billing_company'];
					$customers_metadata['billing_address_1'][0]   = $wpsslw_customer['billing_address_1'];
					$customers_metadata['billing_address_2'][0]   = $wpsslw_customer['billing_address_2'];
					$customers_metadata['billing_city'][0]        = $wpsslw_customer['billing_city'];
					$customers_metadata['billing_postcode'][0]    = $wpsslw_customer['billing_postcode'];
					$customers_metadata['billing_country'][0]     = $wpsslw_customer['billing_country'];
					$customers_metadata['billing_state'][0]       = $wpsslw_customer['billing_state'];
					$customers_metadata['billing_phone'][0]       = $wpsslw_customer['billing_phone'];
					$customers_metadata['billing_email'][0]       = $wpsslw_customer['billing_email'];
					$customers_metadata['shipping_first_name'][0] = $wpsslw_customer['shipping_first_name'];
					$customers_metadata['shipping_last_name'][0]  = $wpsslw_customer['shipping_last_name'];
					$customers_metadata['shipping_company'][0]    = $wpsslw_customer['shipping_company'];
					$customers_metadata['shipping_address_1'][0]  = $wpsslw_customer['shipping_address_1'];
					$customers_metadata['shipping_address_2'][0]  = $wpsslw_customer['shipping_address_2'];
					$customers_metadata['shipping_city'][0]       = $wpsslw_customer['shipping_city'];
					$customers_metadata['shipping_postcode'][0]   = $wpsslw_customer['shipping_postcode'];
					$customers_metadata['shipping_country'][0]    = $wpsslw_customer['shipping_country'];
					$customers_metadata['shipping_state'][0]      = $wpsslw_customer['shipping_state'];
				} elseif ( 'createuser' === (string) $wpsslw_customer['action'] ) {
					$customers_metadata['first_name'][0] = $wpsslw_customer['first_name'];
					$customers_metadata['last_name'][0]  = $wpsslw_customer['last_name'];
				}
			}
			$customers_metadata['profile_image'] = $wpsslw_profile_image;
			$customers_metadata['user_url']      = $wpsslw_customer_values->user_url;
			$customers_metadata['user_email']    = $wpsslw_customer_values->user_email;
			$customers_metadata['roles']         = $wpsslw_customer_roles;
			$customers_metadata['user_login']    = $wpsslw_customer_values->user_login;
			$wpsslw_woo_selections               = stripslashes_deep( parent::wpsslw_option( 'wpssw_woo_customer_headers' ) );
			$wpsslw_classarray                   = array();
			$wpsslw_woo_selections_count         = count( $wpsslw_woo_selections );
			for ( $i = 0; $i < $wpsslw_woo_selections_count; $i++ ) {

				if ( in_array( $wpsslw_woo_selections[ $i ], $wpsslw_static_header_name, true ) ) {
					$wpsslw_classarray[ $wpsslw_woo_selections[ $i ] ] = 'WPSSLW_Customer_Headers';
					continue;
				}

				$wpsslw_classarray[ $wpsslw_woo_selections[ $i ] ] = parent::wpsslw_find_class( $wpsslw_headers, $wpsslw_woo_selections[ $i ] );
			}

			foreach ( $wpsslw_classarray as $headername => $classname ) {
				if ( ! empty( $classname ) ) {
					$wpsslw_customer_row[] = $classname::get_value( $headername, $customers_metadata, $customer_id, $wpsslw_customer, $wpsslw_custom_value );
				} else {
					$wpsslw_customer_row[] = '';
				}
			}
			$wpsslw_customer_row = self::wpsslw_customercleanArray( $wpsslw_customer_row );
			return array( $wpsslw_customer_row );
		}
		/**
		 * Insert customers data into sheet.
		 *
		 * @param int   $user_id customer id.
		 * @param array $data customer data array.
		 */
		public static function wpsslw_insert_customer_data_into_sheet( $user_id, $data = array() ) {
			try {
				if ( ! $user_id ) {
					return;
				}
				if ( ! self::$instance_api->checkcredenatials() ) {
					return;
				}
				$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_customer_spreadsheet_id' );
				$wpsslw_sheetname     = 'All Customers';
				if ( ! parent::wpsslw_check_sheet_exist( $wpsslw_spreadsheetid, $wpsslw_sheetname ) ) {
					return;
				}
				$wpsslw_inputoption = parent::wpsslw_option( 'wpssw_inputoption' );
				if ( ! $wpsslw_inputoption ) {
					$wpsslw_inputoption = 'USER_ENTERED';
				}
				$wpsslw_headers_name = parent::wpsslw_option( 'wpssw_woo_customer_headers' );
				if ( ! empty( $data ) && ( isset( $data['action'] ) && 'wpssw_customer_import' !== (string) $data['action'] && 'wpssw_autoimport_customers_cron_run' !== (string) $data['action'] ) ) {
					$customer = $data;
					$role     = isset( $data['role'] ) ? $data['role'] : '';
				} else {
					$customer              = get_userdata( $user_id );
					$wpsslw_customer_roles = ( false !== $customer && null !== $customer && ! is_wp_error( $customer ) ) ? array_values( $customer->roles ) : array();
					$role                  = isset( $wpsslw_customer_roles[0] ) ? $wpsslw_customer_roles[0] : '';
				}
				if ( empty( $role ) || false === $customer || null === $customer || is_wp_error( $customer ) ) {
					return;
				}
				$wpsslw_sheetname = 'All Customers';
				$wpsslw_sheet     = "'" . $wpsslw_sheetname . "'!A:A";
				$wpsslw_allentry  = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheet );
				$wpsslw_data      = $wpsslw_allentry->getValues();
				$wpsslw_allentry  = null;
				$wpsslw_data      = array_map(
					function( $wpsslw_element ) {
						if ( isset( $wpsslw_element['0'] ) ) {
							return $wpsslw_element['0'];
						} else {
							return '';
						}
					},
					$wpsslw_data
				);

				$is_exists = array_search( (int) $user_id, parent::wpsslw_convert_int( $wpsslw_data ), true );
				if ( $is_exists > 0 && 'customer' !== (string) $role ) {
					self::wpsslw_delete_user( $user_id );
					return;
				}
				$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
				$wpsslw_sheetid             = $wpsslw_existingsheetsnames[ $wpsslw_sheetname ];

				if ( $is_exists > 0 ) {
					$wpsslw_values_array = self::wpsslw_make_customer_value_array( 'update', $customer );
					$rownum              = $is_exists + 1;
					$rangetoupdate       = $wpsslw_sheetname . '!A' . $rownum;
					$params              = array( 'valueInputOption' => 'USER_ENTERED' );
					$requestbody         = self::$instance_api->valuerangeobject( $wpsslw_values_array );
					$param               = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $rangetoupdate, $requestbody, $params );
					$wpsslw_response     = self::$instance_api->updateentry( $param );
				} else {
					if ( 'customer' !== (string) $role ) {
						return;
					}
					$wpsslw_values_array = self::wpsslw_make_customer_value_array( 'insert', $customer );

					$wpsslw_requestbody     = self::$instance_api->valuerangeobject( $wpsslw_values_array );
					$wpsslw_params          = array( 'valueInputOption' => 'USER_ENTERED' );
					$wpsslw_append          = 0;
					$customer_inherit_style = parent::wpsslw_option( 'wpssw_customer_inherit_style' );
					foreach ( $wpsslw_data as $wpsslw_key => $wpsslw_value ) {
						if ( ! empty( $wpsslw_value ) ) {
							if ( ( (int) $user_id < (int) $wpsslw_value ) && $is_exists < 1 ) {
								$wpsslw_append = 1;

								$wpsslw_startindex = $wpsslw_key;
								$wpsslw_endindex   = $wpsslw_key + 1;

								$param = array();
								$param = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );

								if ( 'no' === (string) $customer_inherit_style ) {
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
								$param                          = array();
								$param                          = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_rangetoupdate, $wpsslw_requestbody, $wpsslw_params );
								$wpsslw_response                = self::$instance_api->updateentry( $param );
								break;
							}
						}
					}
					if ( 0 === (int) $wpsslw_append ) {

						$wpsslw_sheetname . '!A' . ( count( $wpsslw_data ) + 1 );
						$param           = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_sheetname, $wpsslw_requestbody, $wpsslw_params );
						$wpsslw_response = self::$instance_api->appendentry( $param );

					}
				}
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
		/**
		 * Clear Customer settings sheet
		 */
		public static function wpsslw_clear_customersheet() {
			$wpsslw_client                       = self::$instance_api->getClient();
			$wpsslw_service                      = new Google_Service_Sheets( $wpsslw_client );
			$wpsslw_customer_spreadsheet_setting = WPSSLW_Settings::wpsslw_option( 'wpssw_customer_spreadsheet_setting' );
			$wpsslw_spreadsheetid                = WPSSLW_Settings::wpsslw_option( 'wpssw_customer_spreadsheet_id' );

			if ( 'yes' !== (string) $wpsslw_customer_spreadsheet_setting ) {
				echo esc_html__( 'Please save settings.', 'wpssw' );
				die();
			}
			$requestbody                = new Google_Service_Sheets_ClearValuesRequest();
			$total_headers              = count( WPSSLW_Settings::wpsslw_option( 'wpssw_woo_customer_headers' ) ) + 1;
			$last_column                = WPSSLW_Settings::wpsslw_get_column_index( $total_headers );
			$wpsslw_existingsheetsnames = array();
			$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
			$wpsslw_existingsheetsnames = array_flip( $wpsslw_existingsheetsnames );
			$wpsslw_sheetname           = 'All Customers';
			if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheetsnames, true ) ) {
				try {
					$range    = $wpsslw_sheetname . '!A2:' . $last_column . '100000';
					$response = $wpsslw_service->spreadsheets_values->clear( $wpsslw_spreadsheetid, $range, $requestbody );
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die();
		}
	}
	new WPSSLW_Customer();
endif;
