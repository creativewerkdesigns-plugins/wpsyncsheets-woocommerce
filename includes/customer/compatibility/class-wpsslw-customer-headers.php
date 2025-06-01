<?php
/**
 * Main WPSyncSheetsWooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSLW_Customer_Headers' ) ) :
	/**
	 * Class WPSSLW_Customer_Headers.
	 */
	class WPSSLW_Customer_Headers extends WPSSLW_Customer_Utils {
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
			add_filter( 'wpsyncsheets_customer_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpsslw_headers = array(
				'user_login'          => 'Customer Username',
				'role'                => 'Customer Role',
				'first_name'          => 'Customer FirstName',
				'last_name'           => 'Customer LastName',
				'nickname'            => 'Customer Nickname',
				'user_email'          => 'Customer EmailID',
				'user_url'            => 'Customer Website',
				'description'         => 'Customer Biographical Info',
				'profile_image'       => 'Customer Profile Image',
				'billing_first_name'  => 'Billing FirstName',
				'billing_last_name'   => 'Billing LastName',
				'billing_company'     => 'Billing Company Name',
				'billing_address_1'   => 'Billing Address1',
				'billing_address_2'   => 'Billing Address2',
				'billing_city'        => 'Billing City',
				'billing_postcode'    => 'Billing Postcode / ZIP',
				'billing_country'     => 'Billing Country',
				'billing_state'       => 'Billing State',
				'billing_phone'       => 'Billing Phone Number',
				'billing_email'       => 'Billing EmailID',
				'shipping_first_name' => 'Shipping FirstName',
				'shipping_last_name'  => 'Shipping LastName',
				'shipping_company'    => 'Shipping Company Name',
				'shipping_address_1'  => 'Shipping Address1',
				'shipping_address_2'  => 'Shipping Address2',
				'shipping_city'       => 'Shipping City',
				'shipping_postcode'   => 'Shipping Postcode / ZIP',
				'shipping_country'    => 'Shipping Country',
				'shipping_state'      => 'Shipping State',
			);
		}

		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpsslw_headers ) ) {
				$headers['WPSSLW_Customer_Headers'] = self::$wpsslw_headers;
			}
			return $headers;
		}

		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpsslw_headers_name Header name.
		 * @param array  $customers_metadata metadata array of customer.
		 * @param int    $customer_id id of the customer being processed.
		 * @param mix    $wpsslw_customer customers data.
		 * @param array  $wpsslw_custom_value custom value.
		 */
		public static function get_value( $wpsslw_headers_name, $customers_metadata, $customer_id, $wpsslw_customer, $wpsslw_custom_value = array() ) {
			return self::prepare_value( $wpsslw_headers_name, $customers_metadata, $customer_id, $wpsslw_customer, $wpsslw_custom_value );
		}

		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpsslw_headers_name Header name.
		 * @param array  $customers_metadata metadata array of customer.
		 * @param int    $customer_id id of the customer being processed.
		 * @param mix    $wpsslw_customer customers data.
		 * @param array  $wpsslw_custom_value custom value.
		 */
		public static function prepare_value( $wpsslw_headers_name, $customers_metadata, $customer_id, $wpsslw_customer, $wpsslw_custom_value ) {

			$wpsslw_value  = '';
			$countries_obj = new WC_Countries();
			$countries     = $countries_obj->get_countries();

			/** Static header dropdown wpssw_static_header_name */
			if ( ! empty( $wpsslw_custom_value ) ) {
				$wpsslw_static_header_name = array_column( $wpsslw_custom_value, 0 );

				if ( in_array( $wpsslw_headers_name, $wpsslw_static_header_name, true ) ) {
					$search_key          = array_search( $wpsslw_headers_name, $wpsslw_static_header_name, true );
					$wpsslw_search_array = $wpsslw_custom_value[ $search_key ];

					if ( 'blank' === (string) $wpsslw_search_array[1] ) {
						$wpsslw_insert_val = Google_Model::NULL_VALUE;
						$wpsslw_value      = $wpsslw_insert_val;
						return $wpsslw_value;
					}
				}
			}

			if ( 'Customer Username' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $customers_metadata['user_login'];
				return $wpsslw_value;
			}
			if ( 'Customer Role' === (string) $wpsslw_headers_name ) {
				$role         = $customers_metadata['roles'];
				$wpsslw_value = $role[0];
				return $wpsslw_value;
			}
			if ( 'Customer FirstName' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $customers_metadata['first_name'][0];
				return $wpsslw_value;
			}
			if ( 'Customer LastName' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $customers_metadata['last_name'][0];
				return $wpsslw_value;
			}
			if ( 'Customer Nickname' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $customers_metadata['nickname'][0];
				return $wpsslw_value;
			}
			if ( 'Customer EmailID' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $customers_metadata['user_email'];
				return $wpsslw_value;
			}
			if ( 'Customer Website' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) html_entity_decode( $customers_metadata['user_url'] );
				return $wpsslw_value;
			}
			if ( 'Customer Biographical Info' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = (string) $customers_metadata['description'][0];
				return $wpsslw_value;
			}
			if ( 'Customer Profile Image' === (string) $wpsslw_headers_name ) {
				$wpsslw_value = $customers_metadata['profile_image'];
				return $wpsslw_value;
			}
			if ( 'Billing FirstName' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['billing_first_name'] ) && ! empty( $customers_metadata['billing_first_name'] ) ) {
					$wpsslw_value = (string) $customers_metadata['billing_first_name'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Billing LastName' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['billing_last_name'] ) && ! empty( $customers_metadata['billing_last_name'] ) ) {
					$wpsslw_value = (string) $customers_metadata['billing_last_name'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Billing Company Name' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['billing_company'] ) && ! empty( $customers_metadata['billing_company'] ) ) {
					$wpsslw_value = (string) $customers_metadata['billing_company'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Billing Address1' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['billing_address_1'] ) && ! empty( $customers_metadata['billing_address_1'] ) ) {
					$wpsslw_value = (string) $customers_metadata['billing_address_1'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Billing Address2' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['billing_address_2'] ) && ! empty( $customers_metadata['billing_address_2'] ) ) {
					$wpsslw_value = (string) $customers_metadata['billing_address_2'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Billing City' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['billing_city'] ) && ! empty( $customers_metadata['billing_city'] ) ) {
					$wpsslw_value = (string) $customers_metadata['billing_city'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Billing Postcode / ZIP' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['billing_postcode'] ) && ! empty( $customers_metadata['billing_postcode'] ) ) {
					$wpsslw_value = (string) $customers_metadata['billing_postcode'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Billing Country' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['billing_country'] ) && ! empty( $customers_metadata['billing_country'] ) ) {
					$wpsslw_value = $customers_metadata['billing_country'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Billing State' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['billing_state'] ) && ! empty( $customers_metadata['billing_state'] ) ) {
					if ( array_key_exists( $customers_metadata['billing_country'][0], $countries ) ) {
						$states = $countries_obj->get_states( $customers_metadata['billing_country'][0] );
						if ( ! empty( $states ) && array_key_exists( $customers_metadata['billing_state'][0], $states ) ) {
							$wpsslw_value = $states[ $customers_metadata['billing_state'][0] ];
						} elseif ( empty( $states ) ) {
							$wpsslw_value = (string) $customers_metadata['billing_state'][0];
						} else {
							$wpsslw_value = '';
						}
					}
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Billing Phone Number' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['billing_phone'] ) && ! empty( $customers_metadata['billing_phone'] ) ) {
					$wpsslw_value = (string) $customers_metadata['billing_phone'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Billing EmailID' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['billing_email'] ) && ! empty( $customers_metadata['billing_email'] ) ) {
					$wpsslw_value = (string) $customers_metadata['billing_email'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Shipping FirstName' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['shipping_first_name'] ) && ! empty( $customers_metadata['shipping_first_name'] ) ) {
					$wpsslw_value = (string) $customers_metadata['shipping_first_name'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Shipping LastName' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['shipping_last_name'] ) && ! empty( $customers_metadata['shipping_last_name'] ) ) {
					$wpsslw_value = (string) $customers_metadata['shipping_last_name'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Shipping Company Name' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['shipping_company'] ) && ! empty( $customers_metadata['shipping_company'] ) ) {
					$wpsslw_value = (string) $customers_metadata['shipping_company'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Shipping Address1' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['shipping_address_1'] ) && ! empty( $customers_metadata['shipping_address_1'] ) ) {
					$wpsslw_value = (string) $customers_metadata['shipping_address_1'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Shipping Address2' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['shipping_address_2'] ) && ! empty( $customers_metadata['shipping_address_2'] ) ) {
					$wpsslw_value = (string) $customers_metadata['shipping_address_2'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Shipping City' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['shipping_city'] ) && ! empty( $customers_metadata['shipping_city'] ) ) {
					$wpsslw_value = (string) $customers_metadata['shipping_city'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Shipping Postcode / ZIP' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['shipping_postcode'] ) && ! empty( $customers_metadata['shipping_postcode'] ) ) {
					$wpsslw_value = (string) $customers_metadata['shipping_postcode'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Shipping Country' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['shipping_country'] ) && ! empty( $customers_metadata['shipping_country'] ) ) {
					$wpsslw_value = $customers_metadata['shipping_country'][0];
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
			if ( 'Shipping State' === (string) $wpsslw_headers_name ) {
				if ( isset( $customers_metadata['shipping_state'] ) && ! empty( $customers_metadata['shipping_state'] ) ) {
					if ( array_key_exists( $customers_metadata['shipping_country'][0], $countries ) ) {
						$states = $countries_obj->get_states( $customers_metadata['shipping_country'][0] );
						if ( ! empty( $states ) && array_key_exists( $customers_metadata['shipping_state'][0], $states ) ) {
							$wpsslw_value = $states[ $customers_metadata['shipping_state'][0] ];
						} elseif ( empty( $states ) ) {
							$wpsslw_value = (string) $customers_metadata['shipping_state'][0];
						} else {
							$wpsslw_value = '';
						}
					}
				} else {
					$wpsslw_value = '';
				}
				return $wpsslw_value;
			}
		}
	}
	new WPSSLW_Customer_Headers();
endif;
