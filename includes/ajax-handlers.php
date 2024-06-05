<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax handler to save the ZP form data to session
 */
function zpsr_cart_item_form_data_callback() {
	$form_data = isset( $_POST['zp_form_data'] ) ? sanitize_text_field( urldecode( $_POST['zp_form_data'] ) ) : '';
	$data = wp_parse_args( $form_data );
	$name = isset( $data['name'] ) ? trim( $data['name'] ) : false;

	if ( empty( $name ) ) {
		wp_send_json_success([
			'error' => 'Por favor, preencha o nome'
		]);
	}

	unset( $data['name'] );

	foreach ( $data as $value ) {
		$value = trim( $value );
		if ( empty( $value ) ) {
			wp_send_json_success([
				'error' => 'Por favor, preencha todos os campos'
			]);
		}
	}

	zpsr_save_form_data_to_session( $form_data );

	wp_send_json_success([
		'error' => null
	]);
}
add_action( 'wp_ajax_zpsr_cart_item_form_data', 'zpsr_cart_item_form_data_callback' );
add_action( 'wp_ajax_nopriv_zpsr_cart_item_form_data', 'zpsr_cart_item_form_data_callback' );

/**
 * Ajax handler to fetch the report when View Report link is clicked.
 */
function zpsr_fetch_report_callback() {
	$image = '';
	$decoded = base64_decode( urldecode( $_POST['form_data'] ) );
	parse_str( $decoded, $data );

	$validated = zp_validate_form( $data );
	if ( ! is_array( $validated )  ) {
		echo json_encode( array( 'error' => $validated ) );
		wp_die();
	}
	$chart = ZP_Chart::get_instance( $validated );
	if ( empty( $chart->planets_longitude ) ) {
		$report = __( 'Something went wrong.', 'zp-sell-reports' );
	} else {
		$birth_report = new ZP_Birth_Report( $chart, $validated );
		$report = wp_kses_post( $birth_report->get_report() );
		// get image seperately because wp_kses_post does not allow data uri
		// Add the image by default only for the "only chart drawing" report
		if ( 'drawing' === $validated['zp-report-variation'] ) {
			$report .= zp_get_chart_drawing( $chart );
		} else {
			$image = zp_maybe_get_chart_drawing( $validated, $chart );
		}
	}
	echo json_encode( array(
		'report' => $report,
		'image' => $image
	) );

	wp_die();
}
add_action( 'wp_ajax_zpsr_fetch_report', 'zpsr_fetch_report_callback' );
add_action( 'wp_ajax_nopriv_zpsr_fetch_report', 'zpsr_fetch_report_callback' );
