<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

use WPSyncSheetsWooCommerce\WPSSLW_Google_API_Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSLW_Order' ) ) :
	/**
	 * Class WPSSLW_Order.
	 */
	class WPSSLW_Order extends WPSSLW_Settings {
		/**
		 * Instance of WPSSLW_Google_API_Functions
		 *
		 * @var $instance_api
		 */
		protected static $instance_api = null;
		/**
		 * Initialization
		 */
		public static function init() {
			$wpsslw_include = new WPSSLW_Include_Action();
			$wpsslw_include->wpsslw_include_orderfield_hook();
			$wpsslw_include->wpsslw_include_order_hook();
			$wpsslw_include->wpsslw_include_order_ajax_hook();
			self::wpsslw_google_api();
		}
		/**
		 * Create Google Api Instance.
		 */
		public static function wpsslw_google_api() {
			if ( null === self::$instance_api ) {
				self::$instance_api = new WPSSLW_Google_API_Functions();
			}
			return self::$instance_api;
		}
		/**
		 * Change order status
		 *
		 * @param int    $wpsslw_order_id .
		 * @param string $wpsslw_old_status .
		 * @param string $wpsslw_new_status .
		 */
		public static function wpsslw_woo_order_status_change_custom( $wpsslw_order_id, $wpsslw_old_status, $wpsslw_new_status ) {

			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) || ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) || ( isset( $_REQUEST['id'] ) && true === is_array( $_REQUEST['id'] ) && count( $_REQUEST['id'] ) > 1 ) ) {
				
				global $wpsslw_multiple_order_data;
				$is_id_values = false;
				if ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) {
					$changed_posts = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['ids'] ) ) );
				}else if( isset( $_REQUEST['id'] ) && true === is_array( $_REQUEST['id'] ) && !empty( $_REQUEST['id'] ) ){
					$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['id'] ) );
					$is_id_values = true;
				} else {
					$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				}
				
				if (false === $is_id_values && isset( $_REQUEST['action'] ) && ( 'trash' !== sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) && 'untrash' !== sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) ) {
					sort( $changed_posts );
				}
				
				// @codingStandardsIgnoreEnd.
				$changed_post_last_index = count( $changed_posts ) - 1;
				if ( true === $is_id_values ) {
					if ( (int) $wpsslw_order_id === (int) $changed_posts[ $changed_post_last_index ] ) {
						$wpsslw_multiple_order_data = array();
					}
				} else {
					if ( (int) $wpsslw_order_id === (int) $changed_posts[0] ) {
						$wpsslw_multiple_order_data = array();
					}
				}

				$wpsslw_multiple_order_data[ $wpsslw_order_id ] = array(
					'old_staus'  => $wpsslw_old_status,
					'new_status' => $wpsslw_new_status,
				);
				if ( true === $is_id_values ) {
					if ( (int) $wpsslw_order_id === (int) $changed_posts[0] ) {
						self::wpsslw_multiple_order_update( $wpsslw_multiple_order_data );
					}
				} elseif ( (int) $wpsslw_order_id === (int) $changed_posts[ $changed_post_last_index ] ) {
					self::wpsslw_multiple_order_update( $wpsslw_multiple_order_data );
				}
				return;
			} else {
				if ( isset( $GLOBALS['wpsslw_multiple_order_data'] ) ) {
					unset( $GLOBALS['wpsslw_multiple_order_data'] );
				}
			}

			$wpsslw_status_array  = wc_get_order_statuses();
			$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );

			$wpsslw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			if ( ! array_key_exists( $wpsslw_spreadsheetid, $wpsslw_spreadsheets_list ) ) {
				return;
			}
			$response                   = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );

			$wpsslw_sheets = array_filter( (array) parent::wpsslw_option( 'wpssw_sheets' ) );
			if ( ! $wpsslw_sheets ) {
				$wpsslw_sheets = self::wpsslw_prepare_sheets();
			}

			$wpsslw_old_staus_name           = '';
			$wpsslw_sheetid                  = '';
			$wpsslw_sheetname                = '';
			$wpsslw_status_array['wc-trash'] = 'Trash';
			foreach ( $wpsslw_status_array as $wpsslw_key => $wpsslw_val ) {
				$wpsslw_status      = substr( $wpsslw_key, strpos( $wpsslw_key, '-' ) + 1 );
				$wpsslw_orderstatus = str_replace( '-', ' ', $wpsslw_status );
				$wpsslw_orderstatus = ucwords( $wpsslw_orderstatus ) . ' Orders';
				if ( $wpsslw_status === $wpsslw_old_status ) {
					if ( array_key_exists( $wpsslw_orderstatus, $wpsslw_sheets ) && isset( $wpsslw_existingsheetsnames[ $wpsslw_sheets[ $wpsslw_orderstatus ] ] ) ) {
						$wpsslw_old_staus_name = $wpsslw_sheets[ $wpsslw_orderstatus ];
						$wpsslw_sheetid        = $wpsslw_existingsheetsnames[ $wpsslw_old_staus_name ];
					}
				}
				if ( $wpsslw_status === $wpsslw_new_status ) {
					if ( array_key_exists( $wpsslw_orderstatus, $wpsslw_sheets ) && isset( $wpsslw_existingsheetsnames[ $wpsslw_sheets[ $wpsslw_orderstatus ] ] ) ) {
						$wpsslw_sheetname = $wpsslw_sheets[ $wpsslw_orderstatus ];
					}
				}
			}
			if ( ! empty( $wpsslw_sheetname ) ) {
				if ( 'trash' === (string) $wpsslw_new_status && isset( $wpsslw_sheets['Trash Orders'] ) && $wpsslw_sheetname === $wpsslw_sheets['Trash Orders'] ) {
					self::wpsslw_insert_data_into_sheet( $wpsslw_order_id, $wpsslw_sheetname, 0, $wpsslw_old_staus_name, $wpsslw_new_status );
				} else {
					self::wpsslw_insert_data_into_sheet( $wpsslw_order_id, $wpsslw_sheetname, 0, $wpsslw_old_staus_name );
				}
			}

			if ( ! empty( $wpsslw_old_staus_name ) && ! empty( $wpsslw_sheetid ) ) {
				self::wpsslw_move_order( $wpsslw_order_id, $wpsslw_sheetid, $wpsslw_old_staus_name );
			}
		}
		/**
		 * Process Update of shop orders
		 *
		 * @param int    $wpsslw_post_id .
		 * @param object $wpsslw_post .
		 */
		public static function wpsslw_wc_woocommerce_process_post_meta( $wpsslw_post_id, $wpsslw_post ) {
			// @codingStandardsIgnoreStart.
			$wpsslw_order_status = isset( $_REQUEST['order_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order_status'] ) ) : '';
			$wpsslw_post_status  = isset( $_REQUEST['post_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ) ) : '';
			// @codingStandardsIgnoreEnd.
			if ( false === strpos( $wpsslw_post_status, 'wc-' ) && 0 === strpos( $wpsslw_order_status, 'wc-' ) ) {
				$wpsslw_post_status = 'wc-' . $wpsslw_post_status;
			}
			if ( $wpsslw_order_status !== $wpsslw_post_status ) {
				return;
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			self::wpsslw_wc_woocommerce_update_post_meta( $wpsslw_post_id, $wpsslw_post );

		}
		/**
		 * Update shop orders
		 *
		 * @param int    $wpsslw_post_id .
		 * @param object $wpsslw_post .
		 */
		public static function wpsslw_wc_woocommerce_update_post_meta( $wpsslw_post_id, $wpsslw_post ) {

			$parameter_type = '';
			if ( is_a( $wpsslw_post, 'WP_Post' ) ) {
				$parameter_type = 'post';
			} elseif ( is_a( $wpsslw_post, 'WC_Order' ) ) {
				$parameter_type = 'wc-order';
			} else {
				return;
			}
			if ( 'post' === $parameter_type && 'shop_order' !== (string) $wpsslw_post->post_type ) {
				return;
			}

			// @codingStandardsIgnoreStart.
			$wpsslw_order_status = isset( $_REQUEST['order_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order_status'] ) ) : '';
			$wpsslw_post_status  = isset( $_REQUEST['post_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ) ) : '';
			// @codingStandardsIgnoreEnd.

			if ( false === strpos( $wpsslw_post_status, 'wc-' ) && 0 === strpos( $wpsslw_order_status, 'wc-' ) ) {
				$wpsslw_post_status = 'wc-' . $wpsslw_post_status;
			}
			if ( $wpsslw_order_status !== $wpsslw_post_status ) {
				return;
			}

			$wpsslw_spreadsheetid     = parent::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpsslw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			if ( ! array_key_exists( $wpsslw_spreadsheetid, $wpsslw_spreadsheets_list ) ) {
				return;
			}
			if ( ! empty( $wpsslw_spreadsheetid ) ) {
				$wpsslw_order        = wc_get_order( $wpsslw_post_id );
				$wpsslw_items        = $wpsslw_order->get_items();
				$wpsslw_headers_name = parent::wpsslw_option( 'wpssw_sheet_headers_list' );
				$wpsslw_sheets       = array_filter( (array) parent::wpsslw_option( 'wpssw_sheets' ) );
				if ( ! $wpsslw_sheets ) {
					$wpsslw_sheets = self::wpsslw_prepare_sheets();
				}
				$wpsslw_header_type = self::wpsslw_is_productwise();
				$wpsslw_inputoption = parent::wpsslw_option( 'wpssw_inputoption' );
				if ( ! $wpsslw_inputoption ) {
					$wpsslw_inputoption = 'USER_ENTERED';
				}

				$wpsslw_values = self::wpsslw_make_value_array( 'update', $wpsslw_order->get_id() );
				do_action( 'wpsslw_update_order', $wpsslw_order->get_id(), $wpsslw_values, $wpsslw_order_status );
				$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
				$wpsslw_sheetname           = '';
				$wpsslw_status              = '';

				$wpsslw_status_array = wc_get_order_statuses();
				foreach ( $wpsslw_status_array as $wpsslw_key => $wpsslw_val ) {
					if ( (string) $wpsslw_key === $wpsslw_order_status ) {
						$wpsslw_status = substr( $wpsslw_key, strpos( $wpsslw_key, '-' ) + 1 );
						$wpsslw_status = str_replace( '-', ' ', $wpsslw_status );
						$wpsslw_status = ucwords( $wpsslw_status ) . ' Orders';
						if ( array_key_exists( $wpsslw_status, $wpsslw_sheets ) && isset( $wpsslw_existingsheetsnames[ $wpsslw_sheets[ $wpsslw_status ] ] ) ) {
							$wpsslw_sheetname = $wpsslw_sheets[ $wpsslw_status ];
							$wpsslw_sheetid   = $wpsslw_existingsheetsnames[ $wpsslw_sheetname ];
						}
					}
				}
				if ( ! empty( $wpsslw_sheetname ) ) {
					$wpsslw_rangetofind = $wpsslw_sheetname . '!A:A';
					$wpsslw_allentry    = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_rangetofind );
					$wpsslw_data        = $wpsslw_allentry->getValues();
					$wpsslw_data        = array_map(
						function( $element ) {
							if ( isset( $element['0'] ) ) {
								return $element['0'];
							} else {
								return '';
							}
						},
						$wpsslw_data
					);
					$wpsslw_num         = array_search( (int) $wpsslw_order->get_id(), parent::wpsslw_convert_int( $wpsslw_data ), true );
					if ( $wpsslw_num > 0 ) {
						$wpsslw_rownum = $wpsslw_num + 1;
						// Add or Remove Row at spreadsheet.
						$wpsslw_ordrow   = 0;
						$wpsslw_notempty = 0;
						end( $wpsslw_data );
						$wpsslw_lastelement = key( $wpsslw_data );
						reset( $wpsslw_data );
						$wpsslw_data_count = count( $wpsslw_data );
						for ( $i = $wpsslw_rownum; $i < $wpsslw_data_count; $i++ ) {
							if ( (int) $wpsslw_data[ $i ] === (int) $wpsslw_order->get_id() ) {
								$wpsslw_ordrow++;
								if ( (int) $wpsslw_lastelement === (int) $i ) {
									$wpsslw_ordrow++;
								}
							} else {
								if ( (int) $wpsslw_lastelement === (int) $i ) {
									$wpsslw_notempty = 1;
									if ( $wpsslw_ordrow > 0 ) {
										$wpsslw_ordrow++;
									}
								} else {
									$wpsslw_ordrow++;
								}
								break;
							}
						}
						$wpsslw_samerow = 0;
						if ( 0 === (int) $wpsslw_ordrow ) {
							$wpsslw_samerow = 1;
						}
						if ( 1 === (int) $wpsslw_samerow && $wpsslw_header_type && 0 === (int) $wpsslw_notempty ) {
							$wpsslw_alphabet   = range( 'A', 'Z' );
							$wpsslw_alphaindex = '';
							$wpsslw_is_id      = array_search( 'Product ID', $wpsslw_headers_name, true );
							if ( $wpsslw_is_id ) {
								$wpsslw_alphaindex = $wpsslw_alphabet[ $wpsslw_is_id + 1 ];
							} else {
								$wpsslw_is_name = array_search( 'Product Name', $wpsslw_headers_name, true );
								if ( $wpsslw_is_name ) {
									$wpsslw_alphaindex = $wpsslw_alphabet[ $wpsslw_is_name + 1 ];
								}
							}
							$wpsslw_alphaindex = 'A';
							if ( '' !== (string) $wpsslw_alphaindex ) {
								$wpsslw_rangetofind = $wpsslw_sheetname . '!' . $wpsslw_alphaindex . $wpsslw_rownum . ':' . $wpsslw_alphaindex;
								$wpsslw_allentry    = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_rangetofind );
								$wpsslw_data        = $wpsslw_allentry->getValues();

								$wpsslw_data = array_map(
									function( $wpsslw_element ) {
										if ( isset( $wpsslw_element['0'] ) ) {
											return $wpsslw_element['0'];
										} else {
											return '';
										}
									},
									$wpsslw_data
								);
								if ( ( count( $wpsslw_values ) < count( $wpsslw_data ) ) ) {
									$wpsslw_ordrow  = count( $wpsslw_data );
									$wpsslw_samerow = 0;
								}
							}
						}
						if ( 1 === (int) $wpsslw_notempty && 0 === (int) $wpsslw_ordrow ) {
							$wpsslw_samerow = 0;
							$wpsslw_ordrow  = 1;
						}
						if ( ( count( $wpsslw_values ) > (int) $wpsslw_ordrow ) && 0 === (int) $wpsslw_samerow ) {// Insert blank row into spreadsheet.
							$wpsslw_endindex                = count( $wpsslw_values ) - (int) $wpsslw_ordrow;
							$wpsslw_endindex                = (int) $wpsslw_endindex + (int) $wpsslw_rownum;
							$param                          = array();
							$param                          = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_rownum, $wpsslw_endindex );
							$wpsslw_batchupdaterequest      = self::$instance_api->insertdimensionobject( $param );
							$requestobject                  = array();
							$requestobject['spreadsheetid'] = $wpsslw_spreadsheetid;
							$requestobject['requestbody']   = $wpsslw_batchupdaterequest;
							$wpsslw_response                = self::$instance_api->formatsheet( $requestobject );
						} elseif ( count( $wpsslw_values ) < (int) $wpsslw_ordrow && 0 === (int) $wpsslw_samerow ) {// Remove extra row from spreadhseet.
							$wpsslw_endindex        = (int) $wpsslw_ordrow - count( $wpsslw_values );
							$wpsslw_endindex        = (int) $wpsslw_endindex + (int) $wpsslw_rownum;
							$param                  = array();
							$param                  = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_rownum, $wpsslw_endindex );
							$deleterequest          = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
							$param                  = array();
							$param['spreadsheetid'] = $wpsslw_spreadsheetid;
							$param['requestarray']  = $deleterequest;
							$wpsslw_response        = self::$instance_api->updatebachrequests( $param );
						}
						// End of add- remove row at spreadsheet.
						$wpsslw_rangetoupdate = $wpsslw_sheetname . '!A' . $wpsslw_rownum;
						$wpsslw_requestbody   = self::$instance_api->valuerangeobject( $wpsslw_values );
						$wpsslw_params        = array( 'valueInputOption' => $wpsslw_inputoption ); // USER_ENTERED.
						$param                = array();
						$param                = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_rangetoupdate, $wpsslw_requestbody, $wpsslw_params );
						$wpsslw_response      = self::$instance_api->updateentry( $param );
					} else {
						self::wpsslw_insert_data_into_sheet( (int) $wpsslw_order->get_id(), $wpsslw_sheetname, 0 );
					}
				}
			}
		}
		/**
		 * Insert Order data into sheet provided by $wpsslw_sheetname.
		 *
		 * @param int    $wpsslw_order_id .
		 * @param string $wpsslw_sheetname .
		 * @param int    $wpsslw_flag .
		 * @param string $wpsslw_old_staus_name .
		 * @param string $wpsslw_new_staus_name .
		 */
		public static function wpsslw_insert_data_into_sheet( $wpsslw_order_id, $wpsslw_sheetname, $wpsslw_flag = 0, $wpsslw_old_staus_name = '', $wpsslw_new_staus_name = '' ) {

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );
			if ( ! parent::wpsslw_check_sheet_exist( $wpsslw_spreadsheetid, $wpsslw_sheetname ) ) {
				return;
			}
			$wpsslw_inputoption = parent::wpsslw_option( 'wpssw_inputoption' );
			if ( ! $wpsslw_inputoption ) {
				$wpsslw_inputoption = 'USER_ENTERED';
			}
			$wpsslw_order        = wc_get_order( $wpsslw_order_id );
			$wpsslw_headers_name = parent::wpsslw_option( 'wpssw_sheet_headers_list' );

			$wpsslw_header_type = self::wpsslw_is_productwise();
			$order_ascdesc      = parent::wpsslw_option( 'wpssw_order_ascdesc' );
			if ( ! empty( $wpsslw_spreadsheetid ) ) {
				$wpsslw_prdarray = self::wpsslw_make_value_array( 'insert', $wpsslw_order_id, $wpsslw_sheetname );
				$wpsslw_sheets   = array_filter( (array) parent::wpsslw_option( 'wpssw_sheets' ) );
				if ( 'trash' === (string) $wpsslw_new_staus_name && isset( $wpsslw_sheets['Trash Orders'] ) && $wpsslw_sheetname === $wpsslw_sheets['Trash Orders'] ) {
					$wpsslw_woo_selections = $wpsslw_headers_name;
					array_unshift( $wpsslw_woo_selections, 'Order Id' );
					$wpsslw_order_status_key = array_search( 'Order Status', $wpsslw_woo_selections, true );
					if ( false !== $wpsslw_order_status_key ) {
						if ( $wpsslw_header_type && 'yes' === (string) parent::wpsslw_option( 'wpssw_repeat_checkbox' ) ) {
							foreach ( $wpsslw_prdarray as &$prdarray ) {
								$prdarray[ $wpsslw_order_status_key ] = 'Trash';
							}
						} else {
							$wpsslw_prdarray[0][ $wpsslw_order_status_key ] = 'Trash';
						}
					}
				}

				do_action( 'wpsslw_insert_new_order', $wpsslw_order_id, $wpsslw_prdarray, $wpsslw_sheetname );
				if ( 1 === (int) $wpsslw_flag ) {
					return $wpsslw_prdarray;
				}
				if ( 0 === (int) $wpsslw_flag ) {
					$wpsslw_values              = $wpsslw_prdarray;
					$wpsslw_sheet               = "'" . $wpsslw_sheetname . "'!A:A";
					$wpsslw_allentry            = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheet );
					$wpsslw_data                = $wpsslw_allentry->getValues();
					$wpsslw_data                = array_map(
						function( $wpsslw_element ) {
							if ( isset( $wpsslw_element['0'] ) && is_numeric( $wpsslw_element['0'] ) ) {
								return $wpsslw_element['0'];
							} else {
								return '';
							}
						},
						$wpsslw_data
					);
					$response                   = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
					$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
					$wpsslw_sheetid             = $wpsslw_existingsheetsnames[ $wpsslw_sheetname ];
					$wpsslw_append              = 0;
					$wpsslw_num                 = array_search( (int) $wpsslw_order_id, parent::wpsslw_convert_int( $wpsslw_data ), true );

					if ( $wpsslw_num < 1 ) {
						$highest_order_id = max( $wpsslw_data );

						if ( ( $highest_order_id ) && $wpsslw_order_id < $highest_order_id ) {
							foreach ( $wpsslw_data as $wpsslw_key => $wpsslw_value ) {
								if ( ! empty( $wpsslw_value ) ) {

									if ( ( (int) $wpsslw_order_id < (int) $wpsslw_value ) && 'descorder' !== (string) $order_ascdesc && $wpsslw_num < 1 ) {
										$wpsslw_append                  = 1;
										$wpsslw_startindex              = $wpsslw_key;
										$wpsslw_endindex                = $wpsslw_key + 1;
										$param                          = array();
										$param                          = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
										$wpsslw_batchupdaterequest      = self::$instance_api->insertdimensionobject( $param );
										$requestobject                  = array();
										$requestobject['spreadsheetid'] = $wpsslw_spreadsheetid;
										$requestobject['requestbody']   = $wpsslw_batchupdaterequest;
										$wpsslw_response                = self::$instance_api->formatsheet( $requestobject );
										$wpsslw_start_index             = $wpsslw_startindex + 1;
										$wpsslw_rangetoupdate           = $wpsslw_sheetname . '!A' . $wpsslw_start_index;
										$wpsslw_requestbody             = self::$instance_api->valuerangeobject( $wpsslw_values );
										$wpsslw_params                  = array( 'valueInputOption' => $wpsslw_inputoption );
										$param                          = array();
										$param                          = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_rangetoupdate, $wpsslw_requestbody, $wpsslw_params );

										$wpsslw_response = self::$instance_api->updateentry( $param );
										break;
									}
								}
							}
						}
					}

					if ( 0 === (int) $wpsslw_append ) {
						$wpsslw_isupdated   = 0;
						$wpsslw_requestbody = self::$instance_api->valuerangeobject( $wpsslw_values );
						$wpsslw_params      = array( 'valueInputOption' => $wpsslw_inputoption );
						if ( count( $wpsslw_data ) > 1 ) {
							$wpsslw_num = array_search( (int) $wpsslw_order_id, parent::wpsslw_convert_int( $wpsslw_data ), true );
							if ( $wpsslw_num > 0 ) {
								$wpsslw_rangetoupdate = $wpsslw_sheetname . '!A' . ( $wpsslw_num + 1 );
							} else {
								$wpsslw_rangetoupdate = $wpsslw_sheetname . '!A' . ( count( $wpsslw_data ) + 1 );
							}
							$param            = array();
							$param            = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_rangetoupdate, $wpsslw_requestbody, $wpsslw_params );
							$wpsslw_response  = self::$instance_api->updateentry( $param );
							$wpsslw_isupdated = 1;
						}
						if ( 0 === (int) $wpsslw_isupdated ) {
							$param           = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_sheetname, $wpsslw_requestbody, $wpsslw_params );
							$wpsslw_response = self::$instance_api->appendentry( $param );
						}
					}
				}
			}
		}
		/**
		 * Equal value array.
		 *
		 * @param array $temp Value array.
		 * @param array $wpsslw_items Item array.
		 *
		 * @return array
		 */
		public static function wpsslw_make_equal( $temp, $wpsslw_items ) {
			$wpsslw_is_repeat = parent::wpsslw_option( 'wpssw_repeat_checkbox' );
			$wpsslw_new_array = array();
			$value            = self::wpsslw_array_flatten( $temp );
			if ( 'yes' === (string) $wpsslw_is_repeat ) {
				for ( $i = 0; $i < $wpsslw_items; $i++ ) {
					$wpsslw_new_array[] = isset( $value[0] ) ? array( $value[0] ) : array();
				}
			} else {
				for ( $i = 0; $i < $wpsslw_items; $i++ ) {
					$wpsslw_new_array[] = isset( $value[ $i ] ) ? array( $value[ $i ] ) : array( 0 => '' );
				}
			}
			return $wpsslw_new_array;
		}
		/**
		 * Mearge arrays
		 *
		 * @param array $array1 .
		 * @param array $array2 .
		 *
		 * @return array
		 */
		public static function wpsslw_array_merge_recursive_distinct( &$array1, &$array2 ) {
			$merged = $array1;
			foreach ( $array2 as $key => &$value ) {
				if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
					$merged[ $key ] = array_merge( $merged[ $key ], $value );
				} else {
					$merged[ $key ] = $value;
				}
			}
			return $merged;
		}
		/**
		 * Prepare array value of order data to insert into sheet.
		 *
		 * @param string $wpsslw_operation operation to perfom on sheet.
		 * @param int    $wpsslw_order_id .
		 * @param string $wpsslw_sheet_name .
		 */
		public static function wpsslw_make_value_array( $wpsslw_operation = 'insert', $wpsslw_order_id = 0, $wpsslw_sheet_name = '' ) {
			$wpsslw_order       = wc_get_order( $wpsslw_order_id );
			$wpsslw_order_data  = $wpsslw_order->get_data();
			$wpsslw_inputoption = parent::wpsslw_option( 'wpssw_inputoption' );
			if ( ! $wpsslw_inputoption ) {
				$wpsslw_inputoption = 'USER_ENTERED';
			}
			$wpsslw_filterd = array();
			$wpsslw_include = new WPSSLW_Include_Action();
			$wpsslw_include->wpsslw_include_order_compatibility_files();
			$wpsslw_header_type = self::wpsslw_is_productwise();
			$wpsslw_headers     = apply_filters( 'wpsyncsheets_order_headers', array() );

			$wpsslw_headers_name = stripslashes_deep( parent::wpsslw_option( 'wpssw_sheet_headers_list' ) );
			$wpsslw_items        = $wpsslw_order->get_items();
			$wpsslw_temp_headers = array();
			$wpsslw_value        = array();
			$wpsslw_prdarray     = array();
			$wpsslw_value[0]     = $wpsslw_order_id;
			$wpsslw_headers_name = stripslashes_deep( parent::wpsslw_option( 'wpssw_sheet_headers_list' ) );

			$wpsslw_headers['WPSSLW_Default'] = parent::wpsslw_array_flatten( $wpsslw_headers['WPSSLW_Default'] );
			$wpsslw_classarray                = array();
			$wpsslw_headers_name_count        = count( $wpsslw_headers_name );
			for ( $i = 0; $i < $wpsslw_headers_name_count; $i++ ) {
				$wpsslw_classarray[ $wpsslw_headers_name[ $i ] ] = parent::wpsslw_find_class( $wpsslw_headers, $wpsslw_headers_name[ $i ] );
			}
			$wpsslw_items = $wpsslw_order->get_items();

			$wpsslw_order_row = array();
			$wpsslw_temp      = array();

			if ( $wpsslw_header_type ) {
				$wpsslw_rcount = 0;
				foreach ( $wpsslw_items as $wpsslw_item ) {
					$wpsslw_order_row[ $wpsslw_rcount ][] = $wpsslw_order_id;
					$wpsslw_temp[ $wpsslw_rcount ][]      = '';
					$wpsslw_rcount++;
				}
			}
			if ( ! $wpsslw_header_type ) {
				$wpsslw_order_row   = array();
				$wpsslw_order_row[] = $wpsslw_order_id;
			}

			foreach ( $wpsslw_classarray as $headername => $classname ) {
				$temp = array();
				if ( ! empty( $classname ) ) {
					$header_value = $classname::get_value( $headername, $wpsslw_order, 'insert' );

					if ( is_array( $header_value ) ) {
						$temp = array_chunk( $header_value, 1 );
					}
					if ( empty( $temp ) ) {
						$temp = $wpsslw_temp;
					}
				} else {
					$temp = $wpsslw_temp;
				}
				if ( $wpsslw_header_type ) {

					if ( count( $temp ) !== count( $wpsslw_items ) ) {
						$temp = self::wpsslw_make_equal( $temp, count( $wpsslw_items ) );
					}

					$wpsslw_order_row = self::wpsslw_array_merge_recursive_distinct( $wpsslw_order_row, $temp );
				} else {
					$wpsslw_val = parent::wpsslw_array_flatten( $temp );
					if ( ! empty( $wpsslw_val ) && ! empty( array_filter( $wpsslw_val ) ) ) {
						$wpsslw_order_row[] = implode( ', ', array_filter( $wpsslw_val ) );
					} else {
						$wpsslw_order_row[] = '';
					}
				}
			}
			if ( $wpsslw_header_type ) {

				if ( ! empty( $items_to_keep ) && ! empty( $wpsslw_order_row ) ) {
					$wpsslw_order_row = array_values( array_intersect_key( $wpsslw_order_row, array_flip( $items_to_keep ) ) );
				}
				foreach ( $wpsslw_order_row as $wpsslw_arrykey => $wpsslw_valarray ) {
					$wpsslw_order_row[ $wpsslw_arrykey ] = self::wpsslw_order_clean_array( $wpsslw_valarray );
				}
			} else {
				$wpsslw_order_row = self::wpsslw_order_clean_array( $wpsslw_order_row );
				$wpsslw_order_row = array( $wpsslw_order_row );
			}
			$wpsslw_order_row = apply_filters( 'woosheets_values', $wpsslw_order_row );

			return $wpsslw_order_row;
		}
		/**
		 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
		 *
		 * @return array Array of settings for @see woocommerce_admin_fields() function.
		 */
		public static function wpsslw_get_settings() {

			$wpsslw_status_array              = wc_get_order_statuses();
			$wpsslw_order_spreadsheet_setting = self::wpsslw_check_order_spreadsheet_setting();
			$wpsslw_settings                  = array(
				'section_first_title'  => array(
					'name' => '',
					'type' => 'title',
					'desc' => '',
					'id'   => 'wc_google_sheet_settings_first_section_start',
				),
				array( 'type' => 'select_spreadsheet' ),
				'section_first_end'    => array(
					'type' => 'sectionend',
					'id'   => 'wc_google_sheet_settings_first_section_end',
				),
				'section_second_title' => array(
					'name' => '',
					'type' => 'title',
					'desc' => '',
					'id'   => 'wc_google_sheet_settings_second_section_start',
				),
				array( 'type' => 'set_sheets' ),
				'section_second_end'   => array(
					'type' => 'sectionend',
					'id'   => 'wc_google_sheet_settings_second_section_end',
				),
				'section_third_title'  => array(
					'name' => '',
					'type' => 'title',
					'desc' => '',
					'id'   => 'wc_google_sheet_settings_third_section_start',
				),
				array( 'type' => 'manage_row_field' ),
				array( 'type' => 'set_headers' ),
				'section_third_end'    => array(
					'type' => 'sectionend',
					'id'   => 'wc_google_sheet_settings_third_section_end',
				),
				'section_fourth_title' => array(
					'name'  => '',
					'type'  => 'title',
					'desc'  => '',
					'class' => 'section_fourth_end',
					'id'    => 'wc_google_sheet_settings_fourth_section_start',
				),
				array( 'type' => 'sync_button' ),
				'section_fourth_end'   => array(
					'type' => 'sectionend',
					'id'   => 'wc_google_sheet_settings_fourth_section_end',
				),
			);

			$wpsslw_custom_status_array = array();
			$wpsslw_settingflag         = 0;
			foreach ( $wpsslw_status_array as $wpsslw_key => $wpsslw_val ) {
				$wpsslw_status = substr( $wpsslw_key, strpos( $wpsslw_key, '-' ) + 1 );
				if ( ! in_array( $wpsslw_status, self::$wpsslw_default_status, true ) ) {
					$wpsslw_settingflag++;
					if ( 1 === (int) $wpsslw_settingflag ) {
						$wpsslw_custom_status_array['section_fifth_title'] = array(
							'name' => '',
							'type' => 'title',
							'desc' => '',
							'id'   => 'wc_google_sheet_settings_second_section_start',
						);
					}
					$wpsslw_custom_status_array['set_custom_sheets'] = array( 'type' => 'set_custom_sheets' );
				}
			}
			if ( $wpsslw_settingflag > 0 ) {
				$wpsslw_custom_status_array['section_fifth_end'] = array(
					'type' => 'sectionend',
					'id'   => 'wc_google_sheet_settings_second_section_end',
				);
			}
			if ( ! empty( $wpsslw_custom_status_array ) ) {
				$wpsslw_settings = array_slice( $wpsslw_settings, 0, 7, true ) + $wpsslw_custom_status_array + array_slice( $wpsslw_settings, 7, count( $wpsslw_settings ) - 1, true );
			}
			return $wpsslw_settings;
		}
		/**
		 * Clear General settings sheets
		 */
		public static function wpsslw_clear_all_sheet() {
			if ( ! isset( $_POST['wpsslw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpsslw_general_settings'] ) ), 'save_general_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpsslw_spreadsheetid     = parent::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpsslw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			if ( ! array_key_exists( $wpsslw_spreadsheetid, $wpsslw_spreadsheets_list ) ) {
				die;
			}
			$requestbody                     = self::$instance_api->clearobject();
			$total_headers                   = count( parent::wpsslw_option( 'wpssw_sheet_headers_list' ) ) + 1;
			$last_column                     = parent::wpsslw_get_column_index( $total_headers );
			$wpsslw_order_status_array       = self::$wpsslw_default_status_slug;
			$wpsslw_status_array             = wc_get_order_statuses();
			$wpsslw_status_array['wc-trash'] = 'Trash';
			$wpsslw_existingsheetsnames      = array();
			$wpsslw_response                 = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheetsnames      = self::$instance_api->get_sheet_list( $wpsslw_response );
			$wpsslw_order_status_array_count = count( $wpsslw_order_status_array );
			$wpsslw_sheets                   = array_filter( (array) parent::wpsslw_option( 'wpssw_sheets' ) );
			if ( ! $wpsslw_sheets ) {
				$wpsslw_sheets = self::wpsslw_prepare_sheets();
			}
			$wpsslw_sheetnames = array();
			foreach ( $wpsslw_status_array as $wpsslw_key => $wpsslw_val ) {
				$wpsslw_status      = substr( $wpsslw_key, strpos( $wpsslw_key, '-' ) + 1 );
				$wpsslw_orderstatus = str_replace( '-', ' ', $wpsslw_status );
				$wpsslw_orderstatus = ucwords( $wpsslw_orderstatus ) . ' Orders';
				if ( array_key_exists( $wpsslw_orderstatus, $wpsslw_sheets ) && isset( $wpsslw_existingsheetsnames[ $wpsslw_sheets[ $wpsslw_orderstatus ] ] ) ) {
					$wpsslw_sheetnames[] = $wpsslw_sheets[ $wpsslw_orderstatus ];
				}
			}
			foreach ( $wpsslw_sheetnames as $wpsslw_sheetname ) {
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
		 * Check for existing spreadsheet
		 */
		public static function wpsslw_check_existing_sheet() {

			if ( ! isset( $_POST['sync_nonce_token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sync_nonce_token'] ) ), 'sync_nonce' )
			) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpsslw_sheetnames = array_filter( (array) parent::wpsslw_option( 'wpssw_sheets' ) );
			if ( ! $wpsslw_sheetnames ) {
				$wpsslw_sheetnames = self::wpsslw_prepare_sheets();
			}
			if ( ! isset( $_POST['id'] ) ) {
				echo esc_html__( 'Spreadsheet id not found.', 'wpssw' );
				die();
			}
			$wpsslw_spreadsheetid = sanitize_text_field( wp_unslash( $_POST['id'] ) );
			if ( 'new' !== (string) $wpsslw_spreadsheetid && 0 !== (int) $wpsslw_spreadsheetid ) {
				$wpsslw_exist               = 0;
				$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
				$wpsslw_existingsheetsnames = array_flip( $wpsslw_existingsheetsnames );
				foreach ( $wpsslw_existingsheetsnames as $sheetname ) {
					if ( in_array( $sheetname, $wpsslw_sheetnames, true ) ) {
						$wpsslw_exist = 1;
						break;
					}
				}
				if ( $wpsslw_exist ) {
					echo 'successful';
					die();
				}
			}
			die();
		}
		/**
		 * Check which header type is selected productwise or orderwise.
		 */
		public static function wpsslw_is_productwise() {
			$wpsslw_header_type = parent::wpsslw_option( 'wpssw_header_format' );
			if ( 'productwise' === (string) $wpsslw_header_type ) {
				return true;
			}
			return false;
		}
		/**
		 * Get order items meta
		 *
		 * @param object $wpsslw_item .
		 */
		public static function wpsslw_getItemmeta( $wpsslw_item = '' ) {
			$wpsslw_meta_html = '';
			if ( ! empty( $wpsslw_item ) ) {
				$wpsslw_variationame = '';
				if ( $wpsslw_item->get_variation_id() ) {
					$wpsslw_variation    = wc_get_product( $wpsslw_item->get_variation_id() );
					$wpsslw_variationame = wp_strip_all_tags( $wpsslw_variation->get_formatted_name() );
				}
				$wpsslw_meta_html .= 'Variation Name: ' . $wpsslw_variationame . '(' . $wpsslw_item->get_variation_id() . ') ,'; // the Variation id.
				if ( $wpsslw_item->get_tax_class() ) {
					$wpsslw_meta_html .= 'Tax Class:' . $wpsslw_item->get_tax_class() . ',';
				}
				$wpsslw_meta_html .= 'Line subtotal:' . $wpsslw_item->get_subtotal() . ','; // Line subtotal (non discounted).
				if ( $wpsslw_item->get_subtotal_tax() ) {
					$wpsslw_meta_html .= 'Line subtotal tax:' . $wpsslw_item->get_subtotal_tax() . ','; // Line subtotal tax (non discounted).
				}
				$wpsslw_meta_html .= 'Line total:' . $wpsslw_item->get_total() . ','; // Line total (discounted).
				if ( $wpsslw_item->get_total_tax() ) {
					$wpsslw_meta_html .= 'Line total tax:' . $wpsslw_item->get_total_tax(); // Line total tax (discounted).
				}
			}
			$wpsslw_meta_html = rtrim( $wpsslw_meta_html, ',' );
			return $wpsslw_meta_html;
		}
		/**
		 * Clean Order data array.
		 *
		 * @param array $wpsslw_array Order data array.
		 * @return array $wpsslw_array
		 */
		public static function wpsslw_order_clean_array( $wpsslw_array ) {
			$wpsslw_max   = count( parent::wpsslw_option( 'wpssw_sheet_headers_list' ) ) + 1;
			$wpsslw_array = parent::wpsslw_cleanarray( $wpsslw_array, $wpsslw_max );
			return $wpsslw_array;
		}
		/**
		 * Format the price values on selecting Price Format option
		 *
		 * @param int|float $price .
		 */
		public static function wpsslw_get_formatted_values( $price ) {
			$wpsslw_price_format = parent::wpsslw_option( 'wpssw_price_format' );
			if ( ! $wpsslw_price_format ) {
				$wpsslw_price_format = 'plain';
			}
			$wpsslw_plain     = '';
			$wpsslw_formatted = '';
			if ( 'plain' === (string) $wpsslw_price_format || (int) $price < 1 ) {
				return $price;
			}
			$priceargs         = wp_parse_args(
				array(),
				array(
					'ex_tax_label'       => false,
					'currency'           => '',
					'decimal_separator'  => wc_get_price_decimal_separator(),
					'thousand_separator' => wc_get_price_thousand_separator(),
					'decimals'           => wc_get_price_decimals(),
					'price_format'       => get_woocommerce_price_format(),
				)
			);
			$unformatted_price = $price;
			$negative          = $price < 0;
			$price             = floatval( $negative ? (int) $price * -1 : $price );
			$price             = number_format( $price, $priceargs['decimals'], $priceargs['decimal_separator'], $priceargs['thousand_separator'] );
			$formatted_price   = ( $negative ? '-' : '' ) . sprintf( $priceargs['price_format'], get_woocommerce_currency_symbol( $priceargs['currency'] ), $price );
			return html_entity_decode( $formatted_price );
		}
		/**
		 * Restore a post from the Trash
		 *
		 * @param string $wpsslw_new_status New status of post.
		 * @param string $wpsslw_old_status Old status of post.
		 * @param object $wpsslw_post Post to restore.
		 */
		public static function wpsslw_wcgs_restore( $wpsslw_new_status, $wpsslw_old_status, $wpsslw_post ) {
			global $post_type;
			$wpsslw_post_type = is_object( $post_type ) ? $post_type->name : $post_type;
			// @codingStandardsIgnoreStart.
			if ( ( 'shop_order' !== (string) $wpsslw_post_type ) || ( isset( $_REQUEST['action'] ) && 'untrash' !== sanitize_text_field( wp_unslash($_REQUEST['action'] ) ) ) ) {
				return;
			}
			$wpsslw_order = wc_get_order( $wpsslw_post->ID );
			if ( isset( $wpsslw_order ) && ! empty( $wpsslw_order ) ) {
				$wpsslw_sheetname = substr( $wpsslw_new_status, strpos( $wpsslw_new_status, '-' ) + 1 );
				if ( 'trash' === (string) $wpsslw_old_status ) {
					self::wpsslw_woo_order_status_change_custom( $wpsslw_post->ID, 'trash', $wpsslw_sheetname );
				}
			}
		}
		/**
		 *
		 * Move a order to the Trash
		 *
		 * @param int $wpsslw_order_id .
		 */
		public static function wpsslw_wcgs_trash_order( $wpsslw_order_id ) {
			$wpsslw_order = wc_get_order( $wpsslw_order_id );
			
			if ( ! is_wp_error( $wpsslw_order ) && ! empty( $wpsslw_order ) ) {

				$wpsslw_sheets = array_filter( (array) self::wpsslw_option( 'wpssw_sheets' ) );
				if ( ! $wpsslw_sheets ) {
					$wpsslw_sheets = self::wpsslw_prepare_sheets();
				}
				if ( $wpsslw_order ) {
					$wpsslw_old_status = trim( $wpsslw_order->get_meta( '_wp_trash_meta_status', true ) );
					if ( 0 === strpos( $wpsslw_old_status, 'wc-' ) ) {
						$wpsslw_old_status = str_replace( 'wc-', '', $wpsslw_old_status );
					}
					/*Remove order detail from old status and move to Trash sheet*/
					self::wpsslw_woo_order_status_change_custom( $wpsslw_order_id, $wpsslw_old_status, 'trash' );
				}
			}
		}
		/**
		 * Move (Delete) order data from sheet provided by $wpsslw_sheetname.
		 *
		 * @param int    $wpsslw_order_id .
		 * @param string $wpsslw_sheetid .
		 * @param string $wpsslw_sheetname .
		 */
		public static function wpsslw_move_order( $wpsslw_order_id, $wpsslw_sheetid, $wpsslw_sheetname ) {

			$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );
			if ( ! parent::wpsslw_check_sheet_exist( $wpsslw_spreadsheetid, $wpsslw_sheetname ) ) {
				return;
			}
			$wpsslw_rangetofind = $wpsslw_sheetname . '!A:A';
			$wpsslw_allentry    = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_rangetofind );
			$wpsslw_data        = $wpsslw_allentry->getValues();
			do_action( 'wpsslw_move_order', $wpsslw_order_id, $wpsslw_sheetname );
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
			$wpsslw_order      = wc_get_order( $wpsslw_order_id );
			$wpsslw_item_count = $wpsslw_order->get_items();
			$wpsslw_num        = array_search( (int) $wpsslw_order_id, parent::wpsslw_convert_int( $wpsslw_data ), true );
			if ( $wpsslw_num > 0 ) {
				$wpsslw_startindex  = $wpsslw_num;
				$wpsslw_header_type = self::wpsslw_is_productwise();
				if ( $wpsslw_header_type ) {
					$wpsslw_endindex = count( $wpsslw_item_count );
					$wpsslw_endindex = $wpsslw_num + $wpsslw_endindex;
				} else {
					$wpsslw_endindex = $wpsslw_num + 1;
				}
				$param                  = array();
				$param                  = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
				$wpsslw_requestbody     = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				$param                  = array();
				$param['spreadsheetid'] = $wpsslw_spreadsheetid;
				$param['requestarray']  = $wpsslw_requestbody;
				$wpsslw_response        = self::$instance_api->updatebachrequests( $param );
			}
		}
		
		/**
		 * Prepare sheetnames array
		 *
		 * @return array $wpsslw_sheets_selected
		 */
		public static function wpsslw_prepare_sheets() {
			$wpsslw_sheets_selected = array();
			$wpsslw_sheets_selected = array_filter( (array) parent::wpsslw_option( 'wpssw_sheets' ) );
			$wpsslw_status_array    = wc_get_order_statuses();

			if ( ! $wpsslw_sheets_selected ) {
				if ( 'yes' === (string) parent::wpsslw_option( 'pending_orders' ) ) {
					$wpsslw_sheets_selected['Pending Orders'] = 'Pending Orders';
				}
				if ( 'yes' === (string) parent::wpsslw_option( 'processing_orders' ) ) {
					$wpsslw_sheets_selected['Processing Orders'] = 'Processing Orders';
				}
				if ( 'yes' === (string) parent::wpsslw_option( 'on_hold_orders' ) ) {
					$wpsslw_sheets_selected['On Hold Orders'] = 'On Hold Orders';
				}
				if ( 'yes' === (string) parent::wpsslw_option( 'completed_orders' ) ) {
					$wpsslw_sheets_selected['Completed Orders'] = 'Completed Orders';
				}
				if ( 'yes' === (string) parent::wpsslw_option( 'cancelled_orders' ) ) {
					$wpsslw_sheets_selected['Cancelled Orders'] = 'Cancelled Orders';
				}
				if ( 'yes' === (string) parent::wpsslw_option( 'refunded_orders' ) ) {
					$wpsslw_sheets_selected['Refunded Orders'] = 'Refunded Orders';
				}
				if ( 'yes' === (string) parent::wpsslw_option( 'failed_orders' ) ) {
					$wpsslw_sheets_selected['Failed Orders'] = 'Failed Orders';
				}
				if ( 'yes' === (string) parent::wpsslw_option( 'trash' ) ) {
					$wpsslw_sheets_selected['Trash Orders'] = 'Trash Orders';
				}
				parent::wpsslw_update_option( 'wpssw_sheets', $wpsslw_sheets_selected );
			}
			return $wpsslw_sheets_selected;
		}
		/**
		 * Update shet on multiple orders update.
		 *
		 * @param array $wpsslw_multiple_order_data updated orders data.
		 */
		public static function wpsslw_multiple_order_update( $wpsslw_multiple_order_data ) {

			if ( empty( $wpsslw_multiple_order_data ) ) {
				return;
			}

			$wpsslw_status_array  = wc_get_order_statuses();
			$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );

			$wpsslw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			if ( ! array_key_exists( $wpsslw_spreadsheetid, $wpsslw_spreadsheets_list ) ) {
				return;
			}
			$response                   = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
			$wpsslw_existingsheetsnames = array_flip( $wpsslw_existingsheetsnames );

			$wpsslw_sheets = array_filter( (array) parent::wpsslw_option( 'wpssw_sheets' ) );
			if ( ! $wpsslw_sheets ) {
				$wpsslw_sheets = self::wpsslw_prepare_sheets();
			}

			$wpsslw_status_array             = wc_get_order_statuses();
			$wpsslw_status_array['wc-trash'] = 'Trash';

			$wpsslw_getactivesheets = array();
			$wpsslw_activesheets    = array();
			$trashed_orders_sheet   = array();
			foreach ( $wpsslw_status_array as $wpsslw_key => $wpsslw_val ) {
				$wpsslw_status      = substr( $wpsslw_key, strpos( $wpsslw_key, '-' ) + 1 );
				$wpsslw_orderstatus = str_replace( '-', ' ', $wpsslw_status );
				$wpsslw_orderstatus = ucwords( $wpsslw_orderstatus ) . ' Orders';
				if ( array_key_exists( $wpsslw_orderstatus, $wpsslw_sheets ) && in_array( $wpsslw_sheets[ $wpsslw_orderstatus ], $wpsslw_existingsheetsnames, true ) ) {
					if ( 'wc-trash' === (string) $wpsslw_key ) {
						$wpsslw_activesheets['trash']                = $wpsslw_orderstatus;
						$trashed_orders_sheet[ $wpsslw_orderstatus ] = $wpsslw_sheets[ $wpsslw_orderstatus ];
					} else {
						$wpsslw_activesheets[ $wpsslw_key ] = $wpsslw_orderstatus;
					}
					$wpsslw_getactivesheets[] = "'" . $wpsslw_sheets[ $wpsslw_orderstatus ] . "'!A:A";
				}
			}

			if ( ! $trashed_orders_sheet ) {
				$trashed_orders_sheet = array();
			}

			$wpsslw_additionalsheetnames = array();

			/*Get First Column Value from all sheets*/
			try {
				$param                  = array();
				$param['spreadsheetid'] = $wpsslw_spreadsheetid;
				$param['ranges']        = array( 'ranges' => $wpsslw_getactivesheets );
				$wpsslw_response        = self::$instance_api->getbatchvalues( $param );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
			$wpsslw_existingorders = array();
			foreach ( $wpsslw_response->getValueRanges() as $wpsslw_response_data ) {
				if ( strpos( $wpsslw_response_data->range, "'!A" ) ) {
					$wpsslw_rangetitle = explode( "'!A", $wpsslw_response_data->range );
				} else {
					$wpsslw_rangetitle = explode( '!A', $wpsslw_response_data->range );
				}
				$wpsslw_sheettitle = str_replace( "'", '', $wpsslw_rangetitle[0] );
				$wpsslw_data       = array_map(
					function( $wpsslw_element ) {
						if ( isset( $wpsslw_element['0'] ) ) {
							return (int) $wpsslw_element['0'];
						} else {
							return '';
						}
					},
					$wpsslw_response_data->values
				);

				$wpsslw_existingorders[ $wpsslw_sheettitle ] = $wpsslw_data;
				$wpsslw_data                                 = null;
			}
			$wpsslw_response = null;

			$wpsslw_header_type = self::wpsslw_is_productwise();
			$order_ascdesc      = parent::wpsslw_option( 'wpssw_order_ascdesc' );
			$repeat_checkbox    = parent::wpsslw_option( 'wpssw_repeat_checkbox' );
			$delete_row_indexes = array();
			$deleterequestarray = array();
			foreach ( $wpsslw_multiple_order_data as $order_id => $order_data ) {
				if ( 'new' === $order_data['old_staus'] ) {
					continue;
				}
				foreach ( $wpsslw_existingorders as $sheet_name => $existingorders_sheet ) {
					$sheet_id = array_search( $sheet_name, $wpsslw_existingsheetsnames, true );
					if ( in_array( $order_id, $existingorders_sheet, true ) ) {
						$wpsslw_num  = array_search( (int) $order_id, parent::wpsslw_convert_int( $existingorders_sheet ), true );
						$start_index = $wpsslw_num;
						if ( $wpsslw_header_type ) {
							$item_count = count( array_keys( $existingorders_sheet, (int) $order_id, true ) );
							$end_index  = $wpsslw_num + $item_count;
						} else {
							$end_index = $wpsslw_num + 1;
						}
						$delete_row_indexes[ $sheet_name ][] = array(
							'start_index' => $start_index,
							'end_index'   => $end_index,
						);
					}
				}
			}

			foreach ( $delete_row_indexes as $sheet_name => $delete_row ) {
				$requests                 = array();
				$delete_row_indexes_count = count( $delete_row );
				array_multisort( array_column( $delete_row, 'start_index' ), SORT_ASC, $delete_row );
				for ( $i = 0;$i < $delete_row_indexes_count;$i++ ) {
					if ( 0 === (int) $i ) {
						$startindex = $delete_row[0]['start_index'];
					} elseif ( $delete_row[ $i ]['start_index'] - $delete_row[ $i - 1 ]['end_index'] > 0 ) {
						$requests[] = array(
							'startIndex' => $startindex,
							'endIndex'   => $delete_row[ $i - 1 ]['end_index'],
						);
						$startindex = $delete_row[ $i ]['start_index'];
					}
					if ( $delete_row_indexes_count - 1 === (int) $i ) {
						$requests[] = array(
							'startIndex' => $startindex,
							'endIndex'   => $delete_row[ $i ]['end_index'],
						);
					}
				}
				array_multisort( array_column( $requests, 'startIndex' ), SORT_DESC, $requests );
				$sheet_id = array_search( $sheet_name, $wpsslw_existingsheetsnames, true );
				foreach ( $requests as $request ) {
					$param                = array();
					$param                = self::$instance_api->prepare_param( $sheet_id, $request['startIndex'], $request['endIndex'] );
					$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				}
				$requests = null;
			}

			if ( ! empty( $deleterequestarray ) ) {

				$delete_row_indexes = null;

				$wpsslw_response    = self::$instance_api->updatebachrequests(
					array(
						'spreadsheetid' => $wpsslw_spreadsheetid,
						'requestarray'  => $deleterequestarray,
					)
				);
				$deleterequestarray = null;
				$wpsslw_response    = null;
				/*Get First Column Value from all sheets*/
				$param                  = array();
				$wpsslw_response        = array();
				$param['spreadsheetid'] = $wpsslw_spreadsheetid;
				$param['ranges']        = array( 'ranges' => $wpsslw_getactivesheets );
				$wpsslw_response        = self::$instance_api->getbatchvalues( $param );

				$wpsslw_existingorders = array();
				foreach ( $wpsslw_response->getValueRanges() as $wpsslw_response_data ) {
					if ( strpos( $wpsslw_response_data->range, "'!A" ) ) {
						$wpsslw_rangetitle = explode( "'!A", $wpsslw_response_data->range );
					} else {
						$wpsslw_rangetitle = explode( '!A', $wpsslw_response_data->range );
					}
					$wpsslw_sheettitle = str_replace( "'", '', $wpsslw_rangetitle[0] );
					$wpsslw_data       = array_map(
						function( $wpsslw_element ) {
							if ( isset( $wpsslw_element['0'] ) && is_numeric( $wpsslw_element['0'] ) ) {
								return (int) $wpsslw_element['0'];
							} else {
								return '';
							}
						},
						$wpsslw_response_data->values
					);

					$wpsslw_existingorders[ $wpsslw_sheettitle ] = $wpsslw_data;
					$wpsslw_data                                 = null;
				}
				$wpsslw_response = null;
			}

			$wpsslw_order_status_key = false;
			if ( in_array( 'trash', array_column( $wpsslw_multiple_order_data, 'new_status' ), true ) ) {
				$wpsslw_woo_selections = stripslashes_deep( parent::wpsslw_option( 'wpssw_sheet_headers_list' ) );
				array_unshift( $wpsslw_woo_selections, 'Order Id' );
				$wpsslw_order_status_key = array_search( 'Order Status', $wpsslw_woo_selections, true );

			}

			foreach ( $wpsslw_multiple_order_data as $order_id => $order_data ) {
				foreach ( $wpsslw_existingorders as $sheet_name => $existingorders_sheet ) {
					$sheet_id           = array_search( $sheet_name, $wpsslw_existingsheetsnames, true );
					$wpsslw_append      = 0;
					$wpsslw_desc_append = 0;
					$wpsslw_order       = wc_get_order( $order_id );

					if ( 'trash' === (string) $order_data['new_status'] && ( empty( $trashed_orders_sheet ) || ! in_array( $sheet_name, $trashed_orders_sheet, true ) ) ) {
						continue;
					} else {
						if ( ! in_array( (string) array_search( $sheet_name, $wpsslw_sheets, true ), array( ucwords( str_replace( '-', ' ', $order_data['new_status'] ) ) . ' Orders' ), true ) ) {
							continue;
						}
						$wpsslw_prdcount = 1;
						if ( $wpsslw_header_type ) {
							$wpsslw_prdcount = count( $wpsslw_order->get_items() );
						}
					}
					$highest_order_id = max( $existingorders_sheet );

					if ( ( $highest_order_id ) && $order_id < $highest_order_id ) {
						foreach ( $existingorders_sheet as $wpsslw_key => $wpsslw_value ) {
							if ( ! empty( $wpsslw_value ) ) {

								$wpsslw_startindex      = $wpsslw_key + 1;
								$insert_row_start_index = $wpsslw_key;
								if ( $wpsslw_header_type ) {
									$wpsslw_endindex      = $wpsslw_startindex + $wpsslw_prdcount;
									$insert_row_end_index = $wpsslw_key + $wpsslw_prdcount;
								} else {
									$wpsslw_endindex      = $wpsslw_startindex + 1;
									$insert_row_end_index = $wpsslw_key + 1;
								}

								if ( ( (int) $order_id < (int) $wpsslw_value ) && 'descorder' !== (string) $order_ascdesc ) {
									$wpsslw_append                       = 1;
									$insert_row_indexes[ $sheet_name ][] = array(
										'start_index' => $insert_row_start_index,
										'end_index'   => $insert_row_end_index,
									);

									$update_row_indexes[ $sheet_name ][ $order_id ] = array(
										'start_index'  => $wpsslw_startindex,
										'end_index'    => $wpsslw_endindex,
										'order_id'     => $order_id,
										'values_count' => $wpsslw_prdcount,

									);
									break;
								}
								if ( 'descorder' === (string) $order_ascdesc ) {
									if ( ( (int) $order_id > (int) $wpsslw_value ) && (int) $wpsslw_value > 0 ) {
										$wpsslw_append                                  = 1;
										$wpsslw_desc_append                             = 1;
										$insert_row_indexes[ $sheet_name ][]            = array(
											'start_index' => $insert_row_start_index,
											'end_index'   => $insert_row_end_index,
										);
										$update_row_indexes[ $sheet_name ][ $order_id ] = array(
											'start_index'  => $wpsslw_startindex,
											'end_index'    => $wpsslw_endindex,
											'order_id'     => $order_id,
											'values_count' => $wpsslw_prdcount,
										);
										break;
									}
								}
							}
						}
					}
					if ( 0 === (int) $wpsslw_desc_append && 'descorder' === (string) $order_ascdesc ) {
						if ( ! $highest_order_id || $order_id > $highest_order_id ) {
							$wpsslw_append = 1;
							if ( $wpsslw_header_type ) {
								$end_index            = 2 + $wpsslw_prdcount;
								$insert_row_end_index = 1 + $wpsslw_prdcount;
							} else {
								$end_index            = 3;
								$insert_row_end_index = 2;
							}
							$insert_row_indexes[ $sheet_name ][]            = array(
								'start_index' => 1,
								'end_index'   => $insert_row_end_index,
							);
							$update_row_indexes[ $sheet_name ][ $order_id ] = array(
								'start_index'  => 2,
								'end_index'    => $end_index,
								'order_id'     => $order_id,
								'values_count' => $wpsslw_prdcount,
							);
						}
					}
					if ( 0 === (int) $wpsslw_append ) {
						if ( $wpsslw_header_type ) {
							$end_index            = count( $existingorders_sheet ) + 1 + $wpsslw_prdcount;
							$insert_row_end_index = count( $existingorders_sheet ) + $wpsslw_prdcount;
						} else {
							$end_index            = count( $existingorders_sheet ) + 2;
							$insert_row_end_index = count( $existingorders_sheet ) + 1;
						}
						$insert_row_indexes[ $sheet_name ][]            = array(
							'start_index' => count( $existingorders_sheet ),
							'end_index'   => $insert_row_end_index,
						);
						$update_row_indexes[ $sheet_name ][ $order_id ] = array(
							'start_index'  => count( $existingorders_sheet ) + 1,
							'end_index'    => $end_index,
							'order_id'     => $order_id,
							'values_count' => $wpsslw_prdcount,
						);
					}
				}
			}

			$insertrequestarray = array();
			foreach ( $insert_row_indexes as  $sheet_name => $insert_row ) {
				$requests                 = array();
				$insert_row_indexes_count = count( $insert_row );
				for ( $i = 0;$i < $insert_row_indexes_count;$i++ ) {
					$requests[] = array(
						'startIndex' => $insert_row[ $i ]['start_index'],
						'endIndex'   => $insert_row[ $i ]['end_index'],
					);
				}
				array_multisort( array_column( $requests, 'startIndex' ), SORT_DESC, $requests );
				$sheet_id = array_search( $sheet_name, $wpsslw_existingsheetsnames, true );
				foreach ( $requests as $request ) {
					$param                = array();
					$param                = self::$instance_api->prepare_param( $sheet_id, $request['startIndex'], $request['endIndex'] );
					$insertrequestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS' );
				}
				$requests = null;
			}

			if ( ! empty( $insertrequestarray ) ) {
				$insert_row_indexes = null;
				$wpsslw_response    = self::$instance_api->updatebachrequests(
					array(
						'spreadsheetid' => $wpsslw_spreadsheetid,
						'requestarray'  => $insertrequestarray,
					)
				);
				$wpsslw_response    = null;
			}

			$update_row_data = array();
			$dimension       = 'ROWS';

			$update_row_indexes_new = array();

			foreach ( $update_row_indexes as  $sheet_name => $update_row ) {
				array_multisort( array_column( $update_row, 'start_index' ), SORT_ASC, $update_row );

				$requests                 = array();
				$update_row_indexes_count = count( $update_row );
				for ( $i = 0;$i < $update_row_indexes_count;$i++ ) {
					$new_start               = $update_row[ $i ]['start_index'];
					$same_startindex_rowkeys = array_keys( parent::wpsslw_convert_int( array_column( $update_row, 'start_index' ) ), (int) $update_row[ $i ]['start_index'], true );

					if ( count( $same_startindex_rowkeys ) > 1 && $same_startindex_rowkeys[0] < $i ) {
						continue;
					}

					for ( $j = 0;$j < $update_row_indexes_count;$j++ ) {
						if ( $j < $i ) {
							$new_start = $new_start + $update_row[ $j ]['values_count'];
						}
						if ( ( 0 === (int) $i || ( $i === $update_row_indexes_count - 1 && $j === $i ) ) && count( $same_startindex_rowkeys ) < 2 ) {
							$param = array();
							if ( 0 === (int) $i ) {
								$param['range'] = $sheet_name . '!A' . $update_row[ $i ]['start_index'];
							} else {
								$param['range'] = $sheet_name . '!A' . $new_start;
							}

							$orderid           = $update_row[ $i ]['order_id'];
							$wpsslw_value_data = self::wpsslw_make_value_array( 'insert', $orderid );

							if ( 'trash' === (string) $wpsslw_multiple_order_data[ $orderid ]['new_status'] && false !== $wpsslw_order_status_key ) {
								if ( $wpsslw_header_type && 'yes' === (string) $repeat_checkbox ) {
									foreach ( $wpsslw_value_data as &$prdarray ) {
										$prdarray[ $wpsslw_order_status_key ] = 'Trash';
									}
								} else {
									$wpsslw_value_data[0][ $wpsslw_order_status_key ] = 'Trash';
								}
							}
							$update_row_data[] = new \Google_Service_Sheets_ValueRange(
								array(
									'range'          => $param['range'],
									'majorDimension' => $dimension,
									'values'         => $wpsslw_value_data,
								)
							);
							$wpsslw_value_data = null;
							break;
						}
						if ( (int) $update_row[ $i ]['start_index'] === (int) $update_row[ $j ]['start_index'] && $update_row[ $i ]['order_id'] !== $update_row[ $j ]['order_id'] ) {

							break;
						}

						if ( $update_row[ $i ]['start_index'] < $update_row[ $j ]['start_index'] ) {
							$param          = array();
							$param['range'] = $sheet_name . '!A' . $new_start;

							$orderid           = $update_row[ $i ]['order_id'];
							$wpsslw_value_data = self::wpsslw_make_value_array( 'insert', $orderid );

							if ( 'trash' === (string) $wpsslw_multiple_order_data[ $orderid ]['new_status'] && false !== $wpsslw_order_status_key ) {
								if ( $wpsslw_header_type && 'yes' === (string) $repeat_checkbox ) {
									foreach ( $wpsslw_value_data as &$prdarray ) {
										$prdarray[ $wpsslw_order_status_key ] = 'Trash';
									}
								} else {
									$wpsslw_value_data[0][ $wpsslw_order_status_key ] = 'Trash';
								}
							}
							$update_row_data[] = new \Google_Service_Sheets_ValueRange(
								array(
									'range'          => $param['range'],
									'majorDimension' => $dimension,
									'values'         => $wpsslw_value_data,
								)
							);
							$wpsslw_value_data = null;
							break;
						}
						if ( count( $same_startindex_rowkeys ) > 1 ) {
							$same_row_start = $update_row[ $i ]['start_index'];
							for ( $k = 0;$k < $i;$k++ ) {
								$same_row_start = $same_row_start + $update_row[ $k ]['values_count'];
							}
							$new_start = $same_row_start;

							$same_rows = array();
							foreach ( $same_startindex_rowkeys as $same_rowkey ) {
								$same_rows[ $update_row[ $same_rowkey ]['order_id'] ] = $update_row[ $same_rowkey ];
							}
							$same_startindex_rowkeys = null;
							if ( 'descorder' === (string) $order_ascdesc ) {
								krsort( $same_rows );
							} else {
								ksort( $same_rows );
							}
							$same_rows_value = array();
							foreach ( $same_rows as $same_row ) {
								$orderid           = $same_row['order_id'];
								$wpsslw_value_data = self::wpsslw_make_value_array( 'insert', $orderid );

								if ( 'trash' === (string) $wpsslw_multiple_order_data[ $orderid ]['new_status'] && false !== $wpsslw_order_status_key ) {
									if ( $wpsslw_header_type && 'yes' === (string) $repeat_checkbox ) {
										foreach ( $wpsslw_value_data as &$prdarray ) {
											$prdarray[ $wpsslw_order_status_key ] = 'Trash';
										}
									} else {
										$wpsslw_value_data[0][ $wpsslw_order_status_key ] = 'Trash';
									}
								}

								$same_rows_value = array_merge( $same_rows_value, $wpsslw_value_data );

								$wpsslw_value_data = null;
							}

							$update_row_data[] = new \Google_Service_Sheets_ValueRange(
								array(
									'range'          => $sheet_name . '!A' . $new_start,
									'majorDimension' => $dimension,
									'values'         => $same_rows_value,
								)
							);
							$same_rows_value   = null;
							break;
						}
					}
				}
			}

			if ( ! empty( $update_row_data ) ) {
				$update_row_indexes = null;
				$wpsslw_inputoption = parent::wpsslw_option( 'wpssw_inputoption' );
				if ( ! $wpsslw_inputoption ) {
					$wpsslw_inputoption = 'USER_ENTERED';
				}

				$requestobject                  = array();
				$requestobject['spreadsheetid'] = $wpsslw_spreadsheetid;

				$requestobject['requestbody'] = new Google_Service_Sheets_BatchUpdateValuesRequest(
					array(
						'valueInputOption' => $wpsslw_inputoption,
						'data'             => $update_row_data,
					)
				);
				$update_row_data              = null;
				$wpsslw_response              = self::$instance_api->multirangevalueupdate( $requestobject );
				$wpsslw_response              = null;
			}
		}
		/**
		 * Get orders count for syncronization
		 *
		 * @param bool $wpsslw_getfirst .
		 */
		public static function wpsslw_get_orders_count( $wpsslw_getfirst = false ) {

			
			if ( ! $wpsslw_getfirst ) {
				if ( ! isset( $_POST['wpsslw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpsslw_general_settings'] ) ), 'save_general_settings' ) ) {
					echo 'error';
					die();
				}
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );
			$spreadsheets_list   = self::$instance_api->get_spreadsheet_listing();
			if ( ! empty( $wpsslw_spreadsheetid ) && ! array_key_exists( $wpsslw_spreadsheetid, $spreadsheets_list ) ) {
				echo 'spreadsheetnotexist';
				die;
			}
			$wpsslw_sheets = array_filter( (array) parent::wpsslw_option( 'wpssw_sheets' ) );
			if ( ! $wpsslw_sheets ) {
				$wpsslw_sheets = self::wpsslw_prepare_sheets();
			}

			$wpsslw_fromdate            = isset( $_POST['sync_all_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all_fromdate'] ) ) : '';
			$wpsslw_todate              = isset( $_POST['sync_all_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all_todate'] ) ) : '';
			$wpsslw_syncall             = isset( $_POST['sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all'] ) ) : '';
			$wpsslw_order_status_array  = self::$wpsslw_default_status_slug;
			
			$wpsslw_existingsheetsnames = array();
			$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
			$wpsslw_existingsheetsnames = array_flip( $wpsslw_existingsheetsnames );

			$wpsslw_status_array             = wc_get_order_statuses();
			$wpsslw_status_array['wc-trash'] = 'Trash';

			$wpsslw_getactivesheets        = array();
			$wpsslw_activesheets           = array();
			foreach ( $wpsslw_status_array as $wpsslw_key => $wpsslw_val ) {
				$wpsslw_status      = substr( $wpsslw_key, strpos( $wpsslw_key, '-' ) + 1 );
				$wpsslw_orderstatus = str_replace( '-', ' ', $wpsslw_status );
				$wpsslw_orderstatus = ucwords( $wpsslw_orderstatus ) . ' Orders';
				if ( array_key_exists( $wpsslw_orderstatus, $wpsslw_sheets ) && in_array( $wpsslw_sheets[ $wpsslw_orderstatus ], $wpsslw_existingsheetsnames, true ) ) {
					if ( 'wc-trash' === (string) $wpsslw_key ) {
						$wpsslw_activesheets['trash'] = $wpsslw_orderstatus;
					} elseif ( 'wc-all' === (string) $wpsslw_key ) {
						$wpsslw_activesheets['all_order'] = $wpsslw_orderstatus;
					} else {
						$wpsslw_activesheets[ $wpsslw_key ] = $wpsslw_orderstatus;
					}
					$wpsslw_getactivesheets[] = "'" . $wpsslw_sheets[ $wpsslw_orderstatus ] . "'!A:A";
				}
			}			
			/*Get First Column Value from all sheets*/
			try {
				$param                  = array();
				$param['spreadsheetid'] = $wpsslw_spreadsheetid;
				$param['ranges']        = array( 'ranges' => $wpsslw_getactivesheets );
				$wpsslw_response         = self::$instance_api->getbatchvalues( $param );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
			
			$wpsslw_existingorders = array();

			foreach ( $wpsslw_response->getValueRanges() as $wpsslw_order ) {
				if ( strpos( $wpsslw_order->range, "'!A" ) ) {
					$wpsslw_rangetitle = explode( "'!A", $wpsslw_order->range );
				} else {
					$wpsslw_rangetitle = explode( '!A', $wpsslw_order->range );
				}
				$wpsslw_sheettitle = str_replace( "'", '', $wpsslw_rangetitle[0] );
				if ( ! is_array( $wpsslw_order->values ) ) {
					$wpsslw_data = array();
				} else {
					$wpsslw_data = array_map(
						function( $wpsslw_element ) {
							if ( isset( $wpsslw_element['0'] ) ) {
								return $wpsslw_element['0'];
							} else {
								return '';
							}
						},
						$wpsslw_order->values
					);
				}

				$wpsslw_existingorders[ $wpsslw_sheettitle ] = $wpsslw_data;
			}

			if ( $wpsslw_getfirst ) {
				return $wpsslw_existingorders;
			}
			$wpsslw_dataarray    = array();
			$wpsslw_isexecute    = 0;
			$response           = array();
			$hpos_order_enabled = parent::wpsslw_check_hpos_order_setting_enabled();
			foreach ( $wpsslw_activesheets as $wpsslw_sheet_slug => $wpsslw_sheetname ) {
				
				// Exclude existing order IDs if applicable.
				$wpsslw_oldids     = parent::wpsslw_convert_int( $wpsslw_existingorders[ $wpsslw_sheets[ $wpsslw_sheetname ] ] );
				$wpsslw_all_orders = array();
				if ( $hpos_order_enabled ) {
					if ( 'All Orders' === (string) $wpsslw_sheetname || $is_product_sheet ) {
						if ( isset( $wpsslw_fromdate ) && isset( $wpsslw_todate ) && $wpsslw_fromdate && $wpsslw_todate ) {

							$wpsslw_query_args = array(
								'limit'        => -1, // Fetch all orders.
								'orderby'      => 'date',
								'order'        => 'ASC',
								'status'       => array_keys( wc_get_order_statuses() ),
								'date_created' => $wpsslw_fromdate . '...' . $wpsslw_todate, // Date range.
								'return'       => 'ids', // Fetch only IDs.
							);
						} else {
							$wpsslw_query_args = array(
								'limit'   => -1, // Fetch all orders.
								'orderby' => 'date',
								'order'   => 'ASC',
								'status'  => array_keys( wc_get_order_statuses() ),
								'return'  => 'ids', // Fetch only IDs.
							);
						}
					} else {
						if ( isset( $wpsslw_fromdate ) && isset( $wpsslw_todate ) && $wpsslw_fromdate && $wpsslw_todate ) {

							$wpsslw_query_args = array(
								'limit'        => -1, // Fetch all orders.
								'orderby'      => 'date',
								'order'        => 'ASC',
								'status'       => $wpsslw_sheet_slug,
								'date_created' => $wpsslw_fromdate . '...' . $wpsslw_todate, // Date range.
								'return'       => 'ids', // Fetch only IDs.
							);
						} else {
							$wpsslw_query_args = array(
								'limit'   => -1, // Fetch all orders.
								'orderby' => 'date',
								'order'   => 'ASC',
								'status'  => $wpsslw_sheet_slug,
								'return'  => 'ids', // Fetch only IDs.
							);
						}
					}

					if ( is_array( $wpsslw_oldids ) && ! empty( $wpsslw_oldids ) ) {
						$wpsslw_query_args['exclude'] = $wpsslw_oldids;
					}
					$wpsslw_all_orders = wc_get_orders( $wpsslw_query_args );
				} else {
					if ( 'All Orders' === (string) $wpsslw_sheetname || $is_product_sheet ) {
						if ( isset( $wpsslw_fromdate ) && isset( $wpsslw_todate ) && $wpsslw_fromdate && $wpsslw_todate ) {
							$wpsslw_query_args = array(
								'post_type'      => 'shop_order',
								'posts_per_page' => -1,
								'order'          => 'ASC',
								'post_status'    => array_keys( wc_get_order_statuses() ),
								'date_query'     => array(
									array(
										'after'     => $wpsslw_fromdate,
										'before'    => $wpsslw_todate,
										'inclusive' => true,
									),
								),
							);
						} else {
							$wpsslw_query_args = array(
								'post_type'      => 'shop_order',
								'posts_per_page' => -1,
								'order'          => 'ASC',
								'post_status'    => array_keys( wc_get_order_statuses() ),
							);
						}
					} else {
						if ( isset( $wpsslw_fromdate ) && isset( $wpsslw_todate ) && $wpsslw_fromdate && $wpsslw_todate ) {
							$wpsslw_query_args = array(
								'post_type'      => 'shop_order',
								'posts_per_page' => -1,
								'order'          => 'ASC',
								'post_status'    => $wpsslw_sheet_slug,
								'date_query'     => array(
									array(
										'after'     => $wpsslw_fromdate,
										'before'    => $wpsslw_todate,
										'inclusive' => true,
									),
								),
							);
						} else {
							$wpsslw_query_args = array(
								'post_type'      => 'shop_order',
								'post_status'    => $wpsslw_sheet_slug,
								'posts_per_page' => -1,
								'order'          => 'ASC',
							);
						}
					}
					$wpsslw_query_args['fields'] = 'ids'; // Fetch only ids.

					if ( is_array( $wpsslw_oldids ) && ! empty( $wpsslw_oldids ) ) {
						$wpsslw_query_args['post__not_in'] = $wpsslw_oldids;
					}

					$wpsslwcustom_query = new WP_Query( $wpsslw_query_args );
					$wpsslw_all_orders  = $wpsslwcustom_query->posts;
				}
				if ( empty( $wpsslw_all_orders ) ) {
					continue;
				}
				$wpsslw_values_array = array();
				$ordercount         = 0;
				foreach ( $wpsslw_all_orders as $wpsslw_order_id ) {
					$wpsslw_order = wc_get_order( $wpsslw_order_id );
					
					if ( 'false' === (string) $wpsslw_syncall ) {
						$wpsslw_orderdate = new DateTime( $wpsslw_order->get_date_created()->format( 'Y-m-d' ) );
						$wpsslw_datefrom  = new DateTime( $wpsslw_fromdate );
						$wpsslw_dateto    = new DateTime( $wpsslw_todate );
						if ( $wpsslw_orderdate < $wpsslw_datefrom || $wpsslw_orderdate > $wpsslw_dateto ) {
							continue;
						}
					}
					$ordercount++;
				}
				
				if ( $ordercount > 0 ) {
					$orderlimit = apply_filters( 'wpssw_order_sync_limit', 500 );
					$response[] = array(
						'sheet_name'  => $wpsslw_sheets[ $wpsslw_sheetname ],
						'sheet_slug'  => $wpsslw_sheet_slug,
						'totalorders' => $ordercount,
						'orderlimit'  => $orderlimit,
					);
				}
			}
			echo wp_json_encode( $response );
			die;
		}
		/**
		 * Syncronize order data sheetwise
		 */
		public static function wpsslw_sync_sheetswise() {
			
			if ( ! isset( $_POST['sync_nonce_token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sync_nonce_token'] ) ), 'sync_nonce' )
			) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpsslw_inputoption   = parent::wpsslw_option( 'wpssw_inputoption' );
			$wpsslw_fromdate      = isset( $_POST['sync_all_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all_fromdate'] ) ) : '';
			$wpsslw_todate        = isset( $_POST['sync_all_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all_todate'] ) ) : '';
			$wpsslw_syncall       = isset( $_POST['sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all'] ) ) : '';
			if ( ! $wpsslw_inputoption ) {
				$wpsslw_inputoption = 'USER_ENTERED';
			}
			$wpsslw_sheet_slug             = isset( $_POST['sheetslug'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetslug'] ) ) : '';
			$wpsslw_sheetname              = isset( $_POST['sheetname'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetname'] ) ) : '';
			$wpsslw_ordercount             = isset( $_POST['ordercount'] ) ? sanitize_text_field( wp_unslash( $_POST['ordercount'] ) ) : '';
			$wpsslw_orderlimit             = isset( $_POST['orderlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['orderlimit'] ) ) : '';
			$order_ascdesc                = parent::wpsslw_option( 'wpssw_order_ascdesc' );
			
			$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
			$wpsslw_sheetid             = $wpsslw_existingsheetsnames[ $wpsslw_sheetname ];
			$wpsslw_sheet               = "'" . $wpsslw_sheetname . "'!A:A";
			$wpsslw_allentry            = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheet );
			$wpsslw_data                = $wpsslw_allentry->getValues();
			if ( ! is_array( $wpsslw_data ) ) {
				$wpsslw_data = array();
			} else {
				$wpsslw_data = array_map(
					function( $wpsslw_element ) {
						if ( isset( $wpsslw_element['0'] ) ) {
							return $wpsslw_element['0'];
						} else {
							return '';
						}
					},
					$wpsslw_data
				);
			}
			$hpos_order_enabled = parent::wpsslw_check_hpos_order_setting_enabled();
			if ( $hpos_order_enabled ) {
				// Base query arguments.
				$base_query_args = array(
					'orderby' => 'ID',
					'order'   => 'ASC',
					'limit'   => isset( $wpsslw_orderlimit ) ? $wpsslw_orderlimit : -1, // Fetch all orders unless a limit is set.
					'return'  => 'ids', // Fetch only IDs.
				);

				// Determine order status based on sheet type.
				if ( 'all_order' === (string) $wpsslw_sheet_slug || $is_product_sheet ) {
					$base_query_args['status'] = array_keys( wc_get_order_statuses() );
				} else {
					$base_query_args['status'] = $wpsslw_sheet_slug;
				}

				// Date filtering.
				if ( isset( $wpsslw_fromdate ) && isset( $wpsslw_todate ) && ! empty( $wpsslw_fromdate ) && ! empty( $wpsslw_todate ) ) {
					$base_query_args['date_created'] = $wpsslw_fromdate . '...' . $wpsslw_todate;
				}

				// Exclude specific order IDs if applicable.
				if ( is_array( $wpsslw_data ) && ! empty( $wpsslw_data ) ) {
					$base_query_args['exclude'] = $wpsslw_data;
				}

				// Order direction.
				if ( 'descorder' === (string) $order_ascdesc ) {
					$base_query_args['order'] = 'DESC';
				}

				// Fetch the orders.
				$wpsslw_all_orders = wc_get_orders( $base_query_args );

			} else {
				if ( 'all_order' === (string) $wpsslw_sheet_slug || $is_product_sheet ) {
					if ( isset( $wpsslw_fromdate ) && isset( $wpsslw_todate ) && ! empty( $wpsslw_fromdate ) && ! empty( $wpsslw_todate ) ) {
						$wpsslw_query_args = array(
							'post_type'      => 'shop_order',
							'posts_per_page' => -1,
							'order'          => 'ASC',
							'orderby'        => 'ID',
							'post_status'    => array_keys( wc_get_order_statuses() ),
							'date_query'     => array(
								array(
									'after'     => $wpsslw_fromdate,
									'before'    => $wpsslw_todate,
									'inclusive' => true,
								),
							),
						);
					} else {
						$wpsslw_query_args = array(
							'post_type'      => 'shop_order',
							'posts_per_page' => -1,
							'order'          => 'ASC',
							'orderby'        => 'ID',
							'post_status'    => array_keys( wc_get_order_statuses() ),
						);
					}
				} else {
					if ( isset( $wpsslw_fromdate ) && isset( $wpsslw_todate ) && ! empty( $wpsslw_fromdate ) && ! empty( $wpsslw_todate ) ) {
						$wpsslw_query_args = array(
							'post_type'      => 'shop_order',
							'posts_per_page' => -1,
							'order'          => 'ASC',
							'orderby'        => 'ID',
							'post_status'    => $wpsslw_sheet_slug,
							'date_query'     => array(
								array(
									'after'     => $wpsslw_fromdate,
									'before'    => $wpsslw_todate,
									'inclusive' => true,
								),
							),
						);
					} else {
						$wpsslw_query_args = array(
							'post_type'      => 'shop_order',
							'post_status'    => $wpsslw_sheet_slug,
							'posts_per_page' => -1,
							'order'          => 'ASC',
							'orderby'        => 'ID',
						);
					}
				}
				if ( 'descorder' === (string) $order_ascdesc ) {
					$wpsslw_query_args['order'] = 'DESC';
				}
				$wpsslw_query_args['fields'] = 'ids'; // Fetch only ids.

				if ( is_array( $wpsslw_data ) && ! empty( $wpsslw_data ) ) {
					$wpsslw_query_args['post__not_in'] = $wpsslw_data;
				}

				$wpsslw_query_args['posts_per_page'] = $wpsslw_orderlimit;
				$wpsslw_query_args['fields']         = 'ids'; // Fetch only ids.

				$wpsslwcustom_query = new WP_Query( $wpsslw_query_args );
				$wpsslw_all_orders  = $wpsslwcustom_query->posts;
			}

			if ( empty( $wpsslw_all_orders ) ) {
				die();
			}

			
			$wpsslw_values_array = array();
			$neworder           = 0;
			foreach ( $wpsslw_all_orders as $wpsslw_order_id ) {

				$wpsslw_order = wc_get_order( $wpsslw_order_id );
				
				
				if ( 'false' === (string) $wpsslw_syncall ) {
					$wpsslw_orderdate = new DateTime( $wpsslw_order->get_date_created()->format( 'Y-m-d' ) );
					$wpsslw_datefrom  = new DateTime( $wpsslw_fromdate );
					$wpsslw_dateto    = new DateTime( $wpsslw_todate );
					if ( $wpsslw_orderdate < $wpsslw_datefrom || $wpsslw_orderdate > $wpsslw_dateto ) {
						continue;
					}
				}
				
				if ( $neworder < $wpsslw_orderlimit ) {

					set_time_limit( 999 );
					$wpsslw_order_data = $wpsslw_order->get_data();
					$wpsslw_status     = $wpsslw_order_data['status'];
					$wpsslw_status     = str_replace( '-', ' ', $wpsslw_status );
					$wpsslw_is_allowed = true;
					if ( 'all_order' === (string) $wpsslw_sheet_slug ) {
						$wpsslw_is_allowed = apply_filters( 'wpssw_order_status_allow', true, ucwords( $wpsslw_status ) . ' Orders' );
					}
					if ( $wpsslw_is_allowed ) {
						if ( $is_product_sheet ) {
							$wpsslw_value = self::wpsslw_make_value_array( 'insert', $wpsslw_order_id, $wpsslw_sheetname );
						} else {
							$wpsslw_value = self::wpsslw_make_value_array( 'insert', $wpsslw_order_id );
						}
						$wpsslw_values_array = array_merge( $wpsslw_values_array, $wpsslw_value );
					}
					$neworder++;
				}
			}
			
			$rangetofind = $wpsslw_sheetname . '!A' . ( count( $wpsslw_data ) + 1 );
			if ( ! empty( $wpsslw_values_array ) ) {
				try {

					$wpsslw_params      = array( 'valueInputOption' => $wpsslw_inputoption );
					$wpsslw_requestbody = self::$instance_api->valuerangeobject( $wpsslw_values_array );
					$param             = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $rangetofind, $wpsslw_requestbody, $wpsslw_params );
					$wpsslw_response    = self::$instance_api->appendentry( $param );

				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die;
		}

		/**
		 * Output Spreadsheet dropdown field.
		 */
		public static function wpsslw_woocommerce_admin_field_select_spreadsheet() {
			$spreadsheets_list               = self::$instance_api->get_spreadsheet_listing();
			$wpsslw_spreadsheetid             = parent::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpsslw_order_spreadsheet_setting = self::wpsslw_check_order_spreadsheet_setting();
			$wpsslw_order_settings_checked    = '';
			if ( 'yes' === (string) $wpsslw_order_spreadsheet_setting ) {
				$wpsslw_order_settings_checked = 'checked';
			}

			?>
			<div class="generalSetting-section">
				<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'Order Settings', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( 'Enable this option to automatically create customized spreadsheets and sheets based on order status for efficient order management & seamless functionality.', 'wpssw' ); ?></p>
				</div>
				<div class="generalSetting-right">
					<label for="order_settings_checkbox">
						<input name="order_settings_checkbox" id="order_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_attr( $wpsslw_order_settings_checked ); ?>>
						<span class="checkbox-switch"></span>
					</label>
				</div>
			</div>
			<div class="generalSetting-section googleSpreadsheet-section ord_spreadsheet_row">
				<div class="generalSetting-left">
				<h4><?php echo esc_html__( 'Google Spreadsheet Settings', 'wpssw' ); ?></h4>
				<p><?php echo esc_html__( 'Your chosen Google Spreadsheet automatically generates a sheet with customized headers based on the below-mentioned settings. Whenever a new order is placed, WPSyncSheets creates a new row to accommodate it with the spreadsheet.', 'wpssw' ); ?></p>
				<div class="createanew-radio">
					<div class="createanew-radio-box">
						<input type="radio" name="spreadsheetselection" value="new" id="createanew">
						<label for="createanew"><?php echo esc_html__( 'Create New Spreadsheet', 'wpssw' ); ?></label>
					</div>
					<div class="createanew-radio-box">
						<input type="radio" name="spreadsheetselection" value="existing" id="existing" checked="checked">
						<label for="existing"><?php echo esc_html__( 'Select Existing Spreadsheet', 'wpssw' ); ?></label>
					</div>
				</div>
				<div id="woocommerce_spreadsheet_container" class="spreadsheet-form">
					<select name="woocommerce_spreadsheet" id="woocommerce_spreadsheet"  class="">
						<?php

						$selected = '';

						foreach ( $spreadsheets_list as $spreadsheetid => $spreadsheetname ) {
							if ( (string) $wpsslw_spreadsheetid === $spreadsheetid ) {
								$selected = 'selected="selected"';
							}
							?>
									<option value="<?php echo esc_attr( $spreadsheetid ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $spreadsheetname ); ?></option>
								<?php $selected = ''; } ?>
							</select>
				</div>
					<div valign="top" id="newsheet" class="newsheetinput spreadsheet-form ord_spreadsheet_inputrow">
						<input class="input-text" name="spreadsheetname" id="spreadsheetname" type="text" placeholder="<?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?>">
					</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Output Syncronization Button
		 */
		public static function wpsslw_woocommerce_admin_field_sync_button() {
			$wpsslw_selections             = parent::wpsslw_option( 'wpssw_sheet_headers_list' );	
			if ( ! empty( $wpsslw_selections ) ) {
				$wpsslw_order_spreadsheet_setting = self::wpsslw_check_order_spreadsheet_setting();
				if ( 'yes' === (string) $wpsslw_order_spreadsheet_setting ) {
					?>
				<div class="generalSetting-section synctr ord_spreadsheet_row">
					<div class="generalSetting-left">
						<h4>
							<span class="wpssw-tooltio-link tooltip-right">
								<?php echo esc_html__( 'Sync Orders', 'wpssw' ); ?>
								<span class="tooltip-text">Export</span>
							</span>
						</h4>
						<p><?php echo esc_html__( "By clicking the 'Click to Sync' button, all orders or orders within the custom date range that are not already present in the sheet will be appended. The existing orders in the sheet will not be updated, providing a seamless synchronization process.", 'wpssw' ); ?></p>
						<div class="sync_all_fromtodate-main">
						<div class="syncall-radio">
							<div class="syncall-radio-box">
								<input type="radio" name="sync_range" value="1" id="sync_all" checked="checked">
								<label for="sync_all"><?php echo esc_html__( 'All Orders', 'wpssw' ); ?></label>
							</div>
							<div class="syncall-radio-box">
								<input type="radio" name="sync_range" value="0" id="sync_daterange">
								<label for="sync_daterange"><?php echo esc_html__( 'Date Range', 'wpssw' ); ?></label>
							</div>
						</div>
						<div class="forminp forminp-select sync_all_fromtodate"> 
							<label for="sync_all_fromdate" >
								<?php echo esc_html__( 'From :', 'wpssw' ); ?>   <input name="sync_all_fromdate" id="sync_all_fromdate" class="sync_all_fromdate" type="date" >
							</label>
							<label for="sync_all_todate" class="sync_todate_label">
								<?php echo esc_html__( 'To :', 'wpssw' ); ?> <input name="sync_all_todate" id="sync_all_todate" class="sync_all_todate" type="date" >
							</label>
						</div>
						</div>
						<div class="sync-button-box">              
							<img src="<?php dirname( __FILE__ ); ?>images/spinner.gif" id="syncloader"><span id="synctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span>
							<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="sync">
							<?php
							esc_html_e( 'Click to Sync', 'wpssw' );
							?>
							</a>
						</div>
					</div>
				</div>
				<?php }
			}
		}
		/**
		 * Output radio button field.
		 */
		public static function wpsslw_woocommerce_admin_field_manage_row_field() {

			$wpsslw_header_format = 'orderwise';
			
			if ( ! empty( $wpsslw_header_format ) ) {
				if ( 'orderwise' === (string) $wpsslw_header_format ) {
					$wpsslw_orderwise   = "checked='checked'";
					$wpsslw_productwise = "disabled='disabled'";
				} else {
					$wpsslw_productwise = "checked='checked' disabled='disabled'";
					$wpsslw_orderwise   = "disabled='disabled'";
				}
				$wpsslw_disableclass = 'disabled';
			} else {
				$wpsslw_orderwise    = "checked='checked'";
				$wpsslw_productwise  = '';
				$wpsslw_disableclass = '';
			}
			?>
			<div class="generalSetting-section ord_spreadsheet_row">
					<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'Manage Row Data', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( 'This setting lets you choose how your data will be organized in the sheet. If you pick "Order-wise," all the order data will be written in a single line. On the other hand, if you go for "Product-wise," each product will have its own separate line. ', 'wpssw' ); ?> <br><strong><?php echo esc_html__( 'Remember, this option is only available when creating a new spreadsheet.', 'wpssw' ); ?></strong></p>
				<div class="forminp radio-box-td generalSetting-right createanew-radio mb-0">
					<input name="header_format" id="orderwise" class="manage-row" value="orderwise" type="radio" <?php echo esc_html( $wpsslw_orderwise ); ?>><label for="orderwise"><?php echo esc_html__( 'Order Wise', 'wpssw' ); ?></label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
					<input name="header_format" id="productwise" class="manage-row <?php echo esc_attr( $wpsslw_disableclass ); ?>" value="productwise" type="radio" <?php echo esc_html( $wpsslw_productwise ); ?>>
					<label for="productwise">
						<span class="wpssw-tooltio-link tooltip-right">
							<?php echo esc_html__( 'Product Wise', 'wpssw' ); ?>
							<span class="tooltip-text">Pro</span>
						</span>
					</label>
				</div>
				</div>
			</div>
					<?php
		}
		/**
		 * Output sheets.
		 */
		public static function wpsslw_woocommerce_admin_field_set_sheets() {
			$wpsslw_sheets          = array();
			$wpsslw_sheets          = self::$wpsslw_default_sheets;
			$wpsslw_sheets_selected = array();
			$wpsslw_sheets_selected = array_filter( (array) parent::wpsslw_option( 'wpssw_sheets' ) );

			if ( ! $wpsslw_sheets_selected ) {
				$wpsslw_sheets_selected = self::wpsslw_prepare_sheets();
			}
			$wpsslw_spreadsheetid       = parent::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );
			$spreadsheets_list         = self::$instance_api->get_spreadsheet_listing();
			$wpsslw_existingsheetsnames = array();
			if ( ! empty( $wpsslw_spreadsheetid ) && array_key_exists( $wpsslw_spreadsheetid, $spreadsheets_list ) ) {
				$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
				$wpsslw_existingsheetsnames = array_flip( $wpsslw_existingsheetsnames );
			}
			?>
			<select id="existing_sheets" hidden="true">
			<?php
			foreach ( $wpsslw_existingsheetsnames as $wpsslw_existingsheet ) {
				?>
					<option value="<?php echo esc_attr( trim( $wpsslw_existingsheet ) ); ?>"><?php echo esc_attr( $wpsslw_existingsheet ); ?></option>
				<?php } ?>
			</select>
			<?php
			if ( ! empty( $wpsslw_sheets ) ) {
				?>
				<div class="generalSetting-section wpssw-section-2 default-order-sheet-section ord_spreadsheet_row">
				<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'Default Order Status as Sheets', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( "Here's a list of sheets based on the default order status provided by WooCommerce, including 'Trash' and 'All Orders'. These selected sheets will be created in your assigned Google Spreadsheets and will neatly organize orders according to their status. These sheets will be automatically updated to keep everything well-managed whenever there are updates or syncs for orders.", 'wpssw' ); ?></p>
				<div class="default-order-sheet-box">
				<?php
				foreach ( $wpsslw_sheets as $wpsslw_key => $wpsslw_val ) {

					$wpsslw_is_check = '';
					$wpsslw_labelid  = strtolower( str_replace( ' ', '_', $wpsslw_val ) );
					if ( array_key_exists( $wpsslw_val, $wpsslw_sheets_selected ) ) {
						$wpsslw_is_check  = 'checked';
						$wpsslw_customval = $wpsslw_sheets_selected[ $wpsslw_val ];
					} else {
						$wpsslw_customval = $wpsslw_val;
					}

					?>
					<div class="default-order-sheet">
						<div class="orderSheet-left">
							<label for="<?php echo esc_attr( $wpsslw_labelid ); ?>">
								<span class="wootextfield"><?php echo esc_html( $wpsslw_customval ); ?></span>
								<span class="ui-icon ui-icon-pencil edit-sheetname wpssw-tooltio-link disabled-pro-version">
									<span class="pencil-icon">
									<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
									</svg>
									</span>
									<span class="tooltip-text edit-tooltip">Upgrade To Pro</span>
								</span>
							</label>
						</div>
						<div class="orderSheet-right forminp forminp-checkbox inside-checkbox">
							<fieldset>
								<label for="<?php echo esc_attr( $wpsslw_labelid ); ?>">
								<input type="checkbox" name="wpssw_sheets_custom[]" value="<?php echo esc_attr( $wpsslw_customval ); ?>" class="sheets_chk1" <?php echo esc_html( $wpsslw_is_check ); ?> hidden="true">
								<input type="checkbox" name="wpssw_sheets[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" id="<?php echo esc_attr( $wpsslw_labelid ); ?>" class="sheets_chk" <?php echo esc_html( $wpsslw_is_check ); ?>>
								<span class="checkbox-switch-new"></span>
								</label>
							</fieldset>
						</div>
					</div>

						<?php
				}
				$wpsslw_pro_sheets = array('All Orders');
				foreach ( $wpsslw_pro_sheets as $wpsslw_key => $wpsslw_val ) {

					$wpsslw_is_check = '';
					$wpsslw_labelid  = strtolower( str_replace( ' ', '_', $wpsslw_val ) );
					if ( array_key_exists( $wpsslw_val, $wpsslw_sheets_selected ) ) {
						$wpsslw_customval = $wpsslw_sheets_selected[ $wpsslw_val ];
					} else {
						$wpsslw_customval = $wpsslw_val;
					}

					?>
					<div class="default-order-sheet disabled-pro-version">
						<div class="orderSheet-left">
							<label>
								<span class="wootextfield">
									<span class="wpssw-tooltio-link tooltip-right">
										<?php echo esc_html( $wpsslw_customval ); ?>
										<span class="tooltip-text">Pro</span>
									</span>
								</span>
								<span class="ui-icon ui-icon-pencil edit-sheetname wpssw-tooltio-link">
									<span class="pencil-icon">
									<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
									</svg>
									</span>
									<span class="tooltip-text edit-tooltip">Upgrade To Pro</span>
								</span>
							</label>
						</div>
						<div class="orderSheet-right forminp forminp-checkbox inside-checkbox">
							<fieldset>
								<label>
									<input type="checkbox"value="<?php echo esc_attr( $wpsslw_customval ); ?>" <?php echo esc_html( $wpsslw_is_check ); ?> hidden="true">
									<input type="checkbox"value="<?php echo esc_attr( $wpsslw_val ); ?>" id="<?php echo esc_attr( $wpsslw_labelid ); ?>" <?php echo esc_html( $wpsslw_is_check ); ?>>
									<span class="checkbox-switch-new"></span>
								</label>
							</fieldset>
						</div>
					</div>

						<?php
				}
				?>
				</div>
				</div>
				</div>
				<?php
			}
		}
		/**
		 * Output Custom sheets.
		 */
		public static function wpsslw_woocommerce_admin_field_set_custom_sheets() {
			$wpsslw_status_array    = wc_get_order_statuses();
			$wpsslw_sheets_selected = array_filter( (array) parent::wpsslw_option( 'wpssw_sheets' ) );
			if ( ! $wpsslw_sheets_selected ) {
				$wpsslw_sheets_selected = self::wpsslw_prepare_sheets();
			}
			?>
			<div class="generalSetting-section custom-order-sheet-section ord_spreadsheet_row">
			<div class="generalSetting-left">
				<h4>
					<label>
						<span class="wpssw-tooltio-link tooltip-right">
							<?php echo esc_html__( 'Custom Order Status as Sheets', 'wpssw' ); ?>
							<span class="tooltip-text">Pro</span>
						</span>
					</label>	
				</h4>
				<p><?php echo esc_html__( 'This option displays the list of custom order statuses created by the WooCommerce third-party plugin. Selected sheets will be created in your Google Spreadsheets, organizing orders based on their status.', 'wpssw' ); ?></p>
			<div class="default-order-sheet-box disabled-pro-version">
			<?php
			foreach ( $wpsslw_status_array as $wpsslw_key => $wpsslw_val ) {
				$wpsslw_status = substr( $wpsslw_key, strpos( $wpsslw_key, '-' ) + 1 );
				if ( ! in_array( $wpsslw_status, self::$wpsslw_default_status, true ) ) {
					$wpsslw_status    = str_replace( '-', ' ', $wpsslw_status );
					$wpsslw_status    = ucwords( $wpsslw_status ) . ' Orders';
					$wpsslw_is_check  = '';
					$wpsslw_labelid   = strtolower( str_replace( ' ', '_', $wpsslw_val ) );
					$wpsslw_val       = ucwords( trim( $wpsslw_val ) ) . ' Orders';
					$wpsslw_customval = $wpsslw_val;
					if ( array_key_exists( $wpsslw_status, $wpsslw_sheets_selected ) ) {
						$wpsslw_customval = trim( $wpsslw_sheets_selected[ $wpsslw_status ] );
					}
					?>
					<div class="custom-order-sheet default-order-sheet">
						<div class="orderSheet-left">
							<label for="<?php echo esc_attr( $wpsslw_labelid ); ?>">
								<span class="wootextfield"><?php echo esc_attr( $wpsslw_customval ); ?></span>
								<span class="ui-icon ui-icon-pencil edit-customsheetname wpssw-tooltio-link">
									<span class="pencil-icon">
									<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
									</svg>
									</span>
									<span class="tooltip-text edit-tooltip">Upgrade To Pro</span>
								</span>
							</label>
						</div>
						<div class="forminp forminp-checkbox orderSheet-right inside-checkbox">
							<fieldset>
							<label for="<?php echo esc_attr( $wpsslw_labelid ); ?>">
							<input type="checkbox" value="<?php echo esc_attr( $wpsslw_customval ); ?>" class="customsheets_chk1" <?php echo esc_html( $wpsslw_is_check ); ?> hidden="true">
							<input type="checkbox" value="<?php echo esc_attr( $wpsslw_status ); ?>" class="customsheets_chk" <?php echo esc_html( $wpsslw_is_check ); ?>>
							<span class="checkbox-switch-new"></span>
							</label>
						</fieldset>
						</div>
					</div>
					<?php
				}
			}
			?>
			</div>
			</div>
			</div>
			<?php
		}
		/**
		 * Output sheet header settings.
		 */
		public static function wpsslw_woocommerce_admin_field_set_headers() {
			$wpsslw_include = new WPSSLW_Include_Action();
			$wpsslw_include->wpsslw_include_order_compatibility_files();
			$wpsslw_header_type         = self::wpsslw_is_productwise();
			$wpsslw_headers             = array();
			$wpsslw_shipping_fields     = array();
			$wpsslw_billing_fields      = array();
			$wpsslw_additional_fields   = array();
			$wpsslw_headers             = apply_filters( 'wpsyncsheets_order_headers', array() );
			$wpsslw_productwise_headers = self::get_headers_by_key( $wpsslw_headers, 'ProductWise' );
			$wpsslw_orderwise_headers   = self::get_headers_by_key( $wpsslw_headers, 'OrderWise' );
			$wpsslw_headers             = array_unique( array_values( parent::wpsslw_array_flatten( $wpsslw_headers ) ) );
			$wpsslw_selections          = stripslashes_deep( parent::wpsslw_option( 'wpssw_sheet_headers_list' ) );
			if ( ! $wpsslw_selections ) {
				$wpsslw_selections = array();
			}
			$wpsslw_selections_custom = stripslashes_deep( parent::wpsslw_option( 'wpssw_sheet_headers_list_custom' ) );
			if ( ! $wpsslw_selections_custom ) {
				$wpsslw_selections_custom = array();
			}
			$wpsslw_prdwise = array_merge( $wpsslw_productwise_headers, $wpsslw_headers );
			$wpsslw_ordwise = array_merge( $wpsslw_orderwise_headers, $wpsslw_headers );
			if ( $wpsslw_header_type ) {
				$wpsslw_headers = $wpsslw_prdwise;
			} else {
				$wpsslw_headers = $wpsslw_ordwise;
			}
			$wpsslw_headers = stripslashes_deep( $wpsslw_headers );
			global $wpsslw_global_headers;
			$wpsslw_global_headers       = $wpsslw_headers;
			$wpsslw_product_selections   = stripslashes_deep( parent::wpsslw_option( 'wpssw_product_sheet_headers_list' ) );
			$wpsslw_static_header        = stripslashes_deep( parent::wpsslw_option( 'wpssw_static_header' ) );
			$wpsslw_static_header_values = stripslashes_deep( parent::wpsslw_option( 'wpssw_static_header_values' ) );
			$wpsslw_custom_value         = array();
			if ( ! $wpsslw_product_selections ) {
				$wpsslw_product_selections = array();
			}
			if ( ! $wpsslw_static_header ) {
				$wpsslw_static_header = array();
			}
			if ( ! $wpsslw_static_header_values ) {
				$wpsslw_static_header_values = array();
			}
			if ( ! empty( $wpsslw_static_header_values ) ) {
				foreach ( $wpsslw_static_header_values as $wpsslw_static_header_value ) {
					if ( strpos( $wpsslw_static_header_value, ',(static_header),' ) ) {
						$wpsslw_static_header_value = str_replace( ',(static_header),', ',', $wpsslw_static_header_value );
						$wpsslw_custom_value[]      = explode( ',', $wpsslw_static_header_value );
					}
				}
			}
			?>
			<div class="generalSetting-section sheetHeaders-section ord_spreadsheet_row">
				<div class="td-wpssw-headers">
					<div class='wpssw_headers'>
						<div class="generalSetting-sheet-headers-row">
							<div class="generalSetting-left">
								<h4><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'If you disable any sheet headers, they will be automatically removed from the current spreadsheet. You can always re-enable the headers you need, save the settings, and update the spreadsheet with the latest data. Clear the existing spreadsheet and click the "Click to Sync" button to initiate the sync process.', 'wpssw' ); ?></p>
							</div>
						</div>
						<br><br>						
						<div class="sheetHeaders-main">
						<ul id="sortable" class="sheetHeaders-box">
							<?php
							$wpsslw_operation = array( 'Insert', 'Update', 'Delete' );
							if ( ! empty( $wpsslw_selections ) ) {
								foreach ( $wpsslw_selections as $wpsslw_key => $wpsslw_val ) {
									if ( in_array( $wpsslw_val, $wpsslw_product_selections, true ) ) {
										continue;
									}
									$wpsslw_static_field        = '';
									$wpsslw_static_field_values = '';
									$wpsslw_is_check            = 'checked';
									$wpsslw_labelid             = strtolower( str_replace( ' ', '_', $wpsslw_val ) );

									$wpsslw_display   = true;
									$wpsslw_classname = '';
									if ( in_array( $wpsslw_val, $wpsslw_operation, true ) ) {
										$wpsslw_display   = false;
										$wpsslw_labelid   = '';
										$wpsslw_classname = strtolower( $wpsslw_val ) . 'order';
									}
									?>
									<li class="default-order-sheet ui-state-default <?php echo esc_html( $wpsslw_classname ); ?>">
										<label>
										<span class="orderSheet-left">
										<span class="wootextfield"><?php echo isset( $wpsslw_selections_custom[ $wpsslw_key ] ) ? esc_attr( $wpsslw_selections_custom[ $wpsslw_key ] ) : esc_attr( $wpsslw_val ); ?></span>
										<?php if ( $wpsslw_display ) { ?>
										<span class="ui-icon ui-icon-pencil wpssw-tooltio-link disabled-pro-version">
											<span class="pencil-icon">
												<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
												</svg>
												</span>
												<span class="tooltip-text edit-tooltip">Upgrade To Pro</span>
										</span>
										<?php } ?>
									</span>
									<span class="orderSheet-right">
									<input type="checkbox" name="header_fields_custom[]" value="<?php echo isset( $wpsslw_selections_custom[ $wpsslw_key ] ) ? esc_attr( $wpsslw_selections_custom[ $wpsslw_key ] ) : esc_attr( $wpsslw_val ); ?>" class="headers_chk1" <?php echo esc_html( $wpsslw_is_check ); ?> hidden="true">
										<?php
										if ( in_array( $wpsslw_val, $wpsslw_static_header, true ) ) {
											?>
										<input type="checkbox" name="header_fields_static[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" hidden="true" checked>
											<?php
										}
										if ( in_array( $wpsslw_val, array_column( $wpsslw_custom_value, 0 ), true ) ) {
											$search_key          = array_search( $wpsslw_val, array_column( $wpsslw_custom_value, 0 ), true );
											$wpsslw_search_array  = $wpsslw_custom_value[ $search_key ];
											$wpsslw_search_keyval = implode( ',(static_header),', $wpsslw_search_array );
											?>
										<input type="checkbox" name="wpssw_static_header_values[]" value="<?php echo esc_attr( $wpsslw_search_keyval ); ?>" hidden="true" checked>
											<?php
										}
										?>
										<input type="checkbox" name="header_fields[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" id="<?php echo esc_attr( $wpsslw_labelid ); ?>" class="headers_chk" <?php echo esc_html( $wpsslw_is_check ); ?>>
										<?php if ( $wpsslw_display ) { ?>
										<span class="checkbox-switch-new disabled-pro-version"></span>
										<?php } ?>
										<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link disabled-pro-version">
											<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
											<mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16">
											<rect x="0.5" width="16" height="16" fill="#D9D9D9"/>
											</mask>
											<g mask="url(#mask0_384_3228)">
											<path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/>
											</g>
											</svg>
											<span class="tooltip-text">Upgrade To Pro</span>
										</span>
									</span>
										</label>
									</li>
									<?php
								}
							}
							if ( ! empty( $wpsslw_headers ) ) {
								foreach ( $wpsslw_headers as $wpsslw_key => $wpsslw_val ) {
									$wpsslw_is_check = '';
									if ( in_array( $wpsslw_val, $wpsslw_selections, true ) ) {
										continue;
									}
									$wpsslw_labelid = strtolower( str_replace( ' ', '_', $wpsslw_val ) );
									$wpsslw_is_check            = 'checked';
									?>
									<li class="default-order-sheet ui-state-default">
										<label>
											<span class="orderSheet-left">
												<span class="wootextfield"><?php echo esc_html( $wpsslw_val ); ?></span>
												<span class="ui-icon ui-icon-pencil wpssw-tooltio-link disabled-pro-version">
													<span class="pencil-icon">
														<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
														</svg>
													</span>
													<span class="tooltip-text edit-tooltip">Upgrade To Pro</span>
												</span>
											</span>
											<span class="orderSheet-right">
												<input type="checkbox" name="header_fields_custom[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" class="headers_chk1" <?php echo esc_html( $wpsslw_is_check ); ?> hidden="true"><input type="checkbox" name="header_fields[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" id="<?php echo esc_attr( $wpsslw_labelid ); ?>" class="headers_chk" <?php echo esc_html( $wpsslw_is_check ); ?>>
												<span class="checkbox-switch-new disabled-pro-version"></span>
												<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link disabled-pro-version">
													<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
														<mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16">
														<rect x="0.5" width="16" height="16" fill="#D9D9D9"/>
														</mask>
														<g mask="url(#mask0_384_3228)">
														<path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/>
														</g>
													</svg>
													<span class="tooltip-text">Upgrade To Pro</span>
												</span>
											</span>
										</label>
									</li>
									<?php
								}
							}
							?>
						</ul>
					</div>
						<input type="hidden" id="prdwise" value="<?php echo esc_attr( implode( ',', $wpsslw_prdwise ) ); ?>">
						<input type="hidden" id="ordwise" value="<?php echo esc_attr( implode( ',', $wpsslw_ordwise ) ); ?>">
					</div>
				</div>
			</div>
			<?php
		}
		/**
		 * Check whether order setting is enabled or not.
		 */
		public static function wpsslw_check_order_spreadsheet_setting() {
			$wpsslw_spreadsheetid             = parent::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpsslw_selections                = stripslashes_deep( parent::wpsslw_option( 'wpssw_sheet_headers_list' ) );
			$wpsslw_order_spreadsheet_setting = self::wpsslw_option( 'wpssw_order_spreadsheet_setting' );
			if ( 'yes' === (string) $wpsslw_order_spreadsheet_setting || ( empty( $wpsslw_order_spreadsheet_setting ) && ( ! empty( $wpsslw_selections ) || '' !== $wpsslw_spreadsheetid ) ) ) {
				return 'yes';
			} else {
				return 'no';
			}
		}
		/**
		 * Get headers using key.
		 *
		 * @param array  $wpsslw_headers .
		 * @param string $array_key .
		 * @return array
		 */
		public static function get_headers_by_key( &$wpsslw_headers = array(), $array_key = '' ) {
			if ( ! is_array( $wpsslw_headers ) ) {
				return false;
			}
			$wpsslw_result = array();
			if ( isset( $wpsslw_headers['WPSSLW_Default'][ $array_key ] ) ) {
				$wpsslw_result = $wpsslw_headers['WPSSLW_Default'][ $array_key ];
				unset( $wpsslw_headers['WPSSLW_Default'][ $array_key ] );
			}
			return $wpsslw_result;
		}
		public static function wpsslw_update_settings() {

			if ( ! isset( $_POST['wpsslw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpsslw_general_settings'] ) ), 'save_general_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}

			if ( isset( $_POST['order_settings_checkbox'] ) ) {
				parent::wpsslw_update_option( 'wpssw_order_spreadsheet_setting', 'yes' );

				$wpsslw_spreadsheetid = self::wpsslw_create_sheet( $_POST );
				if ( isset( $_POST['header_fields'] ) ) {
					$wpsslw_header        = array();
					$wpsslw_header_custom = array();
					
					$wpsslw_header        = array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields'] ) );
					$wpsslw_header_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields_custom'] ) );

					
					$wpsslw_my_headers = stripslashes_deep( $wpsslw_header );
					parent::wpsslw_update_option( 'wpssw_sheet_headers_list', $wpsslw_my_headers );
					$wpsslw_mycustom_headers = stripslashes_deep( $wpsslw_header_custom );
					parent::wpsslw_update_option( 'wpssw_sheet_headers_list_custom', $wpsslw_mycustom_headers );
					
					woocommerce_update_options( self::wpsslw_get_settings() );
					
					parent::wpsslw_update_option( 'wpssw_woocommerce_spreadsheet', $wpsslw_spreadsheetid );

					/*
					* Update Sheetname Option Value.
					*/
					$wpsslw_sheet_names = isset( $_POST['wpssw_sheets'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wpssw_sheets'] ) ) : array();
					$wpsslw_defaultnames = $wpsslw_sheet_names;
					$wpsslw_editednames  = isset( $_POST['wpssw_sheets_custom'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wpssw_sheets_custom'] ) ) : $wpsslw_sheet_names;
					$wpsslw_orderstatus  = array_combine( $wpsslw_defaultnames, $wpsslw_editednames );
					parent::wpsslw_update_option( 'wpssw_sheets', $wpsslw_orderstatus );
					parent::wpsslw_update_option( 'wpssw_header_format', 'orderwise' );
					parent::wpsslw_update_option( 'wpssw_order_ascdesc', 'ascorder' );
					parent::wpsslw_update_option( 'wpssw_inputoption', 'USER_ENTERED' );
				}
			} else {
				parent::wpsslw_update_option( 'wpssw_order_spreadsheet_setting', 'no' );
			}	
			return;
		}

		/**
		 * Create new sheets in selected spreadsheet for General settings Default Order Status options
		 *
		 * @param array $wpsslw_data .
		 * @return string $wpsslw_spreadsheetid
		 */
		public static function wpsslw_create_sheet( $wpsslw_data ) {
			if ( ! isset( $wpsslw_data['wpsslw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $wpsslw_data['wpsslw_general_settings'] ) ), 'save_general_settings' ) ) {
				return;
			}
			$wpsslw_inputoption = parent::wpsslw_option( 'wpssw_inputoption' );
			if ( ! $wpsslw_inputoption ) {
				$wpsslw_inputoption = 'USER_ENTERED';
			}
			if ( 'new' === (string) $wpsslw_data['spreadsheetselection'] ) {
				$wpsslw_newsheetname = trim( $wpsslw_data['spreadsheetname'] );

				/*
				*Create new spreadsheet
				*/
				$requestbody         = self::$instance_api->createspreadsheetobject( $wpsslw_newsheetname );
				$wpsslw_response      = self::$instance_api->createspreadsheet( $requestbody );
				$wpsslw_spreadsheetid = $wpsslw_response['spreadsheetId'];
			} else {
				$wpsslw_spreadsheetid = $wpsslw_data['woocommerce_spreadsheet'];
			}

			if ( ! empty( $wpsslw_data['header_fields'] ) ) {
				$wpsslw_header        = $wpsslw_data['header_fields'];
				$wpsslw_header_custom = $wpsslw_data['header_fields_custom'];

				$wpsslw_headers       = stripslashes_deep( $wpsslw_header );
				$wpsslw_header_custom = stripslashes_deep( $wpsslw_header_custom );
			} else {
				$wpsslw_headers       = stripslashes_deep( parent::wpsslw_option( 'wpssw_sheet_headers_list' ) );
				$wpsslw_header_custom = stripslashes_deep( parent::wpsslw_option( 'wpssw_sheet_headers_list_custom' ) );
			}
			if ( ! $wpsslw_headers ) {
				$wpsslw_headers = array();
			}
			if ( ! $wpsslw_header_custom ) {
				$wpsslw_header_custom = array();
			}
			
			$wpsslw_value        = array();
			$wpsslw_value_custom = array();
			if ( count( $wpsslw_headers ) > 0 ) {
				array_unshift( $wpsslw_headers, 'Order Id' );
				$wpsslw_value = array( $wpsslw_headers );
			}
			if ( count( $wpsslw_header_custom ) > 0 ) {
				array_unshift( $wpsslw_header_custom, 'Order Id' );
				$wpsslw_value_custom = array( $wpsslw_header_custom );
			}
			$wpsslw_sheets_default = self::$wpsslw_default_sheets;
			$response             = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheets = self::$instance_api->get_sheet_list( $response );

			$wpsslw_defaultnames = isset( $wpsslw_data['wpssw_sheets'] ) ? stripslashes_deep( $wpsslw_data['wpssw_sheets'] ) : array();
			$wpsslw_editednames  = isset( $wpsslw_data['wpssw_sheets_custom'] ) ? stripslashes_deep( $wpsslw_data['wpssw_sheets_custom'] ) : $wpsslw_defaultnames;
			$wpsslw_sheets       = array_combine( $wpsslw_defaultnames, $wpsslw_editednames );

			$wpsslw_old_sheets          = (array) parent::wpsslw_option( 'wpssw_sheets' );
			$wpsslw_remove_sheet        = array();
			

			if ( 'new' !== (string) $wpsslw_data['woocommerce_spreadsheet'] ) {
				foreach ( $wpsslw_sheets as $sheetkey => $sheetname ) {
					if ( array_key_exists( $sheetkey, $wpsslw_old_sheets ) ) {
						if ( $sheetname !== $wpsslw_old_sheets[ $sheetkey ] ) {
							$wpsslw_sheetid = isset( $wpsslw_existingsheets[ $wpsslw_old_sheets[ $sheetkey ] ] ) ? $wpsslw_existingsheets[ $wpsslw_old_sheets[ $sheetkey ] ] : '';
							if ( $wpsslw_sheetid && $sheetname ) {
								$param                  = array();
								$param['spreadsheetid'] = $wpsslw_spreadsheetid;
								$param['sheetid']       = $wpsslw_sheetid;
								$param['newsheetname']  = trim( $sheetname );
								$wpsslw_response         = self::$instance_api->updatesheetnameobject( $param );
							}
						}
					}
				}
			}

			$response             = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheets = self::$instance_api->get_sheet_list( $response );

			$wpsslw_status_array             = wc_get_order_statuses();
			$wpsslw_status_array['wc-trash'] = 'Trash';

			$wpsslw_sheetnames  = array();
			$wpsslw_order_array = array();

			foreach ( $wpsslw_status_array as $wpsslw_key => $wpsslw_val ) {
				$wpsslw_status  = substr( $wpsslw_key, strpos( $wpsslw_key, '-' ) + 1 );
				$wpsslw_status  = str_replace( '-', ' ', $wpsslw_status );
				$wpsslw_status  = ucwords( $wpsslw_status ) . ' Orders';
				$old_key_exist = array_key_exists( $wpsslw_status, $wpsslw_old_sheets );
				$new_key_exist = array_key_exists( $wpsslw_status, $wpsslw_sheets );
				if ( true === $new_key_exist ) {
					$wpsslw_order_array[] = 1;
					$wpsslw_sheetnames[]  = $wpsslw_sheets[ $wpsslw_status ];
				}
				if ( false === $new_key_exist && true === $old_key_exist ) {
					$wpsslw_remove_sheet[] = $wpsslw_old_sheets[ $wpsslw_status ];
				}
			}

			$response             = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheets = self::$instance_api->get_sheet_list( $response );
			$wpsslw_existingsheets = array_flip( $wpsslw_existingsheets );
					

			if ( 'new' !== (string) $wpsslw_data['woocommerce_spreadsheet'] ) {
				$wpsslw_sheetnames_count = count( $wpsslw_sheetnames );
				for ( $i = 0; $i < $wpsslw_sheetnames_count; $i++ ) {
					if ( in_array( $wpsslw_sheetnames[ $i ], $wpsslw_existingsheets, true ) ) {
						$wpsslw_order_array[ $i ] = 0;
					} else {
						if ( 1 === (int) $wpsslw_order_array[ $i ] && ! in_array( $wpsslw_sheetnames[ $i ], $wpsslw_existingsheets, true ) ) {
							$wpsslw_order_array[ $i ] = 1;
						}
					}
				}
			}

			$wpsslw_newsheet = 0;

			$wpsslw_order_array_count = count( $wpsslw_order_array );
			for ( $i = 0; $i < $wpsslw_order_array_count; $i++ ) {
				$i               = (int) $i;
				$wpsslw_sheetname = $wpsslw_sheetnames[ $i ];
				if ( 1 === (int) $wpsslw_order_array[ $i ] ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpsslw_spreadsheetid;
					$param['sheetname']     = $wpsslw_sheetname;
					$wpsslw_response         = self::$instance_api->newsheetobject( $param );
					$wpsslw_range            = trim( $wpsslw_sheetname ) . '!A1';
					$wpsslw_params           = array( 'valueInputOption' => $wpsslw_inputoption );
					$wpsslw_requestbody      = self::$instance_api->valuerangeobject( $wpsslw_value_custom );
					$param                  = array();
					$param                  = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_range, $wpsslw_requestbody, $wpsslw_params );
					$wpsslw_response         = self::$instance_api->appendentry( $param );
					$wpsslw_newsheet         = 1;
				}
			}
			if ( 'new' === (string) $wpsslw_data['spreadsheetselection'] ) {
				$param                  = array();
				$param['spreadsheetid'] = $wpsslw_spreadsheetid;
				$wpsslw_response         = self::$instance_api->deletesheetobject( $param );
			}
			if ( 'new' !== (string) $wpsslw_data['woocommerce_spreadsheet'] ) {
				$requestarray           = array();
				$deleterequestarray     = array();
				$wpsslw_old_header_order = parent::wpsslw_option( 'wpssw_sheet_headers_list' );
				if ( ! is_array( $wpsslw_old_header_order ) ) {
					$wpsslw_old_header_order = array();
				}
				

				array_unshift( $wpsslw_old_header_order, 'Order Id' );

				if ( $wpsslw_old_header_order !== $wpsslw_headers ) {
					// Delete deactivate column from sheet.
					$wpsslw_column = array_diff( $wpsslw_old_header_order, $wpsslw_headers );
					if ( ! empty( $wpsslw_column ) ) {
						$wpsslw_column = array_reverse( $wpsslw_column, true );
						foreach ( $wpsslw_column as $columnindex => $columnval ) {
							unset( $wpsslw_old_header_order[ $columnindex ] );
							$wpsslw_old_header_order = array_values( $wpsslw_old_header_order );
							$wpsslw_sheetnames_count = count( $wpsslw_sheetnames );
							for ( $i = 0; $i < $wpsslw_sheetnames_count; $i++ ) {
								if ( in_array( $wpsslw_sheetnames[ $i ], $wpsslw_existingsheets, true ) ) {
									$wpsslw_sheetid = array_search( $wpsslw_sheetnames[ $i ], $wpsslw_existingsheets, true );
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
					}
					try {
						if ( ! empty( $deleterequestarray ) ) {
							$param                  = array();
							$param['spreadsheetid'] = $wpsslw_spreadsheetid;
							$param['requestarray']  = $deleterequestarray;
							$wpsslw_response         = self::$instance_api->updatebachrequests( $param );
						}
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
				if ( $wpsslw_old_header_order !== $wpsslw_headers ) {
					foreach ( $wpsslw_headers as $key => $hname ) {
						if ( 'Order Id' === (string) $hname ) {
							continue;
						}
						$wpsslw_startindex = array_search( $hname, $wpsslw_old_header_order, true );

						if ( false !== $wpsslw_startindex && ( isset( $wpsslw_old_header_order[ $key ] ) && $wpsslw_old_header_order[ $key ] !== $hname ) ) {
							unset( $wpsslw_old_header_order[ $wpsslw_startindex ] );
							$wpsslw_old_header_order = array_merge( array_slice( $wpsslw_old_header_order, 0, $key ), array( 0 => $hname ), array_slice( $wpsslw_old_header_order, $key, count( $wpsslw_old_header_order ) - $key ) );

							$wpsslw_endindex         = $wpsslw_startindex + 1;
							$wpsslw_destindex        = $key;
							$wpsslw_sheetnames_count = count( $wpsslw_sheetnames );
							for ( $i = 0; $i < $wpsslw_sheetnames_count; $i++ ) {
								if ( in_array( $wpsslw_sheetnames[ $i ], $wpsslw_existingsheets, true ) ) {
									$wpsslw_sheetid = array_search( $wpsslw_sheetnames[ $i ], $wpsslw_existingsheets, true );
									if ( $wpsslw_sheetid ) {
										$param              = array();
										$param              = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
										$param['destindex'] = $wpsslw_destindex;
										$requestarray[]     = self::$instance_api->moveDimensionrequests( $param );
									}
								}
							}
						} elseif ( false === (bool) $wpsslw_startindex ) {
							$wpsslw_old_header_order = array_merge( array_slice( $wpsslw_old_header_order, 0, $key ), array( 0 => $hname ), array_slice( $wpsslw_old_header_order, $key, count( $wpsslw_old_header_order ) - $key ) );

							$wpsslw_sheetnames_count = count( $wpsslw_sheetnames );
							
							for ( $i = 0; $i < $wpsslw_sheetnames_count; $i++ ) {
								if ( in_array( $wpsslw_sheetnames[ $i ], $wpsslw_existingsheets, true ) ) {
									$wpsslw_sheetid = array_search( $wpsslw_sheetnames[ $i ], $wpsslw_existingsheets, true );
									if ( $wpsslw_sheetid ) {
										$param            = array();
										$wpsslw_startindex = $key;
										$wpsslw_endindex   = $key + 1;
										$param            = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
										$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'COLUMNS', false );
									}
								}
							}
						}
					}
					if ( ! empty( $requestarray ) ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpsslw_spreadsheetid;
						$param['requestarray']  = $requestarray;
						$wpsslw_response         = self::$instance_api->updatebachrequests( $param );
					}
				}
			}

			$wpsslw_sheetnames_count = count( $wpsslw_sheetnames );
			for ( $i = 0; $i < $wpsslw_sheetnames_count; $i++ ) {
				if ( in_array( $wpsslw_sheetnames[ $i ], $wpsslw_existingsheets, true ) && 0 === (int) $wpsslw_order_array[ $i ] ) {
					$wpsslw_range       = trim( $wpsslw_sheetnames[ $i ] ) . '!A1';
					$wpsslw_requestbody = self::$instance_api->valuerangeobject( $wpsslw_value_custom );
					$wpsslw_params      = array( 'valueInputOption' => $wpsslw_inputoption );
					$param             = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_range, $wpsslw_requestbody, $wpsslw_params );
					$wpsslw_response    = self::$instance_api->updateentry( $param );
				}
			}
			// Delete sheet from spreadsheet on deactivate order status.
			if ( ! empty( $wpsslw_remove_sheet ) ) {
				foreach ( $wpsslw_remove_sheet as $key => $name ) {
					if ( ! in_array( $name, $wpsslw_existingsheets, true ) ) {
						unset( $wpsslw_remove_sheet[ $key ] );
					}
				}
				$wpsslw_remove_sheet = array_values( $wpsslw_remove_sheet );
			}
			if ( ! empty( $wpsslw_remove_sheet ) && 'new' !== (string) $wpsslw_data['woocommerce_spreadsheet'] ) {
				parent::wpsslw_delete_sheet( $wpsslw_spreadsheetid, $wpsslw_remove_sheet, $wpsslw_existingsheets );
			}
			
			$freeze_header        = parent::wpsslw_option( 'freeze_header' );
			$wpsslw_freeze_header = 1;
			$wpsslw_freeze = 1;
			if ( 'yes' !== (string) $freeze_header ) {
				$wpsslw_freeze = 0;
			}
			
			parent::wpsslw_freeze_header( $wpsslw_spreadsheetid, $wpsslw_freeze, '', '', 0, $wpsslw_freeze_header, true, $wpsslw_sheetnames );

			return $wpsslw_spreadsheetid;
		}
	}
	WPSSLW_Order::init();
	endif;
