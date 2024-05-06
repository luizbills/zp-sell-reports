<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Register/load our JavaScript and CSS.
 */
function zpsr_scripts() {
	$dir = plugin_dir_url( __DIR__ ) . 'assets/';
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	// ZP-Sell-Reports form script
	$form_js = 'form';
	$strings = zp_script_localization_data();
	$deps = array( 'jquery' );

	/*
	 * Legacy script
	 * Ensures back-compat with ZP < 1.8
	 *
	 * @todo To be removed in a future version.
	 */
	if ( version_compare( ZODIACPRESS_VERSION, '1.8', '<' ) ) {
		$form_js = 'zp-sell-reports-form';
		$strings = zp_get_script_localization_data();
		$deps[] = 'jquery-ui-autocomplete';
	}

	wp_register_script( 'zp-sell-reports-form', $dir . $form_js . $suffix . '.js', $deps, ZP_SELL_REPORTS_VERSION );
	wp_localize_script( 'zp-sell-reports-form', 'zp_ajax_object', $strings );

	// ZP-Sell-Reports form styles
	wp_register_style( 'zp-sell-reports-form', $dir . 'zp-sell-reports-form.css', array(), ZP_SELL_REPORTS_VERSION );

	// Add ZP core styles to WC customer account pages and receipt page.
	if ( is_account_page() || is_checkout() ) {
		wp_enqueue_style( 'zp' );
		if ( is_rtl() ) {
			wp_enqueue_style( 'zp-rtl' );
		}

		// Add style for View Report button.
		wp_add_inline_style( 'zp', '.woocommerce-order-details .zpsr-fetch-report {
				background: #26264c;
				border: 0;
				border-radius: 2px;
				color: #fff;
				font-weight: 700;
				letter-spacing: 0.046875em;
				line-height: 1;
				padding: 0.84375em 0.875em 0.78125em;
				text-transform: uppercase;
				box-sizing: border-box;
				box-shadow: none;
			}

			.woocommerce-order-details .zpsr-fetch-report:hover,
			.woocommerce-order-details .zpsr-fetch-report:focus {
				color: #fff;
				background-color: #333366;
				box-shadow: none;
			}'
		);
	}

	// Add style for Free Preview Buy Now button
	wp_add_inline_style( 'zp', '.zpsr-buy-now {
				background-color:#FFFF00;
			}'
	);

	/* Add JS to frontend wherever woocommerce.js is loaded.
	* This fetches the report when View Report link is clicked in customer's account area,
	* and hides Add to cart buttons for reports on shop and category pages. */

	wp_add_inline_script( 'woocommerce', '( function( $ ) {
		// Get the data from each report link in this order

		$( ".zpsr-fetch-report" ).each( function( i, obj ) {
			var ReportUrl = $( this ).attr( "href" );
			if ( ReportUrl ) {
				var reportTitle = $( this ).closest( ".product-name" ).find( "a:first" ).text();
				var qString = ReportUrl.split( "?" )[1];

				if ( qString ) {
					var queries = {};
					jQuery.each( qString.split( "&" ),function( c, q ){
						var i = q.split( "=" );
						queries[i[0].toString()] = i[1].toString();
					});

		    		if ( "zpsr-data" in queries ) {

						// Do Ajax to fetch ZP report when link is clicked

						$( this ).click( function() {
							$.ajax( {
								url: "' . admin_url( 'admin-ajax.php' ) . '",
								type: "POST",
								data: {
									form_data: queries["zpsr-data"],
									action: "zpsr_fetch_report",
								},
								dataType: "json",
								success: function( reportData ) {
									if ( reportData.error ) {
										$( ".ui-state-error" ).hide();
										var span = $( "<span />" );
										span.attr( "class", "ui-state-error" );
										span.text( reportData.error );
										$( ".zpsr-fetch-report" ).before( span );

									} else {

										// if neither null, blank, nor false

										if ( $.trim( reportData.report ) && "false" != $.trim(reportData.report ) ) {
												
											$( ".ui-state-error" ).hide();

											// Remove WC stuff
											$( ".woocommerce-table--order-details" ).hide();
											$( ".woocommerce-thankyou-order-details" ).hide();
											$( ".woocommerce-customer-details" ).hide();

											// Go back button

											var backButtonWrap = $( "<p />" );
											backButtonWrap.attr( "id", "zpsr-go-back" );
											var backButton = $( "<a />" );
											backButton.attr( "href", window.location.href );
											backButton.text( "' . apply_filters( 'zpsr_back_button_text', __( 'Go back', 'zp-sell-reports' ) ) . '" );
											backButtonWrap.append( backButton );
											$( ".woocommerce-order-details__title" ).before( backButtonWrap );

											// Display report.

											$( ".woocommerce-order-details__title" ).text( reportTitle );
											$( ".woocommerce-order-details__title" ).after( reportData.report );

											// Insert the chart image.

											switch ("' . $strings['draw'] . '") {
												case "top":
													// Show image at top
													if ( $( ".zp-report-header" ).length ) {
														$( ".zp-report-header" ).after( reportData.image );
													} else {
														$( ".woocommerce-order-details__title" ).after( reportData.image );
													}
												break;
												case "bottom":
													// show image at end of report
													$( ".woocommerce-order-details" ).append( reportData.image );
												break;
											}
											
											// Scroll to top of report

											var distance = $( ".woocommerce-order-details" ).offset().top - 150;
											$( "html,body" ).animate( {
												scrollTop: distance
											}, "slow" );
										}
								
									}
								}
							} );
						return false;
						} ); // end click function

					}

				}
			}

		} );

		/*
		 * Remove add to cart button for ZP Reports on all product archive pages, main shop page,
		 * and from "Related products" on single product page
		 */

		$( ".add_to_cart_button" ).each( function( index, value ) {
			var zpsr_productID = $( this ).attr( "data-product_id" );

	    	$.ajax( {
				type: "GET",
				url: "' . esc_url_raw( rest_url() ) . '" + "wp/v2/product/" + zpsr_productID,
				success: function( response ) {
					var excerpt = response.excerpt.rendered;

					if ( excerpt.indexOf( "[birthreport" ) >= 0 ) {
						$( ".add_to_cart_button[data-product_id=\'" + zpsr_productID + "\']" ).remove();
					}
				}
			} );

		} );

	} )( jQuery );' );	
}
add_action( 'wp_enqueue_scripts', 'zpsr_scripts' );

/**
 * Load the ZP-Sell-Reports js instead of the core ZP js, and load the zpsr CSS.
 */
function zpsr_swap_scripts( $report_atts ) {
	if ( isset( $report_atts['sell'] ) && 'woocommerce' === $report_atts['sell'] ) {

		// Swap the core ZP js with custom ZP-Sell-Reports form js.
		wp_dequeue_script( 'zp' );
		wp_enqueue_script( 'zp-sell-reports-form' );

		// Add the ZP-Sell-Reports form styles
		wp_enqueue_style( 'zp-sell-reports-form' );
	}
}
add_action( 'zp_report_shortcode_before', 'zpsr_swap_scripts' );
