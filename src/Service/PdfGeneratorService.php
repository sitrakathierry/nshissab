<?php

namespace App\Service;
use Dompdf\Dompdf;
class PdfGeneratorService
{
    private $pdf;

    public function __construct()
    {
        $this->pdf = new Dompdf();
    }

    public function generatePdf($content, $filename)
    {
        $this->pdf->loadHtml($content);
        $this->pdf->render();
        // Output the generated PDF to Browser
        $this->pdf->stream($filename, array('Attachment' => false));
    }
}


