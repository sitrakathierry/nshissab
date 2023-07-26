<?php

namespace App\Service;
use Dompdf\Dompdf;
use Talal\LabelPrinter\Printer;
use Talal\LabelPrinter\Mode\Template;
use Talal\LabelPrinter\Command\Barcode;

class PdfGenService
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

    public function printBarCode()
    {
        $stream = stream_socket_client('tcp://192.168.1.8:9100');

        $printer = new Printer(new Template(2, $stream));
        $printer->addCommand(new Barcode('48130', 80, Barcode::WIDTH_SMALL, 'code39', false, 2.5));

        // or QR code
        // $printer->addCommand(new Command\QrCode('https://example.com'));

        $printer->printLabel();
        fclose($stream);
    } 
}


