<?php
/**
 * Functions related to the report product.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Checks if a product is a ZP report product.
 * @param int $product_id The product ID to check
 * @return bool True if the product is a ZP report, otherwise false.
 */
function zpsr_is_report_product( $product_id = 0 ) {
	$product = get_post( $product_id );
	if ( ! empty( $product->post_excerpt ) ) {
		if ( has_shortcode( $product->post_excerpt, 'birthreport' ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Find the product id for the "Birth Report" product
 * @return int $product_id the product id if one exists with this type of birth report, otherwise 0
 */
function zpsr_find_birthreport_product_id() {
	$product_id = 0;
	$loop = get_posts( array( 'post_type' => array('product'), 'posts_per_page' => -1 ) );

	if ( $loop ) {

		foreach ( $loop as $product ) {
			
			if ( zpsr_is_report_product( $product->ID ) ) {

				// make sure this shortcode is for the default "Birth Report" not another variation/type of birthreport.
				$atts = zpsr_parse_shortcode_atts( $product->post_excerpt );// get array of shortcode atts
			    // if no special report is set, then this is our desired birth report
			    if ( is_array( $atts ) && empty( $atts['report'] ) ) {
					$product_id = $product->ID;
					break;
			    }
			}
		}
	}

    return $product_id;
}

/**
 * Remove the ZP form title for WC Report products
 * @todo Can be removed after ZP blocks for Gutenberg editor remove the need for shortcode.
 */
function zpsr_remove_form_title() {
	add_filter( 'zp_shortcode_default_form_title', function ( $title, $atts ) {
		if ( isset( $atts['sell'] ) && 'woocommerce' === $atts['sell'] ) {
			$title = '';
		}
		return $title;
	}, 10, 2 );
}
add_action( 'plugins_loaded', 'zpsr_remove_form_title' );
