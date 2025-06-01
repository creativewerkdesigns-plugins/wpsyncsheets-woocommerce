<?php
/**
 * Main WPSyncSheetsWooCommerce\WPSSLW_Google_API namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-woocommerce
 */

use WPSyncSheetsWooCommerce\WPSSLW_Google_API_Functions;
use Automattic\WooCommerce\Utilities\OrderUtil;
/**
 * Handle plugin installation upon activation.
 *
 * @since 1.0.0
 */
class WPSSLW_Settings {
	/**
	 * Url for plugin api settings documentation.
	 *
	 * @var $doc_sheet_setting .
	 */
	protected static $doc_sheet_setting = 'https://docs.wpsyncsheets.com/wpssw-google-sheets-api-settings/';
	/**
	 * Url for plugin documentation.
	 *
	 * @var $doc_url
	 */
	protected $doc_url = 'https://docs.wpsyncsheets.com/wpssw-introduction/';
	/**
	 * Url for Pro Plugin.
	 *
	 * @var $pro_version_url
	 */
	protected $pro_version_url = 'https://www.wpsyncsheets.com/wpsyncsheets-for-woocommerce/';
	/**
	 * Url for Pro Plugin Compatibility.
	 *
	 * @var $plugins_compatibility_url
	 */
	protected $plugins_compatibility_url = 'https://docs.wpsyncsheets.com/wpssw-plugins-compatibility/';
	/**
	 * Instance of class.
	 *
	 * @var $instance Instance variable of class.
	 */
	protected static $instance = null;
	/**
	 * Instance of WPSSLW_Google_API_Functions class.
	 *
	 * @var $instance_api Instance variable of WPSSLW_Google_API_Functions class.
	 */
	protected static $instance_api = null;
	/**
	 * Default status of post
	 *
	 * @var $wpsslw_default_status
	 */
	protected static $wpsslw_default_status = array( 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' );
	/**
	 * Default sheets
	 *
	 * @var $wpsslw_default_sheets
	 */
	protected static $wpsslw_default_sheets = array( 'Pending Orders', 'Processing Orders', 'On Hold Orders', 'Completed Orders', 'Cancelled Orders', 'Refunded Orders', 'Failed Orders', 'Trash Orders' );
	/**
	 * Default status slug of post
	 *
	 * @var $wpsslw_default_status_slug
	 */
	protected static $wpsslw_default_status_slug = array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' );
	/**
	 * Initialization.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		$wpsslw_include = new WPSSLW_Include_Action();
		$wpsslw_include->wpsslw_include_plugin_hook();
		self::wpsslw_google_api();
	}
	/**
	 * Main WPSSLW_Settings Instance.
	 *
	 * @since 1.0.0
	 *
	 * @return instance
	 */
	public static function wpsslw_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Get an instance of WPSSLW_Google_API_Functions class.
	 *
	 * @return WPSSLW_Google_API_Functions class instance
	 */
	public static function wpsslw_google_api() {
		if ( null === self::$instance_api ) {
			self::$instance_api = new WPSSLW_Google_API_Functions();
		}
		return self::$instance_api;
	}

	/**
	 * Register a plugin menu page.
	 */
	public static function wpsslw_menu_page() {
		global $admin_page_hooks, $_parent_pages;
		if ( ! isset( $admin_page_hooks['wpsyncsheets_lite'] ) ) {
			$wpsslw_page = add_menu_page(
				esc_attr__( 'WPSyncSheets Lite', 'wpssw' ),
				'WPSyncSheets Lite',
				'manage_options',
				'wpsyncsheets_lite',
				'',
				WPSSLW_URL . 'assets/images/dashicons-wpsyncsheets.svg',
				90
			);
		}
		add_submenu_page( 'wpsyncsheets_lite', 'WPSyncSheets Lite For WooCommerce', 'For WooCommerce', 'manage_options', 'wpsyncsheets-woocommerce', __CLASS__ . '::wpsslw_plugin_page', 1 );
		if ( ! isset( $_parent_pages['documentation'] ) ) {
			add_submenu_page( 'wpsyncsheets_lite', 'Documentation', '<div class="wpsslw-support">Documentation</div>', 'manage_options', 'documentation', __CLASS__ . '::wpsslw_handle_external_redirects', 20 );
		}
		self::remove_duplicate_submenu_page();
	}

	/**
	 * Documentation and Support Page Link.
	 *
	 * Redirect the documentation and support page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public static function wpsslw_handle_external_redirects() {
		// phpcs:ignore
		if ( empty( $_GET['page'] ) ) {
			return;
		}
		// phpcs:ignore
		if ( 'documentation' === $_GET['page'] ) {
			// phpcs:ignore
			wp_redirect( WPSSLW_DOC_MENU_URL );
			die;
		}
	}
	/**
	 * Loads the plugin language files.
	 * Load only the wpsslw translation.
	 */
	public static function wpsslw_load_textdomain() {
		load_plugin_textdomain( 'wpsslw', false, WPSSLW_DIRECTORY . '/languages/' );
	}

	/**
	 * Remove duplicate submenu
	 * Submenu page hack: Remove the duplicate WPSyncSheets Plugin link on subpages
	 */
	public static function remove_duplicate_submenu_page() {
		remove_submenu_page( 'wpsyncsheets_lite', 'wpsyncsheets_lite' );
	}
	/**
	 * Enqueue css and js files
	 */
	public static function wpsslw_load_custom_wp_admin_style() {
		// @codingStandardsIgnoreStart.
		if ( is_admin() && ( isset( $_GET['page'] ) && ( 'wpsyncsheets-woocommerce' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) ) ) {
			// @codingStandardsIgnoreEnd.
			wp_register_script( 'wpsslw_wp_admin_js', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/wpsslw-admin-script.js', array(), WPSSLW_VERSION, true );
			wp_localize_script(
				'wpsslw_wp_admin_js',
				'admin_ajax_object',
				array(
					'ajaxurl'          => admin_url( 'admin-ajax.php' ),
					'sync_nonce_token' => wp_create_nonce( 'sync_nonce' ),
				)
			);
			wp_enqueue_script( 'wpsslw_wp_admin_js' );
			wp_register_style( 'wpsslw_wp_admin_css', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/wpsslw-admin-style.css', false, WPSSLW_VERSION );
			wp_enqueue_style( 'wpsslw_wp_admin_css' );

			wp_register_script( 'wpsslw-wp-admin-ui', WPSSLW_URL . 'assets/js/wpsslw-ui.js', array(), WPSSLW_VERSION, true );
			wp_enqueue_script( 'wpsslw-wp-admin-ui' );

			wp_register_style( 'wpsslw-wp-admin-ui', WPSSLW_URL . 'assets/css/wpsslw-ui.css', false, WPSSLW_VERSION );
			wp_enqueue_style( 'wpsslw-wp-admin-ui' );

			wp_register_script( 'wpsslw-bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js', array(), '3.4.1', true );
			wp_enqueue_script( 'wpsslw-bootstrap' );
		}
		wp_add_inline_style(
	        'wp-admin',
	        '#toplevel_page_wpsyncsheets_lite .wp-menu-image img {
	            width: 20px;
	            height: auto;
	            filter: brightness(100);
	        }
	        #toplevel_page_wpsyncsheets_lite:hover .wp-menu-image img {
	            filter: unset;
	        }
	        #toplevel_page_wpsyncsheets_lite.wp-has-current-submenu .wp-menu-image img {
	            filter: brightness(100) !important;
	        }'
	    );
	}
	/**
	 * Enqueue css and js files
	 */
	public static function wpsslw_selectively_enqueue_admin_script() {
		wp_enqueue_script( 'wpsslw-general-script', WPSSLW_URL . 'assets/js/wpsslw-general.js', WPSSLW_VERSION, true, false );
	}
	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $wpsslw_links Plugin Row Meta.
	 * @param mixed $wpsslw_file  Plugin Base file.
	 * @return  array
	 */
	public static function wpsslw_plugin_row_meta( $wpsslw_links, $wpsslw_file ) {
		if ( 'wpsyncsheets-woocommerce/wpsyncsheets-lite-woocommerce.php' === (string) $wpsslw_file ) {
			$wpsslw_row_meta = array(
				'docs' => '<a href="' . esc_url( self::wpsslw_instance()->doc_url ) . '" title="' . esc_attr( __( 'View Documentation', 'wpssw' ) ) . '" target="_blank">' . __( 'View Documentation', 'wpssw' ) . '</a>',
			);
			return array_merge( $wpsslw_links, $wpsslw_row_meta );
		}
		return (array) $wpsslw_links;
	}

	/**
	 * Get wpssw options from database
	 *
	 * @param string $key .
	 * @param string $type .
	 * @return string
	 */
	public static function wpsslw_option( $key = '', $type = '' ) {
		$value = self::$instance_api->wpsslw_option( $key, $type );
		return $value;
	}
	/**
	 * Update wpssw options
	 *
	 * @param string $key .
	 * @param string $value .
	 */
	public static function wpsslw_update_option( $key = '', $value = '' ) {
		self::$instance_api->wpsslw_update_option( $key, $value );
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
		if ( isset( $wpsslw_headers['WPSSLW_Default'][ $array_key ] ) ) {
			$wpsslw_result = $wpsslw_headers['WPSSLW_Default'][ $array_key ];
			unset( $wpsslw_headers['WPSSLW_Default'][ $array_key ] );
		}
		return $wpsslw_result;
	}
	/**
	 * Convert a multi-dimensional array into a single-dimensional array.
	 *
	 * @param array $wpsslw_array .
	 * @return array
	 */
	public static function wpsslw_array_flatten( $wpsslw_array ) {
		if ( ! is_array( $wpsslw_array ) ) {
			return false;
		}
		$wpsslw_result = array();
		foreach ( $wpsslw_array as $wpsslw_key => $wpsslw_value ) {
			if ( is_array( $wpsslw_value ) ) {
				$wpsslw_result = array_merge( $wpsslw_result, self::wpsslw_array_flatten( array_values( $wpsslw_value ) ) );
			} else {
				$wpsslw_result[ $wpsslw_key ] = trim( $wpsslw_value );
			}
		}
		return $wpsslw_result;
	}

	/**
	 * Freeze the headers of the sheet
	 *
	 * @param string $wpsslw_spreadsheetid .
	 * @param int    $wpsslw_freeze .
	 * @param string $oddcolor .
	 * @param string $evencolor .
	 * @param string $wpsslw_color .
	 * @param int    $wpsslw_freeze_header .
	 * @param bool   $rowcolor_disabled .
	 * @param array  $wpsslw_sheets .
	 */
	public static function wpsslw_freeze_header( $wpsslw_spreadsheetid, $wpsslw_freeze, $oddcolor, $evencolor, $wpsslw_color, $wpsslw_freeze_header, $rowcolor_disabled = true, $wpsslw_sheets = array() ) {

		$wpsslw_response    = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
		$wpsslw_requestbody = array();
		foreach ( $wpsslw_response->getSheets() as $wpsslw_key => $wpsslw_value ) {
			// TODO: Assign values to desired properties of `requestBody`.
			if ( isset( $wpsslw_value['properties']['title'] ) && in_array( $wpsslw_value['properties']['title'], $wpsslw_sheets, true ) ) {
				if ( $wpsslw_freeze_header ) {
					$wpsslw_requestbody[] = self::$instance_api->freezeobject( $wpsslw_value['properties']['sheetId'], $wpsslw_freeze );
				}
			}
		}
		if ( ! empty( $wpsslw_requestbody ) ) {
			try {

				$requestbody                    = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
					array( 'requests' => $wpsslw_requestbody )
				);
				$requestobject                  = array();
				$requestobject['spreadsheetid'] = $wpsslw_spreadsheetid;
				$requestobject['requestbody']   = $requestbody;
				self::$instance_api->formatsheet( $requestobject );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
		if ( $wpsslw_color ) {
			self::wpsslw_change_row_background_color( $wpsslw_response->getSheets(), $wpsslw_spreadsheetid, $wpsslw_value['properties']['sheetId'], $oddcolor, $evencolor, $rowcolor_disabled, $wpsslw_sheets );
		}
		// phpcs:ignore
		return;
	}
	/**
	 * Change the row's background color
	 *
	 * @param array  $wpsslw_response .
	 * @param string $wpsslw_spreadsheetid .
	 * @param int    $sheetid .
	 * @param string $oddcolor .
	 * @param string $evencolor .
	 * @param bool   $rowcolor_disabled .
	 * @param array  $wpsslw_sheets .
	 */
	public static function wpsslw_change_row_background_color( $wpsslw_response, $wpsslw_spreadsheetid, $sheetid, $oddcolor, $evencolor, $rowcolor_disabled, $wpsslw_sheets ) {
		if ( ! self::$instance_api->checkcredenatials() ) {
			return;
		}

		list($r, $g, $b)    = array_map(
			function( $c ) {
				return hexdec( str_pad( $c, 2, $c ) );
			},
			str_split( ltrim( $oddcolor, '#' ), strlen( $oddcolor ) > 4 ? 2 : 1 )
		);
		list($er, $eg, $eb) = array_map(
			function( $c ) {
				return hexdec( str_pad( $c, 2, $c ) );
			},
			str_split( ltrim( $evencolor, '#' ), strlen( $evencolor ) > 4 ? 2 : 1 )
		);

		$request       = array();
		$colorrequests = array();
		foreach ( $wpsslw_response as $wpsslw_key => $wpsslw_value ) {
			if ( isset( $wpsslw_value['properties']['title'] ) && ! in_array( $wpsslw_value['properties']['title'], $wpsslw_sheets, true ) ) {
				continue;
			}

			// For Add Conditional Formatting.
			$range                  = array(
				'sheetId' => $wpsslw_value['properties']['sheetId'],
			);
			$param                  = array();
			$param['spreadsheetid'] = $wpsslw_spreadsheetid;
			$param['range']         = $range;
			$param['r']             = $r;
			$param['g']             = $g;
			$param['b']             = $b;
			$param['er']            = $er;
			$param['eg']            = $eg;
			$param['eb']            = $eb;
			$colorrequests[]        = self::$instance_api->addconditionalformatruleobject( $param );
			// END.

			// For Remove Conditional Formatting.
			// phpcs:ignore
			if ( ! isset( $wpsslw_value->conditionalFormats ) ) {
				continue;
			}
			// phpcs:ignore
			$rules = $wpsslw_value->conditionalFormats;

			foreach ( $rules as $rulekey => $rule ) {
				// @codingStandardsIgnoreStart.
				if ( isset( $rule->booleanRule->condition->values ) ) {
					$condition = $rule->booleanRule->condition->values;
					// @codingStandardsIgnoreEnd.
					foreach ( $condition as $key => $value ) {
						// phpcs:ignore
						if ( isset( $value->userEnteredValue ) && ( '=$A:$A=""' === trim( $value->userEnteredValue ) || trim( '=MOD($A:$A,2)=0' ) === $value->userEnteredValue || trim( '=MOD($A:$A,2)=1' ) === $value->userEnteredValue ) ) {
							$request[] = new Google_Service_Sheets_Request(
								array(
									'deleteConditionalFormatRule' => array(
										'sheetId' => $wpsslw_value['properties']['sheetId'],
										'index'   => $rulekey,
									),
								)
							);
						}
					}
				}
			}
			// End.
		}

		// Remove Old Color Code.
		if ( ! empty( $request ) ) {
			try {
				$request            = array_reverse( $request );
				$batchupdaterequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
					array(
						'requests' => array( $request ),
					)
				);
				self::$instance_api->updatebachrequestsrowcolor( $wpsslw_spreadsheetid, $batchupdaterequest );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}

		if ( $rowcolor_disabled ) {
			return;
		}
		try {
			if ( ! empty( $colorrequests ) ) {
				$batchupdaterequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
					array(
						'requests' => $colorrequests,
					)
				);
				self::$instance_api->addconditionalformatrule( $wpsslw_spreadsheetid, $batchupdaterequest );
			}
		} catch ( Exception $e ) {
			echo esc_html( 'Message: ' . $e->getMessage() );
		}
		// phpcs:ignore
		return;
	}

	/**
	 * Delete sheets from spreadsheet
	 *
	 * @param string $wpsslw_spreadsheetid .
	 * @param array  $wpsslw_remove_sheet .
	 * @param array  $wpsslw_existingsheets .
	 */
	public static function wpsslw_delete_sheet( $wpsslw_spreadsheetid, $wpsslw_remove_sheet = array(), $wpsslw_existingsheets = array() ) {
		foreach ( $wpsslw_remove_sheet as $wpsslw_sheetname ) {
			$wpsslw_sid = array_search( $wpsslw_sheetname, $wpsslw_existingsheets, true );
			try {
				$param                  = array();
				$param['spreadsheetid'] = $wpsslw_spreadsheetid;
				$wpsslw_response        = self::$instance_api->deletesheetobject( $param, $wpsslw_sid );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
	}
	/**
	 * Find Related Class.
	 *
	 * @param array  $headers Headers array.
	 * @param string $headername Header Name.
	 */
	public static function wpsslw_find_class( $headers, $headername ) {
		foreach ( $headers as $classname => $classheaders ) {
			if ( in_array( $headername, $classheaders, true ) ) {
				return $classname;
			}
		}
		return '';
	}
	/**
	 * Clean Order data array.
	 *
	 * @param array $wpsslw_array Order data array.
	 * @param int   $wpsslw_max max value.
	 * @return array $wpsslw_array
	 */
	public static function wpsslw_cleanarray( $wpsslw_array, $wpsslw_max ) {
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
	 * Convert to Integer.
	 *
	 * @param string $data Entry ids array.
	 */
	public static function wpsslw_convert_string( $data ) {
		$data = array_map(
			function( $element ) {
				return ( is_string( $element ) ? (string) $element : $element );
			},
			$data
		);
		return $data;
	}
	/**
	 * Convert to Integer.
	 *
	 * @param string $data Entry ids array.
	 */
	public static function wpsslw_convert_int( $data ) {
		$data = array_map(
			function( $element ) {
				return ( is_numeric( $element ) ? (int) $element : $element );
			},
			$data
		);
		return $data;
	}
	/**
	 * Check that given spreadsheet and sheet exists or not.
	 *
	 * @param string $wpsslw_spreadsheetid Spreadsheet ID.
	 * @param string $wpsslw_sheetname Sheet Name.
	 */
	public static function wpsslw_check_sheet_exist( $wpsslw_spreadsheetid, $wpsslw_sheetname ) {
		$wpsslw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
		if ( empty( $wpsslw_spreadsheetid ) || empty( $wpsslw_sheetname ) ) {
			return false;
		}
		if ( ! array_key_exists( $wpsslw_spreadsheetid, $wpsslw_spreadsheets_list ) ) {
			return false;
		} else {
			$wpsslw_response            = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
			$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
			$wpsslw_existingsheets      = array_flip( $wpsslw_existingsheetsnames );
			if ( ! in_array( $wpsslw_sheetname, $wpsslw_existingsheets, true ) ) {
				return false;
			}
		}
		return true;
	}
	/**
	 * Get Sheet's Column index from total headers
	 *
	 * @param int $number .
	 * @return string $letter
	 */
	public static function wpsslw_get_column_index( $number ) {
		if ( $number <= 0 ) {
			return null;
		}
		$temp;
		$letter = '';
		while ( $number > 0 ) {
			$temp   = ( $number - 1 ) % 26;
			$letter = chr( $temp + 65 ) . $letter;
			$number = ( $number - $temp - 1 ) / 26;
		}
		return $letter;
	}
	/**
	 *
	 * Move a post or product to the Trash
	 *
	 * @param int $wpsslw_order_id .
	 */
	public static function wpsslw_wcgs_trash( $wpsslw_order_id ) {

		$wpsslw_order   = wc_get_order( $wpsslw_order_id );
		$wpsslw_product = wc_get_product( $wpsslw_order_id );
		$wpsslw_coupon  = new WC_Coupon( $wpsslw_order_id );
		if ( isset( $wpsslw_order ) && ! empty( $wpsslw_order ) ) {
			global $post_type;
			if ( 'shop_order' !== (string) $post_type ) {
				return;
			}
			$wpsslw_sheets = array_filter( (array) self::wpsslw_option( 'wpssw_sheets' ) );
			if ( ! $wpsslw_sheets ) {
				$wpsslw_sheets = WPSSLW_Order::wpsslw_prepare_sheets();
			}
			if ( $wpsslw_order ) {
				$wpsslw_old_status = $wpsslw_order->get_status();
				/*Remove order detail from old status*/
				WPSSLW_Order::wpsslw_woo_order_status_change_custom( $wpsslw_order_id, $wpsslw_old_status, 'trash' );
			}
		}
		if ( isset( $wpsslw_product ) && ! empty( $wpsslw_product ) ) {
			$wpsslw_product_spreadsheet_setting = self::wpsslw_option( 'wpssw_product_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpsslw_product_spreadsheet_setting ) {
				return;
			}

			$wpsslw_spreadsheetid = self::wpsslw_option( 'wpssw_product_spreadsheet_id' );
			$wpsslw_sheetname     = 'All Products';

			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) ) {
				$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				
				// @codingStandardsIgnoreEnd.
				if ( (int) $wpsslw_order_id === (int) $changed_posts[ count( $changed_posts ) - 1 ] ) {

					sort( $changed_posts );
					$settings = array(
						'setting'        => 'product',
						'setting_enable' => $wpsslw_product_spreadsheet_setting,
						'spreadsheet_id' => $wpsslw_spreadsheetid,
						'sheetname'      => $wpsslw_sheetname,
					);
					self::wpsslw_multiple_update_data( $changed_posts, $settings, false, 'delete' );
				}
				return;
			}

			if ( ! empty( $wpsslw_spreadsheetid ) ) {
				if ( ! self::wpsslw_check_sheet_exist( $wpsslw_spreadsheetid, $wpsslw_sheetname ) ) {
					return;
				}
				$wpsslw_total          = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheetname );
				$response              = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpsslw_sheetid        = $wpsslw_existingsheets[ $wpsslw_sheetname ];
				if ( ! empty( $wpsslw_product->get_parent_id() ) && 'variation' === (string) $wpsslw_product->get_type() ) {
					$wpsslw_order_id = $wpsslw_product->get_parent_id();
				}
				$wpsslw_total_values     = $wpsslw_total->getValues();
				$variation_product_id    = array_search( 'Product Id', $wpsslw_total_values[0], true );
				$variation_product_index = array_column( $wpsslw_total_values, $variation_product_id );
				foreach ( $variation_product_index as $index => $index_ids ) {
					if ( ! isset( $index_ids['0'] ) ) {
						unset( $variation_product_index[ $index ] );
					}
				}
				$product_keys = array_keys( self::wpsslw_convert_int( $variation_product_index ), (int) $wpsslw_order_id, true );
				if ( $wpsslw_sheetid ) {
					$startindex           = $product_keys[0];
					$endindex             = $product_keys[0] + count( $product_keys );
					$param                = array();
					$param                = self::$instance_api->prepare_param( $wpsslw_sheetid, $startindex, $endindex );
					$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				}
				try {
					if ( ! empty( $deleterequestarray ) ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpsslw_spreadsheetid;
						$param['requestarray']  = $deleterequestarray;
						self::$instance_api->updatebachrequests( $param );
					}
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
		}
		if ( ! empty( $wpsslw_coupon ) && empty( $wpsslw_order ) && empty( $wpsslw_product ) ) {
			$wpsslw_spreadsheetid              = self::wpsslw_option( 'wpssw_coupon_spreadsheet_id' );
			$wpsslw_sheetname                  = 'All Coupons';
			$wpsslw_coupon_spreadsheet_setting = self::wpsslw_option( 'wpssw_coupon_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpsslw_coupon_spreadsheet_setting ) {
				return;
			}

			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) ) {
				$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				
				// @codingStandardsIgnoreEnd.
				if ( (int) $wpsslw_order_id === (int) $changed_posts[ count( $changed_posts ) - 1 ] ) {

					$settings = array(
						'setting'        => 'coupon',
						'setting_enable' => $wpsslw_coupon_spreadsheet_setting,
						'spreadsheet_id' => $wpsslw_spreadsheetid,
						'sheetname'      => $wpsslw_sheetname,
					);
					self::wpsslw_multiple_update_data( $changed_posts, $settings, false, 'delete' );

				}
				return;
			}

			if ( ! empty( $wpsslw_spreadsheetid ) ) {
				if ( ! self::wpsslw_check_sheet_exist( $wpsslw_spreadsheetid, $wpsslw_sheetname ) ) {
					return;
				}
				$wpsslw_allentry            = self::$instance_api->get_row_list( $wpsslw_spreadsheetid, $wpsslw_sheetname );
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
				$response                   = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
				$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
				$wpsslw_sheetid             = $wpsslw_existingsheetsnames[ $wpsslw_sheetname ];
				$wpsslw_num                 = array_search( (int) $wpsslw_order_id, self::wpsslw_convert_int( $wpsslw_data ), true );
				if ( $wpsslw_num > 0 ) {
					$wpsslw_startindex      = $wpsslw_num;
					$wpsslw_endindex        = $wpsslw_num + 1;
					$param                  = array();
					$param                  = self::$instance_api->prepare_param( $wpsslw_sheetid, $wpsslw_startindex, $wpsslw_endindex );
					$wpsslw_requestbody     = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
					$param                  = array();
					$param['spreadsheetid'] = $wpsslw_spreadsheetid;
					$param['requestarray']  = $wpsslw_requestbody;
					self::$instance_api->updatebachrequests( $param );
				}
			}
		}
	}

	/**
	 * Untrash a products from the Trash
	 *
	 * @param int $post_id .
	 */
	public static function wpsslw_wcgs_untrash( $post_id ) {
		$wpsslw_product = wc_get_product( $post_id );
		$wpsslw_coupon  = new WC_Coupon( $post_id );

		if ( isset( $wpsslw_product ) && ! empty( $wpsslw_product ) ) {

			global $post_type;
			$wpsslw_post_type = is_object( $post_type ) ? $post_type->name : $post_type;
			// @codingStandardsIgnoreStart.
			if ( ( 'product' !== (string) $wpsslw_post_type ) || ( isset( $_REQUEST['action'] ) && 'untrash' !== sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) ) {
				// @codingStandardsIgnoreEnd.
				return;
			}
			if ( 0 !== (int) $wpsslw_product->get_parent_id() ) {
				return;
			}
			$wpsslw_product_spreadsheet_setting = self::wpsslw_option( 'wpssw_product_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpsslw_product_spreadsheet_setting ) {
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
				if ( (int) $post_id === (int) $changed_posts[ count( $changed_posts ) - 1 ] ) {
					$wpsslw_spreadsheetid = self::wpsslw_option( 'wpssw_product_spreadsheet_id' );
					$wpsslw_sheetname     = 'All Products';
					$settings             = array(
						'setting'        => 'product',
						'setting_enable' => $wpsslw_product_spreadsheet_setting,
						'spreadsheet_id' => $wpsslw_spreadsheetid,
						'sheetname'      => $wpsslw_sheetname,
					);
					self::wpsslw_multiple_update_data( $changed_posts, $settings, false, 'update' );
				}
				return;
			}
			WPSSLW_Product::wpsslw_woocommerce_update_product( $post_id, $wpsslw_product );
		}
		if ( ! empty( $wpsslw_coupon ) && empty( $wpsslw_product ) ) {
			global $post_type;
			$wpsslw_post_type = is_object( $post_type ) ? $post_type->name : $post_type;
			// @codingStandardsIgnoreStart.
			if ( ( 'shop_coupon' !== (string) $wpsslw_post_type ) || ( isset( $_REQUEST['action'] ) && 'untrash' !== sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) ) {
				// @codingStandardsIgnoreEnd.
				return;
			}
			$wpsslw_coupon_spreadsheet_setting = self::wpsslw_option( 'wpssw_coupon_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpsslw_coupon_spreadsheet_setting ) {
				return;
			}

			$wpsslw_spreadsheetid = self::wpsslw_option( 'wpssw_coupon_spreadsheet_id' );
			$wpsslw_sheetname     = 'All Coupons';

			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) || ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) ) {
				if ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) {
					$changed_posts = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['ids'] ) ) );
				} else {
					$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				}
				
				// @codingStandardsIgnoreEnd.
				if ( (int) $post_id === (int) $changed_posts[ count( $changed_posts ) - 1 ] ) {

					$settings = array(
						'setting'        => 'coupon',
						'setting_enable' => $wpsslw_coupon_spreadsheet_setting,
						'spreadsheet_id' => $wpsslw_spreadsheetid,
						'sheetname'      => $wpsslw_sheetname,
					);
					self::wpsslw_multiple_update_data( $changed_posts, $settings, false, 'update' );

				}
				return;
			}
			if ( ! self::wpsslw_check_sheet_exist( $wpsslw_spreadsheetid, $wpsslw_sheetname ) ) {
				return;
			}
			WPSSLW_Coupon::wpsslw_coupon_object_updated_props( $wpsslw_coupon );
		}
	}
	/**
	 * Reset Google API Settings
	 */
	public static function wpsslw_reset_settings() {
		if ( ! current_user_can( 'edit_wpsyncsheets_woocommerce_lite_main_settings' ) ) {
			echo esc_html__( 'You do not have permission to access this page.', 'wpssw' );
			die();
		}
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'save_api_settings' ) ) {
			echo esc_html__( 'Sorry, your nonce did not verifyy.', 'wpsswp' );
			wp_die();
		}
		try {
			$wpsslw_google_settings_value = self::$instance_api->wpsslw_option( 'wpssw_google_settings' );
			$settings                     = array();
			foreach ( $wpsslw_google_settings_value as $key => $value ) {
				$settings[ $key ] = '';
			}
			self::$instance_api->wpsslw_update_option( 'wpssw_google_settings', $settings );
			self::$instance_api->wpsslw_update_option( 'wpssw_google_accessToken', '' );
		} catch ( Exception $e ) {
			echo esc_html( 'Message: ' . $e->getMessage() );
		}
		echo esc_html( 'successful' );
		wp_die();
	}

	/**
	 * Update multiple data to the sheet.
	 *
	 * @param array  $wpsslw_multiple_update_data data to update.
	 * @param array  $settings_data settings data.
	 * @param bool   $settings_check whether the settings checked or not.
	 * @param string $oparation operation to perform on data.
	 */
	public static function wpsslw_multiple_update_data( $wpsslw_multiple_update_data, $settings_data = array(), $settings_check = false, $oparation = '' ) {

		if ( empty( $wpsslw_multiple_update_data ) || empty( $settings_data ) ) {
			return;
		}

		$wpsslw_spreadsheetid = isset( $settings_data['spreadsheet_id'] ) ? $settings_data['spreadsheet_id'] : '';
		$wpsslw_sheetname     = isset( $settings_data['sheetname'] ) ? $settings_data['sheetname'] : '';
		$wpsslw_setting       = isset( $settings_data['setting'] ) ? $settings_data['setting'] : '';

		if ( empty( $wpsslw_spreadsheetid ) || empty( $wpsslw_sheetname ) || empty( $wpsslw_setting ) ) {
			return;
		}

		if ( false === $settings_check ) {
			$wpsslw_spreadsheet_setting = isset( $settings_data['setting_enable'] ) ? $settings_data['setting_enable'] : '';

			if ( 'yes' !== (string) $wpsslw_spreadsheet_setting ) {
				return;
			}
			$wpsslw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			if ( ! array_key_exists( $wpsslw_spreadsheetid, $wpsslw_spreadsheets_list ) ) {
				return;
			}
		}
		$response                   = self::$instance_api->get_sheet_listing( $wpsslw_spreadsheetid );
		$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
		$wpsslw_existingsheetsnames = array_flip( $wpsslw_existingsheetsnames );

		$sheet_id = array_search( $wpsslw_sheetname, $wpsslw_existingsheetsnames, true );
		if ( false === $sheet_id ) {
			return;
		}
		$wpsslw_sheet    = "'" . $wpsslw_sheetname . "'!A:Z";
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

		$wpsslw_inputoption = self::wpsslw_option( 'wpssw_inputoption' );
		if ( ! $wpsslw_inputoption ) {
			$wpsslw_inputoption = 'USER_ENTERED';
		}

		if ( 'product' === (string) $wpsslw_setting ) {
			$wpsslw_inputoption = WPSSLW_Product::wpsslw_get_product_inputoption();
			if ( ! $wpsslw_inputoption ) {
				$wpsslw_inputoption = 'USER_ENTERED';
			}
		}

		if ( 'insert' === (string) $oparation ) {
			$wpsslw_values_array = array();
			foreach ( $wpsslw_multiple_update_data as $update_id ) {
				if ( 'product' === (string) $wpsslw_setting ) {
					$product = wc_get_product( $update_id );
					if ( false === $product || null === $product || is_wp_error( $product ) ) {
						continue;
					}
					if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
						$wpsslw_values_array[] = WPSSLW_Product::wpsslw_make_product_value_array( 'insert', $product->get_id() );
						foreach ( $product->get_children() as $child ) {
							$wpsslw_values_array[] = WPSSLW_Product::wpsslw_make_product_value_array( 'insert', $child, true );
						}
					} else {
						$wpsslw_values_array[] = WPSSLW_Product::wpsslw_make_product_value_array( 'insert', $product->get_id() );
					}
					$product = null;
				} elseif ( 'customer' === (string) $wpsslw_setting ) {
					$customer = new WP_User( $update_id );
					if ( false === $customer || null === $customer || is_wp_error( $customer ) ) {
						continue;
					}
					$wpsslw_values_array = array_merge( $wpsslw_values_array, WPSSLW_Customer::wpsslw_make_customer_value_array( 'insert', $customer ) );
					$customer            = null;
				} elseif ( 'coupon' === (string) $wpsslw_setting ) {
					$coupon = new WC_Coupon( $update_id );
					if ( false === $coupon || null === $coupon || is_wp_error( $coupon ) ) {
						continue;
					}
					$wpsslw_values_array = array_merge( $wpsslw_values_array, WPSSLW_Coupon::wpsslw_make_coupon_value_array( 'insert', $update_id ) );
					$coupon              = null;
				}
			}
			$wpsslw_values_array = array_filter( $wpsslw_values_array );
			$rangetofind         = $wpsslw_sheetname . '!A' . ( count( $wpsslw_data ) + 1 );

			if ( ! empty( $wpsslw_values_array ) ) {
				try {
					$wpsslw_requestbody = self::$instance_api->valuerangeobject( $wpsslw_values_array );
					$wpsslw_params      = array( 'valueInputOption' => $wpsslw_inputoption );
					$param              = self::$instance_api->setparamater( $wpsslw_spreadsheetid, $rangetofind, $wpsslw_requestbody, $wpsslw_params );
					$wpsslw_response    = self::$instance_api->appendentry( $param );
				} catch ( Exception $e ) {
					return;
				}
			}
			return;
		}

		$delete_row_indexes = array();
		$deleterequestarray = array();

		foreach ( $wpsslw_multiple_update_data as $update_id ) {
			if ( in_array( (int) $update_id, $wpsslw_data, true ) ) {
				$wpsslw_num = array_search( (int) $update_id, $wpsslw_data, true );
				$item_count = count( array_keys( $wpsslw_data, (int) $update_id, true ) );

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

		$highest_update_id = max( $wpsslw_data );

		foreach ( $wpsslw_multiple_update_data as $update_key => $update_id ) {
			$wpsslw_append   = 0;
			$wpsslw_prdcount = 1;
			if ( 'product' === (string) $wpsslw_setting ) {
				$product = wc_get_product( $update_id );
				if ( false === $product || null === $product || is_wp_error( $product ) ) {
					continue;
				}
				if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
					$wpsslw_prdcount += count( $product->get_children() );
				}
				$product = null;
			} elseif ( 'customer' === (string) $wpsslw_setting ) {
				$customer = new WP_User( $update_id );
				if ( false === $customer || null === $customer || is_wp_error( $customer ) ) {
					continue;
				}
				$customer = null;
			} elseif ( 'coupon' === (string) $wpsslw_setting ) {
				$coupon = new WC_Coupon( $update_id );
				if ( false === $coupon || null === $coupon || is_wp_error( $coupon ) ) {
					continue;
				}
				$coupon = null;
			}

			if ( ( $highest_update_id ) && $update_id < $highest_update_id ) {
				foreach ( $wpsslw_data as $wpsslw_key => $wpsslw_value ) {
					if ( ! empty( $wpsslw_value ) ) {

						if ( ( (int) $update_id < (int) $wpsslw_value ) ) {
							$wpsslw_startindex = $wpsslw_key + 1;

							$wpsslw_append        = 1;
							$insert_row_indexes[] = array(
								'start_index' => $wpsslw_key,
								'end_index'   => $wpsslw_key + $wpsslw_prdcount,
							);

							$update_row_indexes[] = array(
								'start_index'  => $wpsslw_startindex,
								'end_index'    => $wpsslw_startindex + $wpsslw_prdcount,
								'update_id'    => $update_id,

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
					'update_id'    => $update_id,

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
				$same_startindex_rowkeys = array_keys( self::wpsslw_convert_int( array_column( $update_row_indexes, 'start_index' ) ), (int) $update_row_indexes[ $i ]['start_index'], true );

				if ( count( $same_startindex_rowkeys ) > 1 && $same_startindex_rowkeys[0] < $i ) {
					continue;
				}
				for ( $j = 0;$j < $update_row_indexes_count;$j++ ) {
					$wpsslw_values_array = array();
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
						if ( 'product' === (string) $wpsslw_setting ) {
							$product = wc_get_product( $update_row_indexes[ $i ]['update_id'] );
							if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
								$wpsslw_values_array[] = WPSSLW_Product::wpsslw_make_product_value_array( 'insert', $product->get_id() );
								foreach ( $product->get_children() as $child ) {
									$wpsslw_values_array[] = WPSSLW_Product::wpsslw_make_product_value_array( 'insert', $child, true );
								}
							} else {
								$wpsslw_values_array[] = WPSSLW_Product::wpsslw_make_product_value_array( 'insert', $product->get_id() );
							}
							$product = null;
						} elseif ( 'customer' === (string) $wpsslw_setting ) {
							$customer            = new WP_User( $update_row_indexes[ $i ]['update_id'] );
							$wpsslw_values_array = WPSSLW_Customer::wpsslw_make_customer_value_array( 'insert', $customer );
							$customer            = null;
						} elseif ( 'coupon' === (string) $wpsslw_setting ) {
							$wpsslw_values_array = WPSSLW_Coupon::wpsslw_make_coupon_value_array( 'insert', $update_row_indexes[ $i ]['update_id'] );
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
					if ( (int) $update_row_indexes[ $i ]['start_index'] === (int) $update_row_indexes[ $j ]['start_index'] && $update_row_indexes[ $i ]['update_id'] !== $update_row_indexes[ $j ]['update_id'] ) {

						break;
					}

					if ( $update_row_indexes[ $i ]['start_index'] < $update_row_indexes[ $j ]['start_index'] ) {
						$param          = array();
						$param['range'] = $wpsslw_sheetname . '!A' . $new_start;

						if ( 'product' === (string) $wpsslw_setting ) {
							$product = wc_get_product( $update_row_indexes[ $i ]['update_id'] );
							if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
								$wpsslw_values_array[] = WPSSLW_Product::wpsslw_make_product_value_array( 'insert', $product->get_id() );
								foreach ( $product->get_children() as $child ) {
									$wpsslw_values_array[] = WPSSLW_Product::wpsslw_make_product_value_array( 'insert', $child, true );
								}
							} else {
								$wpsslw_values_array[] = WPSSLW_Product::wpsslw_make_product_value_array( 'insert', $product->get_id() );
							}
							$product = null;
						} elseif ( 'customer' === (string) $wpsslw_setting ) {
							$customer            = new WP_User( $update_row_indexes[ $i ]['update_id'] );
							$wpsslw_values_array = WPSSLW_Customer::wpsslw_make_customer_value_array( 'insert', $customer );
							$customer            = null;
						} elseif ( 'coupon' === (string) $wpsslw_setting ) {
							$wpsslw_values_array = WPSSLW_Coupon::wpsslw_make_coupon_value_array( 'insert', $update_row_indexes[ $i ]['update_id'] );
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
							$same_rows[ $update_row_indexes[ $same_rowkey ]['update_id'] ] = $update_row_indexes[ $same_rowkey ];
						}
						ksort( $same_rows );
						$same_startindex_rowkeys = null;

						$same_rows_value = array();
						foreach ( $same_rows as $same_row ) {
							$wpsslw_values_array = array();
							if ( 'product' === (string) $wpsslw_setting ) {

								$product = wc_get_product( $same_row['update_id'] );
								if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
									$wpsslw_values_array[] = WPSSLW_Product::wpsslw_make_product_value_array( 'insert', $product->get_id() );
									foreach ( $product->get_children() as $child ) {
										$wpsslw_values_array[] = WPSSLW_Product::wpsslw_make_product_value_array( 'insert', $child, true );
									}
								} else {
									$wpsslw_values_array[] = WPSSLW_Product::wpsslw_make_product_value_array( 'insert', $product->get_id() );
								}
								$product = null;
							} elseif ( 'customer' === (string) $wpsslw_setting ) {

								$customer            = new WP_User( $same_row['update_id'] );
								$wpsslw_values_array = WPSSLW_Customer::wpsslw_make_customer_value_array( 'insert', $customer );
								$customer            = null;
							} elseif ( 'coupon' === (string) $wpsslw_setting ) {
								$wpsslw_values_array = WPSSLW_Coupon::wpsslw_make_coupon_value_array( 'insert', $same_row['update_id'] );
							}
							$same_rows_value     = array_merge( $same_rows_value, $wpsslw_values_array );
							$wpsslw_values_array = null;
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

			$requestobject                  = array();
			$requestobject['spreadsheetid'] = $wpsslw_spreadsheetid;

			$requestobject['requestbody'] = new Google_Service_Sheets_BatchUpdateValuesRequest(
				array(
					'valueInputOption' => $wpsslw_inputoption,
					'data'             => $update_row_data,
				)
			);

			$wpsslw_response = self::$instance_api->multirangevalueupdate( $requestobject );
		}
	}
	/**
	 * Check HPOS usage is enabled
	 */
	public static function wpsslw_check_hpos_order_setting_enabled() {
		if ( class_exists( Automattic\WooCommerce\Utilities\OrderUtil::class ) ) {
			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				return true;// HPOS usage is enabled.
			} else {
				return false;// Traditional CPT-based orders are in use.
			}
		} else {
			return false;// Traditional CPT-based orders are in use.
		}
	}
	/**
	 * Pugin settings page
	 */
	public static function wpsslw_plugin_page() {

		
		$wpsslw_error           = '';
		$wpsslw_token_error     = false;
		$wpsslw_error_general   = '';
		$wpsslw_apisettings     = '';
		$wpsslw_generalsettings = '';
		$wpsslw_emsettings      = '';
		$wpsslw_supportsettings = '';
		if ( ! isset( $_GET['tab'] ) ) {
			// Google API Settings Tab.
			if ( isset( $_POST['submit'] ) || isset( $_POST['revoke'] ) ) {
				if ( ! isset( $_POST['wpssw_api_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_api_settings'] ) ), 'save_api_settings' ) ) {
					$wpsslw_error = '<div class="error token_error"><p><strong class="err-msg">' . esc_html__( 'Error: Sorry, your nonce did not verify.', 'wpssw' ) . '</strong></p></div>';
				} else {
					if ( isset( $_POST['client_token'] ) ) {
						$wpsslw_clienttoken = sanitize_text_field( wp_unslash( $_POST['client_token'] ) );
					} else {
						$wpsslw_clienttoken = '';
					}
					if ( isset( $_POST['client_id'] ) && isset( $_POST['client_secret'] ) ) {
						$wpsslw_google_settings = array( sanitize_text_field( wp_unslash( $_POST['client_id'] ) ), sanitize_text_field( wp_unslash( $_POST['client_secret'] ) ), $wpsslw_clienttoken );
					} else {
						$wpsslw_google_settings_value = self::$instance_api->wpsslw_option( 'wpssw_google_settings' );
						$wpsslw_google_settings       = array( $wpsslw_google_settings_value[0], $wpsslw_google_settings_value[1], $wpsslw_clienttoken );
					}

					self::$instance_api->wpsslw_update_option( 'wpssw_google_settings', $wpsslw_google_settings );

					if ( isset( $_POST['revoke'] ) ) {
						$wpsslw_google_settings    = self::$instance_api->wpsslw_option( 'wpssw_google_settings' );
						$wpsslw_google_settings[2] = '';
						self::$instance_api->wpsslw_update_option( 'wpssw_google_settings', $wpsslw_google_settings );
						self::$instance_api->wpsslw_update_option( 'wpssw_google_accessToken', '' );
					}
				}
			}
		}
		if ( isset( $_GET['code'] ) && ! empty( sanitize_text_field( wp_unslash( $_GET['code'] ) ) ) ) {
			$wpsslw_code               = sanitize_text_field( wp_unslash( $_GET['code'] ) );
			$wpsslw_token_value        = $wpsslw_code;
			$wpsslw_google_settings    = self::$instance_api->wpsslw_option( 'wpssw_google_settings' );
			$wpsslw_google_settings[2] = $wpsslw_code;
			self::$instance_api->wpsslw_update_option( 'wpssw_google_settings', $wpsslw_google_settings );
		}

		$enable = self::wpsslw_check_license_key();

		if ( ! $enable || ( isset( $_GET['tab'] ) && ! $enable && 'wpssw-nav-googleapi' !== (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) ) {
			$_GET['tab'] = 'wpssw-nav-googleapi';
		}
		// General Settings Tab.
		if ( isset( $_GET['tab'] ) && 'order-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( ! isset( $_POST['wpsslw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpsslw_general_settings'] ) ), 'save_general_settings' ) ) {
					$wpsslw_error_general = '<strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					WPSSLW_Order::wpsslw_update_settings();
				}
			}
		} elseif ( isset( $_GET['tab'] ) && 'product-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( ! isset( $_POST['wpsslw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpsslw_product_settings'] ) ), 'save_product_settings' ) ) {
					$wpsslw_error_general = '<div class="error token_error"><p><strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					WPSSLW_Product::wpsslw_update_product_settings();
				}
			}
		} elseif ( isset( $_GET['tab'] ) && 'customer-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
					$wpsslw_error_general = '<div class="error token_error"><p><strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					WPSSLW_Customer::wpsslw_update_customer_settings();
				}
			}
		} elseif ( isset( $_GET['tab'] ) && 'coupon-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( ! isset( $_POST['wpssw_coupon_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_coupon_settings'] ) ), 'save_coupon_settings' ) ) {
					$wpsslw_error_general = '<div class="error token_error"><p><strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					WPSSLW_Coupon::wpsslw_update_coupon_settings();
				}
			}
		} elseif ( isset( $_GET['tab'] ) && 'general-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( ! isset( $_POST['wpsslw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpsslw_general_settings'] ) ), 'save_general_settings' ) ) {
					$wpsslw_error_general = '<div class="error token_error"><p><strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					WPSSLW_General::wpsslw_update_general_settings();
				}
			}
		}

		$wpsslw_google_settings_value = self::$instance_api->wpsslw_option( 'wpssw_google_settings' );

		if ( ! empty( $wpsslw_google_settings_value[2] ) ) {
			if ( ! self::$instance_api->checkcredenatials() ) {
				$wpsslw_error = self::$instance_api->getClient( 1 );
				if ( 'Invalid token format' === (string) $wpsslw_error ) {
					$wpsslw_error = '<div class="error token_error"><p><strong class="err-msg">Error: Invalid Token - Revoke Token with below settings and try again.</strong></p></div>';
				} else {
					$wpsslw_error = '<div class="error token_error"><p><strong class="err-msg">Error: ' . $wpsslw_error . '</strong></p></div>';
				}
				$wpsslw_token_error = true;
			}
		}

		$licence_activated = 'activated';

		if ( empty( $wpsslw_error ) && $enable && 'activated' === (string) $licence_activated && isset( $wpsslw_google_settings_value[2] ) && ! empty( $wpsslw_google_settings_value[2] ) ) {
			try {
				$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();

				$spreadsheetid = self::$instance_api->wpsslw_option( 'wpssw_spreadsheet' );
				if ( ! empty( $spreadsheetid ) ) {
					$wpsslw_response            = self::$instance_api->get_sheet_listing( $spreadsheetid );
					$wpsslw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpsslw_response );
				}
			} catch ( Exception $e ) {

				$error  = json_decode( $e->getMessage(), true );
				$reason = '';
				if ( is_array( $error['error'] ) ) {
					$errors = isset( $error['error']['errors'] ) ? $error['error']['errors'] : array();
					foreach ( $errors as $err ) {
						$reason = isset( $err['reason'] ) ? $err['reason'] : '';
					}
				} else {
					$reason = $error['error'];
				}
				if ( 'insufficientPermissions' === (string) $reason ) {
					$wpsslw_error = '<div class="error token_error"><p><strong class="err-msg">Error: Insufficient Permissions - Revoke Token with below settings and when generating access token select all the permissions.</strong></p></div>';
				}
				if ( 'accessNotConfigured' === (string) $reason ) {
					$wpsslw_error = '<div class="error token_error"><p><strong class="err-msg">Error: Access not Configured - Please enable Google Sheets API and Google Drive API. Follow the <a target="_blank" href="https://docs.wpsyncsheets.com/wpssw-google-sheets-api-settings/" style="color:#000;">Google Sheets API Settings documentation</a> to enable APIs.</strong></p></div>';
				}
				if ( empty( $wpsslw_error ) && '' !== (string) $reason ) {
					$wpsslw_error = '<div class="error token_error"><p><strong class="err-msg">Error: Invalid Credentials - Reset settings and try again.</strong></p></div>';
				}
			}
		}
		$show_settings = false;
		if ( false === (bool) $wpsslw_token_error && $enable && 'activated' === (string) $licence_activated && ! empty( $wpsslw_google_settings_value[2] ) ) {
			$show_settings = true;
		}
		$disbledbtn = '';
		if ( ! $enable ) {
			$disbledbtn = 'disabled';
		}
		if ( ! current_user_can( 'edit_wpsyncsheets_woocommerce_lite_main_settings' ) ) {
			$show_settings = false;
		}

		?>

		<!-- .wrap -->
		<div class="wpssw-main-wrap">
			<div class="wpssw-header-main">
				<div class="container">
					<div class="wpssw-header-section">
						<div class="wpssw-header-left">
							<div class="wpssw-logo-section">
								<img src="<?php echo esc_url( WPSSLW_URL . 'assets/images/logo.svg?ver=' ); ?><?php echo esc_attr( WPSSLW_VERSION ); ?>">
							</div>
							<div class="wpssw-nav-top">
								<ul>
									<li>
										<button class="navtablinks wpssw-nav-googleapi" onclick="wpsslwNavTab(event, 'wpssw-nav-googleapi')">
											<svg width="21" height="19" viewBox="0 0 21 19" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M5.21777 19C3.83444 19 2.65527 18.5125 1.68027 17.5375C0.705273 16.5625 0.217773 15.3833 0.217773 14C0.217773 12.7833 0.59694 11.7208 1.35527 10.8125C2.11361 9.90417 3.06777 9.33333 4.21777 9.1V11.175C3.63444 11.375 3.15527 11.7333 2.78027 12.25C2.40527 12.7667 2.21777 13.35 2.21777 14C2.21777 14.8333 2.50944 15.5417 3.09277 16.125C3.67611 16.7083 4.38444 17 5.21777 17C6.05111 17 6.75944 16.7083 7.34277 16.125C7.92611 15.5417 8.21777 14.8333 8.21777 14V13H14.0928C14.2261 12.85 14.3886 12.7292 14.5803 12.6375C14.7719 12.5458 14.9844 12.5 15.2178 12.5C15.6344 12.5 15.9886 12.6458 16.2803 12.9375C16.5719 13.2292 16.7178 13.5833 16.7178 14C16.7178 14.4167 16.5719 14.7708 16.2803 15.0625C15.9886 15.3542 15.6344 15.5 15.2178 15.5C14.9844 15.5 14.7719 15.4542 14.5803 15.3625C14.3886 15.2708 14.2261 15.15 14.0928 15H10.1178C9.88444 16.15 9.31361 17.1042 8.40527 17.8625C7.49694 18.6208 6.43444 19 5.21777 19ZM15.2178 19C14.2844 19 13.4386 18.7708 12.6803 18.3125C11.9219 17.8542 11.3261 17.25 10.8928 16.5H13.5678C13.8011 16.6667 14.0594 16.7917 14.3428 16.875C14.6261 16.9583 14.9178 17 15.2178 17C16.0511 17 16.7594 16.7083 17.3428 16.125C17.9261 15.5417 18.2178 14.8333 18.2178 14C18.2178 13.1667 17.9261 12.4583 17.3428 11.875C16.7594 11.2917 16.0511 11 15.2178 11C14.8844 11 14.5761 11.0458 14.2928 11.1375C14.0094 11.2292 13.7428 11.3667 13.4928 11.55L10.4428 6.475C10.0928 6.40833 9.80111 6.24167 9.56777 5.975C9.33444 5.70833 9.21777 5.38333 9.21777 5C9.21777 4.58333 9.36361 4.22917 9.65527 3.9375C9.94694 3.64583 10.3011 3.5 10.7178 3.5C11.1344 3.5 11.4886 3.64583 11.7803 3.9375C12.0719 4.22917 12.2178 4.58333 12.2178 5V5.2125C12.2178 5.27083 12.2011 5.34167 12.1678 5.425L14.3428 9.075C14.4761 9.04167 14.6178 9.02083 14.7678 9.0125C14.9178 9.00417 15.0678 9 15.2178 9C16.6011 9 17.7803 9.4875 18.7553 10.4625C19.7303 11.4375 20.2178 12.6167 20.2178 14C20.2178 15.3833 19.7303 16.5625 18.7553 17.5375C17.7803 18.5125 16.6011 19 15.2178 19ZM5.21777 15.5C4.80111 15.5 4.44694 15.3542 4.15527 15.0625C3.86361 14.7708 3.71777 14.4167 3.71777 14C3.71777 13.6333 3.83444 13.3167 4.06777 13.05C4.30111 12.7833 4.58444 12.6083 4.91777 12.525L7.26777 8.625C6.78444 8.175 6.40527 7.6375 6.13027 7.0125C5.85527 6.3875 5.71777 5.71667 5.71777 5C5.71777 3.61667 6.20527 2.4375 7.18027 1.4625C8.15527 0.4875 9.33444 0 10.7178 0C12.1011 0 13.2803 0.4875 14.2553 1.4625C15.2303 2.4375 15.7178 3.61667 15.7178 5H13.7178C13.7178 4.16667 13.4261 3.45833 12.8428 2.875C12.2594 2.29167 11.5511 2 10.7178 2C9.88444 2 9.17611 2.29167 8.59277 2.875C8.00944 3.45833 7.71777 4.16667 7.71777 5C7.71777 5.71667 7.93444 6.34583 8.36777 6.8875C8.80111 7.42917 9.35111 7.775 10.0178 7.925L6.64277 13.55C6.67611 13.6333 6.69694 13.7083 6.70527 13.775C6.71361 13.8417 6.71777 13.9167 6.71777 14C6.71777 14.4167 6.57194 14.7708 6.28027 15.0625C5.98861 15.3542 5.63444 15.5 5.21777 15.5Z" fill="#64748B"/>
											</svg>

											API Integration
										</button>
									</li>
									<?php if ( true === (bool) $show_settings ) { ?>
									<li>
										<button class="navtablinks wpssw-nav-settings active" onclick="wpsslwNavTab(event, 'wpssw-nav-settings')">
											<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M7.46797 20L7.06797 16.8C6.8513 16.7167 6.64714 16.6167 6.45547 16.5C6.2638 16.3833 6.0763 16.2583 5.89297 16.125L2.91797 17.375L0.167969 12.625L2.74297 10.675C2.7263 10.5583 2.71797 10.4458 2.71797 10.3375V9.6625C2.71797 9.55417 2.7263 9.44167 2.74297 9.325L0.167969 7.375L2.91797 2.625L5.89297 3.875C6.0763 3.74167 6.26797 3.61667 6.46797 3.5C6.66797 3.38333 6.86797 3.28333 7.06797 3.2L7.46797 0H12.968L13.368 3.2C13.5846 3.28333 13.7888 3.38333 13.9805 3.5C14.1721 3.61667 14.3596 3.74167 14.543 3.875L17.518 2.625L20.268 7.375L17.693 9.325C17.7096 9.44167 17.718 9.55417 17.718 9.6625V10.3375C17.718 10.4458 17.7013 10.5583 17.668 10.675L20.243 12.625L17.493 17.375L14.543 16.125C14.3596 16.2583 14.168 16.3833 13.968 16.5C13.768 16.6167 13.568 16.7167 13.368 16.8L12.968 20H7.46797ZM10.268 13.5C11.2346 13.5 12.0596 13.1583 12.743 12.475C13.4263 11.7917 13.768 10.9667 13.768 10C13.768 9.03333 13.4263 8.20833 12.743 7.525C12.0596 6.84167 11.2346 6.5 10.268 6.5C9.28464 6.5 8.45547 6.84167 7.78047 7.525C7.10547 8.20833 6.76797 9.03333 6.76797 10C6.76797 10.9667 7.10547 11.7917 7.78047 12.475C8.45547 13.1583 9.28464 13.5 10.268 13.5Z" fill="#64748B"/>
										</svg>
											Settings
										</button>
									</li>
									<?php } ?>
									<li>
										<a class="navtablinks wpssw-nav-upgradepro" href="<?php echo esc_url( self::wpsslw_instance()->pro_version_url ); ?>" target="_blank">
											<span class="dashicons dashicons-admin-links"></span>
											Upgrade To Pro
										</a>
									</li>
								</ul>
							</div>
						</div>
						<div class="wpssw-header-right">
							<ul class="wpssw-header-links">
								<li class="version"><span>V<?php echo esc_html( WPSSLW_VERSION ); ?></span></li>
								<li id="wpsswBtn">
									<a target="_blank" href="https://docs.wpsyncsheets.com/wpssw-introduction/">
										<svg width="16" height="20" viewBox="0 0 15 19" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M4.61806 7.6H5.54167C5.78662 7.6 6.02155 7.49991 6.19476 7.32175C6.36797 7.14359 6.46528 6.90196 6.46528 6.65C6.46528 6.39804 6.36797 6.15641 6.19476 5.97825C6.02155 5.80009 5.78662 5.7 5.54167 5.7H4.61806C4.3731 5.7 4.13817 5.80009 3.96496 5.97825C3.79175 6.15641 3.69444 6.39804 3.69444 6.65C3.69444 6.90196 3.79175 7.14359 3.96496 7.32175C4.13817 7.49991 4.3731 7.6 4.61806 7.6ZM4.61806 9.5C4.3731 9.5 4.13817 9.60009 3.96496 9.77825C3.79175 9.95641 3.69444 10.198 3.69444 10.45C3.69444 10.702 3.79175 10.9436 3.96496 11.1218C4.13817 11.2999 4.3731 11.4 4.61806 11.4H10.1597C10.4047 11.4 10.6396 11.2999 10.8128 11.1218C10.986 10.9436 11.0833 10.702 11.0833 10.45C11.0833 10.198 10.986 9.95641 10.8128 9.77825C10.6396 9.60009 10.4047 9.5 10.1597 9.5H4.61806ZM14.7778 6.593C14.7682 6.50573 14.7496 6.41975 14.7224 6.3365V6.251C14.678 6.15332 14.6187 6.06353 14.5469 5.985L9.00521 0.285C8.92886 0.211105 8.84156 0.150177 8.7466 0.1045C8.71903 0.100472 8.69104 0.100472 8.66347 0.1045C8.56965 0.0491542 8.46603 0.013627 8.35868 0H2.77083C2.03596 0 1.33119 0.300267 0.811558 0.834746C0.291926 1.36922 0 2.09413 0 2.85V16.15C0 16.9059 0.291926 17.6308 0.811558 18.1653C1.33119 18.6997 2.03596 19 2.77083 19H12.0069C12.7418 19 13.4466 18.6997 13.9662 18.1653C14.4859 17.6308 14.7778 16.9059 14.7778 16.15V6.65C14.7778 6.65 14.7778 6.65 14.7778 6.593ZM9.23611 3.2395L11.6283 5.7H10.1597C9.91477 5.7 9.67984 5.59991 9.50663 5.42175C9.33342 5.24359 9.23611 5.00196 9.23611 4.75V3.2395ZM12.9306 16.15C12.9306 16.402 12.8332 16.6436 12.66 16.8218C12.4868 16.9999 12.2519 17.1 12.0069 17.1H2.77083C2.52588 17.1 2.29095 16.9999 2.11774 16.8218C1.94453 16.6436 1.84722 16.402 1.84722 16.15V2.85C1.84722 2.59804 1.94453 2.35641 2.11774 2.17825C2.29095 2.00009 2.52588 1.9 2.77083 1.9H7.38889V4.75C7.38889 5.50587 7.68081 6.23078 8.20045 6.76525C8.72008 7.29973 9.42485 7.6 10.1597 7.6H12.9306V16.15ZM10.1597 13.3H4.61806C4.3731 13.3 4.13817 13.4001 3.96496 13.5782C3.79175 13.7564 3.69444 13.998 3.69444 14.25C3.69444 14.502 3.79175 14.7436 3.96496 14.9218C4.13817 15.0999 4.3731 15.2 4.61806 15.2H10.1597C10.4047 15.2 10.6396 15.0999 10.8128 14.9218C10.986 14.7436 11.0833 14.502 11.0833 14.25C11.0833 13.998 10.986 13.7564 10.8128 13.5782C10.6396 13.4001 10.4047 13.3 10.1597 13.3Z" fill="#464D58"/>
										</svg>
										<span class="tooltip-text">Documentation</span>
									</a>
								</li>
								<li>
									<a target="_blank" href="https://wordpress.org/support/plugin/wpsyncsheets-woocommerce/">
										<svg width="20" height="20" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M13.2708 2.87C13.5005 3.00037 13.7724 3.03464 14.0273 2.96532C14.2821 2.896 14.4992 2.72873 14.6311 2.5C14.7403 2.30736 14.9104 2.15639 15.1146 2.07078C15.3188 1.98517 15.5457 1.96975 15.7597 2.02694C15.9736 2.08414 16.1626 2.21071 16.2968 2.38681C16.4311 2.56291 16.5031 2.77858 16.5015 3C16.5015 3.26522 16.3961 3.51957 16.2086 3.70711C16.021 3.89464 15.7666 4 15.5013 4C15.236 4 14.9816 4.10536 14.794 4.29289C14.6065 4.48043 14.5011 4.73478 14.5011 5C14.5011 5.26522 14.6065 5.51957 14.794 5.70711C14.9816 5.89464 15.236 6 15.5013 6C16.0279 5.99966 16.5452 5.86075 17.0011 5.59723C17.4571 5.33371 17.8356 4.95486 18.0987 4.49875C18.3618 4.04264 18.5002 3.52533 18.5 2.9988C18.4998 2.47227 18.361 1.95507 18.0975 1.49917C17.834 1.04326 17.4552 0.664718 16.9991 0.401563C16.5429 0.138409 16.0255 -8.42941e-05 15.4989 3.84915e-08C14.9723 8.43711e-05 14.4549 0.138743 13.9989 0.402044C13.5428 0.665344 13.1641 1.04401 12.9008 1.5C12.8346 1.61413 12.7917 1.74022 12.7745 1.871C12.7574 2.00178 12.7662 2.13466 12.8006 2.262C12.835 2.38934 12.8943 2.50862 12.975 2.61297C13.0557 2.71732 13.1562 2.80467 13.2708 2.87ZM17.5717 10C17.3092 9.96593 17.0438 10.0373 16.8338 10.1985C16.6239 10.3597 16.4864 10.5976 16.4515 10.86C16.2416 12.5552 15.419 14.1151 14.1386 15.246C12.8583 16.3769 11.2085 17.0007 9.50006 17H3.90889L4.55903 16.35C4.74532 16.1626 4.84988 15.9092 4.84988 15.645C4.84988 15.3808 4.74532 15.1274 4.55903 14.94C3.58378 13.9611 2.92007 12.7156 2.65153 11.3603C2.38298 10.005 2.52159 8.60052 3.04992 7.32383C3.57824 6.04714 4.47263 4.95532 5.62044 4.18589C6.76825 3.41646 8.11813 3.00384 9.50006 3C9.76533 3 10.0197 2.89464 10.2073 2.70711C10.3949 2.51957 10.5003 2.26522 10.5003 2C10.5003 1.73478 10.3949 1.48043 10.2073 1.29289C10.0197 1.10536 9.76533 0.999999 9.50006 0.999999C7.80894 1.00705 6.15401 1.49024 4.72486 2.39419C3.29571 3.29815 2.15016 4.58632 1.41944 6.11112C0.688721 7.63592 0.40239 9.33568 0.593251 11.0157C0.784113 12.6956 1.44445 14.2879 2.4986 15.61L0.788246 17.29C0.64946 17.4306 0.555444 17.6092 0.518062 17.8032C0.48068 17.9972 0.501607 18.1979 0.578203 18.38C0.653238 18.5626 0.780663 18.7189 0.944417 18.8293C1.10817 18.9396 1.30093 18.999 1.49839 19H9.50006C11.692 19.0003 13.8087 18.201 15.4531 16.7521C17.0975 15.3031 18.1567 13.3041 18.4319 11.13C18.4502 10.9993 18.4424 10.8662 18.409 10.7385C18.3756 10.6107 18.3172 10.4909 18.2373 10.3858C18.1573 10.2808 18.0573 10.1926 17.9431 10.1264C17.8289 10.0602 17.7026 10.0172 17.5717 10ZM15.8814 7.07C15.6993 6.98945 15.4973 6.96508 15.3013 7L15.1212 7.06L14.9412 7.15L14.7912 7.28C14.7012 7.37215 14.6299 7.48081 14.5811 7.6C14.522 7.72473 14.4945 7.86212 14.5011 8C14.4982 8.13337 14.522 8.26597 14.5711 8.39C14.6228 8.51002 14.6976 8.61873 14.7912 8.71C14.8846 8.80268 14.9955 8.87601 15.1173 8.92577C15.2392 8.97553 15.3697 9.00076 15.5013 9C15.7666 9 16.021 8.89464 16.2086 8.70711C16.3961 8.51957 16.5015 8.26522 16.5015 8C16.5049 7.86882 16.4775 7.73868 16.4215 7.62C16.314 7.37971 16.1217 7.18745 15.8814 7.08V7.07Z" fill="#464D58"/>
										</svg>
										<span class="tooltip-text">Support</span>
									</a>
								</li>
								<li>
									<a class="whatsNew-toggle" href="#">
										<svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M15.6111 1.5157e-07C15.4944 -6.7285e-05 15.3787 0.0231658 15.2709 0.0683712C15.163 0.113577 15.065 0.179867 14.9824 0.263454C14.8999 0.34704 14.8344 0.446282 14.7897 0.555505C14.7451 0.664729 14.7222 0.781791 14.7222 0.9V1.47305C13.9725 2.41418 13.025 3.17422 11.9487 3.69785C10.8723 4.22147 9.69415 4.4955 8.5 4.5H3.16667C2.45966 4.50078 1.78183 4.7855 1.2819 5.29167C0.781971 5.79785 0.500772 6.48415 0.5 7.2V9C0.500772 9.71584 0.781971 10.4021 1.2819 10.9083C1.78183 11.4145 2.45966 11.6992 3.16667 11.7H3.5967L1.46094 16.7458C1.4029 16.8826 1.37934 17.0319 1.39237 17.1803C1.40539 17.3286 1.45461 17.4714 1.53558 17.5957C1.61656 17.7201 1.72677 17.8221 1.85631 17.8927C1.98585 17.9632 2.13068 18.0001 2.27778 18H5.83333C6.00734 18.0001 6.17753 17.9484 6.32276 17.8514C6.46799 17.7543 6.58185 17.6162 6.65018 17.4542L9.07129 11.7342C10.1652 11.8155 11.231 12.123 12.203 12.6377C13.1749 13.1524 14.0323 13.8634 14.7222 14.7268V15.3C14.7222 15.5387 14.8159 15.7676 14.9826 15.9364C15.1493 16.1052 15.3754 16.2 15.6111 16.2C15.8469 16.2 16.073 16.1052 16.2396 15.9364C16.4063 15.7676 16.5 15.5387 16.5 15.3V0.9C16.5001 0.781791 16.4771 0.664728 16.4325 0.555504C16.3878 0.44628 16.3224 0.347037 16.2398 0.263451C16.1572 0.179865 16.0592 0.113574 15.9514 0.0683688C15.8435 0.0231639 15.7279 -6.84694e-05 15.6111 1.5157e-07ZM3.16667 9.9C2.93097 9.89984 2.70497 9.80497 2.5383 9.63622C2.37164 9.46747 2.27794 9.23864 2.27778 9V7.2C2.27794 6.96135 2.37164 6.73253 2.5383 6.56378C2.70497 6.39504 2.93097 6.30016 3.16667 6.3H4.05556V9.9H3.16667ZM5.2474 16.2H3.62587L5.53038 11.7H7.15191L5.2474 16.2ZM14.7222 12.1696C12.9696 10.7077 10.7708 9.90565 8.5 9.89995H5.83333V6.29995H8.5C10.7709 6.29408 12.9696 5.4919 14.7222 4.02985V12.1696Z" fill="#464D58"/>
										</svg>
										<span class="tooltip-text">Change Log</span>
									</a>
								</li>
							</ul>
							<div class="whatsNew-block">
								<div class="block-header">
									<h3>Change Log</h3>
									<button type="button" class="close-block">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
									</button>
								</div>	
								<div class="wpssw-block-content changeLog-conntent">
									<?php
									$changelog = self::wpsslw_get_plugin_changelog( WP_PLUGIN_DIR . '/wpsyncsheets-woocommerce' );
									// phpcs:ignore
									echo nl2br( $changelog );

									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- messages html -->
			<div class="alert-messages">
				<div class="container">
				</div>
			</div>
			<!-- messages html end-->		
			<div class="wpssw-tabs-main">
				<div id="wpssw-nav-googleapi" class="navtabcontent vertical-tabs" 
					<?php
					if ( $show_settings ) {
						echo "style='display:none;'"; 
					}
					?>
				>
					<div id="googleapi-settings" class="wpssw-navtabcontent">
						<?php
						if ( ! current_user_can( 'edit_wpsyncsheets_woocommerce_lite_main_settings' ) ) {
							$show_settings = false;
							?>
						<div class="wpssw-top-subtext generalSetting-section">
							<div class="generalSetting-left">
								<h4><?php echo esc_html__( 'You do not have permission to access this page.', 'wpss' ); ?></h4>
							</div>
						</div>
							<?php
						} else {
							?>
						<div class="wpssw-top-subtext generalSetting-section">
							<div class="generalSetting-left">
							<h4><?php echo esc_html__( 'Google API Settings', 'wpss' ); ?></h4>
							<p><?php echo esc_html__( 'Google APIs allow you to embed Google Services in your online site. To connect Google Drive and Google Sheets with your WordPress website, you need to generate dedicated API keys. To begin the process, kindly log in to your Gmail Account and follow the link to initiate the setup ', 'wpss' ); ?><a href="<?php echo esc_url( self::$doc_sheet_setting ); ?>" target="_blank"><?php echo esc_html__( 'click here', 'wpss' ); ?>.</a></p>
							</div>
						</div>
						<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-woocommerce' ) ); ?>">
							<?php wp_nonce_field( 'save_api_settings', 'wpssw_api_settings' ); ?>
							<div id="universal-message-container wpssw">
								<div class="generalSetting-section">
									<div class="integrationtabform">
										<div class="options">
											<ul class="form-table">
												<li>
													<label> <?php echo esc_html__( 'Client ID', 'wpss' ); ?> </label>
													<div class="forminp forminp-text">
														<input type="text" id="client_id" name="client_id" value="<?php echo isset( $wpsslw_google_settings_value[0] ) ? esc_attr( $wpsslw_google_settings_value[0] ) : ''; ?>" size="80" class = "googlesettinginput" placeholder="Enter Client Id" 
															<?php
															if ( ! empty( $wpsslw_google_settings_value[0] ) ) {
																echo 'readonly';
															}
															?>
														/>
													</div>
												</li>
												<li>
													<label> <?php echo esc_html__( 'Client Secret Key', 'wpss' ); ?> </label>
													<div class="forminp forminp-text">
														<input type="text" id="client_secret" name="client_secret" value="<?php echo isset( $wpsslw_google_settings_value[1] ) ? esc_attr( $wpsslw_google_settings_value[1] ) : ''; ?>" size="80" class = "googlesettinginput" placeholder="Enter Client Secret" 
															<?php
															if ( ! empty( $wpsslw_google_settings_value[1] ) ) {
																echo 'readonly';
															}
															?>
														/>
													</div>
												</li>
												<?php
												if ( ! empty( $wpsslw_google_settings_value[0] ) && ! empty( $wpsslw_google_settings_value[1] ) ) {
														$wpsslw_token_value = $wpsslw_google_settings_value[2];
													?>
												<li>
													<label><?php echo esc_html__( 'Client Token', 'wpss' ); ?></label>
													<?php
													if ( empty( $wpsslw_token_value ) && ! isset( $_GET['code'] ) ) {
														$wpsslw_auth_url = self::$instance_api->getClient();
														self::$instance_api->wpsslw_update_option( 'wpssw_share_enable', true );
														?>
														<div id="authbtn">
															<a href="<?php echo esc_url( $wpsslw_auth_url ); ?>" id="authlink" target="_blank" ><div class="wpssw-button wpssw-button-secondary"><?php echo esc_html__( 'Click here to generate an Authentication Token', 'wpss' ); ?></div></a>
														</div>
														<?php
													}
													$wpsslw_code                  = '';
													$wpsslw_google_settings_value = self::$instance_api->wpsslw_option( 'wpssw_google_settings' );
													?>
													<div  id="authtext" 
													<?php
													if ( ! empty( $wpsslw_token_value ) || $wpsslw_code ) {
														echo 'class = "forminp forminp-text wpssw-authtext" ';
													} else {
														echo 'class="forminp forminp-text"'; }
													?>
														><input type="text" name="client_token" value="<?php echo $wpsslw_token_value ? esc_attr( $wpsslw_token_value ) : esc_attr( $wpsslw_code ); ?>" size="80" placeholder="Please enter authentication code" id="client_token" class="googlesettinginput" 
															<?php
															if ( ! empty( $wpsslw_google_settings_value[2] ) ) {
																echo 'readonly';
															}
															?>
														/>
													</div>
												</li>
												<?php } if ( ! empty( $wpsslw_token_value ) ) { ?>
												<li>
													<label></label>
													<div><input type="submit" name="revoke" id="revoke" value = "Revoke Token" class="wpssw-button wpssw-button-secondary"/></div>
												</li>
												<?php } ?>
											</ul>
										</div>
										<?php
										$site_url = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
										$site_url = str_replace( 'www.', '', $site_url );
										?>
										<div class="submit-section">
											<p class="submit">
												<input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save">
												<?php
												if ( ! empty( $wpsslw_token_value ) || ! empty( $wpsslw_google_settings_value[0] ) || ! empty( $wpsslw_google_settings_value[1] ) ) {
													?>
														<input type="submit" name="reset_settings" id="reset_settings" value = "Reset" class="wpssw-button wpssw-button-primary reset_settings"/>
													<?php } ?>
											</p>
										</div>
									</div>
								</div>
								<div class="generalSetting-section copy-url-table">
									<ul>
										<li>
											<label><?php echo esc_html__( 'Authorized Domain : ', 'wpss' ); ?></label>
											<div class="copy-url-text">
												<span id="authorized_domain"><?php echo esc_html( $site_url ); ?></span>
												<span class="copy-icon wpssw-button" id="a_domain" onclick="wpsswCopy('authorized_domain','a_domain');">
													<svg width="15" height="18" viewBox="0 0 15 18" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path d="M2.16667 17.3334C1.70833 17.3334 1.31597 17.1702 0.989583 16.8438C0.663194 16.5174 0.5 16.1251 0.5 15.6667V4.00008H2.16667V15.6667H11.3333V17.3334H2.16667ZM5.5 14.0001C5.04167 14.0001 4.64931 13.8369 4.32292 13.5105C3.99653 13.1841 3.83333 12.7917 3.83333 12.3334V2.33341C3.83333 1.87508 3.99653 1.48272 4.32292 1.15633C4.64931 0.829942 5.04167 0.666748 5.5 0.666748H13C13.4583 0.666748 13.8507 0.829942 14.1771 1.15633C14.5035 1.48272 14.6667 1.87508 14.6667 2.33341V12.3334C14.6667 12.7917 14.5035 13.1841 14.1771 13.5105C13.8507 13.8369 13.4583 14.0001 13 14.0001H5.5ZM5.5 12.3334H13V2.33341H5.5V12.3334Z" fill="#383E46"/>
													</svg>
													<span class="tooltip-text">Copied</span>
												</span>
											</div>
										</li>
										<li>
											<label><?php echo esc_html__( 'Authorised redirect URIs : ', 'wpss' ); ?></label>
											<div class="copy-url-text">
												<span id="authorized_uri"><?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-woocommerce' ) ); ?></span>
												<span class="copy-icon wpssw-button tooltip-click1" onclick="wpsswCopy('authorized_uri','a_uri');" id="a_uri">
													<svg width="15" height="18" viewBox="0 0 15 18" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M2.16667 17.3334C1.70833 17.3334 1.31597 17.1702 0.989583 16.8438C0.663194 16.5174 0.5 16.1251 0.5 15.6667V4.00008H2.16667V15.6667H11.3333V17.3334H2.16667ZM5.5 14.0001C5.04167 14.0001 4.64931 13.8369 4.32292 13.5105C3.99653 13.1841 3.83333 12.7917 3.83333 12.3334V2.33341C3.83333 1.87508 3.99653 1.48272 4.32292 1.15633C4.64931 0.829942 5.04167 0.666748 5.5 0.666748H13C13.4583 0.666748 13.8507 0.829942 14.1771 1.15633C14.5035 1.48272 14.6667 1.87508 14.6667 2.33341V12.3334C14.6667 12.7917 14.5035 13.1841 14.1771 13.5105C13.8507 13.8369 13.4583 14.0001 13 14.0001H5.5ZM5.5 12.3334H13V2.33341H5.5V12.3334Z" fill="#383E46"/>
													</svg>
													<span class="tooltip-text">Copied</span>
												</span>
											</div>
										</li>
									</ul>
								</div>
							</div>
						</form>
						<?php } ?>
					</div>
				</div>
				<?php if ( true === (bool) $show_settings ) { ?>
				<div id="wpssw-nav-settings" class="navtabcontent wpssw-nav-settings-content" style="display:block;">
					<div class="vertical-tabs">
						<div class="tab">
							<button class="tablinks order-settings" onclick="wpsslwTab(event, 'order-settings')" 
							<?php
							if ( ! empty( $wpsslw_error ) || ! empty( $disbledbtn ) ) {
								echo 'disabled="disabled"'; }
							?>
							> <span class="tabicon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<mask id="mask0_428_2610" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
									<rect width="24" height="24" fill="#D9D9D9"/>
									</mask>
									<g mask="url(#mask0_428_2610)">
									<path d="M5.00033 21C4.717 21 4.45866 20.9125 4.22533 20.7375C3.992 20.5625 3.83366 20.3333 3.75033 20.05L0.95033 9.95C0.866997 9.71667 0.904497 9.5 1.06283 9.3C1.22116 9.1 1.43366 9 1.70033 9H6.75033L11.1503 2.45C11.2337 2.31667 11.3503 2.20833 11.5003 2.125C11.6503 2.04167 11.8087 2 11.9753 2C12.142 2 12.3003 2.04167 12.4503 2.125C12.6003 2.20833 12.717 2.31667 12.8003 2.45L17.2003 9H22.3003C22.567 9 22.7795 9.1 22.9378 9.3C23.0962 9.5 23.1337 9.71667 23.0503 9.95L20.2503 20.05C20.167 20.3333 20.0087 20.5625 19.7753 20.7375C19.542 20.9125 19.2837 21 19.0003 21H5.00033ZM5.50033 19H18.5003L20.7003 11H3.30033L5.50033 19ZM12.0003 17C12.5503 17 13.0212 16.8042 13.4128 16.4125C13.8045 16.0208 14.0003 15.55 14.0003 15C14.0003 14.45 13.8045 13.9792 13.4128 13.5875C13.0212 13.1958 12.5503 13 12.0003 13C11.4503 13 10.9795 13.1958 10.5878 13.5875C10.1962 13.9792 10.0003 14.45 10.0003 15C10.0003 15.55 10.1962 16.0208 10.5878 16.4125C10.9795 16.8042 11.4503 17 12.0003 17ZM9.17533 9H14.8003L11.9753 4.8L9.17533 9Z" fill="#1C1B1F"/>
									</g>
								</svg>
							</span>
							<?php echo esc_html__( 'Order', 'wpss' ); ?> <br><?php echo esc_html__( 'Settings', 'wpss' ); ?></button>	
							<button class="tablinks product-settings" onclick="wpsslwTab(event, 'product-settings')" 
							<?php
							if ( ! empty( $wpsslw_error ) || ! empty( $disbledbtn ) ) {
								echo 'disabled="disabled"'; }
							?>
							> <span class="tabicon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<mask id="mask0_428_2622" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
									<rect width="24" height="24" fill="#D9D9D9"/>
									</mask>
									<g mask="url(#mask0_428_2622)">
									<path d="M5 8V19H19V8H16V14.375C16 14.7583 15.8417 15.0458 15.525 15.2375C15.2083 15.4292 14.8833 15.4417 14.55 15.275L12 14L9.45 15.275C9.11667 15.4417 8.79167 15.4292 8.475 15.2375C8.15833 15.0458 8 14.7583 8 14.375V8H5ZM5 21C4.45 21 3.97917 20.8042 3.5875 20.4125C3.19583 20.0208 3 19.55 3 19V6.525C3 6.29167 3.0375 6.06667 3.1125 5.85C3.1875 5.63333 3.3 5.43333 3.45 5.25L4.7 3.725C4.88333 3.49167 5.1125 3.3125 5.3875 3.1875C5.6625 3.0625 5.95 3 6.25 3H17.75C18.05 3 18.3375 3.0625 18.6125 3.1875C18.8875 3.3125 19.1167 3.49167 19.3 3.725L20.55 5.25C20.7 5.43333 20.8125 5.63333 20.8875 5.85C20.9625 6.06667 21 6.29167 21 6.525V19C21 19.55 20.8042 20.0208 20.4125 20.4125C20.0208 20.8042 19.55 21 19 21H5ZM5.4 6H18.6L17.75 5H6.25L5.4 6ZM10 8V12.75L12 11.75L14 12.75V8H10Z" fill="#1C1B1F"/>
									</g>
								</svg>
							</span>
							<?php echo esc_html__( 'Product', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
							<button class="tablinks customer-settings" onclick="wpsslwTab(event, 'customer-settings')" 
							<?php
							if ( ! empty( $wpsslw_error ) || ! empty( $disbledbtn ) ) {
								echo 'disabled="disabled"'; }
							?>
							> <span class="tabicon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<mask id="mask0_423_2547" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
									<rect width="24" height="24" fill="#D9D9D9"/>
									</mask>
									<g mask="url(#mask0_423_2547)">
									<path d="M10 12C8.9 12 7.95833 11.6083 7.175 10.825C6.39167 10.0417 6 9.1 6 8C6 6.9 6.39167 5.95833 7.175 5.175C7.95833 4.39167 8.9 4 10 4C11.1 4 12.0417 4.39167 12.825 5.175C13.6083 5.95833 14 6.9 14 8C14 9.1 13.6083 10.0417 12.825 10.825C12.0417 11.6083 11.1 12 10 12ZM3 20C2.71667 20 2.47917 19.9042 2.2875 19.7125C2.09583 19.5208 2 19.2833 2 19V17.2C2 16.65 2.14167 16.1333 2.425 15.65C2.70833 15.1667 3.1 14.8 3.6 14.55C4.45 14.1167 5.40833 13.75 6.475 13.45C7.54167 13.15 8.71667 13 10 13H10.35C10.45 13 10.55 13.0167 10.65 13.05C10.5167 13.35 10.4042 13.6625 10.3125 13.9875C10.2208 14.3125 10.15 14.65 10.1 15H10C8.81667 15 7.75417 15.15 6.8125 15.45C5.87083 15.75 5.1 16.05 4.5 16.35C4.35 16.4333 4.22917 16.55 4.1375 16.7C4.04583 16.85 4 17.0167 4 17.2V18H10.3C10.4 18.35 10.5333 18.6958 10.7 19.0375C10.8667 19.3792 11.05 19.7 11.25 20H3ZM10 10C10.55 10 11.0208 9.80417 11.4125 9.4125C11.8042 9.02083 12 8.55 12 8C12 7.45 11.8042 6.97917 11.4125 6.5875C11.0208 6.19583 10.55 6 10 6C9.45 6 8.97917 6.19583 8.5875 6.5875C8.19583 6.97917 8 7.45 8 8C8 8.55 8.19583 9.02083 8.5875 9.4125C8.97917 9.80417 9.45 10 10 10ZM17 18C17.55 18 18.0208 17.8042 18.4125 17.4125C18.8042 17.0208 19 16.55 19 16C19 15.45 18.8042 14.9792 18.4125 14.5875C18.0208 14.1958 17.55 14 17 14C16.45 14 15.9792 14.1958 15.5875 14.5875C15.1958 14.9792 15 15.45 15 16C15 16.55 15.1958 17.0208 15.5875 17.4125C15.9792 17.8042 16.45 18 17 18ZM15.7 19.5C15.5 19.4167 15.3125 19.3292 15.1375 19.2375C14.9625 19.1458 14.7833 19.0333 14.6 18.9L13.525 19.225C13.4083 19.2583 13.3 19.2542 13.2 19.2125C13.1 19.1708 13.0167 19.1 12.95 19L12.35 18C12.2833 17.9 12.2625 17.7917 12.2875 17.675C12.3125 17.5583 12.375 17.4583 12.475 17.375L13.3 16.65C13.2667 16.4167 13.25 16.2 13.25 16C13.25 15.8 13.2667 15.5833 13.3 15.35L12.475 14.625C12.375 14.5417 12.3125 14.4417 12.2875 14.325C12.2625 14.2083 12.2833 14.1 12.35 14L12.95 13C13.0167 12.9 13.1 12.8292 13.2 12.7875C13.3 12.7458 13.4083 12.7417 13.525 12.775L14.6 13.1C14.7833 12.9667 14.9625 12.8542 15.1375 12.7625C15.3125 12.6708 15.5 12.5833 15.7 12.5L15.925 11.4C15.9583 11.2833 16.0167 11.1875 16.1 11.1125C16.1833 11.0375 16.2833 11 16.4 11H17.6C17.7167 11 17.8167 11.0375 17.9 11.1125C17.9833 11.1875 18.0417 11.2833 18.075 11.4L18.3 12.5C18.5 12.5833 18.6875 12.675 18.8625 12.775C19.0375 12.875 19.2167 13 19.4 13.15L20.45 12.775C20.5667 12.725 20.6792 12.725 20.7875 12.775C20.8958 12.825 20.9833 12.9 21.05 13L21.65 14.05C21.7167 14.15 21.7417 14.2583 21.725 14.375C21.7083 14.4917 21.65 14.5917 21.55 14.675L20.7 15.4C20.7333 15.6 20.75 15.8083 20.75 16.025C20.75 16.2417 20.7333 16.45 20.7 16.65L21.525 17.375C21.625 17.4583 21.6875 17.5583 21.7125 17.675C21.7375 17.7917 21.7167 17.9 21.65 18L21.05 19C20.9833 19.1 20.9 19.1708 20.8 19.2125C20.7 19.2542 20.5917 19.2583 20.475 19.225L19.4 18.9C19.2167 19.0333 19.0375 19.1458 18.8625 19.2375C18.6875 19.3292 18.5 19.4167 18.3 19.5L18.075 20.6C18.0417 20.7167 17.9833 20.8125 17.9 20.8875C17.8167 20.9625 17.7167 21 17.6 21H16.4C16.2833 21 16.1833 20.9625 16.1 20.8875C16.0167 20.8125 15.9583 20.7167 15.925 20.6L15.7 19.5Z" fill="#222222"/>
									</g>
								</svg>
							</span>
							<?php echo esc_html__( 'Customer', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?>
							</button>
							<button class="tablinks coupon-settings" onclick="wpsslwTab(event, 'coupon-settings')" 
							<?php
							if ( ! empty( $wpsslw_error ) || ! empty( $disbledbtn ) ) {
								echo 'disabled="disabled"'; }
							?>
							> <span class="tabicon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<mask id="mask0_423_2559" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
									<rect width="24" height="24" fill="#D9D9D9"/>
									</mask>
									<g mask="url(#mask0_423_2559)">
									<path d="M12 13.9L13.5 15.05C13.6833 15.2 13.8833 15.2042 14.1 15.0625C14.3167 14.9208 14.3833 14.7333 14.3 14.5L13.7 12.6L15.375 11.3C15.5583 11.15 15.6083 10.9625 15.525 10.7375C15.4417 10.5125 15.2833 10.4 15.05 10.4H13.1L12.475 8.475C12.3917 8.24167 12.2333 8.125 12 8.125C11.7667 8.125 11.6083 8.24167 11.525 8.475L10.9 10.4H8.925C8.69167 10.4 8.5375 10.5125 8.4625 10.7375C8.3875 10.9625 8.44167 11.15 8.625 11.3L10.25 12.6L9.65 14.525C9.56667 14.7583 9.625 14.9458 9.825 15.0875C10.025 15.2292 10.225 15.225 10.425 15.075L12 13.9ZM4 20C3.45 20 2.97917 19.8042 2.5875 19.4125C2.19583 19.0208 2 18.55 2 18V14.625C2 14.4417 2.05833 14.2833 2.175 14.15C2.29167 14.0167 2.44167 13.9333 2.625 13.9C3.025 13.7667 3.35417 13.525 3.6125 13.175C3.87083 12.825 4 12.4333 4 12C4 11.5667 3.87083 11.175 3.6125 10.825C3.35417 10.475 3.025 10.2333 2.625 10.1C2.44167 10.0667 2.29167 9.98333 2.175 9.85C2.05833 9.71667 2 9.55833 2 9.375V6C2 5.45 2.19583 4.97917 2.5875 4.5875C2.97917 4.19583 3.45 4 4 4H20C20.55 4 21.0208 4.19583 21.4125 4.5875C21.8042 4.97917 22 5.45 22 6V9.375C22 9.55833 21.9417 9.71667 21.825 9.85C21.7083 9.98333 21.5583 10.0667 21.375 10.1C20.975 10.2333 20.6458 10.475 20.3875 10.825C20.1292 11.175 20 11.5667 20 12C20 12.4333 20.1292 12.825 20.3875 13.175C20.6458 13.525 20.975 13.7667 21.375 13.9C21.5583 13.9333 21.7083 14.0167 21.825 14.15C21.9417 14.2833 22 14.4417 22 14.625V18C22 18.55 21.8042 19.0208 21.4125 19.4125C21.0208 19.8042 20.55 20 20 20H4ZM4 18H20V15.45C19.3833 15.0833 18.8958 14.5958 18.5375 13.9875C18.1792 13.3792 18 12.7167 18 12C18 11.2833 18.1792 10.6208 18.5375 10.0125C18.8958 9.40417 19.3833 8.91667 20 8.55V6H4V8.55C4.61667 8.91667 5.10417 9.40417 5.4625 10.0125C5.82083 10.6208 6 11.2833 6 12C6 12.7167 5.82083 13.3792 5.4625 13.9875C5.10417 14.5958 4.61667 15.0833 4 15.45V18Z" fill="#222222"/>
									</g>
								</svg>
							</span> 
							<?php echo esc_html__( 'Coupon', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?>
							</button>
							<?php
							$wpsslw_tabs = apply_filters( 'wpsyncsheets_settings_tabs', array() );
							if ( is_array( $wpsslw_tabs ) && ! empty( $wpsslw_tabs ) ) {
								foreach ( $wpsslw_tabs as $tabkey => $tabname ) {
									?>
										<button class="tablinks <?php echo esc_html( $tabkey ); ?>" onclick="wpsslwTab(event, '<?php echo esc_html( $tabkey ); ?>')" 
										<?php
										if ( ! empty( $wpsslw_error ) || ! empty( $disbledbtn ) ) {
											echo 'disabled="disabled"'; }
										?>
										> <span class="tabicon">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<mask id="mask0_423_2471" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
												<rect width="24" height="24" fill="#D9D9D9"/>
												</mask>
												<g mask="url(#mask0_423_2471)">
												<path d="M13.8753 22H10.1253C9.87534 22 9.65867 21.9167 9.47534 21.75C9.292 21.5833 9.18367 21.375 9.15034 21.125L8.85034 18.8C8.63367 18.7167 8.4295 18.6167 8.23784 18.5C8.04617 18.3833 7.85867 18.2583 7.67534 18.125L5.50034 19.025C5.267 19.1083 5.03367 19.1167 4.80034 19.05C4.567 18.9833 4.38367 18.8417 4.25034 18.625L2.40034 15.4C2.267 15.1833 2.22534 14.95 2.27534 14.7C2.32534 14.45 2.45034 14.25 2.65034 14.1L4.52534 12.675C4.50867 12.5583 4.50034 12.4458 4.50034 12.3375V11.6625C4.50034 11.5542 4.50867 11.4417 4.52534 11.325L2.65034 9.9C2.45034 9.75 2.32534 9.55 2.27534 9.3C2.22534 9.05 2.267 8.81667 2.40034 8.6L4.25034 5.375C4.367 5.14167 4.54617 4.99583 4.78784 4.9375C5.0295 4.87917 5.267 4.89167 5.50034 4.975L7.67534 5.875C7.85867 5.74167 8.05034 5.61667 8.25034 5.5C8.45034 5.38333 8.65034 5.28333 8.85034 5.2L9.15034 2.875C9.18367 2.625 9.292 2.41667 9.47534 2.25C9.65867 2.08333 9.87534 2 10.1253 2H13.8753C14.1253 2 14.342 2.08333 14.5253 2.25C14.7087 2.41667 14.817 2.625 14.8503 2.875L15.1503 5.2C15.367 5.28333 15.5712 5.38333 15.7628 5.5C15.9545 5.61667 16.142 5.74167 16.3253 5.875L18.5003 4.975C18.7337 4.89167 18.967 4.88333 19.2003 4.95C19.4337 5.01667 19.617 5.15833 19.7503 5.375L21.6003 8.6C21.7337 8.81667 21.7753 9.05 21.7253 9.3C21.6753 9.55 21.5503 9.75 21.3503 9.9L19.4753 11.325C19.492 11.4417 19.5003 11.5542 19.5003 11.6625V12.3375C19.5003 12.4458 19.4837 12.5583 19.4503 12.675L21.3253 14.1C21.5253 14.25 21.6503 14.45 21.7003 14.7C21.7503 14.95 21.7087 15.1833 21.5753 15.4L19.7253 18.6C19.592 18.8167 19.4045 18.9625 19.1628 19.0375C18.9212 19.1125 18.6837 19.1083 18.4503 19.025L16.3253 18.125C16.142 18.2583 15.9503 18.3833 15.7503 18.5C15.5503 18.6167 15.3503 18.7167 15.1503 18.8L14.8503 21.125C14.817 21.375 14.7087 21.5833 14.5253 21.75C14.342 21.9167 14.1253 22 13.8753 22ZM12.0503 15.5C13.017 15.5 13.842 15.1583 14.5253 14.475C15.2087 13.7917 15.5503 12.9667 15.5503 12C15.5503 11.0333 15.2087 10.2083 14.5253 9.525C13.842 8.84167 13.017 8.5 12.0503 8.5C11.067 8.5 10.2378 8.84167 9.56284 9.525C8.88784 10.2083 8.55034 11.0333 8.55034 12C8.55034 12.9667 8.88784 13.7917 9.56284 14.475C10.2378 15.1583 11.067 15.5 12.0503 15.5ZM12.0503 13.5C11.6337 13.5 11.2795 13.3542 10.9878 13.0625C10.6962 12.7708 10.5503 12.4167 10.5503 12C10.5503 11.5833 10.6962 11.2292 10.9878 10.9375C11.2795 10.6458 11.6337 10.5 12.0503 10.5C12.467 10.5 12.8212 10.6458 13.1128 10.9375C13.4045 11.2292 13.5503 11.5833 13.5503 12C13.5503 12.4167 13.4045 12.7708 13.1128 13.0625C12.8212 13.3542 12.467 13.5 12.0503 13.5ZM11.0003 20H12.9753L13.3253 17.35C13.842 17.2167 14.3212 17.0208 14.7628 16.7625C15.2045 16.5042 15.6087 16.1917 15.9753 15.825L18.4503 16.85L19.4253 15.15L17.2753 13.525C17.3587 13.2917 17.417 13.0458 17.4503 12.7875C17.4837 12.5292 17.5003 12.2667 17.5003 12C17.5003 11.7333 17.4837 11.4708 17.4503 11.2125C17.417 10.9542 17.3587 10.7083 17.2753 10.475L19.4253 8.85L18.4503 7.15L15.9753 8.2C15.6087 7.81667 15.2045 7.49583 14.7628 7.2375C14.3212 6.97917 13.842 6.78333 13.3253 6.65L13.0003 4H11.0253L10.6753 6.65C10.1587 6.78333 9.6795 6.97917 9.23784 7.2375C8.79617 7.49583 8.392 7.80833 8.02534 8.175L5.55034 7.15L4.57534 8.85L6.72534 10.45C6.642 10.7 6.58367 10.95 6.55034 11.2C6.517 11.45 6.50034 11.7167 6.50034 12C6.50034 12.2667 6.517 12.525 6.55034 12.775C6.58367 13.025 6.642 13.275 6.72534 13.525L4.57534 15.15L5.55034 16.85L8.02534 15.8C8.392 16.1833 8.79617 16.5042 9.23784 16.7625C9.6795 17.0208 10.1587 17.2167 10.6753 17.35L11.0003 20Z" fill="#222222"/>
												</g>
											</svg>
										</span> 
										<?php echo esc_html( $tabname ); ?></button>
									<?php
								}
							}
							?>
							<button class="tablinks general-settings" onclick="wpsslwTab(event, 'general-settings')" 
								<?php
								if ( ! empty( $wpsslw_error ) || ! empty( $disbledbtn ) ) {
									echo 'disabled="disabled"'; }
								?>
								> <span class="tabicon">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<mask id="mask0_423_2471" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
										<rect width="24" height="24" fill="#D9D9D9"/>
										</mask>
										<g mask="url(#mask0_423_2471)">
										<path d="M13.8753 22H10.1253C9.87534 22 9.65867 21.9167 9.47534 21.75C9.292 21.5833 9.18367 21.375 9.15034 21.125L8.85034 18.8C8.63367 18.7167 8.4295 18.6167 8.23784 18.5C8.04617 18.3833 7.85867 18.2583 7.67534 18.125L5.50034 19.025C5.267 19.1083 5.03367 19.1167 4.80034 19.05C4.567 18.9833 4.38367 18.8417 4.25034 18.625L2.40034 15.4C2.267 15.1833 2.22534 14.95 2.27534 14.7C2.32534 14.45 2.45034 14.25 2.65034 14.1L4.52534 12.675C4.50867 12.5583 4.50034 12.4458 4.50034 12.3375V11.6625C4.50034 11.5542 4.50867 11.4417 4.52534 11.325L2.65034 9.9C2.45034 9.75 2.32534 9.55 2.27534 9.3C2.22534 9.05 2.267 8.81667 2.40034 8.6L4.25034 5.375C4.367 5.14167 4.54617 4.99583 4.78784 4.9375C5.0295 4.87917 5.267 4.89167 5.50034 4.975L7.67534 5.875C7.85867 5.74167 8.05034 5.61667 8.25034 5.5C8.45034 5.38333 8.65034 5.28333 8.85034 5.2L9.15034 2.875C9.18367 2.625 9.292 2.41667 9.47534 2.25C9.65867 2.08333 9.87534 2 10.1253 2H13.8753C14.1253 2 14.342 2.08333 14.5253 2.25C14.7087 2.41667 14.817 2.625 14.8503 2.875L15.1503 5.2C15.367 5.28333 15.5712 5.38333 15.7628 5.5C15.9545 5.61667 16.142 5.74167 16.3253 5.875L18.5003 4.975C18.7337 4.89167 18.967 4.88333 19.2003 4.95C19.4337 5.01667 19.617 5.15833 19.7503 5.375L21.6003 8.6C21.7337 8.81667 21.7753 9.05 21.7253 9.3C21.6753 9.55 21.5503 9.75 21.3503 9.9L19.4753 11.325C19.492 11.4417 19.5003 11.5542 19.5003 11.6625V12.3375C19.5003 12.4458 19.4837 12.5583 19.4503 12.675L21.3253 14.1C21.5253 14.25 21.6503 14.45 21.7003 14.7C21.7503 14.95 21.7087 15.1833 21.5753 15.4L19.7253 18.6C19.592 18.8167 19.4045 18.9625 19.1628 19.0375C18.9212 19.1125 18.6837 19.1083 18.4503 19.025L16.3253 18.125C16.142 18.2583 15.9503 18.3833 15.7503 18.5C15.5503 18.6167 15.3503 18.7167 15.1503 18.8L14.8503 21.125C14.817 21.375 14.7087 21.5833 14.5253 21.75C14.342 21.9167 14.1253 22 13.8753 22ZM12.0503 15.5C13.017 15.5 13.842 15.1583 14.5253 14.475C15.2087 13.7917 15.5503 12.9667 15.5503 12C15.5503 11.0333 15.2087 10.2083 14.5253 9.525C13.842 8.84167 13.017 8.5 12.0503 8.5C11.067 8.5 10.2378 8.84167 9.56284 9.525C8.88784 10.2083 8.55034 11.0333 8.55034 12C8.55034 12.9667 8.88784 13.7917 9.56284 14.475C10.2378 15.1583 11.067 15.5 12.0503 15.5ZM12.0503 13.5C11.6337 13.5 11.2795 13.3542 10.9878 13.0625C10.6962 12.7708 10.5503 12.4167 10.5503 12C10.5503 11.5833 10.6962 11.2292 10.9878 10.9375C11.2795 10.6458 11.6337 10.5 12.0503 10.5C12.467 10.5 12.8212 10.6458 13.1128 10.9375C13.4045 11.2292 13.5503 11.5833 13.5503 12C13.5503 12.4167 13.4045 12.7708 13.1128 13.0625C12.8212 13.3542 12.467 13.5 12.0503 13.5ZM11.0003 20H12.9753L13.3253 17.35C13.842 17.2167 14.3212 17.0208 14.7628 16.7625C15.2045 16.5042 15.6087 16.1917 15.9753 15.825L18.4503 16.85L19.4253 15.15L17.2753 13.525C17.3587 13.2917 17.417 13.0458 17.4503 12.7875C17.4837 12.5292 17.5003 12.2667 17.5003 12C17.5003 11.7333 17.4837 11.4708 17.4503 11.2125C17.417 10.9542 17.3587 10.7083 17.2753 10.475L19.4253 8.85L18.4503 7.15L15.9753 8.2C15.6087 7.81667 15.2045 7.49583 14.7628 7.2375C14.3212 6.97917 13.842 6.78333 13.3253 6.65L13.0003 4H11.0253L10.6753 6.65C10.1587 6.78333 9.6795 6.97917 9.23784 7.2375C8.79617 7.49583 8.392 7.80833 8.02534 8.175L5.55034 7.15L4.57534 8.85L6.72534 10.45C6.642 10.7 6.58367 10.95 6.55034 11.2C6.517 11.45 6.50034 11.7167 6.50034 12C6.50034 12.2667 6.517 12.525 6.55034 12.775C6.58367 13.025 6.642 13.275 6.72534 13.525L4.57534 15.15L5.55034 16.85L8.02534 15.8C8.392 16.1833 8.79617 16.5042 9.23784 16.7625C9.6795 17.0208 10.1587 17.2167 10.6753 17.35L11.0003 20Z" fill="#222222"/>
										</g>
									</svg>
								</span>
								<?php echo esc_html__( 'General', 'wpss' ); ?> <br><?php echo esc_html__( 'Settings', 'wpss' ); ?></button>
						</div>
						<div id="order-settings" class="tabcontent active">
							<?php
											
							$wpsslw_google_settings = self::$instance_api->wpsslw_option( 'wpssw_google_settings' );
							if ( ! empty( $wpsslw_google_settings[2] ) ) {
								?>
							<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-woocommerce&tab=order-settings' ) ); ?>" id="mainform">					
								<?php
								wp_nonce_field( 'save_general_settings', 'wpsslw_general_settings' );
								if ( \WPSSLW_Dependencies::wpsslw_woocommerce_active_check() ) {
									if ( self::$instance_api->checkcredenatials() ) {
										woocommerce_admin_fields( WPSSLW_Order::wpsslw_get_settings() );
										?>
									<div class="submit-section">
										<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
									</div>
										<?php
									}
								} else {
									?>
									<div class="generalSetting-section generalSetting-section-message">
										<h4><?php echo 'WPSyncSheets Lite for WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!'; ?></h4>
									</div>
								<?php } ?>
							</form>
								<?php
							} else {
								?>
							<div class="generalSetting-section generalSetting-section-message">
								<h4><?php echo esc_html__( 'Please genearate authentication code from', 'wpss' ); ?>
									<strong><?php echo esc_html__( 'Google API Setting', 'wpss' ); ?></strong>
									<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpss' ); ?></a>
								</h4>
							</div>
							<?php } ?>
						</div>

						<!-- Product Settings html start. -->


						<div id="product-settings" class="tabcontent">
							<?php

							$wpsslw_product_spreadsheet_setting = self::$instance_api->wpsslw_option( 'wpssw_product_spreadsheet_setting' );
							$wpsslw_product_spreadsheet_id      = self::$instance_api->wpsslw_option( 'wpssw_product_spreadsheet_id' );
							$wpsslw_spreadsheet_id              = self::$instance_api->wpsslw_option( 'wpssw_woocommerce_spreadsheet' );
							$wpsslw_prdsheet_id                 = $wpsslw_product_spreadsheet_id;

							$wpsslw_productsheet_name = '';
							if ( self::wpsslw_check_sheet_exist( $wpsslw_prdsheet_id, 'All Products' ) ) {
								$wpsslw_productsheet_name = 'All Products';
							}

							if ( empty( $wpsslw_product_spreadsheet_id ) ) {
								$wpsslw_product_spreadsheet_id = $wpsslw_spreadsheet_id;
							}
								$wpsslw_checked = '';
							if ( 'yes' === (string) $wpsslw_product_spreadsheet_setting ) {
								$wpsslw_checked = 'checked';
							}

							$wpsslw_product_custom_value = array();

							?>
							<?php
							if ( ! empty( $wpsslw_google_settings[2] ) ) {
								?>
							<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-woocommerce&tab=product-settings' ) ); ?>" id="productform">
								<?php wp_nonce_field( 'save_product_settings', 'wpsslw_product_settings' ); ?>
								<input name="product_sheet" id="product_sheet" type="hidden" class="" value="<?php echo esc_html( $wpsslw_productsheet_name ); ?>">					
									<div valign="top" class="checkbox_margin generalSetting-section">
										<div scope="row" class="titledesc generalSetting-left">
											<h4><?php echo esc_html__( 'Product Settings', 'wpssw' ); ?></h4>
											<p><?php echo esc_html__( 'Enable this option to automatically generate customized spreadsheets, including the essential "All Products" sheet.', 'wpssw' ); ?></p>
										</div>
										<div class="forminp generalSetting-right">              
											<label for="product_settings_checkbox">
												<input name="product_settings_checkbox" id="product_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_attr( $wpsslw_checked ); ?>><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 		
											</label>
										</div>
									</div>
									<?php
									$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
									?>
									<div class="generalSetting-section prd_spreadsheet_row googleSpreadsheet-section">
										<div class="generalSetting-left">
											<h4><?php echo esc_html__( 'Google Spreadsheet Settings', 'wpssw' ); ?></h4>
											<p><?php echo esc_html__( 'Upon assigning a Google Spreadsheet, it will automatically generate the "All Products" sheet, populated with specified sheet headers. Additionally, whenever new products are created, WPSyncSheets add new rows dynamically to keep everything up to date.', 'wpssw' ); ?></p>
											<div class="createanew-radio">
												<div class="createanew-radio-box">
													<input type="radio" name="prdsheetselection" value="new" id="prdcreateanew">
													<label for="prdcreateanew"><?php echo esc_html__( 'Create New Spreadsheet', 'wpssw' ); ?></label>
												</div>
												<div class="createanew-radio-box">
													<input type="radio" name="prdsheetselection" value="existing" id="prdexisting" checked="checked">
													<label for="prdexisting"><?php echo esc_html__( 'Select Existing Spreadsheet', 'wpssw' ); ?></label>
												</div>
											</div>
											<div id="product_spreadsheet_container" class="spreadsheet-form">
												<select name="product_spreadsheet" id="product_spreadsheet" style="min-width:150px;" class="">
													<?php
													$selected = '';
													foreach ( $spreadsheets_list as $spreadsheetid => $spreadsheetname ) {
														if ( (string) $wpsslw_product_spreadsheet_id === $spreadsheetid ) {
															$selected = 'selected="selected"';
														}
														?>
													<option value="<?php echo esc_attr( $spreadsheetid ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $spreadsheetname ); ?></option>
													<?php $selected = ''; } ?>
												</select>
											</div>
											<div valign="top" id="product_newsheet" class="newsheetinput spreadsheet-form prd_spreadsheet_inputrow">
												<input name="product_spreadsheet_name" id="product_spreadsheet_name" type="text" class="input-text" value="" placeholder="<?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?>"><span class="checkbox-switch"></span><span class="checkbox-switch"></span>
											</div>
										</div>
									</div>
									<div class="prd_spreadsheet_row generalSetting-section sheetHeaders-section">
										<div class="td-wpssw-headers">
											<div class="wpssw_headers">
												<div class="generalSetting-sheet-headers-row">
													<div class="generalSetting-left">
														<h4><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></h4>
														<p><?php echo esc_html__( 'Any disabled sheet headers will be automatically removed from the current spreadsheet. Re-enable the desired headers, save the settings, and your spreadsheet will be updated with the latest data with a single click of the "Click to Sync" button.', 'wpssw' ); ?></p>
													</div>
												</div>
												<br><br>
												<div class="sheetHeaders-main">
													<ul id="woo-product-sortable" class="sheetHeaders-box">
														<?php
														$wpsslw_woo_is_checked = '';
														$wpsslw_woo_selections = stripslashes_deep( self::$instance_api->wpsslw_option( 'wpssw_woo_product_headers' ) );
														if ( ! $wpsslw_woo_selections ) {
															$wpsslw_woo_selections = array();
														}
														$wpsslw_woo_selections_custom = stripslashes_deep( self::$instance_api->wpsslw_option( 'wpssw_woo_product_headers_custom' ) );
														if ( ! $wpsslw_woo_selections_custom ) {
															$wpsslw_woo_selections_custom = array();
														}

														$wpsslw_include = new WPSSLW_Include_Action();
														$wpsslw_include->wpsslw_include_product_compatibility_files();
														$wpsslw_wooproduct_headers = apply_filters( 'wpsyncsheets_product_headers', array() );
														$wpsslw_wooproduct_headers = self::wpsslw_array_flatten( $wpsslw_wooproduct_headers );
														$wpsslw_operation          = array( 'Insert', 'Update', 'Delete' );
														if ( ! empty( $wpsslw_woo_selections ) ) {
															foreach ( $wpsslw_woo_selections as $wpsslw_key => $wpsslw_val ) {
																$wpsslw_woo_is_checked = 'checked';
																$wpsslw_labelid        = strtolower( str_replace( ' ', '_', $wpsslw_val ) );
																$wpsslw_display        = true;
																$wpsslw_classname      = '';
																if ( in_array( $wpsslw_val, $wpsslw_operation, true ) ) {
																	$wpsslw_display   = false;
																	$wpsslw_labelid   = '';
																	$wpsslw_classname = strtolower( $wpsslw_val ) . 'product';
																}

																?>
														<li class="default-order-sheet ui-state-default <?php echo esc_html( $wpsslw_classname ); ?>">
															<label>
															<span class="orderSheet-left">
																<span class="wootextfield"><?php echo isset( $wpsslw_woo_selections_custom[ $wpsslw_key ] ) ? esc_attr( $wpsslw_woo_selections_custom[ $wpsslw_key ] ) : esc_attr( $wpsslw_val ); ?></span>
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
																	<input type="checkbox" name="wooproduct_custom[]" value="<?php echo isset( $wpsslw_woo_selections_custom[ $wpsslw_key ] ) ? esc_attr( $wpsslw_woo_selections_custom[ $wpsslw_key ] ) : esc_attr( $wpsslw_val ); ?>" class="woo-pro-headers-chk1" <?php echo esc_attr( $wpsslw_woo_is_checked ); ?> hidden="true">
																	<input type="checkbox" name="wooproduct_header_list[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" id="woo-<?php echo esc_attr( $wpsslw_labelid ); ?>" class="woo-pro-headers-chk" <?php echo esc_attr( $wpsslw_woo_is_checked ); ?>>
																	<?php if ( $wpsslw_display ) { ?>
																	<span class="checkbox-switch-new disabled-pro-version"></span>
																	<?php } ?>
																	<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link disabled-pro-version">
																		<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<path d="M1.95875 11.67C1.55759 11.67 1.21418 11.5272 0.928508 11.2415C0.642836 10.9558 0.5 10.6124 0.5 10.2113C0.5 9.81009 0.642836 9.46668 0.928508 9.18101C1.21418 8.89534 1.55759 8.7525 1.95875 8.7525C2.35991 8.7525 2.70332 8.89534 2.98899 9.18101C3.27466 9.46668 3.4175 9.81009 3.4175 10.2113C3.4175 10.6124 3.27466 10.9558 2.98899 11.2415C2.70332 11.5272 2.35991 11.67 1.95875 11.67ZM6.335 11.67C5.93384 11.67 5.59043 11.5272 5.30476 11.2415C5.01909 10.9558 4.87625 10.6124 4.87625 10.2113C4.87625 9.81009 5.01909 9.46668 5.30476 9.18101C5.59043 8.89534 5.93384 8.7525 6.335 8.7525C6.73616 8.7525 7.07957 8.89534 7.36524 9.18101C7.65091 9.46668 7.79375 9.81009 7.79375 10.2113C7.79375 10.6124 7.65091 10.9558 7.36524 11.2415C7.07957 11.5272 6.73616 11.67 6.335 11.67ZM1.95875 7.29375C1.55759 7.29375 1.21418 7.15091 0.928508 6.86524C0.642836 6.57957 0.5 6.23616 0.5 5.835C0.5 5.43384 0.642836 5.09043 0.928508 4.80476C1.21418 4.51909 1.55759 4.37625 1.95875 4.37625C2.35991 4.37625 2.70332 4.51909 2.98899 4.80476C3.27466 5.09043 3.4175 5.43384 3.4175 5.835C3.4175 6.23616 3.27466 6.57957 2.98899 6.86524C2.70332 7.15091 2.35991 7.29375 1.95875 7.29375ZM6.335 7.29375C5.93384 7.29375 5.59043 7.15091 5.30476 6.86524C5.01909 6.57957 4.87625 6.23616 4.87625 5.835C4.87625 5.43384 5.01909 5.09043 5.30476 4.80476C5.59043 4.51909 5.93384 4.37625 6.335 4.37625C6.73616 4.37625 7.07957 4.51909 7.36524 4.80476C7.65091 5.09043 7.79375 5.43384 7.79375 5.835C7.79375 6.23616 7.65091 6.57957 7.36524 6.86524C7.07957 7.15091 6.73616 7.29375 6.335 7.29375ZM1.95875 2.9175C1.55759 2.9175 1.21418 2.77466 0.928508 2.48899C0.642836 2.20332 0.5 1.85991 0.5 1.45875C0.5 1.05759 0.642836 0.71418 0.928508 0.428508C1.21418 0.142836 1.55759 0 1.95875 0C2.35991 0 2.70332 0.142836 2.98899 0.428508C3.27466 0.71418 3.4175 1.05759 3.4175 1.45875C3.4175 1.85991 3.27466 2.20332 2.98899 2.48899C2.70332 2.77466 2.35991 2.9175 1.95875 2.9175ZM6.335 2.9175C5.93384 2.9175 5.59043 2.77466 5.30476 2.48899C5.01909 2.20332 4.87625 1.85991 4.87625 1.45875C4.87625 1.05759 5.01909 0.71418 5.30476 0.428508C5.59043 0.142836 5.93384 0 6.335 0C6.73616 0 7.07957 0.142836 7.36524 0.428508C7.65091 0.71418 7.79375 1.05759 7.79375 1.45875C7.79375 1.85991 7.65091 2.20332 7.36524 2.48899C7.07957 2.77466 6.73616 2.9175 6.335 2.9175Z" fill="#64748B"/>
																		</svg>
																		<span class="tooltip-text">Upgrade To Pro</span>

																	</span>
																</span>
															</label>
														</li>
																<?php
															}
														}
														if ( ! empty( $wpsslw_wooproduct_headers ) ) {
															foreach ( $wpsslw_wooproduct_headers as $wpsslw_key => $wpsslw_val ) {
																$wpsslw_woo_is_checked = '';
																if ( in_array( $wpsslw_val, $wpsslw_woo_selections, true ) ) {
																	continue;
																}
																$wpsslw_labelid        = strtolower( str_replace( ' ', '_', $wpsslw_val ) );
																$wpsslw_woo_is_checked = 'checked';
																?>
																<li class="default-order-sheet ui-state-default">
																	<label>
																	<span class="orderSheet-left">
																		<span class="wootextfield"><?php echo esc_attr( $wpsslw_val ); ?></span>
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
																		<input type="checkbox" name="wooproduct_custom[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" class="woo-pro-headers-chk1" <?php echo esc_attr( $wpsslw_woo_is_checked ); ?> hidden="true">
																		<input type="checkbox" name="wooproduct_header_list[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" id="woo-<?php echo esc_attr( $wpsslw_labelid ); ?>" class="woo-pro-headers-chk" <?php echo esc_attr( $wpsslw_woo_is_checked ); ?>>
																		<span class="checkbox-switch-new disabled-pro-version"></span>
																		<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link disabled-pro-version">
																			<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
																				<path d="M1.95875 11.67C1.55759 11.67 1.21418 11.5272 0.928508 11.2415C0.642836 10.9558 0.5 10.6124 0.5 10.2113C0.5 9.81009 0.642836 9.46668 0.928508 9.18101C1.21418 8.89534 1.55759 8.7525 1.95875 8.7525C2.35991 8.7525 2.70332 8.89534 2.98899 9.18101C3.27466 9.46668 3.4175 9.81009 3.4175 10.2113C3.4175 10.6124 3.27466 10.9558 2.98899 11.2415C2.70332 11.5272 2.35991 11.67 1.95875 11.67ZM6.335 11.67C5.93384 11.67 5.59043 11.5272 5.30476 11.2415C5.01909 10.9558 4.87625 10.6124 4.87625 10.2113C4.87625 9.81009 5.01909 9.46668 5.30476 9.18101C5.59043 8.89534 5.93384 8.7525 6.335 8.7525C6.73616 8.7525 7.07957 8.89534 7.36524 9.18101C7.65091 9.46668 7.79375 9.81009 7.79375 10.2113C7.79375 10.6124 7.65091 10.9558 7.36524 11.2415C7.07957 11.5272 6.73616 11.67 6.335 11.67ZM1.95875 7.29375C1.55759 7.29375 1.21418 7.15091 0.928508 6.86524C0.642836 6.57957 0.5 6.23616 0.5 5.835C0.5 5.43384 0.642836 5.09043 0.928508 4.80476C1.21418 4.51909 1.55759 4.37625 1.95875 4.37625C2.35991 4.37625 2.70332 4.51909 2.98899 4.80476C3.27466 5.09043 3.4175 5.43384 3.4175 5.835C3.4175 6.23616 3.27466 6.57957 2.98899 6.86524C2.70332 7.15091 2.35991 7.29375 1.95875 7.29375ZM6.335 7.29375C5.93384 7.29375 5.59043 7.15091 5.30476 6.86524C5.01909 6.57957 4.87625 6.23616 4.87625 5.835C4.87625 5.43384 5.01909 5.09043 5.30476 4.80476C5.59043 4.51909 5.93384 4.37625 6.335 4.37625C6.73616 4.37625 7.07957 4.51909 7.36524 4.80476C7.65091 5.09043 7.79375 5.43384 7.79375 5.835C7.79375 6.23616 7.65091 6.57957 7.36524 6.86524C7.07957 7.15091 6.73616 7.29375 6.335 7.29375ZM1.95875 2.9175C1.55759 2.9175 1.21418 2.77466 0.928508 2.48899C0.642836 2.20332 0.5 1.85991 0.5 1.45875C0.5 1.05759 0.642836 0.71418 0.928508 0.428508C1.21418 0.142836 1.55759 0 1.95875 0C2.35991 0 2.70332 0.142836 2.98899 0.428508C3.27466 0.71418 3.4175 1.05759 3.4175 1.45875C3.4175 1.85991 3.27466 2.20332 2.98899 2.48899C2.70332 2.77466 2.35991 2.9175 1.95875 2.9175ZM6.335 2.9175C5.93384 2.9175 5.59043 2.77466 5.30476 2.48899C5.01909 2.20332 4.87625 1.85991 4.87625 1.45875C4.87625 1.05759 5.01909 0.71418 5.30476 0.428508C5.59043 0.142836 5.93384 0 6.335 0C6.73616 0 7.07957 0.142836 7.36524 0.428508C7.65091 0.71418 7.79375 1.05759 7.79375 1.45875C7.79375 1.85991 7.65091 2.20332 7.36524 2.48899C7.07957 2.77466 6.73616 2.9175 6.335 2.9175Z" fill="#64748B"/>
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
											</div>
										</div>
									</div>									
									<?php
									if ( ! empty( $wpsslw_prdsheet_id ) && ! empty( $wpsslw_productsheet_name ) ) {
										?>
										<div valign="top" id="prodsynctr" class="prd_spreadsheet_row checkbox_margin generalSetting-section" >
											<div class="titledesc generalSetting-left">
												<h4>
													<span class="wpssw-tooltio-link tooltip-right">
														<?php echo esc_html__( 'Sync Products', 'wpssw' ); ?>
														<span class="tooltip-text">Export</span>
													</span>
												</h4>
												<p><?php echo esc_html__( 'Add new products to your spreadsheet without modifying existing ones by clicking the "Click to Sync" button. This will automatically append all products in the selected date range not already in the sheet.', 'wpssw' ); ?></p>
												<div class="sync_all_fromtodate-main">
													<div class="syncall-radio radio-box-td">
														<div class="syncall-radio-box syncall-radio-allProducts">
															<input type="radio" name="prd_sync_all_checkbox" value="1" id="prd_sync_all" checked="checked">
															<label for="prd_sync_all"><?php echo esc_html__( 'All Products', 'wpssw' ); ?></label>
														</div>
														<div class="syncall-radio-box">		<input type="radio" value="0" disabled="'disabled'" class="disabled">
															<label>
																<span class="wpssw-tooltio-link tooltip-right">
																	Date Range
																</span>
															</label>
														</div>
														<div class="syncall-radio-box">
															<input type="radio" value="0" disabled="'disabled'" class="disabled">
															<label>
																<span class="wpssw-tooltio-link tooltip-right">
																	Product Sale Date Range<span class="tooltip-text">Pro</span>
																</span>
															</label>
														</div>
													</div>			
												</div>
												<div class="sync-button-box">
													<img src="images/spinner.gif" id="prodsyncloader">
													<span id="prodsynctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span>
													<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="prodsync">
														<?php echo esc_html__( 'Click to Sync', 'wpssw' ); ?>
													</a>
												</div>
									</div>
									</div>
										<?php
									}

									?>
									<?php
									if ( WPSSLW_Dependencies::wpsslw_woocommerce_active_check() ) {
										?>
								<div class="submit-section">						
									<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
								</div>
										<?php
									} else {
										?>
									<div class="generalSetting-section generalSetting-section-message">
										<h4><?php echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!'; ?></h4>
									</div>
										<?php
									}
									?>
							</form>
								<?php
							} else {
								?>
							<div class="generalSetting-section generalSetting-section-message">
								<h4><?php echo esc_html__( 'Please genearate authentication code from', 'wpssw' ); ?>
									<strong><?php echo esc_html__( 'Google API Setting ', 'wpssw' ); ?></strong>
									<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
								</h4>
							</div>
							<?php } ?>
						</div>

						<!-- Product Settings html end. -->

						<!-- Customer Settings html start. -->
						<div id="customer-settings" class="tabcontent">
						<?php
							$wpsslw_customer_spreadsheet_setting = self::wpsslw_option( 'wpssw_customer_spreadsheet_setting' );
							$wpsslw_customer_spreadsheet_id      = self::wpsslw_option( 'wpssw_customer_spreadsheet_id' );
							$wpsslw_spreadsheet_id               = self::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );
							$wpsslw_custsheet_id                 = $wpsslw_customer_spreadsheet_id;
							$wpsslw_customersheet_name           = '';
							$wpsslw_spreadsheets_list            = self::$instance_api->get_spreadsheet_listing();

						if ( self::wpsslw_check_sheet_exist( $wpsslw_custsheet_id, 'All Customers' ) ) {
							$wpsslw_customersheet_name = 'All Customers';
						}

						if ( empty( $wpsslw_customer_spreadsheet_id ) ) {
							$wpsslw_customer_spreadsheet_id = $wpsslw_spreadsheet_id;
						}
						$wpsslw_checked = '';
						if ( 'yes' === (string) $wpsslw_customer_spreadsheet_setting ) {
							$wpsslw_checked = 'checked';
						}
						?>
						<?php
						if ( ! empty( $wpsslw_google_settings[2] ) ) {
							?>
						<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-woocommerce&tab=customer-settings' ) ); ?>" id="customerform">
							<?php wp_nonce_field( 'save_customer_settings', 'wpssw_customer_settings' ); ?>
							<input name="customer_sheet" id="customer_sheet" type="hidden" class="" value="<?php echo esc_html( $wpsslw_customersheet_name ); ?>">
								<div valign="top" class="checkbox_margin generalSetting-section">
									<div scope="row" class="titledesc generalSetting-left">
										<h4><?php echo esc_html__( 'Customer Settings', 'wpssw' ); ?></h4>
										<p><?php echo esc_html__( 'Enable this option to streamline your customer management process with personalized spreadsheets and an "All Customers" sheet.', 'wpssw' ); ?></p>
									</div>
									<div class="forminp generalSetting-right">              
										<label for="customer_settings_checkbox">
											<input name="customer_settings_checkbox" id="customer_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_html( $wpsslw_checked ); ?>><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 							
										</label>
									</div>
								</div>
								<?php
								$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
								?>
								<div class="generalSetting-section googleSpreadsheet-section cust_spreadsheet_row">
									<div class="generalSetting-left">
										<h4><?php echo esc_html__( 'Google Spreadsheet Settings', 'wpssw' ); ?></h4>
										<p><?php echo esc_html__( "Once you've assigned a Google Spreadsheet, it will automatically create an 'All Customers' sheet and populate it with headers based on your settings. The spreadsheet will update with new rows whenever new customers are added.", 'wpssw' ); ?></p>
										<div class="createanew-radio">
											<div class="createanew-radio-box">
												<input type="radio" name="custsheetselection" value="new" id="custcreateanew">
												<label for="custcreateanew"><?php echo esc_html__( 'Create New Spreadsheet', 'wpssw' ); ?></label>
											</div>
											<div class="createanew-radio-box">
												<input type="radio" name="custsheetselection" value="existing" id="custexisting" checked="checked">
												<label for="custexisting"><?php echo esc_html__( 'Select Existing Spreadsheet', 'wpssw' ); ?></label>
											</div>
										</div>
										<div id="customer_spreadsheet_container" class="spreadsheet-form">
											<select name="customer_spreadsheet" id="customer_spreadsheet" style="min-width:150px;" class="">
											<?php
											$selected = '';
											foreach ( $spreadsheets_list as $spreadsheetid => $spreadsheetname ) {
												if ( (string) $wpsslw_customer_spreadsheet_id === $spreadsheetid ) {
													$selected = 'selected="selected"';
												}
												?>
												<option value="<?php echo esc_attr( $spreadsheetid ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $spreadsheetname ); ?></option>
												<?php
												$selected = '';
											}
											?>
											</select>
										</div>
										<div valign="top" id="cust_newsheet" class="newsheetinput spreadsheet-form cust_spreadsheet_inputrow">
											<input name="customer_spreadsheet_name" id="customer_spreadsheet_name" type="text" class="input-text" value="" placeholder="<?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?>">
										</div>
									</div>
								</div>
								<div class="cust_spreadsheet_row generalSetting-section sheetHeaders-section">
									<div class="td-wpssw-headers">
										<div class="wpssw_headers">
											<div class="generalSetting-sheet-headers-row">
												<div class="generalSetting-left">
													<h4><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></h4>
													<p><?php echo esc_html__( 'Any disabled sheet headers will be automatically removed from the current spreadsheet. To include specific headers, enable them, save the settings, and click the "Click to Sync" button to update the spreadsheet with the latest data.', 'wpssw' ); ?></p>
												</div>
											</div>
											<br><br>
											<div class="sheetHeaders-main">
												<ul id="woo-customer-sortable" class="sheetHeaders-box">
													<?php
													$wpsslw_woo_is_checked = '';

													$wpsslw_woo_selections = stripslashes_deep( self::wpsslw_option( 'wpssw_woo_customer_headers' ) );
													if ( ! $wpsslw_woo_selections ) {
														$wpsslw_woo_selections = array();
													}
													$wpsslw_woo_selections_custom = stripslashes_deep( self::wpsslw_option( 'wpssw_woo_customer_headers_custom' ) );
													if ( ! $wpsslw_woo_selections_custom ) {
														$wpsslw_woo_selections_custom = array();
													}
													$wpsslw_include = new WPSSLW_Include_Action();
													$wpsslw_include->wpsslw_include_customer_compatibility_files();
													$wpsslw_woocustomer_headers = apply_filters( 'wpsyncsheets_customer_headers', array() );
													$wpsslw_woocustomer_headers = self::wpsslw_array_flatten( $wpsslw_woocustomer_headers );
													$wpsslw_operation           = array( 'Insert', 'Update', 'Delete' );
													if ( ! empty( $wpsslw_woo_selections ) ) {
														foreach ( $wpsslw_woo_selections as $wpsslw_key => $wpsslw_val ) {
															$wpsslw_woo_is_checked = 'checked';
															$wpsslw_labelid        = strtolower( str_replace( ' ', '_', $wpsslw_val ) );
															$wpsslw_display        = true;
															$wpsslw_classname      = '';
															if ( in_array( $wpsslw_val, $wpsslw_operation, true ) ) {
																$wpsslw_display   = false;
																$wpsslw_labelid   = '';
																$wpsslw_classname = strtolower( $wpsslw_val ) . 'customer';
															}
															?>
													<li class="default-order-sheet ui-state-default <?php echo esc_html( $wpsslw_classname ); ?>">
														<label>
															<span class="orderSheet-left">
																<span class="wootextfield"><?php echo isset( $wpsslw_woo_selections_custom[ $wpsslw_key ] ) ? esc_attr( $wpsslw_woo_selections_custom[ $wpsslw_key ] ) : esc_attr( $wpsslw_val ); ?></span>
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
															<input type="checkbox" name="woocustomer_custom[]" value="<?php echo isset( $wpsslw_woo_selections_custom[ $wpsslw_key ] ) ? esc_attr( $wpsslw_woo_selections_custom[ $wpsslw_key ] ) : esc_attr( $wpsslw_val ); ?>" class="woo-cust-headers-chk1" <?php echo esc_html( $wpsslw_woo_is_checked ); ?> hidden="true">

															<input type="checkbox" name="woocustomer_header_list[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" id="woo-<?php echo esc_attr( $wpsslw_labelid ); ?>" class="woo-cust-headers-chk" <?php echo esc_html( $wpsslw_woo_is_checked ); ?>>
															<?php if ( $wpsslw_display ) { ?>
															<span class="checkbox-switch-new disabled-pro-version"></span>
															<?php } ?>
															<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link disabled-pro-version">
																<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<path d="M1.95875 11.67C1.55759 11.67 1.21418 11.5272 0.928508 11.2415C0.642836 10.9558 0.5 10.6124 0.5 10.2113C0.5 9.81009 0.642836 9.46668 0.928508 9.18101C1.21418 8.89534 1.55759 8.7525 1.95875 8.7525C2.35991 8.7525 2.70332 8.89534 2.98899 9.18101C3.27466 9.46668 3.4175 9.81009 3.4175 10.2113C3.4175 10.6124 3.27466 10.9558 2.98899 11.2415C2.70332 11.5272 2.35991 11.67 1.95875 11.67ZM6.335 11.67C5.93384 11.67 5.59043 11.5272 5.30476 11.2415C5.01909 10.9558 4.87625 10.6124 4.87625 10.2113C4.87625 9.81009 5.01909 9.46668 5.30476 9.18101C5.59043 8.89534 5.93384 8.7525 6.335 8.7525C6.73616 8.7525 7.07957 8.89534 7.36524 9.18101C7.65091 9.46668 7.79375 9.81009 7.79375 10.2113C7.79375 10.6124 7.65091 10.9558 7.36524 11.2415C7.07957 11.5272 6.73616 11.67 6.335 11.67ZM1.95875 7.29375C1.55759 7.29375 1.21418 7.15091 0.928508 6.86524C0.642836 6.57957 0.5 6.23616 0.5 5.835C0.5 5.43384 0.642836 5.09043 0.928508 4.80476C1.21418 4.51909 1.55759 4.37625 1.95875 4.37625C2.35991 4.37625 2.70332 4.51909 2.98899 4.80476C3.27466 5.09043 3.4175 5.43384 3.4175 5.835C3.4175 6.23616 3.27466 6.57957 2.98899 6.86524C2.70332 7.15091 2.35991 7.29375 1.95875 7.29375ZM6.335 7.29375C5.93384 7.29375 5.59043 7.15091 5.30476 6.86524C5.01909 6.57957 4.87625 6.23616 4.87625 5.835C4.87625 5.43384 5.01909 5.09043 5.30476 4.80476C5.59043 4.51909 5.93384 4.37625 6.335 4.37625C6.73616 4.37625 7.07957 4.51909 7.36524 4.80476C7.65091 5.09043 7.79375 5.43384 7.79375 5.835C7.79375 6.23616 7.65091 6.57957 7.36524 6.86524C7.07957 7.15091 6.73616 7.29375 6.335 7.29375ZM1.95875 2.9175C1.55759 2.9175 1.21418 2.77466 0.928508 2.48899C0.642836 2.20332 0.5 1.85991 0.5 1.45875C0.5 1.05759 0.642836 0.71418 0.928508 0.428508C1.21418 0.142836 1.55759 0 1.95875 0C2.35991 0 2.70332 0.142836 2.98899 0.428508C3.27466 0.71418 3.4175 1.05759 3.4175 1.45875C3.4175 1.85991 3.27466 2.20332 2.98899 2.48899C2.70332 2.77466 2.35991 2.9175 1.95875 2.9175ZM6.335 2.9175C5.93384 2.9175 5.59043 2.77466 5.30476 2.48899C5.01909 2.20332 4.87625 1.85991 4.87625 1.45875C4.87625 1.05759 5.01909 0.71418 5.30476 0.428508C5.59043 0.142836 5.93384 0 6.335 0C6.73616 0 7.07957 0.142836 7.36524 0.428508C7.65091 0.71418 7.79375 1.05759 7.79375 1.45875C7.79375 1.85991 7.65091 2.20332 7.36524 2.48899C7.07957 2.77466 6.73616 2.9175 6.335 2.9175Z" fill="#64748B"/>
																</svg>

																<span class="tooltip-text">Upgrade To Pro</span>
															</span>
														</span>
														</label>
													</li>
															<?php
														}
													}
													if ( ! empty( $wpsslw_woocustomer_headers ) ) {
														foreach ( $wpsslw_woocustomer_headers as $wpsslw_key => $wpsslw_val ) {
															$wpsslw_woo_is_checked = '';
															if ( in_array( $wpsslw_val, $wpsslw_woo_selections, true ) ) {
																continue;
															}
															$wpsslw_labelid        = strtolower( str_replace( ' ', '_', $wpsslw_val ) );
															$wpsslw_woo_is_checked = 'checked';
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
															<input type="checkbox" name="woocustomer_custom[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" class="woo-cust-headers-chk1" <?php echo esc_html( $wpsslw_woo_is_checked ); ?> hidden="true"><input type="checkbox" name="woocustomer_header_list[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" id="woo-<?php echo esc_attr( $wpsslw_labelid ); ?>" class="woo-cust-headers-chk" <?php echo esc_attr( $wpsslw_woo_is_checked ); ?>>
															<span class="checkbox-switch-new disabled-pro-version"></span>
																<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link disabled-pro-version">
																	<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
																		<path d="M1.95875 11.67C1.55759 11.67 1.21418 11.5272 0.928508 11.2415C0.642836 10.9558 0.5 10.6124 0.5 10.2113C0.5 9.81009 0.642836 9.46668 0.928508 9.18101C1.21418 8.89534 1.55759 8.7525 1.95875 8.7525C2.35991 8.7525 2.70332 8.89534 2.98899 9.18101C3.27466 9.46668 3.4175 9.81009 3.4175 10.2113C3.4175 10.6124 3.27466 10.9558 2.98899 11.2415C2.70332 11.5272 2.35991 11.67 1.95875 11.67ZM6.335 11.67C5.93384 11.67 5.59043 11.5272 5.30476 11.2415C5.01909 10.9558 4.87625 10.6124 4.87625 10.2113C4.87625 9.81009 5.01909 9.46668 5.30476 9.18101C5.59043 8.89534 5.93384 8.7525 6.335 8.7525C6.73616 8.7525 7.07957 8.89534 7.36524 9.18101C7.65091 9.46668 7.79375 9.81009 7.79375 10.2113C7.79375 10.6124 7.65091 10.9558 7.36524 11.2415C7.07957 11.5272 6.73616 11.67 6.335 11.67ZM1.95875 7.29375C1.55759 7.29375 1.21418 7.15091 0.928508 6.86524C0.642836 6.57957 0.5 6.23616 0.5 5.835C0.5 5.43384 0.642836 5.09043 0.928508 4.80476C1.21418 4.51909 1.55759 4.37625 1.95875 4.37625C2.35991 4.37625 2.70332 4.51909 2.98899 4.80476C3.27466 5.09043 3.4175 5.43384 3.4175 5.835C3.4175 6.23616 3.27466 6.57957 2.98899 6.86524C2.70332 7.15091 2.35991 7.29375 1.95875 7.29375ZM6.335 7.29375C5.93384 7.29375 5.59043 7.15091 5.30476 6.86524C5.01909 6.57957 4.87625 6.23616 4.87625 5.835C4.87625 5.43384 5.01909 5.09043 5.30476 4.80476C5.59043 4.51909 5.93384 4.37625 6.335 4.37625C6.73616 4.37625 7.07957 4.51909 7.36524 4.80476C7.65091 5.09043 7.79375 5.43384 7.79375 5.835C7.79375 6.23616 7.65091 6.57957 7.36524 6.86524C7.07957 7.15091 6.73616 7.29375 6.335 7.29375ZM1.95875 2.9175C1.55759 2.9175 1.21418 2.77466 0.928508 2.48899C0.642836 2.20332 0.5 1.85991 0.5 1.45875C0.5 1.05759 0.642836 0.71418 0.928508 0.428508C1.21418 0.142836 1.55759 0 1.95875 0C2.35991 0 2.70332 0.142836 2.98899 0.428508C3.27466 0.71418 3.4175 1.05759 3.4175 1.45875C3.4175 1.85991 3.27466 2.20332 2.98899 2.48899C2.70332 2.77466 2.35991 2.9175 1.95875 2.9175ZM6.335 2.9175C5.93384 2.9175 5.59043 2.77466 5.30476 2.48899C5.01909 2.20332 4.87625 1.85991 4.87625 1.45875C4.87625 1.05759 5.01909 0.71418 5.30476 0.428508C5.59043 0.142836 5.93384 0 6.335 0C6.73616 0 7.07957 0.142836 7.36524 0.428508C7.65091 0.71418 7.79375 1.05759 7.79375 1.45875C7.79375 1.85991 7.65091 2.20332 7.36524 2.48899C7.07957 2.77466 6.73616 2.9175 6.335 2.9175Z" fill="#64748B"/>
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
										</div>
									</div>
								</div>
								<?php
								if ( ! empty( $wpsslw_custsheet_id ) && ! empty( $wpsslw_customersheet_name ) ) {
									?>
								<div valign="top" id="custsynctr" class="cust_spreadsheet_row checkbox_margin generalSetting-section" >
									<div scope="row" class="titledesc generalSetting-left">
										<h4>
											<span class="wpssw-tooltio-link tooltip-right">
												<?php echo esc_html__( 'Sync Customers', 'wpssw' ); ?>
												<span class="tooltip-text">Export</span>
											</span>
										</h4>
										<p><?php echo esc_html__( "Click the 'Click to Sync' button to append new customers (within a specified date range, if desired) not already present in the sheet. Rest assured; existing customer data won't be affected.", 'wpssw' ); ?></p>
										<div class="sync_all_fromtodate-main">
											<div class="syncall-radio
											radio-box-td">
												<div class="syncall-radio-box">
													<input type="radio" name="cust_sync_all_checkbox" value="1" id="cust_sync_all" checked="checked">
													<label for="cust_sync_all"><?php echo esc_html__( 'All Customers', 'wpssw' ); ?></label>
												</div>
												<div class="syncall-radio-box">
													<input type="radio" value="0"  disabled="'disabled'" class="disabled">
													<label>
														<span class="wpssw-tooltio-link tooltip-right"><?php echo esc_html__( 'Date Range', 'wpssw' ); ?><span class="tooltip-text">Pro</span>
														</span>
													</label>
												</div>
											</div>
										</div>
										<div class="sync-button-box">  
											<img src="images/spinner.gif" id="custsyncloader">
											<span id="custsynctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span>
											<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="custsync">
											<?php echo esc_html__( 'Click to Sync', 'wpssw' ); ?></a> 
										</div>
									</div>
								</div>
									<?php
								}
								?>
							<?php
							if ( WPSSLW_Dependencies::wpsslw_is_woocommerce_active() ) {
								?>
							<div class="submit-section">
								<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
							</div>
								<?php
							} else {
								?>
								<div class="generalSetting-section generalSetting-section-message">
									<h4><?php echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!'; ?></h4>
								</div>
							<?php } ?>
						</form>
							<?php
						} else {
							?>
							<div class="generalSetting-section generalSetting-section-message">
								<h4><?php echo esc_html__( 'Please genearate authentication code from', 'wpssw' ); ?>
									<strong><?php echo esc_html__( 'Google API Setting ', 'wpssw' ); ?></strong>
									<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
								</h4>
							</div>
						<?php } ?> 
						</div>
						<!-- Customer Settings html end. -->

						<!-- Coupon Settings html start. -->
						<div id="coupon-settings" class="tabcontent">
						<?php
						$wpsslw_coupon_spreadsheet_setting = self::wpsslw_option( 'wpssw_coupon_spreadsheet_setting' );
						$wpsslw_coupon_spreadsheet_id      = self::wpsslw_option( 'wpssw_coupon_spreadsheet_id' );
						$wpsslw_spreadsheet_id             = self::wpsslw_option( 'wpssw_woocommerce_spreadsheet' );
						$wpsslw_couponsheet_id             = $wpsslw_coupon_spreadsheet_id;
						$wpsslw_couponsheet_name           = '';
						$wpsslw_spreadsheets_list          = self::$instance_api->get_spreadsheet_listing();

						if ( self::wpsslw_check_sheet_exist( $wpsslw_couponsheet_id, 'All Coupons' ) ) {
							$wpsslw_couponsheet_name = 'All Coupons';
						}
						if ( empty( $wpsslw_coupon_spreadsheet_id ) ) {
							$wpsslw_coupon_spreadsheet_id = $wpsslw_spreadsheet_id;
						}
						$wpsslw_checked = '';
						if ( 'yes' === (string) $wpsslw_coupon_spreadsheet_setting ) {
							$wpsslw_checked = 'checked';
						}
						?>
						<?php
						if ( ! empty( $wpsslw_google_settings[2] ) ) {
							?>
						<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-woocommerce&tab=coupon-settings' ) ); ?>" id="couponform">
							<?php wp_nonce_field( 'save_coupon_settings', 'wpssw_coupon_settings' ); ?>
							<input name="coupon_sheet" id="coupon_sheet" type="hidden" class="" value="<?php echo esc_html( $wpsslw_couponsheet_name ); ?>">
								<div valign="top" class="checkbox_margin generalSetting-section">
									<div scope="row" class="titledesc generalSetting-left">
										<h4><?php echo esc_html__( 'Coupon Settings', 'wpssw' ); ?></h4>
										<p><?php echo esc_html__( 'Enhance your coupon management process by enabling this option, automatically creating personalized spreadsheets and an "All Coupons" sheet, ensuring seamless functionality.', 'wpssw' ); ?></p>
									</div>
									<div class="forminp generalSetting-right">              
										<label for="coupon_settings_checkbox">
											<input name="coupon_settings_checkbox" id="coupon_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_html( $wpsslw_checked ); ?>><span class="checkbox-switch"></span><span class="checkbox-switch"></span>
										</label>
									</div>
								</div>
								<?php
									$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
								?>
								<div class="generalSetting-section googleSpreadsheet-section coupon_spreadsheet_row">
									<div class="generalSetting-left">
										<h4><?php echo esc_html__( 'Google Spreadsheet Settings', 'wpssw' ); ?></h4>
										<p><?php echo esc_html__( "Once you've assigned a Google Spreadsheet, it will automatically generate an 'All Coupons' sheet with headers based on your settings. Additionally, new rows will be created whenever new coupons are created.", 'wpssw' ); ?></p>
										<div class="createanew-radio">
											<div class="createanew-radio-box">
												<input type="radio" name="couponsheetselection" value="new" id="couponcreateanew">
												<label for="couponcreateanew"><?php echo esc_html__( 'Create New Spreadsheet', 'wpssw' ); ?></label>
											</div>
											<div class="createanew-radio-box">
												<input type="radio" name="couponsheetselection" value="existing" id="couponexisting" checked="checked">
												<label for="couponexisting"><?php echo esc_html__( 'Select Existing Spreadsheet', 'wpssw' ); ?></label>
											</div>
										</div>
										<div id="coupon_spreadsheet_container" class="spreadsheet-form">
											<select name="coupon_spreadsheet" id="coupon_spreadsheet" style="min-width:150px;" class="">
												<?php
												$selected = '';
												foreach ( $spreadsheets_list as $spreadsheetid => $spreadsheetname ) {
													if ( (string) $wpsslw_coupon_spreadsheet_id === $spreadsheetid ) {
														$selected = 'selected="selected"';
													}
													?>
												<option value="<?php echo esc_attr( $spreadsheetid ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $spreadsheetname ); ?></option>
												<?php $selected = ''; } ?>
											</select>
										</div>
										<div valign="top" id="coupon_newsheet" class="newsheetinput spreadsheet-form coupon_spreadsheet_inputrow">
											<input name="coupon_spreadsheet_name" id="coupon_spreadsheet_name" type="text" class="input-text" value="" placeholder="<?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?>">
										</div>
									</div>
								</div>
								<div class="coupon_spreadsheet_row generalSetting-section sheetHeaders-section ">
									<div class="td-wpssw-headers">
										<div class="wpssw_headers">
											<div class="generalSetting-sheet-headers-row">
												<div class="generalSetting-left">
													<h4 for="sheet_headers"><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></h4>
													<p><?php echo esc_html__( 'Effortlessly tailor your spreadsheet by turning specific sheet headers on or off. Disabled headers will be automatically removed from the current spreadsheet. To update the sheet with the latest data, save your desired header settings and click the "Click to Sync" button.', 'wpssw' ); ?></p>
												</div>
											</div>
											<br><br>
											<div class="sheetHeaders-main">
												<ul id="woo-coupon-sortable" class="sheetHeaders-box">
													<?php
													$wpsslw_woo_is_checked = '';
													$wpsslw_woo_selections = stripslashes_deep( self::wpsslw_option( 'wpssw_woo_coupon_headers' ) );
													if ( ! $wpsslw_woo_selections ) {
														$wpsslw_woo_selections = array();
													}
													$wpsslw_woo_selections_custom = stripslashes_deep( self::wpsslw_option( 'wpssw_woo_coupon_headers_custom' ) );
													if ( ! $wpsslw_woo_selections_custom ) {
														$wpsslw_woo_selections_custom = array();
													}
													$wpsslw_include = new WPSSLW_Include_Action();
													$wpsslw_include->wpsslw_include_coupon_compatibility_files();
													$wpsslw_woocoupon_headers = apply_filters( 'wpsyncsheets_coupon_headers', array() );
													$wpsslw_woocoupon_headers = self::wpsslw_array_flatten( $wpsslw_woocoupon_headers );

													$wpsslw_operation = array( 'Insert', 'Update', 'Delete' );

													if ( ! empty( $wpsslw_woo_selections ) ) {
														foreach ( $wpsslw_woo_selections as $wpsslw_key => $wpsslw_val ) {
															$wpsslw_woo_is_checked = 'checked';
															$wpsslw_labelid        = strtolower( str_replace( ' ', '_', $wpsslw_val ) );
															$wpsslw_display        = true;
															$wpsslw_classname      = '';
															if ( in_array( $wpsslw_val, $wpsslw_operation, true ) ) {
																$wpsslw_display   = false;
																$wpsslw_labelid   = '';
																$wpsslw_classname = strtolower( $wpsslw_val ) . 'coupon';
															}
															?>
														<li class="default-order-sheet ui-state-default <?php echo esc_html( $wpsslw_classname ); ?>">
														<label>
															<span class="orderSheet-left">
															<span class="wootextfield"><?php echo isset( $wpsslw_woo_selections_custom[ $wpsslw_key ] ) ? esc_attr( $wpsslw_woo_selections_custom[ $wpsslw_key ] ) : esc_attr( $wpsslw_val ); ?></span>
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
													<input type="checkbox" name="woocoupon_custom[]" value="<?php echo isset( $wpsslw_woo_selections_custom[ $wpsslw_key ] ) ? esc_attr( $wpsslw_woo_selections_custom[ $wpsslw_key ] ) : esc_attr( $wpsslw_val ); ?>" class="woo-coupon-headers-chk1" <?php echo esc_html( $wpsslw_woo_is_checked ); ?> hidden="true">
													<input type="checkbox" name="woocoupon_header_list[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" id="woo-<?php echo esc_attr( $wpsslw_labelid ); ?>" class="woo-coupon-headers-chk" <?php echo esc_html( $wpsslw_woo_is_checked ); ?>>
															<?php if ( $wpsslw_display ) { ?>
														<span class="checkbox-switch-new disabled-pro-version"></span>
														<?php } ?>
														<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link disabled-pro-version">
															<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
																<path d="M1.95875 11.67C1.55759 11.67 1.21418 11.5272 0.928508 11.2415C0.642836 10.9558 0.5 10.6124 0.5 10.2113C0.5 9.81009 0.642836 9.46668 0.928508 9.18101C1.21418 8.89534 1.55759 8.7525 1.95875 8.7525C2.35991 8.7525 2.70332 8.89534 2.98899 9.18101C3.27466 9.46668 3.4175 9.81009 3.4175 10.2113C3.4175 10.6124 3.27466 10.9558 2.98899 11.2415C2.70332 11.5272 2.35991 11.67 1.95875 11.67ZM6.335 11.67C5.93384 11.67 5.59043 11.5272 5.30476 11.2415C5.01909 10.9558 4.87625 10.6124 4.87625 10.2113C4.87625 9.81009 5.01909 9.46668 5.30476 9.18101C5.59043 8.89534 5.93384 8.7525 6.335 8.7525C6.73616 8.7525 7.07957 8.89534 7.36524 9.18101C7.65091 9.46668 7.79375 9.81009 7.79375 10.2113C7.79375 10.6124 7.65091 10.9558 7.36524 11.2415C7.07957 11.5272 6.73616 11.67 6.335 11.67ZM1.95875 7.29375C1.55759 7.29375 1.21418 7.15091 0.928508 6.86524C0.642836 6.57957 0.5 6.23616 0.5 5.835C0.5 5.43384 0.642836 5.09043 0.928508 4.80476C1.21418 4.51909 1.55759 4.37625 1.95875 4.37625C2.35991 4.37625 2.70332 4.51909 2.98899 4.80476C3.27466 5.09043 3.4175 5.43384 3.4175 5.835C3.4175 6.23616 3.27466 6.57957 2.98899 6.86524C2.70332 7.15091 2.35991 7.29375 1.95875 7.29375ZM6.335 7.29375C5.93384 7.29375 5.59043 7.15091 5.30476 6.86524C5.01909 6.57957 4.87625 6.23616 4.87625 5.835C4.87625 5.43384 5.01909 5.09043 5.30476 4.80476C5.59043 4.51909 5.93384 4.37625 6.335 4.37625C6.73616 4.37625 7.07957 4.51909 7.36524 4.80476C7.65091 5.09043 7.79375 5.43384 7.79375 5.835C7.79375 6.23616 7.65091 6.57957 7.36524 6.86524C7.07957 7.15091 6.73616 7.29375 6.335 7.29375ZM1.95875 2.9175C1.55759 2.9175 1.21418 2.77466 0.928508 2.48899C0.642836 2.20332 0.5 1.85991 0.5 1.45875C0.5 1.05759 0.642836 0.71418 0.928508 0.428508C1.21418 0.142836 1.55759 0 1.95875 0C2.35991 0 2.70332 0.142836 2.98899 0.428508C3.27466 0.71418 3.4175 1.05759 3.4175 1.45875C3.4175 1.85991 3.27466 2.20332 2.98899 2.48899C2.70332 2.77466 2.35991 2.9175 1.95875 2.9175ZM6.335 2.9175C5.93384 2.9175 5.59043 2.77466 5.30476 2.48899C5.01909 2.20332 4.87625 1.85991 4.87625 1.45875C4.87625 1.05759 5.01909 0.71418 5.30476 0.428508C5.59043 0.142836 5.93384 0 6.335 0C6.73616 0 7.07957 0.142836 7.36524 0.428508C7.65091 0.71418 7.79375 1.05759 7.79375 1.45875C7.79375 1.85991 7.65091 2.20332 7.36524 2.48899C7.07957 2.77466 6.73616 2.9175 6.335 2.9175Z" fill="#64748B"/>
															</svg>

															<span class="tooltip-text">Upgrade To Pro</span>
														</span>
													</span>
														</label>
													</li>
															<?php
														}
													}
													if ( ! empty( $wpsslw_woocoupon_headers ) ) {
														foreach ( $wpsslw_woocoupon_headers as $wpsslw_key => $wpsslw_val ) {
															$wpsslw_woo_is_checked = '';
															if ( in_array( $wpsslw_val, $wpsslw_woo_selections, true ) ) {
																continue;
															}
															$wpsslw_labelid = strtolower( str_replace( ' ', '_', $wpsslw_val ) );

															$wpsslw_woo_is_checked = 'checked';
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
																<input type="checkbox" name="woocoupon_custom[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" class="woo-coupon-headers-chk1" <?php echo esc_html( $wpsslw_woo_is_checked ); ?> hidden="true">
																<input type="checkbox" name="woocoupon_header_list[]" value="<?php echo esc_attr( $wpsslw_val ); ?>" id="woo-<?php echo esc_attr( $wpsslw_labelid ); ?>" class="woo-coupon-headers-chk" <?php echo esc_html( $wpsslw_woo_is_checked ); ?>>
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
										</div>
									</div>
								</div>
								<?php
								if ( ! empty( $wpsslw_couponsheet_id ) && ! empty( $wpsslw_couponsheet_name ) ) {
									?>
								<div valign="top" id="couponsynctr" class="coupon_spreadsheet_row checkbox_margin generalSetting-section" >
									<div scope="row" class="titledesc generalSetting-left">
										<h4>
											<span class="wpssw-tooltio-link tooltip-right">
												<?php echo esc_html__( 'Sync Coupons', 'wpssw' ); ?>
												<span class="tooltip-text">Export</span>
											</span>
										</h4>
										<p><?php echo esc_html__( 'The "Click to Sync" button makes it easy to append all or selected date range coupons not already present in the sheet. You can rest assured that existing coupons will not be affected during synchronization.', 'wpssw' ); ?></p>
										<div class="sync_all_fromtodate-main">
											<div class="syncall-radio
											radio-box-td">
												<div class="syncall-radio-box">
													<input type="radio" name="coupon_sync_all_checkbox" value="1" id="coupon_sync_all" checked="checked">
													<label for="coupon_sync_all"><?php echo esc_html__( 'All Coupons', 'wpssw' ); ?></label>
												</div>
												<div class="syncall-radio-box">
													<input type="radio" value="0" disabled="'disabled'" class="disabled">
													<label>
														<span class="wpssw-tooltio-link tooltip-right"><?php echo esc_html__( 'Date Range', 'wpssw' ); ?><span class="tooltip-text">Pro</span>
														</span>
													</label>
												</div>
											</div>
										</div>
										<div class="sync-button-box">  
											<img src="images/spinner.gif" id="couponsyncloader">
											<span id="couponsynctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span>
											<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="couponsync">
												<?php echo esc_html__( 'Click to Sync', 'wpssw' ); ?>
											</a> 
										</div>	
									</div>
								</div>
									<?php
								}

								if ( WPSSLW_Dependencies::wpsslw_is_woocommerce_active() ) {
									?>
							<div class="submit-section">
								<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
							</div>
									<?php
								} else {
									?>
								<div class="generalSetting-section generalSetting-section-message">
									<h4><?php echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!'; ?></h4>
								</div>
									<?php
								}
								?>
						</form>
							<?php
						} else {
							?>
							<div class="generalSetting-section generalSetting-section-message">
								<h4><?php echo esc_html__( 'Please genearate authentication code from', 'wpssw' ); ?>
									<strong><?php echo esc_html__( 'Google API Setting ', 'wpssw' ); ?></strong>
									<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
								</h4>
							</div>
						<?php } ?> 
						</div>
						<!-- Coupon Settings html end. -->

						<?php
						if ( is_array( $wpsslw_tabs ) && ! empty( $wpsslw_tabs ) ) {
							foreach ( $wpsslw_tabs as $tabkey => $tabname ) {
								?>
								<div id="<?php echo esc_attr( $tabkey ); ?>" class="tabcontent">
								<?php do_action( 'wpsyncsheets_tab_' . $tabkey ); ?>
								</div>
								<?php
							}
						}

						$wpsslw_inputoption = self::wpsslw_option( 'wpssw_inputoption' );
						if ( ! $wpsslw_inputoption ) {
							$wpsslw_inputoption = 'USER_ENTERED';
						}

						$wpsslw_user = '';
						$wpsslw_raw  = '';
						if ( 'USER_ENTERED' === (string) $wpsslw_inputoption ) {
							$wpsslw_user = 'selected';
						} else {
							$wpsslw_raw = 'selected';
						}
						?>
						<div id="general-settings" class="tabcontent">
							<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-woocommerce&tab=general-settings' ) ); ?>" id="generalform">
								<div class="generalSetting-section">
									<div class="generalSetting-left">
										<?php $freeze_header = self::$instance_api->wpsslw_option( 'freeze_header' ); ?>
										<h4><?php echo esc_html__( 'Freeze Header', 'wpss' ); ?></h4>
										<p><?php echo esc_html__( 'By enabling this feature, the first row containing the header (or title) information will remain fixed at the top of the sheet even while scrolling down, providing easy access to essential details.', 'wpss' ); ?></p>
									</div>
									<div class="generalSetting-right">
										<label for="freeze_header">
											<input name="freeze_header" id="freeze_header" type="checkbox" class="" value="yes" 
											<?php
											if ( 'yes' === (string) $freeze_header ) {
												echo 'checked';}
											?>
											><span class="checkbox-switch"></span>
										</label>
									</div>
								</div>
								<div class="submit-section">
									<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>	
									<?php
									wp_nonce_field( 'save_general_settings', 'wpsslw_general_settings' );
									?>
								</div>
							</form>
						</div>
					</div>
				</div>
				<?php } ?>
				<?php

				if ( ! empty( $wpsslw_error ) || ! empty( $wpsslw_error_general ) || ! \WPSSLW_Dependencies::wpsslw_woocommerce_active_check() ) {
					$error_msg = '';
					if ( ! \WPSSLW_Dependencies::wpsslw_woocommerce_active_check() ) {
						$error_msg = 'WPSyncSheets Lite for WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!';
						?>
					<input type="hidden" id="error-message" value="<?php echo esc_attr( $error_msg ); ?>">
						<?php
					}
					?>
				<?php } elseif ( isset( $_POST['submit'] ) ) { ?>
					<input type="hidden" id="success-message" value="Settings are saved successfully.">
					<?php
				}
				$token_error_val = 0;
				if ( 'activated' === (string) $licence_activated && $wpsslw_token_error ) {
					$token_error_val = 1;
				}
				?>
				<input type="hidden" id="token-error" value="<?php echo esc_attr( $token_error_val ); ?>">
				<script type="text/javascript">
					jQuery.noConflict();
					jQuery(".wpssw-header-section .whatsNew-toggle, .wpssw-header-section .close-block").click(function(){
						jQuery(".wpssw-header-section .wpssw-header-right").toggleClass('active');
						jQuery(".wpssw-header-section .whatsNew-toggle").toggleClass('active');
					});
				</script>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}
	/**
	 * Check License key.
	 */
	public static function wpsslw_check_license_key() {
		return true;
	}
	/**
	 * Get changelog.
	 *
	 * @param string $plugin_path plugin path.
	 */
	public static function wpsslw_get_plugin_changelog( $plugin_path ) {
		// Path to the readme.txt file.
		$readme_file = $plugin_path . '/readme.txt';

		// Check if the file exists.
		if ( ! file_exists( $readme_file ) ) {
			return '';
		}

		// phpcs:ignore
		$content = file_get_contents ( $readme_file ); // Read the file content.

		if ( ! $content || is_wp_error( $content ) ) {
			return '';
		}

		// Regular expression to extract the Changelog section.
		$pattern = '/==\s*Changelog\s*==\s*(.*?)(==|$)/is';

		// Match the pattern.
		if ( preg_match( $pattern, $content, $matches ) ) {
			// Extracted Changelog section.
			$changelog_content = trim( $matches[1] );

			// Regular expression to parse individual versions.
			$version_pattern = '/=+\s*(\d+\.\d+(\.\d+)?\s*(\(.+?\))?)\s*=+\s*(.*?)(?==+\s*\d+\.\d+(\.\d+)?\s*(\(.+?\))?\s*=+|$)/is';
			preg_match_all( $version_pattern, $changelog_content, $version_matches, PREG_SET_ORDER );

			$html_output = '';

			foreach ( $version_matches as $version_match ) {
				$version = $version_match[1];
				$changes = trim( $version_match[4] );

				// Convert changes to list items.
				$change_items = preg_split( '/\r\n|\r|\n/', $changes );
				$change_items = array_filter( array_map( 'trim', $change_items ) );

				$html_output .= "<h5><strong>Version $version</strong></h5>\n<ol>\n";

				foreach ( $change_items as $item ) {
					// Remove leading '*' and trim the item.
					$item = ltrim( $item, '* ' );
					if ( ! empty( $item ) ) {
						$html_output .= '<li>' . htmlspecialchars( $item ) . "</li>\n";
					}
				}

				$html_output .= "</ol>\n<hr class=\"wp-block-separator has-css-opacity\">\n";
			}

			// Remove any unintended <br> tags.
			return str_replace( array( '<br>', '<br/>', '<br />' ), '', $html_output );
		} else {
			return '';
		}
	}
}
WPSSLW_Settings::init();
