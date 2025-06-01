<?php
/**
 * Main WPSyncSheetsWooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSLW_Coupon_Headers' ) ) :
	/**
	 * Class WPSSLW_Coupon_Headers.
	 */
	class WPSSLW_Coupon_Headers extends WPSSLW_Coupon_Utils {
		/**
		 * Store header list.
		 *
		 * @var array $wpsslw_headers.
		 */
		public static $wpsslw_headers = array();

		/**
		 * Class Contructor.
		 */
		public function __construct() {
			$this->prepare_headers();
			add_filter( 'wpsyncsheets_coupon_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpsslw_headers = array(
				'post_title'                 => 'Coupon Code',
				'post_excerpt'               => 'Description',
				'discount_type'              => 'Discount Type',
				'coupon_amount'              => 'Coupon Amount',
				'free_shipping'              => 'Allow Free Shipping',
				'date_expires'               => 'Coupon Expiry Date',
				'minimum_amount'             => 'Minimum Spend',
				'maximum_amount'             => 'Maximum Spend',
				'individual_use'             => 'Individual Use Only',
				'exclude_sale_items'         => 'Exclude Sale Items',
				'product_ids'                => 'Products',
				'exclude_product_ids'        => 'Exclude Products',
				'product_categories'         => 'Applied Product Categories',
				'exclude_product_categories' => 'Exclude Categories',
				'customer_email'             => 'Allowed Emails',
				'usage_limit'                => 'Usage Limit Per Coupon',
				'usage_limit_per_user'       => 'Usage Limit Per User',
				'limit_usage_to_x_items'     => 'Limit Usage To X Items',
				'usage_count'                => 'Coupon Usage Count',
			);
		}

		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpsslw_headers ) ) {
				$headers['WPSSLW_Coupon_Headers'] = self::$wpsslw_headers;
			}
			return $headers;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpsslw_headers_name Header name.
		 * @param object $wpsslw_coupon coupon object.
		 */
		public static function get_value( $wpsslw_headers_name, $wpsslw_coupon ) {
			return self::prepare_value( $wpsslw_headers_name, $wpsslw_coupon );
		}

		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpsslw_headers_name Header name.
		 * @param object $wpsslw_coupon coupon object.
		 */
		public static function prepare_value( $wpsslw_headers_name, $wpsslw_coupon ) {
			$wpsslw_value = '';
			if ( 'Coupon Code' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $wpsslw_coupon->get_code();
				return $wpsslw_value;
			}
			if ( 'Description' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $wpsslw_coupon->get_description();
				return $wpsslw_value;
			}
			if ( 'Discount Type' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $wpsslw_coupon->get_discount_type();
				return $wpsslw_value;
			}
			if ( 'Coupon Amount' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $wpsslw_coupon->get_amount();
				return $wpsslw_value;
			}
			if ( 'Allow Free Shipping' === (string) $wpsslw_headers_name ) {
				$is_free_shipping = 'No';
				if ( $wpsslw_coupon->get_free_shipping() ) {
					$is_free_shipping = 'Yes';
				}
				$wpsslw_value = $is_free_shipping;
				return $wpsslw_value;
			}
			if ( 'Coupon Expiry Date' === (string) $wpsslw_headers_name ) {
				if ( $wpsslw_coupon->get_date_expires() ) {
					$wpsslw_value = (string) $wpsslw_coupon->get_date_expires()->format( WPSSLW_Settings::wpsslw_option( 'date_format' ) . ' ' . WPSSLW_Settings::wpsslw_option( 'time_format' ) );
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Minimum Spend' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $wpsslw_coupon->get_minimum_amount();
				return $wpsslw_value;
			}
			if ( 'Maximum Spend' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $wpsslw_coupon->get_maximum_amount();
				return $wpsslw_value;
			}
			if ( 'Individual Use Only' === (string) $wpsslw_headers_name ) {
				$is_individual = 'No';
				if ( $wpsslw_coupon->get_individual_use() ) {
					$is_individual = 'Yes';
				}
				$wpsslw_value = $is_individual;
				return $wpsslw_value;
			}

			if ( 'Exclude Sale Items' === (string) $wpsslw_headers_name ) {
				$is_exclude_sale_items = 'No';
				if ( $wpsslw_coupon->get_exclude_sale_items() ) {
					$is_exclude_sale_items = 'Yes';
				}
				$wpsslw_value = $is_exclude_sale_items;
				return $wpsslw_value;
			}
			if ( 'Products' === (string) $wpsslw_headers_name ) {
				if ( is_array( $wpsslw_coupon->get_product_ids() ) && ! empty( $wpsslw_coupon->get_product_ids() ) ) {
					$meta_post = array();
					foreach ( $wpsslw_coupon->get_product_ids() as $product_id ) {
						$meta_post[] = get_post( $product_id )->post_title;
					}
					$wpsslw_value = implode( ',', $meta_post );
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Exclude Products' === (string) $wpsslw_headers_name ) {
				if ( is_array( $wpsslw_coupon->get_excluded_product_ids() ) && ! empty( $wpsslw_coupon->get_excluded_product_ids() ) ) {
					$meta_post = array();
					foreach ( $wpsslw_coupon->get_excluded_product_ids() as $product_id ) {
						$meta_post[] = get_post( $product_id )->post_title;
					}
					$wpsslw_value = implode( ',', $meta_post );
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Applied Product Categories' === (string) $wpsslw_headers_name ) {
				$category_names = array();
				if ( is_array( $wpsslw_coupon->get_product_categories() ) && ! empty( $wpsslw_coupon->get_product_categories() ) ) {
					$wpsslw_category_names = WPSSLW_Coupon::wpsslw_get_all_product_categories();
					$category_ids          = array_flip( $wpsslw_category_names );
					foreach ( $wpsslw_coupon->get_product_categories() as $categorie ) {
						if ( in_array( $categorie, $category_ids, true ) ) {
							$category_names[] = array_search( $categorie, $category_ids, true );
						}
					}
					$wpsslw_value = implode( ',', $category_names );
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Exclude Categories' === (string) $wpsslw_headers_name ) {
				$category_names = array();
				if ( is_array( $wpsslw_coupon->get_excluded_product_categories() ) && ! empty( $wpsslw_coupon->get_excluded_product_categories() ) ) {
					$wpsslw_category_names = WPSSLW_Coupon::wpsslw_get_all_product_categories();
					$category_ids          = array_flip( $wpsslw_category_names );
					foreach ( $wpsslw_coupon->get_excluded_product_categories() as $categorie ) {
						if ( in_array( $categorie, $category_ids, true ) ) {
							$category_names[] = array_search( $categorie, $category_ids, true );
						}
					}
					$wpsslw_value = implode( ',', $category_names );
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Allowed Emails' === (string) $wpsslw_headers_name ) {
				if ( is_array( $wpsslw_coupon->get_email_restrictions() ) && ! empty( $wpsslw_coupon->get_email_restrictions() ) ) {
					$wpsslw_value = implode( ',', $wpsslw_coupon->get_email_restrictions() );
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Usage Limit Per Coupon' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $wpsslw_coupon->get_usage_limit();
				return $wpsslw_value;
			}
			if ( 'Usage Limit Per User' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $wpsslw_coupon->get_usage_limit_per_user();
				return $wpsslw_value;
			}
			if ( 'Limit Usage To X Items' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $wpsslw_coupon->get_limit_usage_to_x_items();
				return $wpsslw_value;
			}
			if ( 'Coupon Usage Count' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = $wpsslw_coupon->get_usage_count();
				return $wpsslw_value;
			}
		}
	}
	new WPSSLW_Coupon_Headers();
endif;
