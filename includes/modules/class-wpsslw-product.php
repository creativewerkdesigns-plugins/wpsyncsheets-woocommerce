<?php
/**
 * Main WPSyncSheetsWooCommerce\WPSSLW_Google_API namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-woocommerce
 */

use WPSyncSheetsWooCommerce\WPSSLW_Google_API_Functions;
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSLW_Product' ) ) :
	/**
	 * Class WPSSLW_Product.
	 */
	class WPSSLW_Product extends WPSSLW_Settings {
		/**
		 * Instance of WPSSLW_Google_API_Functions
		 *
		 * @var $instance_api
		 */
		protected static $instance_api = null;
		/**
		 * Initialization
		 */
		public function __construct() {
			self::wpsslw_google_api();
			$wpsslw_include = new WPSSLW_Include_Action();
			$wpsslw_include->wpsslw_include_product_hook();
			$wpsslw_include->wpsslw_include_product_ajax_hook();
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
		 * Update Products
		 *
		 * @param int    $product_id .
		 * @param object $wpsslw_product .
		 */
		public static function wpsslw_woocommerce_update_product( $product_id, $wpsslw_product ) {

			$wpsslw_product_spreadsheet_setting = parent::wpsslw_option( 'wpssw_product_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpsslw_product_spreadsheet_setting ) {
				return;
			}

			// phpcs:ignore.
			if ( ( isset( $_REQUEST['post_status'] ) && 'trash' === sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ) ) ) || ( isset( $_REQUEST['action'] ) && 'untrash' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) && ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) ) ) {
				return;
			}

			$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_product_spreadsheet_id' );

			$wpsslw_sheetname = 'All Products';

			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) || ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) ) {
				if ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) {
					$changed_posts = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['ids'] ) ) );
				} else {
					$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				}
				
				// @codingStandardsIgnoreEnd.
				if ( (int) $product_id === (int) $changed_posts[ count( $changed_posts ) - 1 ] ) {
					$settings = array(
						'setting'        => 'product',
						'setting_enable' => $wpsslw_product_spreadsheet_setting,
						'spreadsheet_id' => $wpsslw_spreadsheetid,
						'sheetname'      => $wpsslw_sheetname,
					);
					parent::wpsslw_multiple_update_data( $changed_posts, $settings, false, 'update' );
				}
				return;
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			$wpsslw_inputoption = self::wpsslw_get_product_inputoption();
			if ( ! empty( $wpsslw_spreadsheetid ) ) {
				$wpsslw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
				if ( ! parent::wpsslw_check_sheet_exist( $wpsslw_spreadsheetid, $wpsslw_sheetname ) ) {
					return;
				}
				$product                    = wc_get_product( $product_id );
				$wpsslw_total               = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheetname );
				$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
				$wpsslw_sheetid             = $wpsslw_existingsheetsnames[ $wpsslw_sheetname ];
				$wpsslw_total_values        = $wpsslw_total->getValues();
				$variation_productid        = array_search( 'Product Id', $wpsslw_total_values[0], true );
				$variation_product_index    = array_column( $wpsslw_total_values, $variation_productid );
				$product_added_childs       = array();
				$add_varaition_row          = 0;
				$remove_varaition_row       = 0;
				$product_keys               = array_filter( array_keys( parent::wpsslw_convert_int( $variation_product_index ), (int) $product_id, true ) );
				$lastkey_value              = ( array_slice( $product_keys, -1, 1 ) );
				$lastkey                    = '';
				if ( isset( $lastkey_value[0] ) ) {
					$lastkey = $lastkey_value[0];
				}
				if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
					$product_childs = $product->get_children();
					if ( ! empty( $product_keys ) ) {
						if ( count( $product_childs ) > count( $product_keys ) ) {
							$add_varaition_row = count( $product_childs ) - count( $product_keys ) + 1;
						} elseif ( count( $product_childs ) < count( $product_keys ) ) {
							$remove_varaition_row = count( $product_keys ) - count( $product_childs ) - 1;
						}
						if ( $remove_varaition_row > 0 ) {
							if ( $wpsslw_sheetid ) {
								$param                = array();
								$startindex           = $product_keys[0];
								$endindex             = $product_keys[0] + $remove_varaition_row;
								$param                = self::$instance_api->prepare_param( $wpsslw_sheetid, $startindex, $endindex );
								$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
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
					}
					$wpsslw_array_value   = array();
					$wpsslw_array_value[] = self::wpsslw_make_product_value_array( 'insert', $product_id );
					foreach ( $product->get_children() as $child ) {
						$wpsslw_child_array   = self::wpsslw_make_product_value_array( 'insert', $child, true );
						$wpsslw_array_value[] = $wpsslw_child_array;
					}
				} else {
					if ( 'variable' !== (string) $product->get_type() || ( empty( $product->get_children() ) && 'variable' === (string) $product->get_type() ) ) {
						if ( count( $product_keys ) > 1 ) {
							if ( $wpsslw_sheetid ) {
								$param                = array();
								$deleterequestarray   = array();
								$startindex           = $product_keys[0];
								$endindex             = $product_keys[0] + count( $product_keys ) - 1;
								$param                = self::$instance_api->prepare_param( $wpsslw_sheetid, $startindex, $endindex );
								$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
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
					}
					$wpsslw_values = self::wpsslw_make_product_value_array( 'insert', $product_id );
				}
				if ( $add_varaition_row > 0 && ! empty( $product_keys ) ) {
					$insert      = 0;
					$start_index = $lastkey + 1;
					$end_index   = $lastkey + $add_varaition_row + 1;
					if ( $wpsslw_sheetid ) {
						$param          = array();
						$param          = self::$instance_api->prepare_param( $wpsslw_sheetid, $start_index, $end_index );
						$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS' );
					}
					try {
						if ( ! empty( $requestarray ) ) {
							$param                  = array();
							$param['spreadsheetid'] = $wpsslw_spreadsheetid;
							$param['requestarray']  = $requestarray;
							$wpsslw_response        = self::$instance_api->updatebachrequests( $param );
						}
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
				$wpsslw_woo_selections = stripslashes_deep( parent::wpsslw_option( 'wpssw_woo_product_headers' ) );
				if ( ! $wpsslw_woo_selections ) {
					$wpsslw_woo_selections = array();
				}
				array_unshift( $wpsslw_woo_selections, 'Product Id', 'Product Variation Id' );
				$wpsslw_product_name_key = array_search( 'Product Name', $wpsslw_woo_selections, true );
				$rangetofind             = $wpsslw_product_name_key ? parent::wpsslw_get_column_index( $wpsslw_product_name_key + 1 ) : 'A';
				$wpsslw_rangetofind      = $wpsslw_sheetname . '!A:' . $rangetofind;
				$wpsslw_allentry         = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_rangetofind );
				$wpsslw_data             = $wpsslw_allentry->getValues();
				$wpsslw_data             = array_map(
					function( $element ) {
						if ( isset( $element['0'] ) ) {
							return $element['0'];
						} else {
							return '';
						}
					},
					$wpsslw_data
				);
				$wpsslw_num              = array_search( (int) $product_id, parent::wpsslw_convert_int( $wpsslw_data ), true );

				if ( $wpsslw_num > 0 ) {
					if ( isset( $wpsslw_array_value ) && ! empty( $wpsslw_array_value ) ) {
						$wpsslw_rangenum      = $wpsslw_num + 1;
						$wpsslw_rangetoupdate = $wpsslw_sheetname . '!A' . $wpsslw_rangenum;
						$wpsslw_requestbody   = self::$instance_api->valuerangeobject( $wpsslw_array_value );
						$wpsslw_params        = array( 'valueInputOption' => $wpsslw_inputoption ); // USER_ENTERED.
						$param                = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_rangetoupdate, $wpsslw_requestbody, $wpsslw_params );
						$wpsslw_response      = self::$instance_api->updateentry( $param );
					} else {
						$wpsslw_rangenum      = $wpsslw_num + 1;
						$wpsslw_rangetoupdate = $wpsslw_sheetname . '!A' . $wpsslw_rangenum;
						$wpsslw_requestbody   = self::$instance_api->valuerangeobject( array( $wpsslw_values ) );
						$wpsslw_params        = array( 'valueInputOption' => $wpsslw_inputoption ); // USER_ENTERED.
						$param                = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_rangetoupdate, $wpsslw_requestbody, $wpsslw_params );
						$wpsslw_response      = self::$instance_api->updateentry( $param );
					}
				} else {
					$wpsslw_isupdated = 0;
					if ( isset( $wpsslw_array_value ) && ! empty( $wpsslw_array_value ) ) {
						$requestarray      = array();
						$wpsslw_startindex = '';
						$wpsslw_endindex   = '';
						if ( count( $wpsslw_data ) > 1 ) {
							$wpsslw_startindex = count( $wpsslw_data );
						}
						$wpsslw_requestbody = self::$instance_api->valuerangeobject( $wpsslw_array_value );
						$wpsslw_params      = array( 'valueInputOption' => $wpsslw_inputoption );
						if ( count( $wpsslw_data ) > 1 ) {
							if ( ! $wpsslw_startindex ) {
								$wpsslw_startindex = count( $wpsslw_data );
							}
							$wpsslw_rangetoupdate = $wpsslw_sheetname . '!A' . ( $wpsslw_startindex + 1 );
							$param                = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_rangetoupdate, $wpsslw_requestbody, $wpsslw_params );
							$wpsslw_response      = self::$instance_api->updateentry( $param );
							$wpsslw_isupdated     = 1;
							$i++;
						}
						if ( 0 === (int) $wpsslw_isupdated ) {
							$param           = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_sheetname, $wpsslw_requestbody, $wpsslw_params );
							$wpsslw_response = self::$instance_api->appendentry( $param );
						}
					} else {
						$requestarray      = array();
						$wpsslw_startindex = '';
						$wpsslw_endindex   = '';
						if ( count( $wpsslw_data ) > 1 ) {
							$wpsslw_startindex = count( $wpsslw_data );
						}
						$wpsslw_requestbody = self::$instance_api->valuerangeobject( array( $wpsslw_values ) );
						$wpsslw_params      = array( 'valueInputOption' => $wpsslw_inputoption );
						if ( $wpsslw_startindex ) {
							$wpsslw_rangetoupdate = $wpsslw_sheetname . '!A' . ( $wpsslw_startindex + 1 );
							$param                = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_rangetoupdate, $wpsslw_requestbody, $wpsslw_params );
							$wpsslw_response      = self::$instance_api->updateentry( $param );
							$wpsslw_isupdated     = 1;
						}
						if ( 0 === (int) $wpsslw_isupdated ) {
							$param           = array();
							$param           = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_sheetname, $wpsslw_requestbody, $wpsslw_params );
							$wpsslw_response = self::$instance_api->appendentry( $param );
						}
					}
				}
			}
			remove_action( 'woocommerce_update_product', __CLASS__ . '::wpsslw_woocommerce_update_product', 10, 2 );
		}
		/**
		 * Get products count for syncronization
		 */
		public static function wpsslw_get_product_count() {

			if ( ! isset( $_POST['wpsslw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpsslw_product_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpsslw_product_spreadsheet_setting = parent::wpsslw_option( 'wpssw_product_spreadsheet_setting' );
			$wpsslw_spreadsheetid               = parent::wpsslw_option( 'wpssw_product_spreadsheet_id' );

			if ( 'yes' !== (string) $wpsslw_product_spreadsheet_setting ) {
				return;
			}
			$wpsslw_sheetname = 'All Products';
			$args             = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			);
			$args['fields']   = 'ids'; // Fetch only ids.

			$wpsslw_sheet    = "'" . $wpsslw_sheetname . "'!A:A";
			$wpsslw_allentry = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheet );

			$wpsslw_data     = $wpsslw_allentry->getValues();
			$wpsslw_allentry = null;
			$wpsslw_data     = array_map(
				function( $wpsslw_element ) {
					if ( isset( $wpsslw_element['0'] ) ) {
						return (int) $wpsslw_element['0'];
					} else {
						return '';
					}
				},
				$wpsslw_data
			);
			if ( is_array( $wpsslw_data ) ) {
				$wpsslw_data = array_values( array_filter( array_unique( $wpsslw_data ) ) );
				if ( ! empty( $wpsslw_data ) ) {
					$args['post__not_in'] = $wpsslw_data;
					$wpsslw_data          = null;
				}
			}
			$products = new WP_Query( $args );

			if ( 1 === (int) $wpml_filters_removed ) {
				self::wpsslw_add_wpml_filters();
			}

			$productcount = 0;
			$productcount = $products->found_posts;

			$productlimit = apply_filters( 'wpssw_product_sync_limit', 500 );

			$response   = array();
			$response[] = array(
				'sheet_name'    => 'All Products',
				'sheet_slug'    => 'all_products',
				'totalproducts' => $productcount,
				'productlimit'  => $productlimit,
			);

			echo wp_json_encode( $response );
			die;
		}
		/**
		 * Sync Products
		 */
		public static function wpsslw_sync_products() {
			if ( ! isset( $_POST['wpsslw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpsslw_product_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpsslw_product_spreadsheet_setting = parent::wpsslw_option( 'wpssw_product_spreadsheet_setting' );
			$wpsslw_spreadsheetid               = parent::wpsslw_option( 'wpssw_product_spreadsheet_id' );

			if ( 'yes' !== (string) $wpsslw_product_spreadsheet_setting ) {
				return;
			}

			$wpsslw_inputoption  = self::wpsslw_get_product_inputoption();
			$wpsslw_sheetname    = 'All Products';
			$wpsslw_productcount = isset( $_POST['productcount'] ) ? sanitize_text_field( wp_unslash( $_POST['productcount'] ) ) : '';
			$wpsslw_productlimit = isset( $_POST['productlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['productlimit'] ) ) : '';

			$wpsslw_syncall         = isset( $_POST['prd_sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all'] ) ) : '';
			$args                   = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			);
			$args['fields']         = 'ids'; // Fetch only ids.
			$args['posts_per_page'] = $wpsslw_productlimit;

			$wpsslw_sheet      = "'" . $wpsslw_sheetname . "'!A:A";
			$wpsslw_allentry   = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheet );
			$wpsslw_data       = $wpsslw_allentry->getValues();
			$wpsslw_allentry   = null;
			$wpsslw_data       = array_map(
				function( $wpsslw_element ) {
					if ( isset( $wpsslw_element['0'] ) ) {
						return (int) $wpsslw_element['0'];
					} else {
						return '';
					}
				},
				$wpsslw_data
			);
			$wpsslw_data_count = count( $wpsslw_data );
			if ( is_array( $wpsslw_data ) && ! empty( $wpsslw_data ) ) {
				$args['post__not_in'] = array_values( array_filter( array_unique( $wpsslw_data ) ) );
				$wpsslw_data          = null;
			}

			$products = new WP_Query( $args );

			if ( empty( $products ) ) {
				die();
			}

			$rangetofind         = $wpsslw_sheetname . '!A' . ( $wpsslw_data_count + 1 );
			$wpsslw_values_array = array();
			$newproduct          = 0;

			$product_ids = $products->posts;
			foreach ( $product_ids as $product_id ) {
				if ( $newproduct < $wpsslw_productlimit ) {
					set_time_limit( 999 );
					$product = wc_get_product( $product_id );
					if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
						$wpsslw_values_array[] = self::wpsslw_make_product_value_array( 'insert', $product_id );
						foreach ( $product->get_children() as $child ) {
							$wpsslw_values_array[] = self::wpsslw_make_product_value_array( 'insert', $child, true );
						}
					} else {
						$wpsslw_values_array[] = self::wpsslw_make_product_value_array( 'insert', $product_id );
					}
					$newproduct++;
				}
			}
			if ( ! empty( $wpsslw_values_array ) ) {
				try {
					$wpsslw_requestbody = self::$instance_api->valuerangeobject( $wpsslw_values_array );
					$wpsslw_params      = array( 'valueInputOption' => $wpsslw_inputoption );
					if ( $wpsslw_data_count > 1 ) {
						$param           = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $rangetofind, $wpsslw_requestbody, $wpsslw_params );
						$wpsslw_response = self::$instance_api->appendentry( $param );
					} else {
						$param           = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_sheet, $wpsslw_requestbody, $wpsslw_params );
						$wpsslw_response = self::$instance_api->appendentry( $param );
					}
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die;
		}
		/**
		 * Clear Product settings sheet
		 */
		public static function wpsslw_clear_productsheet() {
			$wpsslw_product_spreadsheet_setting = parent::wpsslw_option( 'wpssw_product_spreadsheet_setting' );
			$wpsslw_spreadsheetid               = parent::wpsslw_option( 'wpssw_product_spreadsheet_id' );

			if ( 'yes' !== (string) $wpsslw_product_spreadsheet_setting ) {
				echo 'Please save settings.';
				die();
			}
			$requestbody                = self::$instance_api->clearobject();
			$total_headers              = count( parent::wpsslw_option( 'wpssw_woo_product_headers' ) ) + 2;
			$last_column                = parent::wpsslw_get_column_index( $total_headers );
			$wpsslw_existingsheetsnames = array();
			$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
			$wpsslw_existingsheetsnames = array_flip( $wpsslw_existingsheetsnames );
			$wpsslw_sheetname           = 'All Products';
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
		 * Prepare array value of product data to insert into sheet.
		 *
		 * @param string  $wpsslw_operation operation to perfom on sheet.
		 * @param int     $wpsslw_product_id Produt ID.
		 * @param boolean $wpsslw_child True if child product.
		 * @return array $product_value_array
		 */
		public static function wpsslw_make_product_value_array( $wpsslw_operation = 'insert', $wpsslw_product_id = 0, $wpsslw_child = false ) {
			if ( ! $wpsslw_product_id ) {
				return array();
			}
			$wpsslw_include = new WPSSLW_Include_Action();
			$wpsslw_include->wpsslw_include_product_compatibility_files();
			$wpsslw_product                           = wc_get_product( $wpsslw_product_id );
			$wpsslw_woo_selections                    = stripslashes_deep( parent::wpsslw_option( 'wpssw_woo_product_headers' ) );
			$wpsslw_headers                           = apply_filters( 'wpsyncsheets_product_headers', array() );
			$wpsslw_classarray                        = array();
			$wpsslw_headers['WPSSLW_Default_Headers'] = parent::wpsslw_array_flatten( $wpsslw_headers['WPSSLW_Default_Headers'] );
			$wpsslw_custom_value                      = array();

			$wpsslw_woo_selections_count = count( $wpsslw_woo_selections );
			for ( $i = 0; $i < $wpsslw_woo_selections_count; $i++ ) {
				$wpsslw_classarray[ $wpsslw_woo_selections[ $i ] ] = parent::wpsslw_find_class( $wpsslw_headers, $wpsslw_woo_selections[ $i ] );
			}
			$wpsslw_product_row = array();
			if ( ! empty( $wpsslw_product->get_parent_id() ) && 'grouped' !== (string) $wpsslw_product->get_type() ) {
				$pid                  = $wpsslw_product->get_parent_id();
				$wpsslw_product_row[] = $pid;
			} else {
				$wpsslw_product_row[] = $wpsslw_product_id;
			}
			if ( ! empty( $wpsslw_product->get_parent_id() ) && 'variation' === (string) $wpsslw_product->get_type() ) {
				$wpsslw_product_row[] = $wpsslw_product->get_id();
			} else {
				$wpsslw_product_row[] = '';
			}
			foreach ( $wpsslw_classarray as $headername => $classname ) {
				if ( ! empty( $classname ) ) {
					$wpsslw_product_row[] = $classname::get_value( $headername, $wpsslw_product, $wpsslw_child, $wpsslw_custom_value );
				} else {
					$wpsslw_product_row[] = '';
				}
			}
			$wpsslw_product_row = parent::wpsslw_cleanarray( $wpsslw_product_row, count( $wpsslw_woo_selections ) + 1 );
			return $wpsslw_product_row;
		}

		/**
		 * Get inputoption for product settings.
		 */
		public static function wpsslw_get_product_inputoption() {
			$wpsslw_inputoption = parent::wpsslw_option( 'wpssw_inputoption' );
			if ( ! $wpsslw_inputoption ) {
				$wpsslw_prdinputoption = parent::wpsslw_option( 'wpssw_prd_inputoption' );
				if ( ! $wpsslw_prdinputoption ) {
					$wpsslw_inputoption = 'USER_ENTERED';
				} else {
					$wpsslw_inputoption = $wpsslw_prdinputoption;
				}
			}
			return $wpsslw_inputoption;
		}
		/**
		 * Update sheet on multiple products update.
		 *
		 * @param array  $wpsslw_multiple_product_data updated products data.
		 * @param bool   $settings_check settings checked or not.
		 * @param string $oparation oparation type.
		 */
		public static function wpsslw_multiple_product_update( $wpsslw_multiple_product_data, $settings_check = false, $oparation = '' ) {
			if ( empty( $wpsslw_multiple_product_data ) ) {
				return;
			}

			$wpsslw_spreadsheetid = parent::wpsslw_option( 'wpssw_product_spreadsheet_id' );

			if ( false === $settings_check ) {
				$wpsslw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
				if ( ! array_key_exists( $wpsslw_spreadsheetid, $wpsslw_spreadsheets_list ) ) {
					return;
				}
			}
			$response                   = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
			$wpsslw_existingsheetsnames = array_flip( $wpsslw_existingsheetsnames );
			$wpsslw_sheetname           = 'All Products';

			$sheet_id = array_search( $wpsslw_sheetname, $wpsslw_existingsheetsnames, true );
			if ( false === $sheet_id ) {
				return;
			}
			$wpsslw_sheet    = "'" . $wpsslw_sheetname . "'!A:A";
			$wpsslw_allentry = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheet );
			$wpsslw_data     = $wpsslw_allentry->getValues();
			$wpsslw_allentry = null;
			$wpsslw_data     = array_map(
				function( $wpsslw_element ) {
					if ( isset( $wpsslw_element['0'] ) ) {
						return (int) $wpsslw_element['0'];
					} else {
						return '';
					}
				},
				$wpsslw_data
			);

			$delete_row_indexes = array();
			$deleterequestarray = array();

			foreach ( $wpsslw_multiple_product_data as $product_id ) {
				if ( in_array( (int) $product_id, $wpsslw_data, true ) ) {
					$wpsslw_num = array_search( (int) $product_id, $wpsslw_data, true );
					$item_count = count( array_keys( $wpsslw_data, (int) $product_id, true ) );

					$delete_row_indexes[] = array(
						'start_index' => $wpsslw_num,
						'end_index'   => $wpsslw_num + $item_count,
					);
				}
			}

			if ( ! empty( $delete_row_indexes ) ) {
				$requests                 = array();
				$delete_row_indexes_count = count( $delete_row_indexes );
				array_multisort( array_column( $delete_row_indexes, 'start_index' ), SORT_ASC, $delete_row_indexes );
				for ( $i = 0;$i < $delete_row_indexes_count;$i++ ) {
					if ( 0 === (int) $i ) {
						$startindex = $delete_row_indexes[0]['start_index'];
					} elseif ( $delete_row_indexes[ $i ]['start_index'] - $delete_row_indexes[ $i - 1 ]['end_index'] > 0 ) {
						$requests[] = array(
							'startIndex' => $startindex,
							'endIndex'   => $delete_row_indexes[ $i - 1 ]['end_index'],
						);
						$startindex = $delete_row_indexes[ $i ]['start_index'];
					}
					if ( $delete_row_indexes_count - 1 === (int) $i ) {
						$requests[] = array(
							'startIndex' => $startindex,
							'endIndex'   => $delete_row_indexes[ $i ]['end_index'],
						);
					}
				}
				array_multisort( array_column( $requests, 'startIndex' ), SORT_DESC, $requests );
				foreach ( $requests as $request ) {
					$param                = array();
					$param                = self::$instance_api->prepare_param( $sheet_id, $request['startIndex'], $request['endIndex'] );
					$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				}
			}

			if ( ! empty( $deleterequestarray ) ) {
				$param                  = array();
				$param['spreadsheetid'] = $wpsslw_spreadsheetid;
				$param['requestarray']  = $deleterequestarray;
				$wpsslw_response        = self::$instance_api->updatebachrequests( $param );
			}

			if ( 'delete' === (string) $oparation ) {
				return;
			}

			if ( ! empty( $deleterequestarray ) ) {
				$delete_row_indexes = null;
				$deleterequestarray = null;
				$wpsslw_response    = null;

				$wpsslw_allentry = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheet );

				$wpsslw_data     = $wpsslw_allentry->getValues();
				$wpsslw_allentry = null;
				$wpsslw_data     = array_map(
					function( $wpsslw_element ) {
						if ( isset( $wpsslw_element['0'] ) ) {
							return (int) $wpsslw_element['0'];
						} else {
							return '';
						}
					},
					$wpsslw_data
				);

			}
			$wpsslw_data_count = count( $wpsslw_data );

			$highest_product_id = max( $wpsslw_data );

			foreach ( $wpsslw_multiple_product_data as $product_id ) {
				$wpsslw_append = 0;
				$product       = wc_get_product( $product_id );
				if ( false === $product || null === $product || is_wp_error( $product ) ) {
					continue;
				}

				$wpsslw_prdcount = 1;
				if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
					$wpsslw_prdcount += count( $product->get_children() );
				}

				if ( ( $highest_product_id ) && $product_id < $highest_product_id ) {
					foreach ( $wpsslw_data as $wpsslw_key => $wpsslw_value ) {
						if ( ! empty( $wpsslw_value ) ) {

							if ( ( (int) $product_id < (int) $wpsslw_value ) ) {
								$wpsslw_startindex = $wpsslw_key + 1;

								$wpsslw_append        = 1;
								$insert_row_indexes[] = array(
									'start_index' => $wpsslw_key,
									'end_index'   => $wpsslw_key + $wpsslw_prdcount,
								);

								$update_row_indexes[] = array(
									'start_index'  => $wpsslw_startindex,
									'end_index'    => $wpsslw_startindex + $wpsslw_prdcount,
									'product_id'   => $product_id,

									'values_count' => $wpsslw_prdcount,
								);

								break;
							}
						}
					}
				}

				if ( 0 === (int) $wpsslw_append ) {

					$wpsslw_startindex = $wpsslw_data_count + 1;

					$insert_row_indexes[] = array(
						'start_index' => $wpsslw_data_count,
						'end_index'   => $wpsslw_data_count + $wpsslw_prdcount,
					);
					$update_row_indexes[] = array(
						'start_index'  => $wpsslw_startindex,
						'end_index'    => $wpsslw_startindex + $wpsslw_prdcount,
						'product_id'   => $product_id,

						'values_count' => $wpsslw_prdcount,
					);

				}
			}
			$wpsslw_data = null;

			if ( ! empty( $insert_row_indexes ) ) {
				$requests           = array();
				$insertrequestarray = array();

				array_multisort( array_column( $insert_row_indexes, 'start_index' ), SORT_DESC, $insert_row_indexes );
				foreach ( $insert_row_indexes as $request ) {
					$param                = array();
					$param                = self::$instance_api->prepare_param( $sheet_id, $request['start_index'], $request['end_index'] );
					$insertrequestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS' );
				}

				if ( ! empty( $insertrequestarray ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpsslw_spreadsheetid;
					$param['requestarray']  = $insertrequestarray;
					$wpsslw_response        = self::$instance_api->updatebachrequests( $param );

					$insert_row_indexes = null;
					$insertrequestarray = null;
					$wpsslw_response    = null;
				}
			}

			$update_row_data = array();

			$update_row_indexes_new = array();
			if ( ! empty( $update_row_indexes ) ) {
				array_multisort( array_column( $update_row_indexes, 'start_index' ), SORT_ASC, $update_row_indexes );

				$requests                 = array();
				$update_row_indexes_count = count( $update_row_indexes );
				$dimension                = 'ROWS';
				for ( $i = 0;$i < $update_row_indexes_count;$i++ ) {
					$new_start               = $update_row_indexes[ $i ]['start_index'];
					$same_startindex_rowkeys = array_keys( parent::wpsslw_convert_int( array_column( $update_row_indexes, 'start_index' ) ), (int) $update_row_indexes[ $i ]['start_index'], true );

					if ( count( $same_startindex_rowkeys ) > 1 && $same_startindex_rowkeys[0] < $i ) {
						continue;
					}
					for ( $j = 0;$j < $update_row_indexes_count;$j++ ) {

						if ( $j < $i ) {
							$new_start = $new_start + $update_row_indexes[ $j ]['values_count'];
						}
						if ( ( 0 === (int) $i || ( $i === $update_row_indexes_count - 1 && $j === $i ) ) && count( $same_startindex_rowkeys ) < 2 ) {
							$param = array();
							if ( 0 === (int) $i ) {
								$param['range'] = $wpsslw_sheetname . '!A' . $update_row_indexes[ $i ]['start_index'];
							} else {
								$param['range'] = $wpsslw_sheetname . '!A' . $new_start;
							}
							$product             = wc_get_product( $update_row_indexes[ $i ]['product_id'] );
							$wpsslw_values_array = array();
							if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
								$wpsslw_values_array[] = self::wpsslw_make_product_value_array( 'insert', $product->get_id() );
								foreach ( $product->get_children() as $child ) {
									$wpsslw_values_array[] = self::wpsslw_make_product_value_array( 'insert', $child, true );
								}
							} else {
								$wpsslw_values_array[] = self::wpsslw_make_product_value_array( 'insert', $product->get_id() );
							}
							$update_row_data[]   = new \Google_Service_Sheets_ValueRange(
								array(
									'range'          => $param['range'],
									'majorDimension' => $dimension,
									'values'         => $wpsslw_values_array,
								)
							);
							$wpsslw_values_array = null;
							break;
						}
						if ( (int) $update_row_indexes[ $i ]['start_index'] === (int) $update_row_indexes[ $j ]['start_index'] && $update_row_indexes[ $i ]['product_id'] !== $update_row_indexes[ $j ]['product_id'] ) {

							break;
						}

						if ( $update_row_indexes[ $i ]['start_index'] < $update_row_indexes[ $j ]['start_index'] ) {
							$param          = array();
							$param['range'] = $wpsslw_sheetname . '!A' . $new_start;

							$product             = wc_get_product( $update_row_indexes[ $i ]['product_id'] );
							$wpsslw_values_array = array();
							if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
								$wpsslw_values_array[] = self::wpsslw_make_product_value_array( 'insert', $product->get_id() );
								foreach ( $product->get_children() as $child ) {
									$wpsslw_values_array[] = self::wpsslw_make_product_value_array( 'insert', $child, true );
								}
							} else {
								$wpsslw_values_array[] = self::wpsslw_make_product_value_array( 'insert', $product->get_id() );
							}
							$update_row_data[]   = new \Google_Service_Sheets_ValueRange(
								array(
									'range'          => $param['range'],
									'majorDimension' => $dimension,
									'values'         => $wpsslw_values_array,
								)
							);
							$wpsslw_values_array = null;
							break;
						}
						if ( count( $same_startindex_rowkeys ) > 1 ) {
							$same_row_start = $update_row_indexes[ $i ]['start_index'];
							for ( $k = 0;$k < $i;$k++ ) {
								$same_row_start = $same_row_start + $update_row_indexes[ $k ]['values_count'];
							}
							$new_start = $same_row_start;

							$same_rows = array();
							foreach ( $same_startindex_rowkeys as $same_rowkey ) {
								$same_rows[ $update_row_indexes[ $same_rowkey ]['product_id'] ] = $update_row_indexes[ $same_rowkey ];
							}
							ksort( $same_rows );
							$same_startindex_rowkeys = null;

							$same_rows_value = array();
							foreach ( $same_rows as $same_row ) {
								$product             = wc_get_product( $same_row['product_id'] );
								$wpsslw_values_array = array();
								if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
									$wpsslw_values_array[] = self::wpsslw_make_product_value_array( 'insert', $product->get_id() );
									foreach ( $product->get_children() as $child ) {
										$wpsslw_values_array[] = self::wpsslw_make_product_value_array( 'insert', $child, true );
									}
								} else {
									$wpsslw_values_array[] = self::wpsslw_make_product_value_array( 'insert', $product->get_id() );
								}

								$same_rows_value = array_merge( $same_rows_value, $wpsslw_values_array );
							}
							$wpsslw_values_array = null;

							$param          = array();
							$param['range'] = $wpsslw_sheetname . '!A' . $new_start;

							$update_row_data[] = new \Google_Service_Sheets_ValueRange(
								array(
									'range'          => $param['range'],
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

				$wpsslw_inputoption = self::wpsslw_get_product_inputoption();
				if ( ! $wpsslw_inputoption ) {
					$wpsslw_inputoption = 'USER_ENTERED';
				}

				$requestobject                  = array();
				$requestobject['spreadsheetid'] = $wpsslw_spreadsheetid;
				$requestobject['requestbody']   = new Google_Service_Sheets_BatchUpdateValuesRequest(
					array(
						'valueInputOption' => $wpsslw_inputoption,
						'data'             => $update_row_data,
					)
				);
				$wpsslw_response                = self::$instance_api->multirangevalueupdate( $requestobject );
			}
		}

		/**
		 *
		 * Save Product settings tab's settings.
		 */
		public static function wpsslw_update_product_settings() {

			if ( ! isset( $_POST['wpsslw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpsslw_product_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( isset( $_POST['wooproduct_header_list'] ) && isset( $_POST['wooproduct_custom'] ) ) {
				$wpsslw_woo_product_headers        = array_map( 'sanitize_text_field', wp_unslash( $_POST['wooproduct_header_list'] ) );
				$wpsslw_woo_product_headers_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['wooproduct_custom'] ) );
				if ( isset( $_POST['product_settings_checkbox'] ) ) {

					if ( isset( $_POST['prdsheetselection'] ) && 'new' === (string) sanitize_text_field( wp_unslash( $_POST['prdsheetselection'] ) ) ) {
						$wpsslw_newsheetname = isset( $_POST['product_spreadsheet_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['product_spreadsheet_name'] ) ) ) : '';

						/*
						 *Create new spreadsheet
						 */
						$wpsslw_newsheetname  = trim( $wpsslw_newsheetname );
						$requestbody          = self::$instance_api->createspreadsheetobject( $wpsslw_newsheetname );
						$wpsslw_response      = self::$instance_api->createspreadsheet( $requestbody );
						$wpsslw_spreadsheetid = $wpsslw_response['spreadsheetId'];
					} else {
						$wpsslw_spreadsheetid = isset( $_POST['product_spreadsheet'] ) ? sanitize_text_field( wp_unslash( $_POST['product_spreadsheet'] ) ) : '';
					}
					parent::wpsslw_update_option( 'wpssw_product_spreadsheet_id', $wpsslw_spreadsheetid );
					parent::wpsslw_update_option( 'wpssw_product_spreadsheet_setting', 'yes' );
				} else {
					parent::wpsslw_update_option( 'wpssw_product_spreadsheet_setting', 'no' );
					parent::wpsslw_update_option( 'wpssw_product_spreadsheet_id', '' );
					parent::wpsslw_update_option( 'wpssw_bulk_insert_products', '' );

					return;
				}

				if ( isset( $_POST['prd_sync_lang_all'] ) && 1 === (int) $_POST['prd_sync_lang_all'] ) {

					parent::wpsslw_update_option( 'wpssw_prd_sync_lang_all', 1 );

				} else {

					parent::wpsslw_update_option( 'wpssw_prd_sync_lang_all', 0 );

				}

				$response              = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpsslw_existingsheets = array_flip( $wpsslw_existingsheets );

				$wpsslw_sheets        = array( 'All Products' );
				$wpsslw_order_array[] = 1;
				$wpsslw_sheetnames    = array( 'All Products' );
				$wpsslw_inputoption   = self::wpsslw_get_product_inputoption();

				$requestarray       = array();
				$deleterequestarray = array();

				if ( count( $wpsslw_woo_product_headers ) > 0 ) {
					array_unshift( $wpsslw_woo_product_headers, 'Product Id', 'Product Variation Id' );
				}
				if ( count( $wpsslw_woo_product_headers_custom ) > 0 ) {
					array_unshift( $wpsslw_woo_product_headers_custom, 'Product Id', 'Product Variation Id' );
				}

				$wpsslw_old_header_product = parent::wpsslw_option( 'wpssw_woo_product_headers' );
				if ( empty( $wpsslw_old_header_product ) ) {
					$wpsslw_old_header_product = array();
				}
				if ( count( $wpsslw_old_header_product ) > 0 ) {
					array_unshift( $wpsslw_old_header_product, 'Product Id', 'Product Variation Id' );
				}

				if ( 'new' !== (string) sanitize_text_field( wp_unslash( $_POST['prdsheetselection'] ) ) ) {
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
					$i                = (int) $i;
					$wpsslw_sheetname = $wpsslw_sheetnames[ $i ];
					if ( 1 === (int) $wpsslw_order_array[ $i ] ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpsslw_spreadsheetid;
						$param['sheetname']     = $wpsslw_sheetname;
						$wpsslw_response        = self::$instance_api->newsheetobject( $param );
						$wpsslw_range           = trim( $wpsslw_sheetname ) . '!A1';
						$wpsslw_params          = array( 'valueInputOption' => $wpsslw_inputoption );
						$wpsslw_requestbody     = self::$instance_api->valuerangeobject( array( $wpsslw_woo_product_headers_custom ) );
						$param                  = array();
						$param                  = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_range, $wpsslw_requestbody, $wpsslw_params );
						$wpsslw_response        = self::$instance_api->appendentry( $param );
						$wpsslw_newsheet        = 1;
					}
				}

				if ( 'new' === (string) sanitize_text_field( wp_unslash( $_POST['prdsheetselection'] ) ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpsslw_spreadsheetid;
					$wpsslw_response        = self::$instance_api->deletesheetobject( $param );
				}

				if ( $wpsslw_old_header_product !== $wpsslw_woo_product_headers ) {
					$update_sheets = array();
					foreach ( $wpsslw_sheets as $wpsslw_sheetname ) {
						if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
							$update_sheets[] = $wpsslw_sheetname;
						}
					}

					if ( ! empty( $update_sheets ) ) {
						$wpsslw_existingsheets = array();
						$response              = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
						$wpsslw_existingsheets = self::$instance_api->get_sheet_list( $response );
						$wpsslw_existingsheets = array_flip( $wpsslw_existingsheets );
						// Delete deactivate column from sheet.
						$wpsslw_column = array_diff( $wpsslw_old_header_product, $wpsslw_woo_product_headers );
						if ( ! empty( $wpsslw_column ) ) {
							$wpsslw_column = array_reverse( $wpsslw_column, true );
							foreach ( $wpsslw_column as $columnindex => $columnval ) {
								unset( $wpsslw_old_header_product[ $columnindex ] );
								$wpsslw_old_header_product = array_values( $wpsslw_old_header_product );
								foreach ( $update_sheets as $wpsslw_sheetname ) {
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
				}
				if ( $wpsslw_old_header_product !== $wpsslw_woo_product_headers ) {
					foreach ( $wpsslw_woo_product_headers as $key => $hname ) {
						if ( 'Product Id' === (string) $hname || 'Product Variation Id' === (string) $hname ) {
							continue;
						}
						$wpsslw_startindex = array_search( $hname, $wpsslw_old_header_product, true );
						if ( false !== $wpsslw_startindex && ( isset( $wpsslw_old_header_product[ $key ] ) && $wpsslw_old_header_product[ $key ] !== $hname ) ) {
							unset( $wpsslw_old_header_product[ $wpsslw_startindex ] );
							$wpsslw_old_header_product = array_merge( array_slice( $wpsslw_old_header_product, 0, $key ), array( 0 => $hname ), array_slice( $wpsslw_old_header_product, $key, count( $wpsslw_old_header_product ) - $key ) );
							$wpsslw_endindex           = $wpsslw_startindex + 1;
							$wpsslw_destindex          = $key;
							foreach ( $wpsslw_sheets as $wpsslw_sheetname ) {
								if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
									$wpsslw_sheetid = array_search( $wpsslw_sheetname, $wpsslw_existingsheets, true );
									if ( $wpsslw_sheetid ) {
										$param              = array();
										$param              = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
										$param['destindex'] = $wpsslw_destindex;
										$requestarray[]     = self::$instance_api->moveDimensionrequests( $param );
									}
								}
							}
						} elseif ( false === (bool) $wpsslw_startindex ) {
							$wpsslw_old_header_product = array_merge( array_slice( $wpsslw_old_header_product, 0, $key ), array( 0 => $hname ), array_slice( $wpsslw_old_header_product, $key, count( $wpsslw_old_header_product ) - $key ) );
							$product_inherit_style     = self::wpsslw_option( 'wpssw_product_inherit_style' );
							foreach ( $wpsslw_sheets as $wpsslw_sheetname ) {
								if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
									$wpsslw_sheetid = array_search( $wpsslw_sheetname, $wpsslw_existingsheets, true );
									if ( $wpsslw_sheetid ) {
										$param             = array();
										$wpsslw_startindex = $key;
										$wpsslw_endindex   = $key + 1;
										$param             = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
										if ( 'no' === (string) $product_inherit_style ) {
											$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'COLUMNS', false );
										} else {
											$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'COLUMNS', true );
										}
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

				// Delete sheet from spreadsheet on deactivate order status.
				if ( ! empty( $wpsslw_remove_sheet ) ) {
					foreach ( $wpsslw_remove_sheet as $key => $name ) {
						if ( ! in_array( $name, $wpsslw_existingsheets, true ) ) {
							unset( $wpsslw_remove_sheet[ $key ] );
						}
					}
					$wpsslw_remove_sheet = array_values( $wpsslw_remove_sheet );
				}
				if ( ! empty( $wpsslw_remove_sheet ) && 'new' === (string) sanitize_text_field( wp_unslash( $_POST['prdsheetselection'] ) ) ) {
					parent::wpsslw_delete_sheet( $wpsslw_spreadsheetid, $wpsslw_remove_sheet, $wpsslw_existingsheets );
				}

				$wpsslw_existingsheets = array();
				$response              = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpsslw_existingsheets = array_flip( $wpsslw_existingsheets );

				$freeze_header        = parent::wpsslw_option( 'freeze_header' );
				$wpsslw_freeze_header = 1;
				$wpsslw_freeze        = 1;
				if ( 'yes' !== (string) $freeze_header ) {
					$wpsslw_freeze = 0;
				}

				parent::wpsslw_freeze_header( $wpsslw_spreadsheetid, $wpsslw_freeze, '', '', 0, $wpsslw_freeze_header, true, $wpsslw_sheetnames );

				foreach ( $wpsslw_sheets as $wpsslw_sheetname ) {
					if ( in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
						$wpsslw_range       = trim( $wpsslw_sheetname ) . '!A1';
						$wpsslw_requestbody = self::$instance_api->valuerangeobject( array( $wpsslw_woo_product_headers_custom ) );
						$wpsslw_params      = array( 'valueInputOption' => $wpsslw_inputoption );
						$param              = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $wpsslw_range, $wpsslw_requestbody, $wpsslw_params );
						$wpsslw_response    = self::$instance_api->updateentry( $param );
					}
				}
				parent::wpsslw_update_option( 'wpssw_woo_product_headers', array_map( 'sanitize_text_field', wp_unslash( $_POST['wooproduct_header_list'] ) ) );
				parent::wpsslw_update_option( 'wpssw_woo_product_headers_custom', array_map( 'sanitize_text_field', wp_unslash( $_POST['wooproduct_custom'] ) ) );

				/*
				 * Update Static Headers
				 */
				if ( isset( $_POST['product_header_fields_static'] ) && is_array( $_POST['product_header_fields_static'] ) ) {
					$wpsslw_product_static_header = array_map( 'sanitize_text_field', wp_unslash( $_POST['product_header_fields_static'] ) );
					parent::wpsslw_update_option( 'wpssw_product_static_header', $wpsslw_product_static_header );
				}
				parent::wpsslw_update_option( 'wpssw_product_setting_sheets', $wpsslw_sheets );
			}
		}
	}
	new WPSSLW_Product();
endif;
