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
		$this->SetFillColor(90, 52, 59);
		$this->Rect(10, 10, 190, 10, 'F');
		$this->Rect(10, 10, 190, 70, 'D');
	}

	function set_text($event_titme, $ticket_title, $ticket_price) {
		$this->Text(80, 17, 'Grand Conference Ticket');
		$this->Text(20, 35, 'Event:');
		$this->Text(40, 35, $event_titme);

		$this->Text(20, 45, 'Ticket:');
		$this->Text(40, 45, $ticket_title);

		$this->Text(20, 55, 'Price:');
		$this->Text(40, 55, $ticket_price);

		$this->Image(WP_PLUGIN_DIR . '/woocommerce-ticketshit/includes/logo_black.jpg', 20, 60, 40, 5, 'JPEG');
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