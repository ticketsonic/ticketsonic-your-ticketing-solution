<?php
require_once WOO_TS_PATH . '/vendor/autoload.php';

class MPDF_Generator {
	private $mpdf;
	public $extension = 'pdf';
	private $w = 210;
	private $h = 90;

	public function __construct() {
		$this->mpdf = new \Mpdf\Mpdf([
			'mode' => 'utf-8',
			'format' => [$this->h, $this->w],
			'orientation' => 'L',
			'default_font_size' => 14,
			'default_font' => 'arial'
		]);
	}

	function generate_ticket($name, $description, $price, $sensitive_decoded, $ticket_file_abs_path) {
		$this->AddPage();
        $this->SetBackground();
        
        $this->SetText($name, $description, $price);
        $this->SetQR(qr_binary_to_binary(base64_encode($sensitive_decoded)));
        // TODO: Check if it is writable
        $this->Output('F', $ticket_file_abs_path);
	}

	function AddPage() {
		$this->mpdf->AddPage();
	}

	function SetBackground() {
		$uploads_dir = wp_get_upload_dir();
		$image_path = $uploads_dir['basedir'] . '/woocommerce-ticketshit/pdf_background.jpg';
		if (file_exists($image_path))
		$this->mpdf->Image($image_path, 0, 0, $this->w, $this->h, 'jpg', '', true, false);
	}

	function SetText($event_titme, $ticket_title, $ticket_price) {
		$this->mpdf->WriteText(80, 17, 'Grand Conference Ticket');
		$this->mpdf->WriteText(20, 35, 'Event:');
		$this->mpdf->WriteText(40, 35, $event_titme);

		$this->mpdf->WriteText(20, 45, 'Ticket:');
		$this->mpdf->WriteText(40, 45, $ticket_title);

		$this->mpdf->WriteText(20, 55, 'Price:');
		$this->mpdf->WriteText(40, 55, $ticket_price);
	}

	function SetQR($data) {
		$this->mpdf->SetFillColor(0,0,0);
		
		foreach($data as $key => $row) {
			$this->mpdf->SetXY(150, 30 + $key);
			for($i = 0; $i < $row->count(); $i++)
				if ($row[$i] == 1)
					$this->mpdf->Cell(1,1,'',0, 0, '', true);
				else
					$this->mpdf->Cell(1,1,'',0, 0, '', false);
			$this->mpdf->Ln();
		}
	}

	function Output($type, $path) {
		$this->mpdf->Output($path, $type);
	}
}
?>