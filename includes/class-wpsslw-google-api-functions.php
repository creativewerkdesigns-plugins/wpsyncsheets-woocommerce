<?php
/**
 * Main WPSyncSheetsWooCommerce\WPSSLW_Google_API namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-woocommerce
 */

namespace WPSyncSheetsWooCommerce;

/**
 * Google API Method Class
 *
 * @since 1.0.0
 */
class WPSSLW_Google_API_Functions extends \WPSSLW_Google_API {
	/**
	 * Google Sheet Object
	 *
	 * @var object
	 * @since 1.0.0
	 */
	private static $instance_service = null;
	/**
	 * Google Drive Object
	 *
	 * @var object
	 * @since 1.0.0
	 */
	private static $instance_drive = null;
	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( self::checkcredenatials() ) {
			self::loadobject();
		}
	}
	/**
	 * Load Google API Library.
	 *
	 * @since 1.0.0
	 */
	public function loadobject() {
		self::$instance_service = self::get_client_object();
		self::$instance_drive   = self::get_drive_object();
	}
	/**
	 * Include Google API Library.
	 *
	 * @since 1.0.0
	 */
	public function wpsslw_load_library() {
		if ( ! class_exists( 'ComposerAutoloaderInita672e4231f706419bd66ef535c4ab40e' ) ) {
			require_once WPSSLW_PLUGIN_PATH . '/lib/vendor/autoload.php';
		}
	}
	/**
	 * Generate Google Sheet Object.
	 *
	 * @since 1.0.0
	 */
	public function get_client_object() {
		if ( null === self::$instance_service ) {
			$client                 = self::getClient();
			self::$instance_service = new \Google_Service_Sheets( $client );
		}
		return self::$instance_service;
	}
	/**
	 * Regenerate Google Sheet Object.
	 *
	 * @since 1.0.0
	 */
	public function refreshobject() {
		self::$instance_service = null;
		self::get_client_object();
	}
	/**
	 * Regenerate Google Drive Object.
	 *
	 * @since 1.0.0
	 */
	public function get_drive_object() {
		if ( null === self::$instance_drive ) {
			$client               = self::getClient();
			self::$instance_drive = new \Google_Service_Drive( $client );
		}
		return self::$instance_drive;
	}
	/**
	 * Get Google Drive Object.
	 */
	public static function get_object_drive_object() {
		self::$instance_drive = null;
	}
	/**
	 * Check Google Credenatials.
	 *
	 * @since 1.0.0
	 */
	public function checkcredenatials() {
		$wpsslw_google_settings_value = self::wpsslw_option( 'wpssw_google_settings' );
		$clientid                     = isset( $wpsslw_google_settings_value[0] ) ? $wpsslw_google_settings_value[0] : '';
		$clientsecert                 = isset( $wpsslw_google_settings_value[1] ) ? $wpsslw_google_settings_value[1] : '';
		$auth_token                   = isset( $wpsslw_google_settings_value[2] ) ? $wpsslw_google_settings_value[2] : '';
		if ( empty( $clientid ) || empty( $clientsecert ) || empty( $auth_token ) ) {
			return false;
		} else {
			try {
				if ( self::getClient() ) {
					return true;
				} else {
					return false;
				}
			} catch ( Exception $e ) {
				return false;
			}
		}
	}
	/**
	 * Get meta vlaue.
	 *
	 * @param object $key plugin meta key.
	 * @param string $type boolean value.
	 */
	public static function wpsslw_option( $key = '', $type = '' ) {
		$value = parent::wpsslw_option( $key, $type );
		return $value;
	}
	/**
	 * Update meta value.
	 *
	 * @param object $key plugin meta key.
	 * @param string $value plugin meta value.
	 */
	public static function wpsslw_update_option( $key = '', $value = '' ) {
		$value = parent::wpsslw_update_option( $key, $value );
		return $value;
	}
	/**
	 * Generate token for the user and refresh the token if it's expired.
	 *
	 * @param int $flag for getting error code.
	 * @return array
	 */
	public function getClient( $flag = 0 ) {
		$this->wpsslw_load_library();
		$wpsslw_google_settings_value = self::wpsslw_option( 'wpssw_google_settings' );
		$clientid                     = isset( $wpsslw_google_settings_value[0] ) ? $wpsslw_google_settings_value[0] : '';
		$clientsecert                 = isset( $wpsslw_google_settings_value[1] ) ? $wpsslw_google_settings_value[1] : '';
		$auth_token                   = isset( $wpsslw_google_settings_value[2] ) ? $wpsslw_google_settings_value[2] : '';
		$client                       = new \Google_Client();
		$client->setApplicationName( 'WPSyncSheets Lite For WooCommerce - WooCommerce Google Spreadsheet Addon' );
		$client->setScopes( \Google_Service_Sheets::SPREADSHEETS_READONLY );
		$client->setScopes( \Google_Service_Drive::DRIVE_METADATA_READONLY );
		$client->addScope( \Google_Service_Sheets::SPREADSHEETS );
		$client->setClientId( $clientid );
		$client->setClientSecret( $clientsecert );
		$client->setRedirectUri( esc_html( admin_url( 'admin.php?page=wpsyncsheets-woocommerce' ) ) );
		$client->setAccessType( 'offline' );
		$client->setPrompt('consent');
		// Load previously authorized credentials from a database.
		try {
			if ( empty( $auth_token ) ) {
				$auth_url = $client->createAuthUrl();
				return $auth_url;
			}
			$wpsslw_accesstoken = parent::wpsslw_option( 'wpssw_google_accessToken' );
			if ( ! empty( $wpsslw_accesstoken ) ) {
				$accesstoken = json_decode( $wpsslw_accesstoken, true );
			} else {
				if ( empty( $auth_token ) ) {
					$auth_url = $client->createAuthUrl();
					return $auth_url;
				} else {
					$authcode = trim( $auth_token );
					// Exchange authorization code for an access token.
					$accesstoken = $client->fetchAccessTokenWithAuthCode( $authcode );
					if(! isset( $accesstoken['refresh_token'] ) || empty( $accesstoken['refresh_token'] ) ){
						$accesstoken['refresh_token'] = $client->getRefreshToken();
					}
					// Store the credentials to disk.
					parent::wpsslw_update_option( 'wpssw_google_accessToken', wp_json_encode( $accesstoken ) );
				}
			}
			// Check for invalid token.
			if ( is_array( $accesstoken ) && isset( $accesstoken['error'] ) && ! empty( $accesstoken['error'] ) ) {
				if ( $flag ) {
					return $accesstoken['error'];
				}
				return false;
			}
			$client->setAccessToken( $accesstoken );
			// Refresh the token if it's expired.
			if ( $client->isAccessTokenExpired() ) {
				// save refresh token to some variable.				
				$refreshtokensaved = ( isset( $accesstoken['refresh_token'] ) && !empty( $accesstoken['refresh_token'] ) ) ? $accesstoken['refresh_token'] : $client->getRefreshToken();
				if( Null === $refreshtokensaved || empty( $refreshtokensaved ) ){
					if ( $flag ) {
						$m = 'Please revoke the token and generate it again.';
						return $m;
					} else {
						return false;
					}
				}
				$newaccesstoken = $client->fetchAccessTokenWithRefreshToken( $refreshtokensaved );

				if ( is_array( $newaccesstoken ) && isset( $newaccesstoken['error'] ) && ! empty( $newaccesstoken['error'] ) ) {
					if ( $flag ) {
						return $newaccesstoken['error'];
					}
					return false;
				}
				
				// pass access token to some variable.
				$accesstokenupdated = $client->getAccessToken();
				if(! isset( $accesstokenupdated['refresh_token'] ) ){
					// append refresh token.
					$accesstokenupdated['refresh_token'] = $refreshtokensaved;
				}
				// Set the new access token.
				parent::wpsslw_update_option( 'wpssw_google_accessToken', wp_json_encode( $accesstokenupdated ) );
				$accesstoken = json_decode( wp_json_encode( $accesstokenupdated ), true );
				$client->setAccessToken( $accesstoken );
			}
		} catch ( Exception $e ) {
			if ( $flag ) {
				return $e->getMessage();
			} else {
				return false;
			}
		}
		return $client;
	}
	/**
	 * Fetch Spreadsheet list from Google Drive.
	 *
	 * @param array $sheetarray Spreadsheet array.
	 * @since 1.0.0
	 *
	 * @return array.
	 */
	public function get_spreadsheet_listing( $sheetarray = array() ) {

		if ( self::checkcredenatials() ) {
			self::get_object_drive_object();
			self::loadobject();
		} else {
			return $sheetarray;
		}
		// Print the names and IDs for up to 10 files.
		$optparams    = array(
			'fields' => 'nextPageToken, files(id, name, mimeType)',
			'q'      => "mimeType='application/vnd.google-apps.spreadsheet' and trashed = false",
		);
		$results      = self::$instance_drive->files->listFiles( $optparams );
		$sheetarray[] = __( 'Select Google Spreeadsheet', 'wpssw' );
		if ( count( $results->getFiles() ) > 0 ) {
			foreach ( $results->getFiles() as $file ) {
				$sheetarray[ $file->getId() ] = $file->getName();
			}
		}
		return $sheetarray;
	}
	/**
	 * Retrieve the list of sheets from the Google Spreadsheet.
	 *
	 * @param string $spreadsheetid Spreadsheet id.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function get_sheet_listing( $spreadsheetid = '' ) {
		self::refreshobject();
		return parent::get_sheets( self::$instance_service, $spreadsheetid );
	}
	/**
	 * Fetch row from Google Sheet.
	 *
	 * @param array $spreadsheetid Spreadsheet ID.
	 * @param array $sheetname Sheet Name.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function get_row_list( $spreadsheetid, $sheetname ) {
		self::refreshobject();
		$param                  = array();
		$param['spreadsheetid'] = trim( $spreadsheetid );
		$param['sheetname']     = trim( $sheetname );
		return parent::get_values( self::$instance_service, $param );
	}
	/**
	 * Create sheet array.
	 *
	 * @param object $response_object google sheet object.
	 * @since 1.0.0
	 *
	 * @return array.
	 */
	public function get_sheet_list( $response_object ) {
		$sheets = array();
		foreach ( $response_object->getSheets() as $key => $value ) {
			$sheets[ $value['properties']['title'] ] = $value['properties']['sheetId'];
		}
		return $sheets;
	}
	/**
	 * Create deleteDimension Object.
	 *
	 * @param array  $param contains sheetid,startindex,endindex.
	 * @param string $dimension either COLUMN or ROW for request dimension.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function deleteDimensionrequests( $param = array(), $dimension = 'COLUMNS' ) {
		$requests = array(
			'deleteDimension' => array(
				'range' => array(
					'sheetId'    => $param['sheetid'],
					'dimension'  => $dimension,
					'startIndex' => $param['startindex'],
					'endIndex'   => $param['endindex'],
				),
			),
		);
		return $requests;
	}
	/**
	 * Create insertDimension Object.
	 *
	 * @param array $param contains sheetid,startindex,endindex.
	 * @param bool  $inheritstyle inherit style or not.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function insertdimensionobject( $param = array(), $inheritstyle = false ) {
		$requests           = $this->insertdimensionrequests( $param, 'ROWS', $inheritstyle );
		$batchupdaterequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => $requests,
			)
		);
		return $batchupdaterequest;
	}
	/**
	 * Freeze Row Object
	 *
	 * @param int $sheetid Sheet ID.
	 * @param int $wpsslw_freeze 0 - Unfreeze Row, 1 - Freeze Row.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function freezeobject( $sheetid = 0, $wpsslw_freeze = 0 ) {
		$requestbody = array(
			'updateSheetProperties' => array(
				'properties' => array(
					'sheetId'        => $sheetid,
					'gridProperties' => array(
						'frozenRowCount' => $wpsslw_freeze,
					),
				),
				'fields'     => 'gridProperties.frozenRowCount',
			),
		);
		return $requestbody;
	}
	/**
	 * Google_Service_Sheets_Spreadsheet Object
	 *
	 * @param string $spreadsheetname Spreadsheet Name.
	 * @param string $sheetname Sheet Name.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function newspreadsheetobject( $spreadsheetname = '', $sheetname = '' ) {
		$requestbody = new \Google_Service_Sheets_Spreadsheet(
			array(
				'properties' => array(
					'title' => $spreadsheetname,
				),
				'sheets'     => array(
					'properties' => array(
						'title' => $sheetname,
					),
				),
			)
		);
		return $requestbody;
	}
	/**
	 * Prepare parameter array.
	 *
	 * @param string $spreadsheetid Spreadsheet Name.
	 * @param string $range Sheet Name.
	 * @param array  $requestbody requestbody param.
	 * @param array  $params array.
	 * @since 1.0.0
	 *
	 * @return array.
	 */
	public function setparamater( $spreadsheetid = '', $range = '', $requestbody = array(), $params = array() ) {
		$param                  = array();
		$param['spreadsheetid'] = $spreadsheetid;
		$param['range']         = $range;
		$param['requestbody']   = $requestbody;
		$param['params']        = $params;
		return $param;
	}
	/**
	 * Prepare parameter array.
	 *
	 * @param int $sheetid Sheet ID.
	 * @param int $startindex Start Index.
	 * @param int $endindex End Index.
	 * @since 1.0.0
	 *
	 * @return array.
	 */
	public function prepare_param( $sheetid, $startindex, $endindex ) {
		$param               = array();
		$param['sheetid']    = $sheetid;
		$param['startindex'] = $startindex;
		$param['endindex']   = $endindex;
		return $param;
	}
	/**
	 * Google_Service_Sheets_Spreadsheet Object
	 *
	 * @param string $spreadsheetname Spreadsheet Name.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function createspreadsheetobject( $spreadsheetname = '' ) {
		$wpsslw_requestbody = new \Google_Service_Sheets_Spreadsheet(
			array(
				'properties' => array(
					'title' => $spreadsheetname,
				),
			)
		);
		return $wpsslw_requestbody;
	}
	/**
	 * Create new sheet
	 *
	 * @param string $param .
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function newsheetobject( $param = array() ) {
		$batchupdaterequest   = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => array(
					'addSheet' => array(
						'properties' => array(
							'title' => $param['sheetname'],
						),
					),
				),
			)
		);
		$param['requestbody'] = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $param );
	}
	/**
	 * Update batch requests.
	 *
	 * @param array $param contains requests.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function updatebachrequests( $param = array() ) {
		$batchupdaterequest             = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => $param['requestarray'],
			)
		);
		$requestobject['spreadsheetid'] = $param['spreadsheetid'];
		$requestobject['requestbody']   = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $requestobject );
	}
	/**
	 * Update batch requests.
	 *
	 * @param string $spreadsheetid spreadsheetid.
	 * @param array  $batchupdaterequest batch update request array.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function updatebachrequestsrowcolor( $spreadsheetid, $batchupdaterequest ) {

		$requestobject['spreadsheetid'] = $spreadsheetid;
		$requestobject['requestbody']   = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $requestobject );
	}
	/**
	 * Create moveDimension Object.
	 *
	 * @param array $param contains sheetid,startindex,endindex,destinationIndex.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function moveDimensionrequests( $param = array() ) {
		$requests = new \Google_Service_Sheets_Request(
			array(
				'moveDimension' => array(
					'source'           => array(
						'dimension'  => 'COLUMNS',
						'sheetId'    => $param['sheetid'],
						'startIndex' => $param['startindex'],
						'endIndex'   => $param['endindex'],
					),
					'destinationIndex' => $param['destindex'],
				),
			)
		);
		return $requests;
	}
	/**
	 * Create insertDimension Object.
	 *
	 * @param array  $param contains sheetid,startindex,endindex.
	 * @param string $dimension either COLUMN or ROW for request dimension.
	 * @param bool   $inheritstyle inherit style or not.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function insertdimensionrequests( $param = array(), $dimension = 'COLUMNS', $inheritstyle = false ) {

		$requests = array(
			'insertDimension' => array(
				'range'             => array(
					'sheetId'    => $param['sheetid'],
					'dimension'  => $dimension,
					'startIndex' => $param['startindex'],
					'endIndex'   => $param['endindex'],
				),
				'inheritFromBefore' => true,
			),
		);

		return $requests;

	}
	/**
	 * Get Values from multiple sheets.
	 *
	 * @param array $param contains spreadsheetid,ranges.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function getbatchvalues( $param = array() ) {
		self::refreshobject();
		return parent::batchget( self::$instance_service, $param );
	}
	/**
	 * Create Google_Service_Sheets_ValueRange Object.
	 *
	 * @param array $values_data Values Array.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function valuerangeobject( $values_data = array() ) {
		$requestbody = new \Google_Service_Sheets_ValueRange( array( 'values' => $values_data ) );
		return $requestbody;
	}
	/**
	 * Create Google_Service_Sheets_ClearValuesRequest Object.
	 *
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function clearobject() {
		$requestbody = new \Google_Service_Sheets_ClearValuesRequest();
		return $requestbody;
	}
	/**
	 * Insert new column, Freeze first row to google spreadsheet.
	 *
	 * @param array $param contains spreadsheetid,requestbody.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function formatsheet( $param = array() ) {
		return parent::batchupdate( self::$instance_service, $param );
	}
	/**
	 * Update entry to google sheet.
	 *
	 * @param array $param contains spreadsheetid, range, requestbody, params.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function updateentry( $param = array() ) {
		return parent::update_entry( self::$instance_service, $param );
	}
	/**
	 * Append entry to google sheet.
	 *
	 * @param array $param contains spreadsheetid, range, requestbody, params.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function appendentry( $param = array() ) {
		return parent::append_entry( self::$instance_service, $param );
	}
	/**
	 * Create new spreadsheet in Google Drive.
	 *
	 * @param array $requestbody requestbody object.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function createspreadsheet( $requestbody = array() ) {
		return parent::create_spreadsheet( self::$instance_service, $requestbody );
	}
	/**
	 * Clear Sheet Value.
	 *
	 * @param array $param spreadsheetid,sheetname,requestbody.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function clear( $param = array() ) {
		return parent::clearsheet( self::$instance_service, $param );
	}
	/**
	 * Delete embeded object
	 *
	 * @param string $param .
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function deleteembeddedobject( $param = array() ) {
		$batchupdaterequest   = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => array(
					'deleteEmbeddedObject' => array(
						'objectId' => $param['chart_ID'],
					),
				),
			)
		);
		$param['requestbody'] = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $param );
	}
	/**
	 * Create AddChart object.
	 *
	 * @param string $param .
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function addchartobject( $param = array() ) {

		$chart_property = array();
		$chart_type     = '';
		if ( 'pie' === (string) $param['graph_type'] ) {
			$chart_type     = 'pieChart';
			$chart_property = array(
				'threeDimensional' => true,
				'legendPosition'   => 'LEFT_LEGEND',
				'domain'           => array(
					'sourceRange' => array(
						'sources' => array(
							'sheetId'          => $param['graph_sheetID'],
							'startRowIndex'    => $param['startRowIndex'],
							'endRowIndex'      => $param['endRowIndex'],
							'startColumnIndex' => 0,
							'endColumnIndex'   => 1,
						),
					),
				),
				'series'           => array(
					'sourceRange' => array(
						'sources' => array(
							'sheetId'          => $param['graph_sheetID'],
							'startRowIndex'    => $param['startRowIndex'],
							'endRowIndex'      => $param['endRowIndex'],
							'startColumnIndex' => 1,
							'endColumnIndex'   => $param['endColumnIndex'],
						),
					),
				),
			);
		} else {
			$chart_type     = 'basicChart';
			$chart_property = array(
				'chartType'      => $param['graph_type'],
				'legendPosition' => 'BOTTOM_LEGEND',
				'axis'           => array(
					array(
						'position' => 'BOTTOM_AXIS',
						'title'    => $param['bottom_axisname'],
					),
					array(
						'position' => 'LEFT_AXIS',
						'title'    => $param['left_axisname'],
					),
				),
				'domains'        => array(
					'domain' => array(
						'sourceRange' => array(
							'sources' => array(
								'sheetId'          => $param['graph_sheetID'],
								'startRowIndex'    => $param['startRowIndex'],
								'endRowIndex'      => $param['endRowIndex'],
								'startColumnIndex' => 0,
								'endColumnIndex'   => 1,
							),
						),
					),
				),
				'series'         => array(
					'series'     => array(
						'sourceRange' => array(
							'sources' => array(
								'sheetId'          => $param['graph_sheetID'],
								'startRowIndex'    => $param['startRowIndex'],
								'endRowIndex'      => $param['endRowIndex'],
								'startColumnIndex' => 1,
								'endColumnIndex'   => $param['endColumnIndex'],
							),
						),
					),
					'targetAxis' => 'LEFT_AXIS',
				),
				'headerCount'    => 1,
			);
		}
		$batchupdaterequest   = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => array(
					'addChart' => array(
						'chart' => array(
							'spec'     => array(
								'title'     => $param['graph_title'],
								$chart_type => $chart_property,
							),
							'position' => array(
								'overlayPosition' => array(
									'anchorCell'   => array(
										'sheetId'     => $param['graph_sheetID'],
										'rowIndex'    => $param['row_overlayPosition'],
										'columnIndex' => 1,
									),
									'widthPixels'  => 1215,
									'heightPixels' => 450,
								),
							),
						),
					),
				),
			)
		);
		$param['requestbody'] = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $param );
	}
	/**
	 * Delete default sheet
	 *
	 * @param string $param .
	 * @param int    $sheetid Sheet id.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function deletesheetobject( $param = array(), $sheetid = 0 ) {
		$batchupdaterequest   = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => array(
					'deleteSheet' => array(
						'sheetId' => $sheetid,
					),
				),
			)
		);
		$param['requestbody'] = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $param );
	}
	/**
	 * Create Conditional Format Rule object.
	 *
	 * @param string $param .
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function addconditionalformatruleobject( $param = array() ) {
		$requests = array(
			new Google_Service_Sheets_Request(
				array(
					'addConditionalFormatRule' => array(
						'rule' => array(
							'ranges'      => array( $param['range'] ),
							'booleanRule' => array(
								'condition' => array(
									'type'   => 'CUSTOM_FORMULA',
									'values' => array( array( 'userEnteredValue' => '=MOD($A:$A;2)=1' ) ),
								),
								'format'    => array(
									'backgroundColor' => array(
										'red'   => $param['r'] / 255,
										'green' => $param['g'] / 255,
										'blue'  => $param['b'] / 255,
									),
								),
							),
						),
					),
				)
			),
			new Google_Service_Sheets_Request(
				array(
					'addConditionalFormatRule' => array(
						'rule' => array(
							'ranges'      => array( $param['range'] ),
							'booleanRule' => array(
								'condition' => array(
									'type'   => 'CUSTOM_FORMULA',
									'values' => array( array( 'userEnteredValue' => '=MOD($A:$A;2)=0' ) ),
								),
								'format'    => array(
									'backgroundColor' => array(
										'red'   => $param['er'] / 255,
										'green' => $param['eg'] / 255,
										'blue'  => $param['eb'] / 255,
									),
								),
							),
						),
					),
				)
			),
			new Google_Service_Sheets_Request(
				array(
					'addConditionalFormatRule' => array(
						'rule' => array(
							'ranges'      => array( $param['range'] ),
							'booleanRule' => array(
								'condition' => array(
									'type'   => 'CUSTOM_FORMULA',
									'values' => array( array( 'userEnteredValue' => '=$A:$A=""' ) ),
								),
								'format'    => array(
									'backgroundColor' => array(
										'red'   => 255 / 255,
										'green' => 255 / 255,
										'blue'  => 255 / 255,
									),
								),
							),
						),
					),
				)
			),
		);
		return $requests;
	}
	/**
	 * Create Conditional Format Rule object.
	 *
	 * @param string $wpsslw_spreadsheetid spreadsheetid.
	 * @param array  $batchupdaterequest batch update request array.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function addconditionalformatrule( $wpsslw_spreadsheetid, $batchupdaterequest ) {
		$param['spreadsheetid'] = $wpsslw_spreadsheetid;
		$param['requestbody']   = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $param );
	}
	/**
	 * Update sheetname
	 *
	 * @param string $param .
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function updatesheetnameobject( $param = array() ) {
		$batchupdaterequest             = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => array(
					'updateSheetProperties' => array(
						'properties' => array(
							'sheetId' => $param['sheetid'],
							'title'   => $param['newsheetname'],
						),
						'fields'     => 'title',
					),
				),
			)
		);
		$requestobject['spreadsheetid'] = $param['spreadsheetid'];
		$requestobject['requestbody']   = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $requestobject );
	}
	/**
	 * Create Google_Service_Sheets_ValueRange request object.
	 *
	 * @param array  $param contains range,values.
	 * @param string $dimension either COLUMN or ROW for request dimension..
	 *
	 * @return object.
	 */
	public function multirangevalueobject( $param = array(), $dimension = 'COLUMN' ) {
		$requestbody = new \Google_Service_Sheets_ValueRange(
			array(
				'range'          => $param['range'],
				'majorDimension' => $dimension,
				'values'         => $param['values'],
			)
		);
		return $requestbody;
	}
	/**
	 * Create Google_Service_Sheets_BatchUpdateValuesRequest request object.
	 *
	 * @param array $param contains input option,data array.
	 *
	 * @return object.
	 */
	public function multirangevaluerequestbody( $param = array() ) {
		$requestbody = new Google_Service_Sheets_BatchUpdateValuesRequest(
			array(
				'valueInputOption' => $param['valueInputOption'],
				'data'             => $param['data'],
			)
		);
		return $requestbody;
	}

	/**
	 * Insert new column, Freeze first row to google spreadsheet.
	 *
	 * @param array $param contains spreadsheetid,requestbody.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function multirangevalueupdate( $param = array() ) {
		return parent::batchupdatevalues( self::$instance_service, $param );
	}
}
