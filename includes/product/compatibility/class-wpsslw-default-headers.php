<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSLW_Default_Headers' ) ) :
	/**
	 * Class WPSSLW_Default_Headers.
	 */
	class WPSSLW_Default_Headers extends WPSSLW_Product_Utils {
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

			add_filter( 'wpsyncsheets_product_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {

			$wpsslw_wooproduct_headers['common'] = array(
				'post_title'            => 'Product Name',

				'post_status'           => 'Product Status',

				'post_name'             => 'Product Slug',

				'post_link'             => 'Product Link',

				'category_ids'          => 'Product Categories',

				'product_category_ids'  => 'Product Category Ids',

				'post_excerpt'          => 'Product Short Description',

				'post_content'          => 'Product Description',

				'_sku'                  => 'Product SKU',

				'type'                  => 'Product Type',

				'raw_image'             => 'Product Image',

				'product_image_preview' => 'Product Image Preview',

				'tag_ids'               => 'Product Tags',

				'attributes'            => 'Product Attribute',

				'_regular_price'        => 'Product Regular Price',

				'_sale_price'           => 'Product Sale Price',

				'_weight'               => 'Product Weight',

				'_height'               => 'Product Height',

				'_width'                => 'Product Width',

				'_length'               => 'Product Length',

				'_dimensions'           => 'Product Dimensions',

			);
			self::$wpsslw_headers = $wpsslw_wooproduct_headers;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 * @return array $headers
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpsslw_headers ) ) {
				$headers['WPSSLW_Default_Headers'] = self::$wpsslw_headers;
			}
			return $headers;
		}
		/**
		 * Get Product price.
		 *
		 * @param mixed int or float $price price of product to be formatted.
		 * @param array              $args argument to format price.
		 * @return mixed int or float $price
		 */
		public static function wpsslw_price( $price, $args = array() ) {
			$args = apply_filters(
				'wc_price_args',
				wp_parse_args(
					$args,
					array(
						'ex_tax_label'       => false,
						'currency'           => '',
						'decimal_separator'  => wc_get_price_decimal_separator(),
						'thousand_separator' => wc_get_price_thousand_separator(),
						'decimals'           => wc_get_price_decimals(),
						'price_format'       => get_woocommerce_price_format(),
					)
				)
			);

			$original_price = $price;

			// Convert to float to avoid issues on PHP 8.
			$price = (float) $price;

			$unformatted_price = $price;
			$negative          = $price < 0;

			/**
			 * Filter raw price.
			 *
			 * @param float        $raw_price      Raw price.
			 * @param float|string $original_price Original price as float, or empty string. Since 5.0.0.
			 */
			$price = $negative ? $price * -1 : $price;
			if ( $args['decimals'] < 1 ) {
				$args['decimals'] = apply_filters( 'wpssw_price_number_decimal', 2 );
			}

			/**
			 * Filter formatted price.
			 *
			 * @param float        $formatted_price    Formatted price.
			 * @param float        $price              Unformatted price.
			 * @param int          $decimals           Number of decimals.
			 * @param string       $decimal_separator  Decimal separator.
			 * @param string       $thousand_separator Thousand separator.
			 * @param float|string $original_price     Original price as float, or empty string. Since 5.0.0.
			 */
			$price = apply_filters( 'formatted_woocommerce_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'], $original_price );

			if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
				$price = wc_trim_zeros( $price );
			}
			return $price;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpsslw_headers_name Header name.
		 * @param object $wpsslw_product product object.
		 * @param bool   $wpsslw_child true if child product.
		 * @param array  $wpsslw_custom_value custom value.
		 */
		public static function get_value( $wpsslw_headers_name, $wpsslw_product, $wpsslw_child, $wpsslw_custom_value = array() ) {
			return self::prepare_value( $wpsslw_headers_name, $wpsslw_product, $wpsslw_child, $wpsslw_custom_value );
		}
		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpsslw_headers_name Header name.
		 * @param object $wpsslw_product product object.
		 * @param bool   $wpsslw_child true if child product.
		 * @param array  $wpsslw_custom_value custom value.
		 */
		public static function prepare_value( $wpsslw_headers_name, $wpsslw_product, $wpsslw_child, $wpsslw_custom_value ) {

			$wpsslw_value      = '';
			$wpsslw_headerlist = self::get_header_list();

			if ( 'Product Name' === (string) $wpsslw_headers_name ) {
				if ( 'variation' === (string) $wpsslw_product->get_type() && ! empty( $wpsslw_product->get_children() ) && true === (bool) $wpsslw_child ) {
					$wpsslw_value = $wpsslw_product->get_name();
				} else {
					$wpsslw_value = $wpsslw_product->get_title();
				}
				return $wpsslw_value;
			}
			if ( 'Product Short Description' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				$wpsslw_value = $wpsslw_product->get_short_description();
				return $wpsslw_value;
			}
			if ( 'Product Description' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				if ( 'variation' !== (string) $wpsslw_product->get_type() ) {
					$wpsslw_value = $wpsslw_product->get_description();
				}
				return $wpsslw_value;
			}
			if ( 'Product Status' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				if ( 'variation' !== (string) $wpsslw_product->get_type() ) {
					$wpsslw_value = $wpsslw_product->get_status();
				}
				return $wpsslw_value;
			}
			if ( 'Product Slug' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				$wpsslw_value = $wpsslw_product->get_slug();
				return $wpsslw_value;
			}
			if ( 'Product Link' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				if ( 'variation' === (string) $wpsslw_product->get_type() && ! empty( $wpsslw_product->get_children() ) ) {
					$wpsslw_value = get_permalink( $wpsslw_product->get_id() );
				} else {
					$wpsslw_value = get_permalink( $wpsslw_product->get_id() );
				}
				return $wpsslw_value;
			}
			if ( ( 'Product Categories' === (string) $wpsslw_headers_name || 'Product Category Ids' === (string) $wpsslw_headers_name ) && true !== (bool) $wpsslw_child ) {
				if ( ! empty( $wpsslw_product->get_parent_id() ) && 'grouped' !== (string) $wpsslw_product->get_type() ) {
					$pid = $wpsslw_product->get_parent_id();
				} else {
					$pid = $wpsslw_product->get_id();
				}
				if ( 'Product Category Ids' === (string) $wpsslw_headers_name ) {
					$product_cats = wp_get_post_terms( $pid, 'product_cat', array( 'fields' => 'ids' ) );
					if ( is_array( $product_cats ) && ! empty( $product_cats ) ) {
						sort( $product_cats );
					}
				} else {
					$product_cats = wp_get_post_terms( $pid, 'product_cat', array( 'fields' => 'names' ) );
				}
				$product_category = array();
				if ( is_array( $product_cats ) && ! empty( $product_cats ) ) {
					$product_category = $product_cats;
				}
				if ( 'variation' !== (string) $wpsslw_product->get_type() ) {
					$wpsslw_value = implode( ', ', $product_category );
				}
				return $wpsslw_value;
			}
			if ( 'Product SKU' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				if ( 'variation' !== (string) $wpsslw_product->get_type() ) {
					$wpsslw_value = $wpsslw_product->get_sku();
				}
				return $wpsslw_value;
			}
			if ( 'Product Type' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				$wpsslw_value = $wpsslw_product->get_type();
				return $wpsslw_value;
			}
			if ( ( 'Product Image' === (string) $wpsslw_headers_name || 'Product Image Preview' === (string) $wpsslw_headers_name ) && true !== (bool) $wpsslw_child ) {
				$attachment[0]    = '';
				$image_attachment = array();
				if ( has_post_thumbnail( $wpsslw_product->get_id() ) ) {
					$attachment_ids = get_post_thumbnail_id( $wpsslw_product->get_id() );
					$attachment     = wp_get_attachment_image_src( $attachment_ids, 'full' );
					if ( ! isset( $attachment[0] ) ) {
						$attachment[0] = '';
					}
					if ( 'Product Image Preview' === (string) $wpsslw_headers_name ) {
						$image_attachment[] = '=IMAGE("' . $attachment[0] . '")';
					} else {
						$image_attachment[] = $attachment[0];
					}
				}
				if ( 'Product Image' === (string) $wpsslw_headers_name ) {
					$gallery_image_ids = $wpsslw_product->get_gallery_image_ids();
					$gallery_image_ids = array_filter( $gallery_image_ids );
					if ( ! empty( $gallery_image_ids ) ) {
						foreach ( $gallery_image_ids as $gallery_image ) {
							$gallery_attachment = wp_get_attachment_image_src( $gallery_image, 'full' );

							if ( isset( $gallery_attachment[0] ) ) {
								$image_attachment[] = $gallery_attachment[0];
							}
						}
					}
				}
				if ( is_array( $image_attachment ) && ! empty( $image_attachment ) ) {
					$images = implode( '|', $image_attachment );
				} else {
					$images = '';
				}
				$wpsslw_value = $images;
				return $wpsslw_value;
			}
			if ( 'Product Tags' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				$taglist = array();
				// get an array of the WP_Term objects for a defined product ID.
				$terms = wp_get_post_terms( $wpsslw_product->get_id(), 'product_tag' );
				// Loop through each product tag for the current product.
				if ( count( $terms ) > 0 ) {
					foreach ( $terms as $term ) {
						$term_name = $term->name; // Product tag Name.
						// Set the product tag names in an array.
						$taglist[] = $term_name;
					}
				}
				$wpsslw_value = implode( ', ', $taglist );
				return $wpsslw_value;
			}
			if ( 'Product Attribute' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				$attributes_value = array();
				$attributes       = $wpsslw_product->get_attributes();
				foreach ( $attributes as $attrkey => $attrvalue ) {
					if ( $attrvalue->get_id() < 1 && false === strpos( $attrkey, 'pa_' ) ) {
						$attributes_value[] = $attrvalue->get_name();
					} else {
						$attrkey            = rawurldecode( $attrkey );
						$attributes_value[] = wc_attribute_label( $attrkey );
					}
				}
				if ( 'variation' !== (string) $wpsslw_product->get_type() ) {
					$wpsslw_value = implode( '|', $attributes_value );
				}
				return $wpsslw_value;
			}
			if ( 'Product Regular Price' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				$wpsslw_value = $wpsslw_product->get_regular_price();
				$wpsslw_value = self::wpsslw_price( $wpsslw_value );
				return $wpsslw_value;
			}
			if ( 'Product Sale Price' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				$wpsslw_value = $wpsslw_product->get_sale_price();
				$wpsslw_value = self::wpsslw_price( $wpsslw_value );
				return $wpsslw_value;
			}

			if ( 'Product Weight' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				$wpsslw_value = $wpsslw_product->get_weight();
				return $wpsslw_value;
			}
			if ( 'Product Height' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				$wpsslw_value = $wpsslw_product->get_height();
				return $wpsslw_value;
			}
			if ( 'Product Width' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				$wpsslw_value = $wpsslw_product->get_width();
				return $wpsslw_value;
			}
			if ( 'Product Length' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				$wpsslw_value = $wpsslw_product->get_length();
				return $wpsslw_value;
			}
			if ( 'Product Dimensions' === (string) $wpsslw_headers_name && true !== (bool) $wpsslw_child ) {
				$wpsslw_value = html_entity_decode( wc_format_dimensions( $wpsslw_product->get_dimensions( false ) ), ENT_QUOTES );
				return $wpsslw_value;
			}
		}
	}
	new WPSSLW_Default_Headers();
endif;
