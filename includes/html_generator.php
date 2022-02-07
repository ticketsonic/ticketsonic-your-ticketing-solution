<?php
require_once TS_YTS_PATH . '/vendor/autoload.php';

class TS_YTS_HTML_Generator {
	private $html = '';

	public $extension = 'html';

	public function __construct() {
		$this->html = ts_yts_get_option( 'email_body', '' );
	}

	public function generate_file( $name, $description, $price, $sensitive_decoded, $ticket_file_abs_path ) {
		$qr = $this->get_html_qr( ts_yts_get_qr_matrix( base64_encode( $sensitive_decoded ) ) );
		$this->html = str_replace( '[ticket_qr]', $qr, $this->html );
		$this->html = str_replace( '[ticket_title]', $name, $this->html );
		$this->html = str_replace( '[ticket_description]', $description, $this->html );
		$this->html = str_replace( '[ticket_price]', $price, $this->html );

		$result = file_put_contents( $ticket_file_abs_path, $this->html );

		if ( false === $result ) {
			return array( 'status' => 'failure' );
		}

		return array( 'status' => 'success' );
	}

	public function get_html_qr( $data ) {
		$output = '<table>';

		foreach ( $data as $key => $row ) {
			$output .= '<tr>';
			for ( $i = 0; $i < $row->count(); $i++ )
				if ( 1 === $row[ $i ] )
					$output .= '<td class="black-square"></td>';
				else
					$output .= '<td class="white-square"></td>';

			$output .= '</tr>';
		}
		$output .= '</table>';

		return $output;
	}
}
