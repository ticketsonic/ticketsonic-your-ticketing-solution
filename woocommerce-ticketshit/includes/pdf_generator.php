<?php
require('fpdf/fpdf.php');

class PDF_Generator extends FPDF {
	function __construct() {
		parent::__construct('L','mm',array(210,90));

		$this->SetFont('Arial','',14);
		$this->SetTopMargin(10);
		$this->SetLeftMargin(10);
		$this->SetRightMargin(10);
	}

	function generate_ticket($name, $description, $price, $sensitive_decoded, $ticket_file_abs_path) {
		$this->AddPage();
        $this->SetBackground();
        
        $this->SetText($name, $description, $price);
        $this->SetQR(qr_binary_to_binary(base64_encode($sensitive_decoded)));
        // TODO: Check if it is writable
        $this->Output('F', $ticket_file_abs_path);
	}

	function SetBackground() {
		$uploads_dir = wp_get_upload_dir();
		$image_path = $uploads_dir['basedir'] . '/woocommerce-ticketshit/pdf_background.jpg';
		if (file_exists($image_path))
			$this->Image($image_path, 0, 0, $this->w, $this->h);
	}

	function SetText($event_titme, $ticket_title, $ticket_price) {
		$this->Text(80, 17, 'Grand Conference Ticket');
		$this->Text(20, 35, 'Event:');
		$this->Text(40, 35, $event_titme);

		$this->Text(20, 45, 'Ticket:');
		$this->Text(40, 45, $ticket_title);

		$this->Text(20, 55, 'Price:');
		$this->Text(40, 55, $ticket_price);
	}

	function SetQR($data) {
		$this->SetFillColor(0,0,0);
		foreach($data as $key => $row) {
			$this->SetXY(150, 30 + $key);
			for($i = 0; $i < strlen($row); $i++)
				if ($row[$i] == 1)
					$this->Cell(1,1,'',0, 0, '', true);
				else
					$this->Cell(1,1,'',0, 0, '', false);
			$this->Ln();
		}
	}
}
?>