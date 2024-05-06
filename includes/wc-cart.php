<?php
/**
 * Manages the data for report orders while in the cart.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save the form data session
 * @param the validated form data string which will be saved
 */
function zpsr_save_form_data_to_session( $form_data ) {
	WC()->session->set( 'zp_form_data', $form_data );
	// also save to PHP session as a backup
	if ( session_status() === PHP_SESSION_NONE ) {
		session_start();
	}
	$_SESSION['zpsr_form_data'] = $form_data;
}

/**
 * Attach the ZP form data to the cart item data
 */
function zpsr_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
	if ( zpsr_is_report_product( $product_id ) ) {
		$form_data = WC()->session->get( 'zp_form_data' );
		if ( empty( $form_data ) ) {
			// As a backup, get our data from regular PHP session
			if ( session_status() === PHP_SESSION_NONE ) {
				session_start();
			}
			$form_data = isset( $_SESSION['zpsr_form_data'] ) ? $_SESSION['zpsr_form_data'] : '';
		}

		if ( $form_data ) {
			$cart_item_data['zp_form_data'] = $form_data;
			// delete both of our session items
			WC()->session->set( 'zp_form_data', null );
			if ( isset( $_SESSION['zpsr_form_data'] ) ) {
				unset( $_SESSION['zpsr_form_data'] );
			}
		}
	}
	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'zpsr_add_cart_item_data', 10, 3 );

/**
 * Display the name and birth year for reports in the cart.
 */
function zpsr_display_name_in_cart( $product_name, $cart_item, $cart_item_key ) {
	if ( is_checkout() || empty( $cart_item['zp_form_data'] ) ) {
		return $product_name;
	}
	parse_str( $cart_item['zp_form_data'], $data );
	return sprintf( '%1$s <p><strong>%2$s</strong> %3$s<br /><strong>%4$s</strong> %5$s</p>', $product_name, __( 'For:', 'zp-sell-reports' ), sanitize_text_field( $data['name'] ), __( 'Birth year:', 'zp-sell-reports' ), sanitize_text_field( $data['year'] ) );
}
add_filter( 'woocommerce_cart_item_name', 'zpsr_display_name_in_cart', 10, 3 );

/**
 * Check reports in the cart to make sure they have form data.
 */
add_action( 'woocommerce_check_cart_items', function () {
	// make sure each report has the form data
	foreach ( WC()->cart->get_cart() as $key => $item ) {
		if ( zpsr_is_report_product( $item['product_id'] ) ) {
			if ( empty( $item['zp_form_data'] ) ) {
				WC()->cart->remove_cart_item( $key );// Delete item from cart
				wc_add_notice( __( 'Sorry, please try again. The form you entered for your report order was not saved.', 'zp-sell-reports' ), 'error' );
			}
		}
	}
} );
