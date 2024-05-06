<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Add a Free Preview settings section to the Natal settings tab.
 */
add_filter( 'zp_settings_sections_natal', 'zpsr_free_preview_settings_section' );
function zpsr_free_preview_settings_section( $sections ) {
	$sections['free_preview'] = __( 'Free Preview', 'zp-sell-reports' );
	return $sections;
}

/**
 * The Free Preview settings 
 */
function zpsr_free_preview_settings( $settings ) {
	$settings['free_preview'] = array(
					'free_preview_length' => array(
						'id'	=> 'free_preview_length',
						'name'	=> __( 'Length of Preview', 'zp-sell-reports' ),
						'desc'	=> __( 'How many characters would you like to show in the preview for each interpretation? Default is 60.', 'zp-sell-reports' ),
						'type'	=> 'text',
						'size'	=> 'small',
						'std'	=> '60'
					),
					'buy_now_text' => array(
						'id'	=> 'buy_now_text',
						'name'	=> __( 'Buy Now Text', 'zp-sell-reports' ),
						'desc'	=> __( 'This is the text for the link to buy the full report.', 'zp-sell-reports' ),
						'type'	=> 'text',
						'std'	=> __( 'Buy the full report', 'zp-sell-reports' )
					),

	);
	return $settings;

}

add_filter( 'zp_settings_natal', 'zpsr_free_preview_settings' );

/**
 * Sanitize the length field
 *
 * @param string $input The field value
 * @return string $input Sanitizied value
 */
function zpsr_sanitize_length_field( $input, $key ) {
	// Must be numeric and positive.
	if ( 'free_preview_length' === trim( $key ) ) {
		if ( ! is_numeric( $input ) || '0' === $input ) {
			return 60;
		} else {
			return abs( $input );
		}
	}
	return trim( $input );
}
add_filter( 'zp_settings_sanitize_text', 'zpsr_sanitize_length_field', 10, 2 );

/**
 * Adds the privacy message on WP privacy page.
 */
add_action( 'admin_init', 'zpsr_add_privacy_message' );
function zpsr_add_privacy_message() {
	if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
		$content = '
			<div contenteditable="false">' .
				'<p class="wp-policy-help">' .
					__( 'This sample language includes the basics around what personal data your store may collect when a customer purchases a ZodiacPress astrology report in your WooCommerce store. You can use it as suggested text for your privacy policy, however, we recommend consulting with a lawyer when deciding what information to disclose on your privacy policy.', 'zp-sell-reports' ) .
				'</p>' .
			'</div>' .
			'<h2>' . __( 'What we collect and store', 'zp-sell-reports' ) . '</h2>' .
			'<div class="wp-suggested-text"><p><strong>' .
			__( 'Suggested text:', 'zp-sell-reports' ) .
			'</strong></p>' .
			'<p>' . __( 'We collect personal information on the birth report order form. This information includes date of birth, time of birth, and place of birth (collectively, the "Birth Data"). We store the Birth Data with your order. The Birth Data is then used to create the astrology report to fullfill your order.', 'zp-sell-reports' ) . '</p>' .
			'</div>';
		wp_add_privacy_policy_content( 'ZodiacPress Sell Reports', $content );
	}
}
