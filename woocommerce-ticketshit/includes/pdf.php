<?php
require('fpdf/fpdf.php');

class PDF extends FPDF {
	function __construct() {
		parent::__construct('L','mm',array(210,90));

		$this->SetFont('Arial','',14);
		$this->SetTopMargin(10);
		$this->SetLeftMargin(10);
		$this->SetRightMargin(10);
	}

	function set_background() {
		$uploads_dir = wp_get_upload_dir();
		$image_path = $uploads_dir['basedir'] . '/woocommerce-ticketshit/pdf_logo.jpg';
		if (file_exists($image_path))
			$this->Image($image_path, 0, 0, $this->w, $this->h);
	}

	function set_text($event_titme, $ticket_title, $ticket_price) {
		$this->Text(80, 17, 'Grand Conference Ticket');
		$this->Text(20, 35, 'Event:');
		$this->Text(40, 35, $event_titme);

		$this->Text(20, 45, 'Ticket:');
		$this->Text(40, 45, $ticket_title);

		$this->Text(20, 55, 'Price:');
		$this->Text(40, 55, $ticket_price);
	}

	function set_qr($data) {
		$this->SetFillColor(0,0,0);
		foreach($data as $key => $row) {
			$this->SetXY(150, 30 + $key);
			for($i = 0; $i < strlen($row); $i++)
				if ($row[$i] == 1)
					$this->Cell(1,1,'',0, 0, '', true);
				else
					$this->Cell(1,1,'',0, 0, '', false);
			//}
			$this->Ln();
		}
	}
}
?>