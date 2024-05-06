<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The class used to build and display the free preview of the report.
 */
class ZPSR_Preview_Report {
	/**
	 * The validated form data.
	 */
	private $validated_form;

	/**
	 * The Chart object.
	 */
	private $chart;

	/**
	 * The number of characters to show for each interpretation in the free preview.
	 */
	private $length = 60;

	/**
	 * The product id that sells the full report of this preview.
	 */
	private $product_id;

	/**
	 * The text for the "buy to read full report" link.
	 */
	private $link_text = '';

	/**
	 * The HTML for the "buy to read full report" link.
	 */
	private $buy_now_link = '';

	/**
	 * The introduction for the full report.
	 */
	private $intro = '';

	/**
	 * The HTML output for report.
	 */
	private $html;

	/**
	 * Constructor.
	 *
	 * @param array $_validated_form Form data submitted by user requesting preview
	 * @param object $_chart A ZP_Chart object
	 */
	public function __construct( $_validated_form, $_chart ) {
		$this->chart = $_chart;
		$this->save_form_data( $_validated_form );
		$this->product_id = zpsr_find_birthreport_product_id();

		$zp_options = get_option( 'zodiacpress_settings' );

		if ( ! empty( $zp_options['free_preview_length'] ) ) {
			$this->length = (int) $zp_options['free_preview_length'];
		}
		if ( ! empty( $zp_options['buy_now_text'] ) ) {
			$this->link_text = $zp_options['buy_now_text'];
		}
		if ( ! empty( $zp_options['birthreport_intro'] ) ) {
			$this->intro = $zp_options['birthreport_intro'];
		}

		$this->build_buy_now_link();

		if ( $this->product_id ) {
			$this->build_report();	
		} else {
			$this->html = '';
		}
	}

	/**
	 * Save the form data to session and set the $validated_form property
	 */
	private function save_form_data( $validated_form ) {
		// Remove the regular report action from the form data.
		if ( ! empty( $validated_form['action'] ) ) {
			$validated_form['action'] = '';
		}
		// Save the form data string so they can later click to buy full report and not have to renter data.
		$form_data_string = http_build_query( $validated_form );
		zpsr_save_form_data_to_session( $form_data_string );

		$this->validated_form = $validated_form;
	}

	/**
	 * Sets the HTML for the buy now link to the $buy_now_link property.
	 */
	private function build_buy_now_link() {
		if ( $this->product_id ) {
			$url = wc_get_cart_url() . '?add-to-cart=' . $this->product_id;// url to buy the full report
			$this->buy_now_link = apply_filters( 'zpsr_buy_now_link', '<a href="' . esc_url( $url ) . '" class="zpsr-buy-now">[' . esc_html( $this->link_text ) . ']</a>' );
		}
	}

	/**
	 * Return the beginning portion of a string. The length can be specified in the settings.
	 */
	private function clip_string( $text ) {
		$text = strip_tags( $text );
		if ( strlen( $text ) > $this->length ) {
			$space = strpos( $text, ' ', $this->length );
			$text = $space ? substr( $text, 0, $space ) : substr( $text, 0, $this->length );
		}
		return $text;
	}

	/**
	 * Build the teaser preview sentence with a link to buy the full report.
	 * @param string $text The paragraph of interpretations text to build this preview for.
	 * @return string $html
	 */
	private function build_teaser_sentence( $text ) {
		$html = '<p>';
		$html .= $this->clip_string( $text );// Take only the beginning few characters
		$html .= ' [...] ';
		$html .= $this->buy_now_link;// Append a link to buy the full report
		$html .= '<p>';	
		return $html;
	}

	/**
	 * Piece together the short preview and set the $html property.
	 */
	private function build_report() {
		if ( ! is_array( $this->validated_form ) ) {
			return;
		}

		if ( ! is_object( $this->chart ) ) {
			return;
		}

		$out = '';
		$report_text = '';
		$birth_report = new ZP_Birth_Report( $this->chart, $this->validated_form );
		$report_text .= $birth_report->get_interpretations( 'planets_in_signs' );
		$report_text .= $birth_report->get_interpretations( 'planets_in_houses' );
		$report_text .= $birth_report->get_interpretations( 'aspects' );

		// A new doc object so we can take only the pieces of the report that we want to show.
	    $dom = new domDocument;

		// Ensure UTF-8 is respected by using 'mb_convert_encoding'
		$dom->loadHTML( mb_convert_encoding( $report_text, 'HTML-ENTITIES', 'UTF-8' ) );

	    // remove white space
	    $dom->preserveWhiteSpace = false;

	    // Loop through all elements

		foreach( $dom->getElementsByTagName( '*' ) as $el ) {

			if ( 'h3' === $el->nodeName ) {
				$out .= $dom->saveHTML( $el );// take the section headings
				
			} elseif ( 'p' === $el->nodeName ) {

				$class = $el->getAttribute('class');

				if ( 'zp-subheading' === $class ) {

					$out .= $dom->saveHTML( $el ); // take the subheadings

				} elseif ( '' === $class ) { // regular paragraphs of interps text
						
					// Find only the 1st paragraph of interps text in a section
					$previous_el = $el->previousSibling;
					if ( is_object( $previous_el ) ) {
						if ( method_exists( $previous_el, 'getAttribute') ) {
							// If the previous el is a subheading, this is the 1st paragraph
							if ( 'zp-subheading' === $previous_el->getAttribute( 'class' ) ) {
								// Take only the beginning of the paragraph
								$out .= $this->build_teaser_sentence( $el->nodeValue );
							}	
						}
					}
						
				}

			}

		}

		// Prepend Intro
		if ( $this->intro ) {
			$intro = '<h3 class="zp-report-section-title zp-intro-title">' . apply_filters( 'birthreport_intro_title', __( 'Introduction', 'zp-sell-reports' ) ) . '</h3>';

			$intro .= $this->build_teaser_sentence( $this->intro );

			$out = $intro . $out;
		}

		$this->html = $out;
	}

	/**
	 * Returns the HTML for the preview report.
	 */
	public function get_html() {
		return $this->html;
	}	

}
