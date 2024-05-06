<?php
/**
 * Manages completed report orders.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Insert the View Report link into the customer's order view, after the item meta, but not to emails.
 */
function zpsr_insert_report_link( $item_id, $item, $order ) {
	// only add link to ZP Report products
	$form_data = wc_get_order_item_meta( $item_id, '_zp_form_data', true );
	if ( ! $form_data ) {
		return;
	}

	// get the name of templates used now
	$templates_used = array();
	foreach ( debug_backtrace() as $called_file ) {
		foreach ( $called_file as $index ) {
			if ( is_array( $index ) ) {// avoid errors
				if ( ! empty( $index[0] ) ) {
					if ( is_string( $index[0] ) ) {// eliminate long arrays
						$templates_used[] = $index[0];
					}
				}
			}
		}
	}

	// Do not add link to any emails
	foreach ( $templates_used as $template_name ) {
		// check each file name for '/emails/'
		if ( strpos( $template_name, '/emails/' ) !== false ) {
			return;
		}
	}

	// Only allow View Report link if payment is complete, unless "_grant_access_after_payment" is enabled
	$status = $order->get_status();
	$html_before = '<em>';
	$html_after = '</em> <mark class="order-status">(' . wc_get_order_status_name( $status ) . ')</mark>';
	if ( 'completed' == $status
		|| ( 'yes' === get_option( 'woocommerce_downloads_grant_access_after_payment' ) && 'processing'== $status ) ) {
		$param = urlencode( base64_encode( $form_data ) );
		$html_before = '<a href="#?zpsr-data=' . $param . '" id="zpsr-item-' . $item_id . '" class="zpsr-fetch-report">';
		$html_after = '</a>';
	}

	echo $html_before . apply_filters( 'zpsr_view_report_link_text', __( 'View Your Report', 'zp-sell-reports' ) ) . $html_after;

}
add_action( 'woocommerce_order_item_meta_end', 'zpsr_insert_report_link', 10, 3);

/**
 * Hide the ZP form data string meta from the admin order view since it's for internal use to generate the report.
 */
function zpsr_hide_form_data_meta( $meta ) {
	$meta[] = '_zp_form_data';
	$meta[] = __( 'Birth year', 'zp-sell-reports' );
	return $meta;
}
add_filter( 'woocommerce_hidden_order_itemmeta', 'zpsr_hide_form_data_meta' );

/**
 * Delete ZP birthdata meta from order item
 * @param object $order A WooCommerce order object
 */
function zpsr_delete_order_item_birthdata_meta( $order ) {
	// meta keys to delete
	$meta_keys = array(
				__( 'Birth year', 'zp-sell-reports' ),
				__( 'Name', 'zp-sell-reports' ),
				'_Born',
				'_Place',
				'_zp_form_data' );
	$items = $order->get_items();
	foreach( $items as $item ) {
		$item_id = $item->get_id();
		foreach ( $meta_keys as $k ) {
			wc_delete_order_item_meta( $item_id, $k );
		}
	}
}
