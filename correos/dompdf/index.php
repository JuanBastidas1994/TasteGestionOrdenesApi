<?php
ob_start();
require "htmlapdf.php";
$html = ob_get_clean();

require_once "./dompdf/autoload.inc.php";

// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
//$dompdf->stream("test.pdf"); //FORZAR DESCARGA
$file = $dompdf->output();
file_put_contents("pdfTemp/htmlapdf.pdf", $file);
?>