<?php
/**
 * Manages the data for report orders during checkout.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display the name and birth year for reports at checkout.
 */
function zpsr_display_name_at_checkout( $qty, $cart_item, $cart_item_key ) {
    if ( empty( $cart_item['zp_form_data'] ) ) {
        return $qty;
    }
 	parse_str( $cart_item['zp_form_data'], $data );
    return sprintf( '%1$s <p><strong>%2$s</strong> %3$s<br /><strong>%4$s</strong> %5$s</p>', $qty, __( 'For:', 'zp-sell-reports' ), sanitize_text_field( $data['name'] ), __( 'Birth year:', 'zp-sell-reports' ), sanitize_text_field( $data['year'] ) );
}
add_filter( 'woocommerce_checkout_cart_item_quantity', 'zpsr_display_name_at_checkout', 10, 3 );

/**
 * Officially save ZP form data to the order as meta.
 * @param WC_Order_Item_Product $item
 * @param string                $cart_item_key
 * @param array                 $values
 * @param WC_Order              $order
 */
function zpsr_add_form_data_to_order_item( $item, $cart_item_key, $values, $order ) {
    if ( empty( $values['zp_form_data'] ) ) {
        return;
    }

    $data_string = sanitize_text_field( $values['zp_form_data'] );
  	parse_str( $data_string, $data );

	// In case they arrived to checkout via a free preview, remove _preview from the report-variation
	$data['zp-report-variation'] = str_replace( '_preview', '', $data['zp-report-variation'] );
	$data_string = http_build_query( $data );
   
  	// public order data visible to customer
    $item->add_meta_data( __( 'Name', 'zp-sell-reports' ), $data['name'] );
	$item->add_meta_data( __( 'Birth year', 'zp-sell-reports' ), $data['year'] );

	// private admin data
	$month = zp_get_i18n_months( $data['month'] );
	$born_str = $data['day'] . ' ' . $month . ' ' . $data['year'];
	if ( empty( $data['unknown_time'] ) ) {
		$born_str .= ', ' . $data['hour'] . ':' . $data['minute'];
	}
	$item->add_meta_data( '_Born', $born_str );
	$item->add_meta_data( '_Place', $data['place'] );
	$item->add_meta_data( '_zp_form_data', $data_string );

}
add_action( 'woocommerce_checkout_create_order_line_item', 'zpsr_add_form_data_to_order_item', 10, 4 );
