<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Parse shortcode attributes into an array
 * @return mixed. array of birthreport shortcode attributes if any, or any empty array if no attributes set for the birthreport shortcode, or false if the birthreport shortcode was not found in this content.
 */
function zpsr_parse_shortcode_atts( $content ) {
	$output = false;
	//get shortcode regex pattern wordpress function
	$pattern = get_shortcode_regex();
	if ( preg_match_all( '/'. $pattern .'/s', $content, $matches ) ) {
	    foreach( $matches[0] as $key => $value ) {

	    	// only deal with the birthreport shortcode, not other shortcodes
	    	if ( 0 === strpos( $value, '[birthreport' ) ) {

		        // $matches[3] return the shortcode attribute as string
		        // replace space with '&' for parse_str() function
		        $get = str_replace(" ", "&" , $matches[3][$key] );
		        parse_str( $get, $output );
		        if ( ! $output ) {
		        	$output = array();
		        }

		    }

	    }
	
	}

	return $output;
}
