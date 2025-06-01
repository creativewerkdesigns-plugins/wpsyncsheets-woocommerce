<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSLW_Default' ) ) :
	/**
	 * Class WPSSLW_Default.
	 */
	class WPSSLW_Default extends WPSSLW_Order_Utils {
		/**
		 * Store header list.
		 *
		 * @var array $WPSSLW_headers.
		 */
		public static $wpsslw_orderwise_headers = array();
		/**
		 * Store header list.
		 *
		 * @var array $wpsslw_headers.
		 */
		public static $wpsslw_essential_headers = array();
		/**
		 * Class Contructor.
		 */
		public function __construct() {
			$this->prepare_headers();
			add_filter( 'wpsyncsheets_order_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpsslw_essential_headers = array(
				'_order_tax'            => 'Tax Total',
				'_order_total'          => 'Order Total',
				'_payment_method_title' => 'Payment Method',
				'_billing_first_name'   => 'Billing First name',
				'_billing_last_name'    => 'Billing Last Name',
				'_billing_address_1'    => 'Billing Address 1',
				'_billing_address_2'    => 'Billing Address 2',
				'_billing_city'         => 'Billing City',
				'_billing_state'        => 'Billing State',
				'_billing_postcode'     => 'Billing Postcode',
				'_billing_country'      => 'Billing Country',
				'_billing_company'      => 'Billing Company Name',
				'_shipping_first_name'  => 'Shipping First Name',
				'_shipping_last_name'   => 'Shipping Last Name',
				'_shipping_address_1'   => 'Shipping Address 1',
				'_shipping_address_2'   => 'Shipping Address 2',
				'_shipping_city'        => 'Shipping City',
				'_shipping_state'       => 'Shipping State',
				'_shipping_postcode'    => 'Shipping Postcode',
				'_shipping_country'     => 'Shipping Country',
				'shipping_method'       => 'Shipping Method Title',
				'coupons'               => 'Coupons Codes',
				'_billing_email'        => 'Email',
				'_billing_phone'        => 'Phone',
				'_customer_note'        => 'Customer Note',
				'post_date'             => 'Created Date',
			);
			self::$wpsslw_orderwise_headers = array( 'Product name(QTY)(SKU)' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpsslw_orderwise_headers ) ) {
				$headers['WPSSLW_Default']['OrderWise'] = self::$wpsslw_orderwise_headers;
			}
			if ( ! empty( self::$wpsslw_essential_headers ) ) {
				$headers['WPSSLW_Default']['Essential'] = self::$wpsslw_essential_headers;
			}
			return $headers;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpsslw_headers_name Header name.
		 * @param object $wpsslw_order order object.
		 * @param string $wpsslw_operation operation to perfom on sheet.
		 * @param array  $wpsslw_custom_value .
		 */
		public static function get_value( $wpsslw_headers_name, $wpsslw_order, $wpsslw_operation = 'insert', $wpsslw_custom_value = array() ) {
			return self::prepare_value( $wpsslw_headers_name, $wpsslw_order, $wpsslw_operation, $wpsslw_custom_value );
		}
		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpsslw_headers_name Header name.
		 * @param object $wpsslw_order order object.
		 * @param string $wpsslw_operation operation to perfom on sheet.
		 * @param array  $wpsslw_custom_value .
		 */
		public static function prepare_value( $wpsslw_headers_name, $wpsslw_order, $wpsslw_operation, $wpsslw_custom_value = array() ) {
			$wpsslw_order = wc_get_order( $wpsslw_order->get_id() );

			$wpsslw_items = $wpsslw_order->get_items();

			$wpsslw_order_data  = $wpsslw_order->get_data();
			$wpsslw_arr         = explode( ' ', trim( $wpsslw_headers_name ) );
			$wpsslw_inputoption = WPSSLW_Settings::wpsslw_option( 'wpssw_inputoption' );
			$wpsslw_value       = array();
			if ( 'Billing' === (string) $wpsslw_arr[0] ) {
				$wpsslw_strs = trim( strtolower( substr( $wpsslw_headers_name, 8 ) ) );
				if ( 'insert' === (string) $wpsslw_operation ) {
					$wpsslw_name = str_replace( ' ', '_', $wpsslw_strs );
					if ( 'Billing Postcode' === (string) $wpsslw_headers_name ) {
						if ( 'RAW' === (string) $wpsslw_inputoption ) {
							$wpsslw_insert_val = $wpsslw_order_data['billing'][ $wpsslw_name ] ? $wpsslw_order_data['billing'][ $wpsslw_name ] : '';
						} else {
							$wpsslw_insert_val = $wpsslw_order_data['billing'][ $wpsslw_name ] ? "'" . $wpsslw_order_data['billing'][ $wpsslw_name ] : '';
						}
					} elseif ( 'Billing Company Name' === (string) $wpsslw_headers_name ) {
						$wpsslw_insert_val = $wpsslw_order_data['billing']['company'] ? $wpsslw_order_data['billing']['company'] : '';
					} elseif ( 'Billing Country' === (string) $wpsslw_headers_name ) {
						if ( $wpsslw_order->get_billing_country() ) {
							$wpsslw_insert_val = $wpsslw_order->get_billing_country();
						} else {
							$wpsslw_insert_val = '';
						}
					} elseif ( 'Billing State' === (string) $wpsslw_headers_name ) {
						if ( $wpsslw_order->get_billing_country() ) {
							$wpsslw_states     = WC()->countries->get_states( $wpsslw_order->get_billing_country() );
							$wpsslw_insert_val = ! empty( $wpsslw_states[ $wpsslw_order->get_billing_state() ] ) ? $wpsslw_states[ $wpsslw_order->get_billing_state() ] : '';
						} else {
							$wpsslw_insert_val = $wpsslw_order->get_billing_state();
						}
					} else {
						$wpsslw_insert_val = $wpsslw_order_data['billing'][ $wpsslw_name ] ? $wpsslw_order_data['billing'][ $wpsslw_name ] : '';
					}
					$wpsslw_value[] = $wpsslw_insert_val;
					return $wpsslw_value;
				} else {
					$wpsslw_name = '_billing_' . str_replace( ' ', '_', $wpsslw_strs );
					if ( 'Billing Postcode' === (string) $wpsslw_headers_name ) {
						if ( 'RAW' === (string) $wpsslw_inputoption ) {
							// @codingStandardsIgnoreStart.
							$wpsslw_insert_val = isset( $_REQUEST[ $wpsslw_name ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $wpsslw_name ] ) ) : '';
							// @codingStandardsIgnoreEnd.
						} else {
							// @codingStandardsIgnoreStart.
							$wpsslw_insert_val = isset( $_REQUEST[ $wpsslw_name ] ) ? "'" . sanitize_text_field( wp_unslash( $_REQUEST[ $wpsslw_name ] ) ) : '';
							// @codingStandardsIgnoreEnd.
						}
					} elseif ( 'Billing Address' === (string) $wpsslw_headers_name ) {
						$wpsslw_states          = WC()->countries->get_states( $wpsslw_order->get_billing_country() );
						$wpsslw_country         = $wpsslw_order->get_billing_country();
						$wpsslw_billing_country = empty( $wpsslw_country ) ? '' : WC()->countries->countries[ $wpsslw_country ];
						// @codingStandardsIgnoreStart.
						$wpsslw_billing_state   = isset( $_REQUEST['_billing_state'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_billing_state'] ) ) : '';
						// @codingStandardsIgnoreEnd.
						$wpsslw_states_name = isset( $wpsslw_states[ $wpsslw_order->get_billing_state() ] ) ? $wpsslw_states[ $wpsslw_order->get_billing_state() ] : $wpsslw_billing_state;
						$wpsslw_insert_val  = $wpsslw_order_data['billing']['address_1'] . '
' . $wpsslw_order_data['billing']['address_2'] . '
' . $wpsslw_order_data['billing']['city'] . '
' . $wpsslw_order_data['billing']['postcode'] . '
' . $wpsslw_states_name . '
' . $wpsslw_country;
					} elseif ( 'Billing Company Name' === (string) $wpsslw_headers_name ) {
						// phpcs:ignore.
						$wpsslw_insert_val = isset( $_REQUEST['_billing_company'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_billing_company'] ) ): '';
					} elseif ( 'Billing Country' === (string) $wpsslw_headers_name ) {
						$wpsslw_insert_val = $wpsslw_order->get_billing_country();
					} elseif ( 'Billing State' === (string) $wpsslw_headers_name ) {
						if ( $wpsslw_order->get_billing_country() ) {
							$wpsslw_states = WC()->countries->get_states( $wpsslw_order->get_billing_country() );
							// phpcs:ignore.
							$wpsslw_insert_val = ! empty( $wpsslw_states[ $_REQUEST['_billing_state'] ] ) ? $wpsslw_states[ sanitize_text_field( wp_unslash( $_REQUEST['_billing_state'] ) ) ] : '';
						} else {
							$wpsslw_insert_val = $wpsslw_order->get_billing_state();
						}
					} else {
						// phpcs:ignore.
						$wpsslw_insert_val = isset( $_REQUEST[ $wpsslw_name ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $wpsslw_name ] ) ) : '';
					}
					$wpsslw_value[] = trim( $wpsslw_insert_val );
					return $wpsslw_value;
				}
			}
			if ( 'Shipping' === (string) $wpsslw_arr[0] ) {
				$wpsslw_shipping_method_title = '';
				$wpsslw_strs                  = trim( strtolower( substr( $wpsslw_headers_name, 9 ) ) );
				$wpsslw_name                  = str_replace( ' ', '_', $wpsslw_strs );
				if ( 'Shipping Method Title' === (string) $wpsslw_headers_name ) {
					foreach ( $wpsslw_order->get_items( 'shipping' ) as $wpsslw_item_id => $wpsslw_shipping_item_obj ) {
						$wpsslw_shipping_method_title = $wpsslw_shipping_item_obj->get_method_title();
					}
					$wpsslw_insert_val = $wpsslw_shipping_method_title ? $wpsslw_shipping_method_title : '';
				} elseif ( 'Shipping Postcode' === (string) $wpsslw_headers_name ) {
					if ( 'RAW' === (string) $wpsslw_inputoption ) {
						$wpsslw_insert_val = $wpsslw_order_data['shipping'][ $wpsslw_name ] ? $wpsslw_order_data['shipping'][ $wpsslw_name ] : '';
					} else {
						$wpsslw_insert_val = $wpsslw_order_data['shipping'][ $wpsslw_name ] ? "'" . $wpsslw_order_data['shipping'][ $wpsslw_name ] : '';
					}
				} elseif ( 'Shipping Company Name' === (string) $wpsslw_headers_name ) {
					$wpsslw_insert_val = $wpsslw_order_data['shipping']['company'] ? $wpsslw_order_data['shipping']['company'] : '';
				} elseif ( 'Shipping Country' === (string) $wpsslw_headers_name ) {
					$wpsslw_insert_val = $wpsslw_order->get_shipping_country();
				} elseif ( 'Shipping State' === (string) $wpsslw_headers_name ) {
					$wpsslw_states     = WC()->countries->get_states( $wpsslw_order->get_shipping_country() );
					$wpsslw_insert_val = ! empty( $wpsslw_states[ $wpsslw_order->get_shipping_state() ] ) ? $wpsslw_states[ $wpsslw_order->get_shipping_state() ] : $wpsslw_order_data['shipping']['state'];
				} else {
					$wpsslw_insert_val = $wpsslw_order_data['shipping'][ $wpsslw_name ] ? $wpsslw_order_data['shipping'][ $wpsslw_name ] : '';
				}
				$wpsslw_value[] = trim( $wpsslw_insert_val );
				return $wpsslw_value;
			}
			if ( 'Tax Total' === (string) $wpsslw_headers_name ) {
				$wpsslw_taxes_total = $wpsslw_order->get_total_tax();
				$wpsslw_insert_val  = $wpsslw_taxes_total ? $wpsslw_taxes_total : 0;
				$wpsslw_insert_val  = WPSSLW_Order::wpsslw_get_formatted_values( $wpsslw_insert_val );
				$wpsslw_value[]     = $wpsslw_insert_val;
				return $wpsslw_value;
			}
			if ( 'Coupons Codes' === (string) $wpsslw_headers_name ) {
				$wpsslw_version     = '3.7.0';
				$wpsslw_coupon_code = '';
				global $woocommerce;
				if ( version_compare( $woocommerce->version, $wpsslw_version, '>=' ) ) {
					$wpsslw_get_coupon_codes = implode( ',', $wpsslw_order->get_coupon_codes() );
					$wpsslw_insert_val       = $wpsslw_get_coupon_codes ? $wpsslw_get_coupon_codes : '';
				} else {
					$wpsslw_get_used_coupons = implode( ',', $wpsslw_order->get_used_coupons() );
					$wpsslw_insert_val       = $wpsslw_get_used_coupons ? $wpsslw_get_used_coupons : '';
				}
				$wpsslw_value[] = $wpsslw_insert_val;
				return $wpsslw_value;
			}
			if ( 'Customer Note' === (string) $wpsslw_headers_name ) {
				$wpsslw_insert_val = $wpsslw_order_data['customer_note'] ? $wpsslw_order_data['customer_note'] : '';
				$wpsslw_value[]    = $wpsslw_insert_val;
				return $wpsslw_value;
			}
			if ( 'Order Total' === (string) $wpsslw_headers_name ) {

				$wpsslw_order = wc_get_order( $wpsslw_order->get_id() );

				$refunds        = $wpsslw_order->get_refunds();
				$total_refunded = 0;

				foreach ( $refunds as $refund ) {
					$total_refunded += $refund->get_amount();
				}

				$wpsslw_order_total = (float) $wpsslw_order_data['total'] - (float) $total_refunded;

				$wpsslw_total      = WPSSLW_Order::wpsslw_get_formatted_values( $wpsslw_order_total );
				$wpsslw_insert_val = $wpsslw_total ? $wpsslw_total : 0;
				$wpsslw_value[]    = $wpsslw_insert_val;
				return $wpsslw_value;
			}
			if ( 'Payment Method' === (string) $wpsslw_headers_name ) {
				$wpsslw_insert_val = $wpsslw_order_data['payment_method_title'] ? $wpsslw_order_data['payment_method_title'] : '';
				$wpsslw_value[]    = $wpsslw_insert_val;
				return $wpsslw_value;
			}
			if ( 'Email' === (string) $wpsslw_headers_name ) {
				if ( 'insert' === (string) $wpsslw_operation ) {
					$wpsslw_insert_val = $wpsslw_order_data['billing']['email'] ? $wpsslw_order_data['billing']['email'] : '';
				} else {
					// phpcs:ignore.
					$wpsslw_insert_val = isset( $_REQUEST['_billing_email'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_billing_email'] ) ) : '';
				}
				$wpsslw_value[] = $wpsslw_insert_val;
				return $wpsslw_value;
			}
			if ( 'Phone' === (string) $wpsslw_headers_name ) {
				if ( 'RAW' === (string) $wpsslw_inputoption ) {
					if ( 'insert' === (string) $wpsslw_operation ) {
						$wpsslw_insert_val = $wpsslw_order_data['billing']['phone'] ? $wpsslw_order_data['billing']['phone'] : '';
					} else {
						// phpcs:ignore.
						$wpsslw_insert_val = isset( $_REQUEST['_billing_phone'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_billing_phone'] ) ) : '';
					}
				} else {
					if ( 'insert' === (string) $wpsslw_operation ) {
						$wpsslw_insert_val = $wpsslw_order_data['billing']['phone'] ? "'" . $wpsslw_order_data['billing']['phone'] : '';
					} else {
						// phpcs:ignore.
						$wpsslw_insert_val = isset( $_REQUEST['_billing_phone'] ) ? "'" . sanitize_text_field( wp_unslash( $_REQUEST['_billing_phone'] ) ) : '';
					}
				}
				$wpsslw_value[] = $wpsslw_insert_val;
				return $wpsslw_value;
			}
			if ( 'Created Date' === (string) $wpsslw_headers_name ) {
				$wpsslw_insert_val = $wpsslw_order_data['date_created']->format( WPSSLW_Settings::wpsslw_option( 'date_format' ) . ' ' . WPSSLW_Settings::wpsslw_option( 'time_format' ) );
				$wpsslw_value[]    = $wpsslw_insert_val;
				return $wpsslw_value;
			}
			if ( 'Product name(QTY)(SKU)' === (string) $wpsslw_headers_name ) {
				$wpsslw_prod_qty = '';
				$wpsslw_items    = $wpsslw_order->get_items();
				foreach ( $wpsslw_items as $wpsslw_item ) {
					$wpsslw_product_variation_id = $wpsslw_item['variation_id'];
					if ( $wpsslw_product_variation_id ) {
						$wpsslw_product = wc_get_product( $wpsslw_item['variation_id'] );
					} else {
						$wpsslw_product = wc_get_product( $wpsslw_item['product_id'] );
					}
					$wpsslw_sku = '';
					if ( $wpsslw_product ) {
						$wpsslw_sku = '(' . $wpsslw_product->get_sku() . ')';
					}
					$wpsslw_product_name = $wpsslw_item['name'] . '(' . $wpsslw_item['quantity'] . ')' ? $wpsslw_item['name'] . '(' . $wpsslw_item['quantity'] . ')' . $wpsslw_sku : '';
					$wpsslw_prod_qty    .= ',' . $wpsslw_product_name;
				}
				$wpsslw_value[] = ltrim( $wpsslw_prod_qty, ',' );
				return $wpsslw_value;
			}
			if ( 'Product QTY Total' === (string) $wpsslw_headers_name ) {
				$wpsslw_prod_qty = 0;
				$wpsslw_items    = $wpsslw_order->get_items();
				foreach ( $wpsslw_items as $wpsslw_item ) {
					$wpsslw_prod_qty = $wpsslw_prod_qty + $wpsslw_item['quantity'];
				}
				$wpsslw_value[] = (int) $wpsslw_prod_qty;
				return $wpsslw_value;
			}
			return $wpsslw_value;
		}
	}
	new WPSSLW_Default();
endif;
