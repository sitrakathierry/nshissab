<?php

namespace App\Service;
use Dompdf\Dompdf;
use Talal\LabelPrinter\Printer;
use Talal\LabelPrinter\Mode\Template;
use Talal\LabelPrinter\Command\Barcode;
// use Mike42\Escpos\Printer;
use Symfony\Component\Process\Process;
use phpseclib\Net\SNMP;

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

    public function printBarCode($ipAdress)
    {
        dd($ipAdress) ;
        // $stream = stream_socket_client('tcp://192.168.1.8:9100');

        // $printer = new Printer(new Template(2, $stream));
        // $printer->addCommand(new Barcode('48130', 80, Barcode::WIDTH_SMALL, 'code39', false, 2.5));

        // or QR code
        // $printer->addCommand(new Command\QrCode('https://example.com'));

        // $printer->printLabel();
        // fclose($stream);
    } 

    function getPrinterIp($printerName)
    {
        $output = [];
        $return_var = 0;
        exec("ping -n 1 $printerName", $output, $return_var);

        if ($return_var === 0) {
            // Le ping a réussi, obtenir l'adresse IP à partir de la première ligne du résultat du ping
            preg_match('/\[(.*?)\]/', $output[0], $matches);
            if (isset($matches[1])) {
                return $matches[1];
            }
        }

        return null;
    }

    public function discoverPrinters()
    {  
        $printersWithKeys = array();
        if (stristr(PHP_OS, 'win')) {
            $command = 'wmic printer get name';
            $output = shell_exec($command);
            
            $lines = explode(PHP_EOL, $output); // Divise la sortie en lignes

            // $printers = array_filter($lines);
            $printers = array_filter(array_map('trim', $lines));

            // Créer un nouveau tableau avec la clé "name" pour chaque élément
            foreach ($printers as $printer) {
                $printerIp = $this->getPrinterIp($printer) ;
                if(is_null($printerIp)){
                    $printerIp = "-" ;
                }
                $printersWithKeys[] = array('name' => $printer,'ip' => $printerIp);
            }

        } else {
            echo "Cette fonctionnalité n'est disponible que sur Windows.";
        }

        return $printersWithKeys  ;
    }

    // public function discoverPrinters()
    // {
    //     $printers = array();

    //     // IP range to scan (adjust as needed)
    //     $startIP = '192.168.1.1';
    //     $endIP = '192.168.1.255';

    //     // SNMP community (default is 'public')
    //     $community = 'public';

    //     // SNMP OID for printer name
    //     $oidName = '1.3.6.1.2.1.1.5.0';

    //     // Loop through IP range and query printers
    //     $currentIP = ip2long($startIP);
    //     $endIP = ip2long($endIP);

    //     while ($currentIP <= $endIP) {
    //         $ip = long2ip($currentIP);

    //         // Use phpseclib to perform SNMP query
    //         $snmp = new SNMP(SNMP::VERSION_2C, $ip, $community);
    //         $printerName = $snmp->get($oidName);

    //         if ($printerName !== false) {
    //             $printers[] = array(
    //                 'ip' => $ip,
    //                 'name' => $printerName,
    //             );
    //         }

    //         // Increment the IP address
    //         $currentIP = ($currentIP + 1) & 0xFFFFFFFF;
    //     }

    //     return $printers;
    // }

}


