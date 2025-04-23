<?php
require_once __DIR__ . '/../vendor/autoload.php';

function generatePDF($title = 'Document', $html = '', $filename = 'document.pdf', $output = 'I') {
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('SmartInventory System');
    $pdf->SetTitle($title);
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($filename, $output); // 'I' = inline, 'D' = download, 'F' = save to server
}
?>