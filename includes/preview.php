<?php
/**
 * Handles the free preview of the birth report.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Hooks into the ZP_Birth_Report to display only a short preview.
 * @param $content empty string
 * @param array $validated_form Validated form data
 * @param object $chart ZP_Chart object
 */
add_filter( 'zp_birthreport_preview_report', 'zpsr_preview_report_display', 10, 4 );
function zpsr_preview_report_display( $content, $validated_form, $chart ) {
	$preview = new ZPSR_Preview_Report( $validated_form, $chart );
	$content = $preview->get_html();
    $content = wp_kses_post( $content );
	return $content;
}

/**
 * Show no form title for Preview form
 */
add_filter( 'zp_shortcode_default_form_title', 'zpsr_remove_free_preview_form_title', 10, 2 );
function zpsr_remove_free_preview_form_title( $title, $atts ) {
	if ( isset( $atts['report'] ) && 'birthreport_preview' === $atts['report'] ) {
		$title = '';
	}
	return $title;
}
