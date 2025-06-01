/**
 * Admin Enqueue Script
 *
 * @package     wpsyncsheets-for-woocommerce
 */

(function ($) {
	"use strict";
	$( document ).ready(
		function () {
			// Enable/Disable Header field input.
			$( 'input[name="spreadsheetselection"]' ).on(
				"change",
				function () {
					var newRequest = $( this ).val();
					if (String( newRequest ) === "new") {
						$( "#header_fields" ).prop( "disabled", false );
						$( "#newsheet" ).show();
						$( ".synctr" ).hide();
						$( ".ord_import_row" ).hide();
						$( "#view_spreadsheet" ).hide();
						$( "#clear_spreadsheet" ).hide();
						$( "#down_spreadsheet" ).hide();
						$( "#woocommerce_spreadsheet_container" ).hide();

						$( ".headers_chk" ).attr( "disabled", false );
						$( "#wpssw-headers-notice" ).hide();

					} else {
						$( "#header_fields" ).prop( "disabled", "disabled" );
						$( "#newsheet" ).hide();
						$( "#view_spreadsheet" ).show();
						$( "#clear_spreadsheet" ).show();
						$( "#down_spreadsheet" ).show();
						$( "#woocommerce_spreadsheet_container" ).show();

						$( "#wpssw-headers-notice" ).show();

						if (String( newRequest ) === "" || parseInt( newRequest ) === 0) {
							$( "#synctr" ).hide();
						} else {
							$( ".synctr" ).show();
						}
					}
				}
			);

			var existingSheets = "";
			existingSheets     = $( "#existing_sheets option" )
			.map(
				function () {
					return $( this ).val();
				}
			)
			.get();
			$( document ).on(
				"change",
				".sheets_chk",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", false );
					}
				}
			);

			$( document ).on(
				"change",
				"#order_settings_checkbox",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( ".ord_spreadsheet_inputrow" ).removeClass( "ord_spreadsheet_row" );
						$( ".ord_spreadsheet_row" ).fadeIn();

						var order_spreadsheet = $( "#woocommerce_spreadsheet" ).val();
						if (String( order_spreadsheet ) === "new") {
							$( ".ord_spreadsheet_inputrow" ).show();
						} else if (String( order_spreadsheet ) === "") {
							$( ".ord_import_row.import_row" ).hide();
						}
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".ord_spreadsheet_inputrow" ).addClass( "ord_spreadsheet_row" );
						$( ".ord_spreadsheet_row" ).fadeOut();
					}
				}
			);
			$( document ).on(
				"change",
				"#product_settings_checkbox",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( ".prd_spreadsheet_inputrow" ).removeClass( "prd_spreadsheet_row" );
						$( ".prd_spreadsheet_row" ).fadeIn();
						var product_spreadsheet = $( "#product_spreadsheet" ).val();
						if (String( product_spreadsheet ) === "new") {
							$( ".prd_spreadsheet_inputrow" ).show();
						} else if (String( product_spreadsheet ) === "") {
							$( ".prd_import_row.import_row" ).hide();

						}
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".prd_spreadsheet_inputrow" ).addClass( "prd_spreadsheet_row" );
						$( ".prd_spreadsheet_row" ).fadeOut();
					}
				}
			);

			$( document ).on(
				"change",
				"#customer_settings_checkbox",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( ".cust_spreadsheet_inputrow" ).removeClass( "cust_spreadsheet_row" );
						$( ".cust_spreadsheet_row" ).fadeIn();
						var customer_spreadsheet = $( "#customer_spreadsheet" ).val();
						if (String( customer_spreadsheet ) === "new") {
							$( ".cust_spreadsheet_inputrow" ).show();
						} else if (String( customer_spreadsheet ) === "") {
							$( ".cust_import_row.import_row" ).hide();
						}
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".cust_spreadsheet_inputrow" ).addClass( "cust_spreadsheet_row" );
						$( ".cust_spreadsheet_row" ).fadeOut();
					}
				}
			);
			$( document ).on(
				"change",
				"#coupon_settings_checkbox",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( ".coupon_spreadsheet_inputrow" ).removeClass( "coupon_spreadsheet_row" );
						$( ".coupon_spreadsheet_row" ).fadeIn();
						var coupon_spreadsheet = $( "#coupon_spreadsheet" ).val();
						if (String( coupon_spreadsheet ) === "new") {
							$( ".coupon_spreadsheet_inputrow" ).show();
						} else if (String( coupon_spreadsheet ) === "") {
							$( ".coupon_import_row.import_row" ).hide();
						}
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".coupon_spreadsheet_inputrow" ).addClass( "coupon_spreadsheet_row" );
						$( ".coupon_spreadsheet_row" ).fadeOut();
					}
				}
			);
			// Validate newsheet name.
			$( "#mainform" ).on(
				"submit",
				function () {
					var is_enable = $( "#order_settings_checkbox" ).is( ":checked" );
					if ( ! is_enable) {
					} else {
						var isFormValid = true;
						var newRequest  = $( "#woocommerce_spreadsheet" ).val();

						var sheetSelection = $(
							'input[name="spreadsheetselection"]:checked'
						).val();

						if (String( sheetSelection ) === "new") {
							if (parseInt( $( "#spreadsheetname" ).val().length ) === 0) {
								$( "#newsheet" ).addClass( "highlight" );
								isFormValid = false;
							} else {
								$( this ).removeClass( "highlight" );
							}
							if ( ! isFormValid) {
								alert( "Please Enter Spreadsheet Name" );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#spreadsheetname" ).focus();
							}
							return isFormValid;
						} else {
							if (String( newRequest ) === "" || parseInt( newRequest ) === 0) {
								alert( "Please Select Spreadsheet." );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#woocommerce_spreadsheet" ).focus();
								return false;
							}
						}
						var headerformat = $(
							"input[type=radio][name=header_format]:checked"
						).val();
						if (
						$( "input[type=radio][name=header_format]" ).length > 0 &&
						(String( headerformat ) === "" || typeof headerformat === "undefined")
						) {
							alert( "Please select header format." );
							$( "html, body" ).animate(
								{
									scrollTop: $( "#header_format" ).first().offset().top - 140,
								},
								1200
							);
							$( "#header_format" ).focus();
							return false;
						}

						if (
						parseInt(
							$( ".default-order-sheet-section" ).find(
								"input[type=checkbox]:checked"
							).length
						) === 0
						) {
							var orderSheetPresent = false;

							if (Boolean( orderSheetPresent ) === false) {
								alert(
									"Please select at least one order status to get it work in spreadsheet"
								);
								$( "html, body" ).animate(
									{
										scrollTop:
										$( ".default-order-sheet-section" ).first().offset().top - 140,
									},
									1200
								);
								setTimeout(
									function () {
										$( ".default-order-sheet-section" )
										.first()
										.css( "border", "1px solid #ff5859" );
									},
									1000
								);
								$( ".default-order-sheet-section" ).first().focus();
								return false;
							}
						}
					}
				}
			);
			// Validate newsheet name.
			$( "#productform" ).on(
				"submit",
				function () {
					var is_enable = $( "#product_settings_checkbox" ).is( ":checked" );
					if ( ! is_enable) {
					} else {
						var isFormValid       = true;
						var newRequest        = $( "#product_spreadsheet" ).val();
						var spreadsheetOption = $(
							'input[name="prdsheetselection"]:checked'
						).val();

						if (String( spreadsheetOption ) === "new") {
							if (parseInt( $( "#product_spreadsheet_name" ).val().length ) === 0) {
								$( ".prd_spreadsheet_inputrow" ).addClass( "highlight" );
								isFormValid = false;
							} else {
								$( this ).removeClass( "highlight" );
							}
							if ( ! isFormValid) {
								alert( "Please Enter Spreadsheet Name" );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#product_spreadsheet_name" ).focus();
							}
							return isFormValid;
						} else {
							if (String( newRequest ) === "" || parseInt( newRequest ) === 0) {
								alert( "Please Select Spreadsheet." );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#product_spreadsheet" ).focus();
								return false;
							}
						}
					}
				}
			);
			$( "#customerform" ).on(
				"submit",
				function () {
					var is_enable = $( "#customer_settings_checkbox" ).is( ":checked" );
					if ( ! is_enable) {
					} else {
						var isFormValid = true;
						var newRequest  = $( "#customer_spreadsheet" ).val();

						var spreadsheetOption = $(
							'input[name="custsheetselection"]:checked'
						).val();
						if (String( spreadsheetOption ) === "new") {
							if (parseInt( $( "#customer_spreadsheet_name" ).val().length ) === 0) {
								$( ".cust_spreadsheet_inputrow" ).addClass( "highlight" );
								isFormValid = false;
							} else {
								$( this ).removeClass( "highlight" );
							}
							if ( ! isFormValid) {
								alert( "Please Enter Spreadsheet Name" );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#customer_spreadsheet_name" ).focus();
							}
							return isFormValid;
						} else {
							if (String( newRequest ) === "" || parseInt( newRequest ) === 0) {
								alert( "Please Select Spreadsheet." );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#customer_spreadsheet" ).focus();
								return false;
							}
						}
					}
				}
			);
			// Validate newsheet name.
			$( "#couponform" ).on(
				"submit",
				function () {
					var is_enable = $( "#coupon_settings_checkbox" ).is( ":checked" );
					if ( ! is_enable) {
					} else {
						var isFormValid       = true;
						var newRequest        = $( "#coupon_spreadsheet" ).val();
						var spreadsheetOption = $(
							'input[name="couponsheetselection"]:checked'
						).val();

						if (String( spreadsheetOption ) === "new") {
							if (parseInt( $( "#coupon_spreadsheet_name" ).val().length ) === 0) {
								$( ".coupon_spreadsheet_inputrow" ).addClass( "highlight" );
								isFormValid = false;
							} else {
								$( this ).removeClass( "highlight" );
							}
							if ( ! isFormValid) {
								alert( "Please Enter Spreadsheet Name" );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#coupon_spreadsheet_name" ).focus();
							}
							return isFormValid;
						} else {
							if (String( newRequest ) === "" || parseInt( newRequest ) === 0) {
								alert( "Please Select Spreadsheet." );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#coupon_spreadsheet" ).focus();
								return false;
							}
						}
					}
				}
			);
			$( 'input[name="prdsheetselection"]' ).on(
				"change",
				function () {
					var newrequest = $( this ).val();
					if (newrequest == "new") {
						$( ".prd_spreadsheet_inputrow" ).show();
						$( "#product_spreadsheet_container" ).hide();
						$( "#prodsynctr" ).hide();
						$( ".prd_import_row.import_row" ).hide();
					} else {
						$( ".prd_spreadsheet_inputrow" ).hide();
						$( "#product_spreadsheet_container" ).show();
						var prdSheetId = $( "#product_spreadsheet" ).val();
						if (
						String( newrequest ) === "" ||
						parseInt( newrequest ) === 0 ||
						String( prdSheetId ) === "" ||
						parseInt( prdSheetId ) === 0 ||
						String( prdSheetId ) === "new"
						) {
							$( "#prodsynctr" ).hide();
							$( ".prd_import_row.import_row" ).hide();
						} else {
							$( "#prodsynctr" ).show();
						}
					}
				}
			);

			$( 'input[name="custsheetselection"]' ).on(
				"change",
				function () {
					var newrequest = $( this ).val();
					if (newrequest == "new") {
						$( ".cust_spreadsheet_inputrow" ).show();
						$( "#customer_spreadsheet_container" ).hide();
						$( "#custsynctr" ).hide();
						$( ".cust_import_row" ).hide();
					} else {
						$( ".cust_spreadsheet_inputrow" ).hide();
						$( "#customer_spreadsheet_container" ).show();
						var sheetId = $( "#customer_spreadsheet" ).val();
						if (
						String( newrequest ) === "" ||
						parseInt( newrequest ) === 0 ||
						String( sheetId ) === "" ||
						parseInt( sheetId ) === 0 ||
						String( sheetId ) === "new"
						) {
							$( "#custsynctr" ).hide();
							$( ".cust_import_row" ).hide();
						} else {
							$( "#custsynctr" ).show();
						}
					}
				}
			);
			$( 'input[name="couponsheetselection"]' ).on(
				"change",
				function () {
					var newrequest = $( this ).val();
					if (newrequest == "new") {
						$( ".coupon_spreadsheet_inputrow" ).show();
						$( "#coupon_spreadsheet_container" ).hide();
						$( "#couponsynctr" ).hide();
						$( ".coupon_import_row" ).hide();
					} else {
						$( ".coupon_spreadsheet_inputrow" ).hide();
						$( "#coupon_spreadsheet_container" ).show();
						var sheetId = $( "#coupon_spreadsheet" ).val();
						if (
						String( newrequest ) === "" ||
						parseInt( newrequest ) === 0 ||
						String( sheetId ) === "" ||
						parseInt( sheetId ) === 0 ||
						String( sheetId ) === "new"
						) {
							$( "#couponsynctr" ).hide();
							$( ".coupon_import_row" ).hide();
						} else {
							$( "#couponsynctr" ).show();
						}
					}
				}
			);
		}
	);
	$( document ).ready(
		function () {
			$( "#reset_settings" ).on(
				"click",
				function (e) {
					e.preventDefault();
					var wpnonce = jQuery( '#wpssw_api_settings' ).val();
					jQuery.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data: "action=wpsslw_reset_settings&_wpnonce=" + wpnonce,
							beforeSend: function () {
								if (
								confirm(
									"It will unselect all spreadsheets from all settings tabs, so you need to set them up again. Are you sure you want to reset settings?"
								)
								) {
								} else {
									return false;
								}
							},
							success: function (response) {
								if (String( response ) === "successful") {
									location.reload();
								} else {
									alert( response );
								}
							},
							error: function (s) {
								alert( "Error" );
							},
						}
					);
				}
			);
			$( "#sync" ).on(
				"click",
				function (e) {
					doAjax();
				}
			);
			$( "#prodsync" ).on(
				"click",
				function (e) {
					doProductAjax();
				}
			);
			$( "#custsync" ).on(
				"click",
				function (e) {
					doCustomerAjax();
				}
			);
			$( "#couponsync" ).on(
				"click",
				function (e) {
					doCouponAjax();
				}
			);
			var productLimit  = 500;
			var productCount  = 0;
			var totalPrdSheet = 0;
			var syncPrdSheet  = 0;
			var nextPrdSheet  = 1;

			function doProductAjax(args) {
				var productnonce = $( "#wpsslw_product_settings" ).val();

				$( "#prodsyncloader" ).show();
				$( "#prodsynctext" ).show();
				$( "#prodsync" ).hide();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
							"action=wpsslw_get_product_count&wpsslw_product_settings=" +
							productnonce,

						success: function (response) {
							obj           = JSON.parse( response );
							totalPrdSheet = Object.keys( obj ).length;
							if (totalPrdSheet > 0) {
								var sheetName     = obj[syncPrdSheet]["sheet_name"];
								var sheetSlug     = obj[syncPrdSheet]["sheet_slug"];
								var totalproducts = obj[syncPrdSheet]["totalproducts"];
								productLimit      = obj[syncPrdSheet]["productlimit"];
								if (parseInt( totalproducts ) < 2000) {
									productLimit = 200;
									if (parseInt( totalproducts ) < 200) {
										productLimit = parseInt( totalproducts );
									}
								}
								syncProductData(
									totalproducts,
									productLimit,
									sheetName,
									sheetSlug
								);
							} else {
								alert( "All Products are synchronize successfully." );
								displayproductsync();
								resetproductsync();
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displayproductsync();
						resetproductsync();
					}
				);
			}

			var totalSheet = 0;
			var syncSheet  = 0;
			var orderLimit = 500;
			var orderCount = 0;
			var nextSheet  = 1;
			var obj;
			function doAjax(args) {
				var wpsswGeneralSettings = $( "#wpsslw_general_settings" ).val();
				var syncAll              = $( 'input[name="sync_range"]:checked' ).val();
				var syncAllFromdate      = $( "#sync_all_fromdate" ).val();
				var syncAllTodate        = $( "#sync_all_todate" ).val();
				if (
				parseInt( syncAll ) === 0 &&
				(String( syncAllFromdate ) === "" || String( syncAllTodate ) === "")
				) {
					alert( "From Date and To Date should not be blank." );
					return false;
				} else if (syncAllFromdate > syncAllTodate) {
					alert( "From Date should not be greater than To Date." );
					return false;
				} else {
					$( "#syncloader" ).show();
					$( "#synctext" ).show();
					$( "#sync" ).hide();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data:
							"action=wpsslw_get_orders_count&wpsslw_general_settings=" +
							wpsswGeneralSettings +
							"&sync_all_fromdate=" +
							syncAllFromdate +
							"&sync_all_todate=" +
							syncAllTodate +
							"&sync_all=" +
							syncAll,
							success: function (response) {
								if (String( response ) === "error") {
									alert( "Sorry, your nonce did not verify." );
									displayordersync();
									resetordersync();
								} else if (String( response ) === "spreadsheetnotexist") {
									alert( "Please save your settings first and try again." );
									displayordersync();
									resetordersync();
									$( "html, body" ).animate(
										{
											scrollTop: $( "html, body" ).get( 0 ).scrollHeight,
										},
										2000
									);
									return false;
								} else if (String( response ) === "sheetnotexist") {
									alert(
										"Selected Order status sheet is not present in your spreadsheet so to sync orders first save your settings and try again."
									);
									displayordersync();
									resetordersync();
									$( "html, body" ).animate(
										{
											scrollTop: $( "html, body" ).get( 0 ).scrollHeight,
										},
										2000
									);
									return false;
								} else {
									obj        = JSON.parse( response );
									totalSheet = Object.keys( obj ).length;
									if (totalSheet > 0) {
										var sheetName   = obj[syncSheet]["sheet_name"];
										var sheetSlug   = obj[syncSheet]["sheet_slug"];
										var totalOrders = obj[syncSheet]["totalorders"];
										orderLimit      = obj[syncSheet]["orderlimit"];
										if (parseInt( totalOrders ) < 2000) {
											orderLimit = 200;
											if (parseInt( totalOrders ) < 200) {
												orderLimit = parseInt( totalOrders );
											}
										}
										syncData(
											sheetName,
											sheetSlug,
											totalOrders,
											orderLimit,
											syncAll,
											syncAllFromdate,
											syncAllTodate
										);
									} else {
										alert( "All Orders are synchronize successfully" );
										displayordersync();
										resetordersync();
									}
								}
							},
						}
					).fail(
						function () {
							alert( "Error" );
							displayordersync();
							resetordersync();
						}
					);
				}
			}
			function syncData(
			sheetName,
			sheetSlug,
			totalOrders,
			orderLimit,
			syncAll,
			syncAllFromdate,
			syncAllTodate
			) {
				if (parseInt( totalOrders ) < 2000) {
					orderLimit = 200;
					if (parseInt( totalOrders ) < 200) {
						orderLimit = parseInt( totalOrders );
					}
				}
				if (totalOrders > orderCount) {
					orderCount = orderCount + orderLimit;
				} else if (
				totalOrders < orderCount &&
				parseInt( nextSheet ) === 1 &&
				totalSheet != syncSheet + 1
				) {
						orderCount = orderCount + orderLimit;
						nextSheet  = 0;
				}
				if (totalOrders > orderLimit && orderCount < totalOrders) {
					$( "#synctext" ).html(
						"Synchronizing : " +
						orderCount +
						" / " +
						totalOrders +
						" " +
						sheetName
					);
				} else {
					$( "#synctext" ).html(
						"Synchronizing : " +
						totalOrders +
						" / " +
						totalOrders +
						" " +
						sheetName
					);
				}
				var sync_nonce_token;
				sync_nonce_token = admin_ajax_object.sync_nonce_token;
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpsslw_sync_sheetswise&sheetslug=" +
						sheetSlug +
						"&sheetname=" +
						sheetName +
						"&orderlimit=" +
						orderLimit +
						"&ordercount=" +
						orderCount +
						"&sync_all_fromdate=" +
						syncAllFromdate +
						"&sync_all_todate=" +
						syncAllTodate +
						"&sync_all=" +
						syncAll +
						"&sync_nonce_token=" +
						sync_nonce_token,
						success: function (response) {
							if (
							parseInt( totalSheet ) === syncSheet + 1 &&
							totalOrders <= orderCount
							) {
								totalSheet = 0;
								syncSheet  = 0;
								orderLimit = 500;
								orderCount = 0;
								nextSheet  = 1;
								obj        = {};
								if (String( response ) === "successful") {
									alert( "All Orders are synchronize successfully" );
									displayordersync();
									resetordersync();
								} else {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
									displayordersync();
									resetordersync();
								}
							}
						},
						complete: function () {
							var objLength = Object.keys( obj ).length;
							if (0 !== objLength) {
								var sheetName   = obj[syncSheet]["sheet_name"];
								var sheetSlug   = obj[syncSheet]["sheet_slug"];
								var totalOrders = obj[syncSheet]["totalorders"];
								orderLimit      = obj[syncSheet]["orderlimit"];
								if (totalOrders > orderCount) {
									setTimeout(
										function () {
											syncData(
												sheetName,
												sheetSlug,
												totalOrders,
												orderLimit,
												syncAll,
												syncAllFromdate,
												syncAllTodate
											);
										},
										2000
									);
								} else if (
								totalOrders < orderCount &&
								parseInt( nextSheet ) === 1 &&
								totalSheet != syncSheet + 1
								) {
										setTimeout(
											function () {
													syncData(
														sheetName,
														sheetSlug,
														totalOrders,
														orderLimit,
														syncAll,
														syncAllFromdate,
														syncAllTodate
													);
											},
											2000
										);
								} else {
									if (totalSheet > syncSheet + 1) {
										syncSheet       = syncSheet + 1;
										orderCount      = 0;
										nextSheet       = 1;
										var sheetName   = obj[syncSheet]["sheet_name"];
										var sheetSlug   = obj[syncSheet]["sheet_slug"];
										var totalOrders = obj[syncSheet]["totalorders"];
										orderLimit      = obj[syncSheet]["orderlimit"];
										setTimeout(
											function () {
												syncData(
													sheetName,
													sheetSlug,
													totalOrders,
													orderLimit,
													syncAll,
													syncAllFromdate,
													syncAllTodate
												);
											},
											2000
										);
									}
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );

						displayordersync();
						resetordersync();
					}
				);
			}
			function displayordersync() {
				$( "#syncloader" ).hide();
				$( "#synctext" ).hide();
				$( "#synctext" ).html( "Synchronizing..." );
				document.getElementById( "sync" ).style.display = "inline-block";
			}
			function resetordersync(){
				totalSheet = 0;
				syncSheet  = 0;
				orderLimit = 500;
				orderCount = 0;
				nextSheet  = 1;
				obj        = {};
			}

			function syncProductData(
			totalproducts,
			productLimit,
			sheetName,
			sheetSlug
			) {
				var productnonce = $( "#wpsslw_product_settings" ).val();

				if (parseInt( totalproducts ) < 2000) {
					productLimit = 200;
					if (parseInt( totalproducts ) < 200) {
						productLimit = parseInt( totalproducts );
					}
				}
				if (totalproducts > productCount) {
					productCount = productCount + productLimit;
				} else if (totalproducts < productCount &&
				parseInt( nextPrdSheet ) === 1 &&
				totalPrdSheet != syncPrdSheet + 1) {
					productCount = productCount + productLimit;
					nextPrdSheet = 0;
				}

				if (totalproducts > productLimit && productCount < totalproducts) {
					$( "#prodsynctext" ).html(
						"Synchronizing : " + productCount + " / " + totalproducts +
						" " +
						sheetName
					);
				} else {
					$( "#prodsynctext" ).html(
						"Synchronizing : " + totalproducts + " / " + totalproducts +
						" " +
						sheetName
					);
				}
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpsslw_sync_products&sheetslug=" +
						sheetSlug +
						"&sheetname=" +
						sheetName +
						"&productlimit=" +
						productLimit +
						"&productcount=" +
						productCount +
						"&wpsslw_product_settings=" +
						productnonce,
						success: function (response) {
							if (
							parseInt( totalPrdSheet ) === syncPrdSheet + 1 && totalproducts <= productCount) {

								if (response == "successful") {
									alert( "All Products are synchronize successfully" );
								} else {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
								}
								displayproductsync();
								resetproductsync();
							}
						},
						complete: function () {

							var objLength = Object.keys( obj ).length;
							if (0 !== objLength) {
								var sheetName     = obj[syncPrdSheet]["sheet_name"];
								var sheetSlug     = obj[syncPrdSheet]["sheet_slug"];
								var totalproducts = obj[syncPrdSheet]["totalproducts"];
								productLimit      = obj[syncPrdSheet]["productlimit"];
								if (totalproducts > productCount) {
									setTimeout(
										function () {
											syncProductData(
												totalproducts,
												productLimit,
												sheetName,
												sheetSlug
											);
										},
										2000
									);
								} else if (
								totalproducts < productCount &&
								parseInt( nextPrdSheet ) === 1 &&
								totalPrdSheet != syncPrdSheet + 1
								) {
										setTimeout(
											function () {
												syncProductData(
													totalproducts,
													productLimit,
													sheetName,
													sheetSlug
												);
											},
											2000
										);
								} else {
									if (totalPrdSheet > syncPrdSheet + 1) {
										syncPrdSheet      = syncPrdSheet + 1;
										productCount      = 0;
										nextPrdSheet      = 1;
										var sheetName     = obj[syncPrdSheet]["sheet_name"];
										var sheetSlug     = obj[syncPrdSheet]["sheet_slug"];
										var totalproducts = obj[syncPrdSheet]["totalproducts"];
										productLimit      = obj[syncPrdSheet]["orderlimit"];
										setTimeout(
											function () {
												syncProductData(
													totalproducts,
													productLimit,
													sheetName,
													sheetSlug
												);
											},
											2000
										);
									}
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displayproductsync();
						resetproductsync();
					}
				);
			}
			function displayproductsync() {
				$( "#prodsyncloader" ).hide();
				$( "#prodsynctext" ).hide();
				$( "#prodsynctext" ).html( "Synchronizing..." );
				document.getElementById( "prodsync" ).style.display = "inline-block";
			}
			function resetproductsync(){
				productLimit  = 500;
				productCount  = 0;
				obj           = {};
				totalPrdSheet = 0;
				syncPrdSheet  = 0;
				nextPrdSheet  = 1;
			}
			var customerLimit = 500;
			var customerCount = 0;
			function doCustomerAjax(args) {
				var customernonce = $( "#wpssw_customer_settings" ).val();
				$( "#custsyncloader" ).show();
				$( "#custsynctext" ).show();
				$( "#custsync" ).hide();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpsslw_get_customer_count&wpssw_customer_settings=" +
						customernonce,
						success: function (response) {
							try {
								if (String( response ) === "notfound") {
									alert( "No customers found for synchronization." );
									displaycustomersync();
								} else if (String( response ) === "error") {
									alert( "Sorry, your nonce did not verify." );
									displaycustomersync();
								} else {
									obj                = JSON.parse( response );
									var totalcustomers = obj.totalcustomers;
									customerLimit      = obj.customerlimit;
									if (totalcustomers > 0) {
										if (parseInt( totalcustomers ) < 2000) {
											customerLimit = 200;
											if (parseInt( totalcustomers ) < 200) {
												customerLimit = parseInt( totalcustomers );
											}
										}
										syncCustomerData(
											totalcustomers,
											customerLimit
										);
									} else {
										alert( "All Customers data are synchronize successfully" );
										displaycustomersync();
									}
								}
							} catch (e) {
								alert( response );
								displaycustomersync();
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displaycustomersync();
					}
				);
			}
			var couponLimit = 500;
			var couponCount = 0;
			function doCouponAjax(args) {
				var couponnonce = $( "#wpssw_coupon_settings" ).val();
				$( "#couponsyncloader" ).show();
				$( "#couponsynctext" ).show();
				$( "#couponsync" ).hide();

				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpsslw_get_coupon_count&wpssw_coupon_settings=" +
						couponnonce,
						success: function (response) {
							try {
								obj              = JSON.parse( response );
								var totalcoupons = obj.totalcoupons;
								couponLimit      = obj.couponlimit;
								if (totalcoupons > 0) {
									if (parseInt( totalcoupons ) < 2000) {
										couponLimit = 200;
										if (parseInt( totalcoupons ) < 200) {
											couponLimit = parseInt( totalcoupons );
										}
									}
									syncCouponData(
										totalcoupons,
										couponLimit
									);
								} else {
									alert( "All Coupons data are synchronize successfully" );
									displaycouponsync();
								}
							} catch (e) {
								alert( response );
								displaycouponsync();
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displaycouponsync();
					}
				);
			}

			function displaycouponsync() {
				$( "#couponsyncloader" ).hide();
				$( "#couponsynctext" ).hide();
				$( "#couponsynctext" ).html( "Synchronizing..." );
				document.getElementById( "couponsync" ).style.display = "inline-block";
			}
			function syncCustomerData(
			totalcustomers,
			customerLimit
			) {
				if (totalcustomers > customerCount) {
					if (parseInt( totalcustomers ) < 2000) {
						customerLimit = 200;
						if (parseInt( totalcustomers ) < 200) {
							customerLimit = parseInt( totalcustomers );
						}
					}
					customerCount = customerCount + customerLimit;
				} else if (totalcustomers < customerCount) {
					customerCount = customerCount + customerLimit;
				}
				if (totalcustomers > customerLimit && customerCount < totalcustomers) {
					$( "#custsynctext" ).html(
						"Synchronizing : " + customerCount + " / " + totalcustomers
					);
				} else {
					$( "#custsynctext" ).html(
						"Synchronizing : " + totalcustomers + " / " + totalcustomers
					);
				}
				var customernonce = $( "#wpssw_customer_settings" ).val();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpsslw_sync_customers&customerlimit=" +
						customerLimit +
						"&customercount=" +
						customerCount +
						"&wpssw_customer_settings=" +
						customernonce,
						success: function (response) {
							if (totalcustomers <= customerCount) {
								customerLimit = 500;
								customerCount = 0;
								obj           = {};
								if (response == "successful") {
									alert( "All Customers data are synchronize successfully" );
									displaycustomersync();
								} else {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
									displaycustomersync();
								}
							}
						},
						complete: function () {
							if (0 !== obj.length) {
								var totalcustomers = obj.totalcustomers;
								customerLimit      = obj.customerlimit;
								if (parseInt( totalcustomers ) < 2000) {
									customerLimit = 200;
									if (parseInt( totalcustomers ) < 200) {
										customerLimit = parseInt( totalcustomers );
									}
								}
								if (totalcustomers > customerCount) {
									setTimeout(
										function () {
											syncCustomerData(
												totalcustomers,
												customerLimit
											);
										},
										2000
									);
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						customerLimit = 500;
						customerCount = 0;
						obj           = {};
						displaycustomersync();
					}
				);
			}
			function displaycustomersync() {
				$( "#custsyncloader" ).hide();
				$( "#custsynctext" ).hide();
				$( "#custsynctext" ).html( "Synchronizing..." );
				document.getElementById( "custsync" ).style.display = "inline-block";
			}
			function syncCouponData(
			totalcoupons,
			couponLimit
			) {
				if (totalcoupons > couponCount) {
					if (parseInt( totalcoupons ) < 2000) {
						couponLimit = 200;
						if (parseInt( totalcoupons ) < 200) {
							couponLimit = parseInt( totalcoupons );
						}
					}
					couponCount = couponCount + couponLimit;
				} else if (totalcoupons < couponCount) {
					couponCount = couponCount + couponLimit;
				}
				if (totalcoupons > couponLimit && couponCount < totalcoupons) {
					$( "#couponsynctext" ).html(
						"Synchronizing : " + couponCount + " / " + totalcoupons
					);
				} else {
					$( "#couponsynctext" ).html(
						"Synchronizing : " + totalcoupons + " / " + totalcoupons
					);
				}
				var couponnonce = $( "#wpssw_coupon_settings" ).val();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpsslw_sync_coupons&couponlimit=" +
						couponLimit +
						"&couponcount=" +
						couponCount +
						"&couponnonce=" +
						couponnonce,
						success: function (response) {
							if (totalcoupons <= couponCount) {
								couponLimit = 500;
								couponCount = 0;
								obj         = {};
								if (response == "successful") {
									alert( "All Coupons data are synchronize successfully" );
									displaycouponsync();
								} else {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
									displaycouponsync();
								}
							}
						},
						complete: function () {
							if (0 !== obj.length) {
								var totalcoupons = obj.totalcoupons;
								couponLimit      = obj.couponlimit;
								if (parseInt( totalcoupons ) < 2000) {
									couponLimit = 200;
									if (parseInt( totalcoupons ) < 200) {
										couponLimit = parseInt( totalcoupons );
									}
								}
								if (totalcoupons > couponCount) {
									setTimeout(
										function () {
											syncCouponData(
												totalcoupons,
												couponLimit
											);
										},
										2000
									);
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						couponLimit = 500;
						couponCount = 0;
						obj         = {};
						displaycouponsync();
					}
				);
			}
			$( document ).on(
				"click",
				".wpssw_new_token",
				function (e) {
					e.preventDefault();
					$( ".tablinks.googleapi-settings" ).addClass( "active" );
				}
			);

			$( document ).on(
				"click",
				"#clear_spreadsheet",
				function (e) {
					e.preventDefault();
					var wpsswGeneralSettings = $( "#wpsslw_general_settings" ).val();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data:
							"action=wpsslw_clear_all_sheet&wpsslw_general_settings=" +
							wpsswGeneralSettings,

							beforeSend: function () {
								if (
								confirm(
									"Are you sure you want to enable Clear Spreadsheet? If you do, it will remove all your orders from the spreadsheet, and you'll be left only with the headers of the sheet."
								)
								) {
									$( "#clearloader" ).attr( "src", "images/spinner.gif" );
									$( "#clearloader" ).show();
								} else {
									return false;
								}
							},
							success: function (response) {
								$( "#clearloader" ).hide();
								if (String( response ) === "successful") {
									alert( "Spreadsheet Cleared successfully" );
								} else if (String( response ) === "spreadsheetnotexist") {
									alert( "Please save your settings first and try again." );
									return false;
								} else if (String( response ) === "sheetnotexist") {
									alert(
										"Selected Order status sheet is not present in your spreadsheet so to clear spreadsheet first save your settings and try again."
									);
									$( "html, body" ).animate(
										{
											scrollTop: $( "html, body" ).get( 0 ).scrollHeight,
										},
										3000
									);
									return false;
								} else {
									alert( response );
								}
							},
							error: function (s) {
								alert( "Error" );
								$( "#clearloader" ).hide();
							},
						}
					);
				}
			);
			$( document ).on(
				"click",
				"#clear_productsheet",
				function (e) {
					e.preventDefault();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data: "action=wpsslw_clear_productsheet",
							beforeSend: function () {
								if (
								confirm(
									"Are you sure? It will clear all your product data within the spreadsheet and you would have remained only with sheet headers."
								)
								) {
									$( "#clearprdloader" ).attr( "src", "images/spinner.gif" );
									$( "#clearprdloader" ).show();
								} else {
									return false;
								}
							},
							success: function (response) {
								if (response == "successful") {
									alert( "Spreadsheet Cleared successfully" );
									$( "#clearprdloader" ).hide();
								} else {
									alert( response );
									$( "#clearprdloader" ).hide();
								}
							},
							error: function (s) {
								alert( "Error" );
								$( "#clearprdloader" ).hide();
							},
						}
					);
				}
			);
			$( document ).on(
				"click",
				"#clear_customersheet",
				function (e) {
					e.preventDefault();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data: "action=wpsslw_clear_customersheet",
							beforeSend: function () {
								if (
								confirm(
									"Are you sure? You want to enable Clear Spreadsheet this is clear all your customer data within the spreadsheet and you would be remained only with sheet headers."
								)
								) {
									$( "#clearcustloader" ).attr( "src", "images/spinner.gif" );
									$( "#clearcustloader" ).show();
								} else {
									return false;
								}
							},
							success: function (response) {
								if (response == "successful") {
									alert( "Spreadsheet Cleared successfully" );
									$( "#clearcustloader" ).hide();
								} else {
									alert( response );
									$( "#clearcustloader" ).hide();
								}
							},
							error: function (s) {
								alert( "Error" );
								$( "#clearcustloader" ).hide();
							},
						}
					);
				}
			);

			$( document ).on(
				"click",
				"#clear_couponsheet",
				function (e) {
					e.preventDefault();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data: "action=wpsslw_clear_couponsheet",
							beforeSend: function () {
								if (
								confirm(
									"Are you sure? You want to enable Clear Spreadsheet this is clear all your coupon data within the spreadsheet and you would be remained only with sheet headers."
								)
								) {
									$( "#clearcouponloader" ).attr( "src", "images/spinner.gif" );
									$( "#clearcouponloader" ).show();
								} else {
									return false;
								}
							},
							success: function (response) {
								if (response == "successful") {
									alert( "Spreadsheet Cleared successfully" );
									$( "#clearcouponloader" ).hide();
								} else {
									alert( response );
									$( "#clearcouponloader" ).hide();
								}
							},
							error: function (s) {
								alert( "Error" );
								$( "#clearcouponloader" ).hide();
							},
						}
					);
				}
			);
		}
	);
	// Check for existing sheets.
	$( document ).ready(
		function () {
			var prevSheetId = $( "#woocommerce_spreadsheet" ).val();
			$( "#woocommerce_spreadsheet" ).on(
				"change",
				function () {
					var sheetId = $( this ).val();
					if (String( sheetId ) === prevSheetId) {
						$( "#header_fields" ).prop( "disabled", "disabled" );
						$( "#newsheet" ).hide();
						$( "#view_spreadsheet" ).show();
						$( "#clear_spreadsheet" ).show();
						$( "#down_spreadsheet" ).show();
						$( "#woocommerce_spreadsheet_container" ).show();

						$( "#wpssw-headers-notice" ).show();
						$( ".synctr" ).show();

						return true;
					}

					var sync_nonce_token;
					sync_nonce_token = admin_ajax_object.sync_nonce_token;
					if (sheetId != null && sheetId != "" && sheetId != "new") {
						$.ajax(
							{
								url: admin_ajax_object.ajaxurl,
								type: "post",
								data: {
									action: "wpssw_check_existing_sheet",
									id: sheetId,
									sync_nonce_token: sync_nonce_token,
								},
								success: function (response) {
									if (String( response ) === "successful") {
										alert(
											"Selected spreadsheet will be mismatch match your order data with respect to the sheet headers so please create new spreadsheet or select different spreadsheet."
										);
										$( "#woocommerce_spreadsheet" ).val( prevSheetId );
									} else if (String( response )) {
										alert( response );
										$( "#woocommerce_spreadsheet" ).val( prevSheetId );
									} else {
										$( "#header_fields" ).prop( "disabled", false );
										$( ".synctr" ).css( "display", "none" );
										$( ".ord_import_row" ).css( "display", "none" );
									}
								},
							}
						);
					}
				}
			);

			var prevPrdSheetId = $( "#product_spreadsheet" ).val();
			$( "#product_spreadsheet" ).on(
				"change",
				function () {
					var sheetId = $( this ).val();
					if (String( sheetId ) === prevPrdSheetId) {
						$( "#prd_view_spreadsheet" ).show();
						$( "#clear_productsheet" ).show();
						$( "#prd_down_spreadsheet" ).show();
						$( "#prodsynctr" ).show();
					} else {
						$( "#prd_view_spreadsheet" ).hide();
						$( "#clear_productsheet" ).hide();
						$( "#prd_down_spreadsheet" ).hide();
						$( "#prodsynctr" ).hide();
						$( ".prd_import_row.import_row" ).hide();
					}
				}
			);
			var prevCustSheetId = $( "#customer_spreadsheet" ).val();
			$( "#customer_spreadsheet" ).on(
				"change",
				function () {
					var sheetId = $( this ).val();
					if (String( sheetId ) === prevCustSheetId) {
						$( "#cust_view_spreadsheet" ).show();
						$( "#clear_customersheet" ).show();
						$( "#cust_down_spreadsheet" ).show();
						$( "#custsynctr" ).show();
					} else {
						$( "#cust_view_spreadsheet" ).hide();
						$( "#clear_customersheet" ).hide();
						$( "#cust_down_spreadsheet" ).hide();
						$( "#custsynctr" ).hide();
						$( ".cust_import_row" ).hide();
					}
				}
			);
			var prevCouponSheetId = $( "#coupon_spreadsheet" ).val();
			$( "#coupon_spreadsheet" ).on(
				"change",
				function () {
					var sheetId = $( this ).val();
					if (String( sheetId ) === prevCouponSheetId) {
						$( "#coupon_view_spreadsheet" ).show();
						$( "#clear_couponsheet" ).show();
						$( "#coupon_down_spreadsheet" ).show();
						$( "#couponsynctr" ).show();
					} else {
						$( "#coupon_view_spreadsheet" ).hide();
						$( "#clear_couponsheet" ).hide();
						$( "#coupon_down_spreadsheet" ).hide();
						$( "#couponsynctr" ).hide();
						$( ".coupon_import_row" ).hide();
					}
				}
			);
		}
	);
	$( document ).ready(
		function () {
			$( "#authlink" ).on(
				"click",
				function (e) {
					$( "#authbtn" ).hide();
					document.getElementById( "authtext" ).style.display = "inline-block";
				}
			);
			$( "#revoke" ).on(
				"click",
				function (e) {
					document.getElementById( "authtext" ).style.display     = "none";
					document.getElementById( "client_token" ).style.display = "none";
				}
			);
		}
	);
	$( document ).ready(
		function () {
			// Get the input fields.
			var licenseKeyInput = $( "#wpssw_license_key" );
			var activateButton  = $( "[name='wpssw_license_activate']" );

			// Add event listener to the activate button.
			activateButton.on(
				"click",
				function (event) {
					// Check if the license key input is empty.
					if (licenseKeyInput.val().trim() === "") {
						event.preventDefault(); // Prevent form submission.
						alert( "Please enter a license key." ); // Display an error message or take any desired action.
					}
				}
			);
		}
	);
	$( document ).ready(
		function () {
			var licensekey   = $( "#wpssw_license_key" ).val();
			var client_token = "";
			if ($( "#client_token" ).length > 0) {
				client_token = $( "#client_token" ).val();
			}
			var isactive = false;
			if ($( '[name="wpssw_license_activate"]' ).length > 0) {
				isactive = true;
			}
			var tokenError = $( "#token-error" ).val();

			if (licensekey == "" || isactive) {
				wpsslwNavTab( event, "wpssw-nav-license" );
				$( ".wpssw-nav-license" ).addClass( "active" );
				$( ".wpssw-nav-googleapi" ).prop( "disabled", true );
				$( ".wpssw-nav-settings" ).prop( "disabled", true );
			} else if (client_token == "" || 1 === parseInt( tokenError )) {
				wpsslwNavTab( event, "wpssw-nav-googleapi" );
				$( ".wpssw-nav-googleapi" ).addClass( "active" );
			} else {
				wpsslwNavTab( event, "wpssw-nav-settings" );
				$( ".wpssw-nav-settings" ).addClass( "active" );
				var activeTab = getParameterByName( "tab" );
				if (activeTab != null) {
					var currenttab = activeTab;
					if ("wpssw-nav-license" == activeTab) {
						activeTab = "order-settings";
					}
					wpsslwTab( event, activeTab );
					if ("wpssw-nav-license" == currenttab) {
						$( "button.order-settings" ).addClass( "active" );
					} else {
						$( "button." + currenttab ).addClass( "active" );
					}
				} else {
					var classNm = "button.order-settings";
					$( classNm ).addClass( "active" );
				}
			}
			if ($( "#error-message" ).length > 0) {
				var message = $( "#error-message" ).val();
				$( ".alert-messages .container" ).append(
					'<div class="alert alert-danger fade in alert-dismissible" role="alert"><svg width="32" height="32" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:none;stroke:#721c24;stroke-linecap:round;stroke-linejoin:round;stroke-width:20px;}</style></defs><g data-name="Layer 2" id="Layer_2"><g data-name="E410, Error, Media, media player, multimedia" id="E410_Error_Media_media_player_multimedia"><circle class="cls-1" cx="256" cy="256" r="246"/><line class="cls-1" x1="371.47" x2="140.53" y1="140.53" y2="371.47"/><line class="cls-1" x1="371.47" x2="140.53" y1="371.47" y2="140.53"/></g></g></svg>' +
					message +
					'<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a></div>'
				);
			}
			if ($( "#success-message" ).length > 0) {
				var message = $( "#success-message" ).val();
				$( ".alert-messages .container" ).append(
					'<div class="alert alert-success fade in alert-dismissible" role="alert"><svg fill="#000000" height="32" width="32" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"  viewBox="0 0 52 52" xml:space="preserve"><g><path d="M26,0C11.664,0,0,11.663,0,26s11.664,26,26,26s26-11.663,26-26S40.336,0,26,0z M26,50C12.767,50,2,39.233,2,26S12.767,2,26,2s24,10.767,24,24S39.233,50,26,50z"/><path d="M38.252,15.336l-15.369,17.29l-9.259-7.407c-0.43-0.345-1.061-0.274-1.405,0.156c-0.345,0.432-0.275,1.061,0.156,1.406l10,8C22.559,34.928,22.78,35,23,35c0.276,0,0.551-0.114,0.748-0.336l16-18c0.367-0.412,0.33-1.045-0.083-1.411C39.251,14.885,38.62,14.922,38.252,15.336z"/></g></svg>' +
					message +
					'<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a></div>'
				);
			}
			if ($( "#info-message" ).length > 0) {
				var message = $( "#info-message" ).val();
				$( ".alert-messages .container" ).append(
					'<div class="alert alert-info fade in alert-dismissible"><svg fill="#000000" width="32" height="32" viewBox="0 0 1920 1920" xmlns="http://www.w3.org/2000/svg"><path d="M960 0c530.193 0 960 429.807 960 960s-429.807 960-960 960S0 1490.193 0 960 429.807 0 960 0Zm0 101.053c-474.384 0-858.947 384.563-858.947 858.947S485.616 1818.947 960 1818.947 1818.947 1434.384 1818.947 960 1434.384 101.053 960 101.053Zm-42.074 626.795c-85.075 39.632-157.432 107.975-229.844 207.898-10.327 14.249-10.744 22.907-.135 30.565 7.458 5.384 11.792 3.662 22.656-7.928 1.453-1.562 1.453-1.562 2.94-3.174 9.391-10.17 16.956-18.8 33.115-37.565 53.392-62.005 79.472-87.526 120.003-110.867 35.075-20.198 65.9 9.485 60.03 47.471-1.647 10.664-4.483 18.534-11.791 35.432-2.907 6.722-4.133 9.646-5.496 13.23-13.173 34.63-24.269 63.518-47.519 123.85l-1.112 2.886c-7.03 18.242-7.03 18.242-14.053 36.48-30.45 79.138-48.927 127.666-67.991 178.988l-1.118 3.008a10180.575 10180.575 0 0 0-10.189 27.469c-21.844 59.238-34.337 97.729-43.838 138.668-1.484 6.37-1.484 6.37-2.988 12.845-5.353 23.158-8.218 38.081-9.82 53.42-2.77 26.522-.543 48.24 7.792 66.493 9.432 20.655 29.697 35.43 52.819 38.786 38.518 5.592 75.683 5.194 107.515-2.048 17.914-4.073 35.638-9.405 53.03-15.942 50.352-18.932 98.861-48.472 145.846-87.52 41.11-34.26 80.008-76 120.788-127.872 3.555-4.492 3.555-4.492 7.098-8.976 12.318-15.707 18.352-25.908 20.605-36.683 2.45-11.698-7.439-23.554-15.343-19.587-3.907 1.96-7.993 6.018-14.22 13.872-4.454 5.715-6.875 8.77-9.298 11.514-9.671 10.95-19.883 22.157-30.947 33.998-18.241 19.513-36.775 38.608-63.656 65.789-13.69 13.844-30.908 25.947-49.42 35.046-29.63 14.559-56.358-3.792-53.148-36.635 2.118-21.681 7.37-44.096 15.224-65.767 17.156-47.367 31.183-85.659 62.216-170.048 13.459-36.6 19.27-52.41 26.528-72.201 21.518-58.652 38.696-105.868 55.04-151.425 20.19-56.275 31.596-98.224 36.877-141.543 3.987-32.673-5.103-63.922-25.834-85.405-22.986-23.816-55.68-34.787-96.399-34.305-45.053.535-97.607 15.256-145.963 37.783Zm308.381-388.422c-80.963-31.5-178.114 22.616-194.382 108.33-11.795 62.124 11.412 115.76 58.78 138.225 93.898 44.531 206.587-26.823 206.592-130.826.005-57.855-24.705-97.718-70.99-115.729Z" fill-rule="evenodd"/></svg>' +
					message +
					'<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a></div>'
				);
			}
		}
	);
	$( window ).load(
		function () {
			$( ".synctr" ).hide();
			$( ".ord_import_row" ).hide();
			$( ".wpssw_crud_ord_row" ).hide();
			$( ".cust_import_row" ).hide();
			$( ".wpssw_crud_cust_row" ).hide();
			$( "#wpssw-headers-notice" ).hide();

			if ($( "#order_settings_checkbox" ).is( ":checked" )) {
				$( ".ord_spreadsheet_row" ).show();
			} else {
				$( ".ord_spreadsheet_row" ).hide();
			}
			if ($( "#product_settings_checkbox" ).is( ":checked" )) {
				$( ".prd_spreadsheet_row" ).show();
			} else {
				$( ".prd_spreadsheet_row" ).hide();
			}
			if ($( "#customer_settings_checkbox" ).is( ":checked" )) {
				$( ".cust_spreadsheet_row" ).show();
			} else {
				$( ".cust_spreadsheet_row" ).hide();
			}
			if ($( "#coupon_settings_checkbox" ).is( ":checked" )) {
				$( ".coupon_spreadsheet_row" ).show();
			} else {
				$( ".coupon_spreadsheet_row" ).hide();
			}

			var prevSheetId = $( "#woocommerce_spreadsheet" ).val();

			if (prevSheetId != "") {
				$( ".synctr" ).show();
				$( "#wpssw-headers-notice" ).show();
			}

			var i        = 1;
			var temp     = 0;
			var tblCount = $( "#mainform > table" ).length;
			$( "#mainform table" ).each(
				function () {
					if (parseInt( tblCount ) === i) {
						$( this ).addClass( "wpssw-section-last" );
					} else {
						if (tblCount > 5 && parseInt( i ) === 3) {
							$( this ).addClass( "wpssw-section-2" );
							temp = 1;
						} else {
							$( this ).addClass( "wpssw-section-" + (i - temp) );
						}
					}
					i++;
				}
			);
			$(
				".wpssw-section-4 label input[type='checkbox'],.wpssw-section-last label input[type='checkbox'],#product_names_as_sheet"
			).after( "<span class='checkbox-switch'></span>" );
		}
	);
	$( "#licence_submit" ).on(
		"click",
		function (e) {
			e.preventDefault();
			$( ".wpssw-license-result" ).html( "" );
			$( "#licence_submit" ).hide();
			$( "#licenceloader" ).show();
			$( "#licencetext" ).show();
			wpsswLicenseCheck( "activate" );
		}
	);
	$( ".tm-deactivate-license" ).on(
		"click",
		function (e) {
			e.preventDefault();
			wpsswLicenseCheck( "deactivate" );
		}
	);
	$( document ).ready(
		function () {
			$( ".sync_all_fromtodate" ).hide();
			$( "#spreadsheet_url" ).hide();

			$( 'input[name="sync_range"]' ).on(
				"change",
				function () {
					var syncAll = $( this ).val();
					if (1 === parseInt( syncAll )) {
						$( ".sync_all_fromtodate" ).fadeOut();
						$( "#sync_all_fromdate" ).removeAttr( "required" );
						$( "#sync_all_todate" ).removeAttr( "required" );
					} else {
						$( ".sync_all_fromtodate" ).fadeIn();
						$( "#sync_all_fromdate" ).attr( "required", "required" );
						$( "#sync_all_todate" ).attr( "required", "required" );
					}
				}
			);
		}
	);
	$( document ).ready(
		function () {
			var newRequest = $( "#woocommerce_spreadsheet" ).val();
			if (newRequest != "new" && newRequest != "" && newRequest != 0) {
				var slink =
				'<a id="view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				newRequest +
				'" class="wpssw-button wpssw-tooltio-link view_spreadsheet"><svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M9.49967 10.8334C10.5413 10.8334 11.4268 10.4688 12.1559 9.73962C12.8851 9.01046 13.2497 8.12504 13.2497 7.08337C13.2497 6.04171 12.8851 5.15629 12.1559 4.42712C11.4268 3.69796 10.5413 3.33337 9.49967 3.33337C8.45801 3.33337 7.57259 3.69796 6.84342 4.42712C6.11426 5.15629 5.74967 6.04171 5.74967 7.08337C5.74967 8.12504 6.11426 9.01046 6.84342 9.73962C7.57259 10.4688 8.45801 10.8334 9.49967 10.8334ZM9.49967 9.33337C8.87467 9.33337 8.34343 9.11462 7.90593 8.67712C7.46843 8.23962 7.24967 7.70837 7.24967 7.08337C7.24967 6.45837 7.46843 5.92712 7.90593 5.48962C8.34343 5.05212 8.87467 4.83337 9.49967 4.83337C10.1247 4.83337 10.6559 5.05212 11.0934 5.48962C11.5309 5.92712 11.7497 6.45837 11.7497 7.08337C11.7497 7.70837 11.5309 8.23962 11.0934 8.67712C10.6559 9.11462 10.1247 9.33337 9.49967 9.33337ZM9.49967 13.3334C7.4719 13.3334 5.62467 12.7674 3.95801 11.6355C2.29134 10.5035 1.08301 8.98615 0.333008 7.08337C1.08301 5.1806 2.29134 3.66324 3.95801 2.53129C5.62467 1.39935 7.4719 0.833374 9.49967 0.833374C11.5275 0.833374 13.3747 1.39935 15.0413 2.53129C16.708 3.66324 17.9163 5.1806 18.6663 7.08337C17.9163 8.98615 16.708 10.5035 15.0413 11.6355C13.3747 12.7674 11.5275 13.3334 9.49967 13.3334ZM9.49967 11.6667C11.0691 11.6667 12.5101 11.2535 13.8226 10.4271C15.1351 9.60073 16.1386 8.48615 16.833 7.08337C16.1386 5.6806 15.1351 4.56601 13.8226 3.73962C12.5101 2.91323 11.0691 2.50004 9.49967 2.50004C7.93023 2.50004 6.48926 2.91323 5.17676 3.73962C3.86426 4.56601 2.86079 5.6806 2.16634 7.08337C2.86079 8.48615 3.86426 9.60073 5.17676 10.4271C6.48926 11.2535 7.93023 11.6667 9.49967 11.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">View Spreadsheet</span></a><a id="clear_spreadsheet" href="" class="wpssw-button wpssw-tooltio-link"><svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M8.66674 8.66671H10.3334V2.83337C10.3334 2.59726 10.2535 2.39935 10.0938 2.23962C9.9341 2.0799 9.73619 2.00004 9.50008 2.00004C9.26397 2.00004 9.06605 2.0799 8.90633 2.23962C8.7466 2.39935 8.66674 2.59726 8.66674 2.83337V8.66671ZM3.66674 12H15.3334V10.3334H3.66674V12ZM2.45841 17H4.50008V15.3334C4.50008 15.0973 4.57994 14.8993 4.73966 14.7396C4.89938 14.5799 5.0973 14.5 5.33341 14.5C5.56952 14.5 5.76744 14.5799 5.92716 14.7396C6.08688 14.8993 6.16674 15.0973 6.16674 15.3334V17H8.66674V15.3334C8.66674 15.0973 8.7466 14.8993 8.90633 14.7396C9.06605 14.5799 9.26397 14.5 9.50008 14.5C9.73619 14.5 9.9341 14.5799 10.0938 14.7396C10.2535 14.8993 10.3334 15.0973 10.3334 15.3334V17H12.8334V15.3334C12.8334 15.0973 12.9133 14.8993 13.073 14.7396C13.2327 14.5799 13.4306 14.5 13.6667 14.5C13.9029 14.5 14.1008 14.5799 14.2605 14.7396C14.4202 14.8993 14.5001 15.0973 14.5001 15.3334V17H16.5417L15.7084 13.6667H3.29174L2.45841 17ZM16.5417 18.6667H2.45841C1.91674 18.6667 1.47924 18.4514 1.14591 18.0209C0.812577 17.5903 0.715354 17.1112 0.854243 16.5834L2.00008 12V10.3334C2.00008 9.87504 2.16327 9.48268 2.48966 9.15629C2.81605 8.8299 3.20841 8.66671 3.66674 8.66671H7.00008V2.83337C7.00008 2.13893 7.24313 1.54865 7.72924 1.06254C8.21535 0.57643 8.80563 0.333374 9.50008 0.333374C10.1945 0.333374 10.7848 0.57643 11.2709 1.06254C11.757 1.54865 12.0001 2.13893 12.0001 2.83337V8.66671H15.3334C15.7917 8.66671 16.1841 8.8299 16.5105 9.15629C16.8369 9.48268 17.0001 9.87504 17.0001 10.3334V12L18.1459 16.5834C18.3265 17.1112 18.2466 17.5903 17.9063 18.0209C17.566 18.4514 17.1112 18.6667 16.5417 18.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">Clear Spreadsheet</span> </a><img src="" id="clearloader"><a id="down_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				newRequest +
				'/export?format=xlsx" class="wpssw-button wpssw-tooltio-link down_spreadsheet"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="#383E46" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3.33c2.98 1.72 5 4.96 5 8.66 0 5.52-4.48 10-10 10 -5.53 0-10-4.48-10-10 0-3.71 2.01-6.94 5-8.67m1 8.66l4 4m0 0l4-4m-4 4v-14"/></svg><span class="tooltip-text">Download Spreadsheet</span></a> ';
				$( "#woocommerce_spreadsheet" ).after( slink );
			}
			var product_spreadsheet = $( "#product_spreadsheet" ).val();
			if (
			product_spreadsheet != "new" &&
			product_spreadsheet != "" &&
			product_spreadsheet != 0
			) {
				var slink =
				'<a id="prd_view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				product_spreadsheet +
				'" class="wpssw-button wpssw-tooltio-link view_spreadsheet"><svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M9.49967 10.8334C10.5413 10.8334 11.4268 10.4688 12.1559 9.73962C12.8851 9.01046 13.2497 8.12504 13.2497 7.08337C13.2497 6.04171 12.8851 5.15629 12.1559 4.42712C11.4268 3.69796 10.5413 3.33337 9.49967 3.33337C8.45801 3.33337 7.57259 3.69796 6.84342 4.42712C6.11426 5.15629 5.74967 6.04171 5.74967 7.08337C5.74967 8.12504 6.11426 9.01046 6.84342 9.73962C7.57259 10.4688 8.45801 10.8334 9.49967 10.8334ZM9.49967 9.33337C8.87467 9.33337 8.34343 9.11462 7.90593 8.67712C7.46843 8.23962 7.24967 7.70837 7.24967 7.08337C7.24967 6.45837 7.46843 5.92712 7.90593 5.48962C8.34343 5.05212 8.87467 4.83337 9.49967 4.83337C10.1247 4.83337 10.6559 5.05212 11.0934 5.48962C11.5309 5.92712 11.7497 6.45837 11.7497 7.08337C11.7497 7.70837 11.5309 8.23962 11.0934 8.67712C10.6559 9.11462 10.1247 9.33337 9.49967 9.33337ZM9.49967 13.3334C7.4719 13.3334 5.62467 12.7674 3.95801 11.6355C2.29134 10.5035 1.08301 8.98615 0.333008 7.08337C1.08301 5.1806 2.29134 3.66324 3.95801 2.53129C5.62467 1.39935 7.4719 0.833374 9.49967 0.833374C11.5275 0.833374 13.3747 1.39935 15.0413 2.53129C16.708 3.66324 17.9163 5.1806 18.6663 7.08337C17.9163 8.98615 16.708 10.5035 15.0413 11.6355C13.3747 12.7674 11.5275 13.3334 9.49967 13.3334ZM9.49967 11.6667C11.0691 11.6667 12.5101 11.2535 13.8226 10.4271C15.1351 9.60073 16.1386 8.48615 16.833 7.08337C16.1386 5.6806 15.1351 4.56601 13.8226 3.73962C12.5101 2.91323 11.0691 2.50004 9.49967 2.50004C7.93023 2.50004 6.48926 2.91323 5.17676 3.73962C3.86426 4.56601 2.86079 5.6806 2.16634 7.08337C2.86079 8.48615 3.86426 9.60073 5.17676 10.4271C6.48926 11.2535 7.93023 11.6667 9.49967 11.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">View Spreadsheet</span> </a><a id="clear_productsheet" href="" class="wpssw-button wpssw-tooltio-link"><svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M8.66674 8.66671H10.3334V2.83337C10.3334 2.59726 10.2535 2.39935 10.0938 2.23962C9.9341 2.0799 9.73619 2.00004 9.50008 2.00004C9.26397 2.00004 9.06605 2.0799 8.90633 2.23962C8.7466 2.39935 8.66674 2.59726 8.66674 2.83337V8.66671ZM3.66674 12H15.3334V10.3334H3.66674V12ZM2.45841 17H4.50008V15.3334C4.50008 15.0973 4.57994 14.8993 4.73966 14.7396C4.89938 14.5799 5.0973 14.5 5.33341 14.5C5.56952 14.5 5.76744 14.5799 5.92716 14.7396C6.08688 14.8993 6.16674 15.0973 6.16674 15.3334V17H8.66674V15.3334C8.66674 15.0973 8.7466 14.8993 8.90633 14.7396C9.06605 14.5799 9.26397 14.5 9.50008 14.5C9.73619 14.5 9.9341 14.5799 10.0938 14.7396C10.2535 14.8993 10.3334 15.0973 10.3334 15.3334V17H12.8334V15.3334C12.8334 15.0973 12.9133 14.8993 13.073 14.7396C13.2327 14.5799 13.4306 14.5 13.6667 14.5C13.9029 14.5 14.1008 14.5799 14.2605 14.7396C14.4202 14.8993 14.5001 15.0973 14.5001 15.3334V17H16.5417L15.7084 13.6667H3.29174L2.45841 17ZM16.5417 18.6667H2.45841C1.91674 18.6667 1.47924 18.4514 1.14591 18.0209C0.812577 17.5903 0.715354 17.1112 0.854243 16.5834L2.00008 12V10.3334C2.00008 9.87504 2.16327 9.48268 2.48966 9.15629C2.81605 8.8299 3.20841 8.66671 3.66674 8.66671H7.00008V2.83337C7.00008 2.13893 7.24313 1.54865 7.72924 1.06254C8.21535 0.57643 8.80563 0.333374 9.50008 0.333374C10.1945 0.333374 10.7848 0.57643 11.2709 1.06254C11.757 1.54865 12.0001 2.13893 12.0001 2.83337V8.66671H15.3334C15.7917 8.66671 16.1841 8.8299 16.5105 9.15629C16.8369 9.48268 17.0001 9.87504 17.0001 10.3334V12L18.1459 16.5834C18.3265 17.1112 18.2466 17.5903 17.9063 18.0209C17.566 18.4514 17.1112 18.6667 16.5417 18.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">Clear Spreadsheet</span></a>   <img src="" id="clearprdloader"><a id="prd_down_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				product_spreadsheet +
				'/export?format=xlsx" class="wpssw-button wpssw-tooltio-link down_spreadsheet"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="#383E46" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3.33c2.98 1.72 5 4.96 5 8.66 0 5.52-4.48 10-10 10 -5.53 0-10-4.48-10-10 0-3.71 2.01-6.94 5-8.67m1 8.66l4 4m0 0l4-4m-4 4v-14"/></svg><span class="tooltip-text">Download Spreadsheet</span></a> ';
				$( "#product_spreadsheet" ).after( slink );
			}
			var customer_spreadsheet = $( "#customer_spreadsheet" ).val();
			if (
			customer_spreadsheet != "new" &&
			customer_spreadsheet != "" &&
			customer_spreadsheet != 0
			) {
				var slink =
				'<a id="cust_view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				customer_spreadsheet +
				'" class="wpssw-button wpssw-tooltio-link view_spreadsheet"><svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M9.49967 10.8334C10.5413 10.8334 11.4268 10.4688 12.1559 9.73962C12.8851 9.01046 13.2497 8.12504 13.2497 7.08337C13.2497 6.04171 12.8851 5.15629 12.1559 4.42712C11.4268 3.69796 10.5413 3.33337 9.49967 3.33337C8.45801 3.33337 7.57259 3.69796 6.84342 4.42712C6.11426 5.15629 5.74967 6.04171 5.74967 7.08337C5.74967 8.12504 6.11426 9.01046 6.84342 9.73962C7.57259 10.4688 8.45801 10.8334 9.49967 10.8334ZM9.49967 9.33337C8.87467 9.33337 8.34343 9.11462 7.90593 8.67712C7.46843 8.23962 7.24967 7.70837 7.24967 7.08337C7.24967 6.45837 7.46843 5.92712 7.90593 5.48962C8.34343 5.05212 8.87467 4.83337 9.49967 4.83337C10.1247 4.83337 10.6559 5.05212 11.0934 5.48962C11.5309 5.92712 11.7497 6.45837 11.7497 7.08337C11.7497 7.70837 11.5309 8.23962 11.0934 8.67712C10.6559 9.11462 10.1247 9.33337 9.49967 9.33337ZM9.49967 13.3334C7.4719 13.3334 5.62467 12.7674 3.95801 11.6355C2.29134 10.5035 1.08301 8.98615 0.333008 7.08337C1.08301 5.1806 2.29134 3.66324 3.95801 2.53129C5.62467 1.39935 7.4719 0.833374 9.49967 0.833374C11.5275 0.833374 13.3747 1.39935 15.0413 2.53129C16.708 3.66324 17.9163 5.1806 18.6663 7.08337C17.9163 8.98615 16.708 10.5035 15.0413 11.6355C13.3747 12.7674 11.5275 13.3334 9.49967 13.3334ZM9.49967 11.6667C11.0691 11.6667 12.5101 11.2535 13.8226 10.4271C15.1351 9.60073 16.1386 8.48615 16.833 7.08337C16.1386 5.6806 15.1351 4.56601 13.8226 3.73962C12.5101 2.91323 11.0691 2.50004 9.49967 2.50004C7.93023 2.50004 6.48926 2.91323 5.17676 3.73962C3.86426 4.56601 2.86079 5.6806 2.16634 7.08337C2.86079 8.48615 3.86426 9.60073 5.17676 10.4271C6.48926 11.2535 7.93023 11.6667 9.49967 11.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">View Spreadsheet</span></a> <a id="clear_customersheet" href="" class="wpssw-button  wpssw-tooltio-link"><svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M8.66674 8.66671H10.3334V2.83337C10.3334 2.59726 10.2535 2.39935 10.0938 2.23962C9.9341 2.0799 9.73619 2.00004 9.50008 2.00004C9.26397 2.00004 9.06605 2.0799 8.90633 2.23962C8.7466 2.39935 8.66674 2.59726 8.66674 2.83337V8.66671ZM3.66674 12H15.3334V10.3334H3.66674V12ZM2.45841 17H4.50008V15.3334C4.50008 15.0973 4.57994 14.8993 4.73966 14.7396C4.89938 14.5799 5.0973 14.5 5.33341 14.5C5.56952 14.5 5.76744 14.5799 5.92716 14.7396C6.08688 14.8993 6.16674 15.0973 6.16674 15.3334V17H8.66674V15.3334C8.66674 15.0973 8.7466 14.8993 8.90633 14.7396C9.06605 14.5799 9.26397 14.5 9.50008 14.5C9.73619 14.5 9.9341 14.5799 10.0938 14.7396C10.2535 14.8993 10.3334 15.0973 10.3334 15.3334V17H12.8334V15.3334C12.8334 15.0973 12.9133 14.8993 13.073 14.7396C13.2327 14.5799 13.4306 14.5 13.6667 14.5C13.9029 14.5 14.1008 14.5799 14.2605 14.7396C14.4202 14.8993 14.5001 15.0973 14.5001 15.3334V17H16.5417L15.7084 13.6667H3.29174L2.45841 17ZM16.5417 18.6667H2.45841C1.91674 18.6667 1.47924 18.4514 1.14591 18.0209C0.812577 17.5903 0.715354 17.1112 0.854243 16.5834L2.00008 12V10.3334C2.00008 9.87504 2.16327 9.48268 2.48966 9.15629C2.81605 8.8299 3.20841 8.66671 3.66674 8.66671H7.00008V2.83337C7.00008 2.13893 7.24313 1.54865 7.72924 1.06254C8.21535 0.57643 8.80563 0.333374 9.50008 0.333374C10.1945 0.333374 10.7848 0.57643 11.2709 1.06254C11.757 1.54865 12.0001 2.13893 12.0001 2.83337V8.66671H15.3334C15.7917 8.66671 16.1841 8.8299 16.5105 9.15629C16.8369 9.48268 17.0001 9.87504 17.0001 10.3334V12L18.1459 16.5834C18.3265 17.1112 18.2466 17.5903 17.9063 18.0209C17.566 18.4514 17.1112 18.6667 16.5417 18.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">Clear Spreadsheet</span></a>   <img src="" id="clearcustloader"><a id="cust_down_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				customer_spreadsheet +
				'/export?format=xlsx" class="wpssw-button wpssw-tooltio-link down_spreadsheet"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="#383E46" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3.33c2.98 1.72 5 4.96 5 8.66 0 5.52-4.48 10-10 10 -5.53 0-10-4.48-10-10 0-3.71 2.01-6.94 5-8.67m1 8.66l4 4m0 0l4-4m-4 4v-14"/></svg><span class="tooltip-text">Download Spreadsheet</span></a> ';
				$( "#customer_spreadsheet" ).after( slink );
			}
			var coupon_spreadsheet = $( "#coupon_spreadsheet" ).val();
			if (
			coupon_spreadsheet != "new" &&
			coupon_spreadsheet != "" &&
			coupon_spreadsheet != 0
			) {
				var slink =
				'<a id="coupon_view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				coupon_spreadsheet +
				'" class="wpssw-button wpssw-tooltio-link view_spreadsheet"><svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M9.49967 10.8334C10.5413 10.8334 11.4268 10.4688 12.1559 9.73962C12.8851 9.01046 13.2497 8.12504 13.2497 7.08337C13.2497 6.04171 12.8851 5.15629 12.1559 4.42712C11.4268 3.69796 10.5413 3.33337 9.49967 3.33337C8.45801 3.33337 7.57259 3.69796 6.84342 4.42712C6.11426 5.15629 5.74967 6.04171 5.74967 7.08337C5.74967 8.12504 6.11426 9.01046 6.84342 9.73962C7.57259 10.4688 8.45801 10.8334 9.49967 10.8334ZM9.49967 9.33337C8.87467 9.33337 8.34343 9.11462 7.90593 8.67712C7.46843 8.23962 7.24967 7.70837 7.24967 7.08337C7.24967 6.45837 7.46843 5.92712 7.90593 5.48962C8.34343 5.05212 8.87467 4.83337 9.49967 4.83337C10.1247 4.83337 10.6559 5.05212 11.0934 5.48962C11.5309 5.92712 11.7497 6.45837 11.7497 7.08337C11.7497 7.70837 11.5309 8.23962 11.0934 8.67712C10.6559 9.11462 10.1247 9.33337 9.49967 9.33337ZM9.49967 13.3334C7.4719 13.3334 5.62467 12.7674 3.95801 11.6355C2.29134 10.5035 1.08301 8.98615 0.333008 7.08337C1.08301 5.1806 2.29134 3.66324 3.95801 2.53129C5.62467 1.39935 7.4719 0.833374 9.49967 0.833374C11.5275 0.833374 13.3747 1.39935 15.0413 2.53129C16.708 3.66324 17.9163 5.1806 18.6663 7.08337C17.9163 8.98615 16.708 10.5035 15.0413 11.6355C13.3747 12.7674 11.5275 13.3334 9.49967 13.3334ZM9.49967 11.6667C11.0691 11.6667 12.5101 11.2535 13.8226 10.4271C15.1351 9.60073 16.1386 8.48615 16.833 7.08337C16.1386 5.6806 15.1351 4.56601 13.8226 3.73962C12.5101 2.91323 11.0691 2.50004 9.49967 2.50004C7.93023 2.50004 6.48926 2.91323 5.17676 3.73962C3.86426 4.56601 2.86079 5.6806 2.16634 7.08337C2.86079 8.48615 3.86426 9.60073 5.17676 10.4271C6.48926 11.2535 7.93023 11.6667 9.49967 11.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">View Spreadsheet</span></a> <a id="clear_couponsheet" href="" class="wpssw-button wpssw-tooltio-link"><svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M8.66674 8.66671H10.3334V2.83337C10.3334 2.59726 10.2535 2.39935 10.0938 2.23962C9.9341 2.0799 9.73619 2.00004 9.50008 2.00004C9.26397 2.00004 9.06605 2.0799 8.90633 2.23962C8.7466 2.39935 8.66674 2.59726 8.66674 2.83337V8.66671ZM3.66674 12H15.3334V10.3334H3.66674V12ZM2.45841 17H4.50008V15.3334C4.50008 15.0973 4.57994 14.8993 4.73966 14.7396C4.89938 14.5799 5.0973 14.5 5.33341 14.5C5.56952 14.5 5.76744 14.5799 5.92716 14.7396C6.08688 14.8993 6.16674 15.0973 6.16674 15.3334V17H8.66674V15.3334C8.66674 15.0973 8.7466 14.8993 8.90633 14.7396C9.06605 14.5799 9.26397 14.5 9.50008 14.5C9.73619 14.5 9.9341 14.5799 10.0938 14.7396C10.2535 14.8993 10.3334 15.0973 10.3334 15.3334V17H12.8334V15.3334C12.8334 15.0973 12.9133 14.8993 13.073 14.7396C13.2327 14.5799 13.4306 14.5 13.6667 14.5C13.9029 14.5 14.1008 14.5799 14.2605 14.7396C14.4202 14.8993 14.5001 15.0973 14.5001 15.3334V17H16.5417L15.7084 13.6667H3.29174L2.45841 17ZM16.5417 18.6667H2.45841C1.91674 18.6667 1.47924 18.4514 1.14591 18.0209C0.812577 17.5903 0.715354 17.1112 0.854243 16.5834L2.00008 12V10.3334C2.00008 9.87504 2.16327 9.48268 2.48966 9.15629C2.81605 8.8299 3.20841 8.66671 3.66674 8.66671H7.00008V2.83337C7.00008 2.13893 7.24313 1.54865 7.72924 1.06254C8.21535 0.57643 8.80563 0.333374 9.50008 0.333374C10.1945 0.333374 10.7848 0.57643 11.2709 1.06254C11.757 1.54865 12.0001 2.13893 12.0001 2.83337V8.66671H15.3334C15.7917 8.66671 16.1841 8.8299 16.5105 9.15629C16.8369 9.48268 17.0001 9.87504 17.0001 10.3334V12L18.1459 16.5834C18.3265 17.1112 18.2466 17.5903 17.9063 18.0209C17.566 18.4514 17.1112 18.6667 16.5417 18.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">Clear Spreadsheet</span></a>   <img src="" id="clearcouponloader"><a id="coupon_down_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				coupon_spreadsheet +
				'/export?format=xlsx" class="wpssw-button wpssw-tooltio-link down_spreadsheet"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="#383E46" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3.33c2.98 1.72 5 4.96 5 8.66 0 5.52-4.48 10-10 10 -5.53 0-10-4.48-10-10 0-3.71 2.01-6.94 5-8.67m1 8.66l4 4m0 0l4-4m-4 4v-14"/></svg><span class="tooltip-text">Download Spreadsheet</span></a> ';
				$( "#coupon_spreadsheet" ).after( slink );
			}
		}
	);
})( jQuery );



function wpsslwTab(evt, tabName) {
	"use strict";
	var i, tabContent, tabLinks;
	tabContent           = document.getElementsByClassName( "tabcontent" );
	var tabContentlength = tabContent.length;
	for (i = 0; i < tabContentlength; i++) {
		tabContent[i].style.display = "none";
	}
	tabLinks      = document.getElementsByClassName( "tablinks" );
	var tablength = tabLinks.length;
	for (i = 0; i < tablength; i++) {
		tabLinks[i].className = tabLinks[i].className.replace( " active", "" );
	}
	document.getElementById( tabName ).style.display = "block";
	var type = typeof event;
	if (type !== "undefined") {
		evt.currentTarget.className += " active";
	}
}
function wpsslwNavTab(evt, tabName) {
	"use strict";
	var i, tabContent, tabLinks;
	tabContent           = document.getElementsByClassName( "navtabcontent" );
	var tabContentlength = tabContent.length;
	for (i = 0; i < tabContentlength; i++) {
		tabContent[i].style.display = "none";
	}
	tabLinks      = document.getElementsByClassName( "navtablinks" );
	var tablength = tabLinks.length;
	for (i = 0; i < tablength; i++) {
		tabLinks[i].className = tabLinks[i].className.replace( " active", "" );
	}
	document.getElementById( tabName ).style.display = "block";
	var type = typeof event;
	if (type !== "undefined") {
		evt.currentTarget.className += " active";
	}
}
function getParameterByName(name, url) {
	"use strict";
	if ( ! url) {
		url = window.location.href;
	}
	name      = name.replace( /[\[\]]/g, "\\jQuery&" );
	var regex = new RegExp( "[?&]" + name + "(=([^&#]*)|&|#|jQuery)" ),
	results   = regex.exec( url );
	if ( ! results) {
		return null;
	}
	if ( ! results[2]) {
		return "";
	}
	return decodeURIComponent( results[2].replace( /\+/g, " " ) );
}
function wpsswLicenseCheck(action) {
	"use strict";
	if (String( jQuery( "#ws_envato" ).val() ) === "") {
		jQuery( ".wpssw-license-result" ).html(
			'<div class="error"><p>Please enter Envato API Token</p></div>'
		);
		jQuery( "#licenceloader" ).hide();
		jQuery( "#licencetext" ).hide();
		jQuery( "#licence_submit" ).show();
		return false;
	}
	var data = {
		action: "wpssw_" + action + "_license",
		username: jQuery( "#ws_username" ).val(),
		key: jQuery( "#ws_purchase" ).val(),
		api_key: jQuery( "#ws_envato" ).val(),
		agree_transmit: jQuery( "#agree_transmit:checked" ).val(),
		wpnonce: jQuery( "#_wpnonce" ).val(),
	};
	jQuery
	.post(
		admin_ajax_object.ajaxurl,
		data,
		function (response) {
			var html;
			if ( ! response || parseInt( response ) === -1) {
				html =
				'<div class="error"><p>Please enter valid Envato API Token</p></div>';
			} else if (
			response &&
			response.message &&
			response.result &&
			(String( response.result ) === "-3" ||
			String( response.result ) === "-2" ||
			String( response.result ) === "wp_error" ||
			String( response.result ) === "server_error")
			) {
				html = response.message;
			} else if (
			response &&
			response.message &&
			response.result &&
			String( response.result ) === "4"
			) {
				html = response.message;
			} else {
				html = "";
			}
			jQuery( ".wpssw-license-result" ).html( html );
			jQuery( "#licenceloader" ).hide();
			jQuery( "#licencetext" ).hide();
			jQuery( "#licence_submit" ).show();
		},
		"json"
	)
	.always( function (response) {} );
}
function wpsswCopy(id, targetid) {
	var copyText   = document.getElementById( id );
	var textArea   = document.createElement( "textarea" );
	textArea.value = copyText.textContent;
	document.body.appendChild( textArea );
	textArea.select();
	document.execCommand( "Copy" );
	textArea.remove();
	if ( ! jQuery( "#" + targetid ).hasClass( "tooltip-click" )) {
		jQuery( "#" + targetid ).addClass( "tooltip-click" );
		setTimeout(
			function () {
				jQuery( "#" + targetid ).removeClass( "tooltip-click" );
			},
			1000
		);
	}
}
