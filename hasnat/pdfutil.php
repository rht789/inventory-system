<?php
require_once('../vendor/autoload.php');

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 14);
$pdf->Write(0, 'barcode printing in batch');
$pdf->Output('hello.pdf', 'I');

?>
