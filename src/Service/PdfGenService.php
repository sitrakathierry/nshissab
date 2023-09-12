<?php

namespace App\Service;
use Dompdf\Dompdf;
use Dompdf\Options;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

use Symfony\Component\Process\Process;
use phpseclib\Net\SNMP;
use Symfony\Component\HttpFoundation\Response;

class PdfGenService
{
    private $pdf;

    public function __construct()
    {
        $this->pdf = new Dompdf();
    }

    public function generatePdf($content, $filename)
    {
        // Créez une instance Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Chargez le contenu HTML dans Dompdf
        $dompdf->loadHtml($content);

        // Générez le PDF
        $dompdf->render();

        // Renvoyez la réponse avec le PDF généré
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
            ]
        );
        // $this->pdf->loadHtml($content);
        // $this->pdf->render();
        // // Output the generated PDF to Browser
        // $this->pdf->stream($filename, array('Attachment' => false));
    }

    public function printBarCode($printerName)
    {
        // Remplacez ces valeurs par l'adresse IP et le port réels de l'imprimante
        $printerIp = '127.0.0.1';
        $printerPort = 26443;

        // Spécifiez le nom de l'imprimante (vous pouvez utiliser n'importe quel nom que vous souhaitez)
        $printerName = 'NomDeVotreImprimante';

        // Créer un tableau avec les options supplémentaires, y compris le nom de l'imprimante
        $options = array('printer' => $printerName);

        // Créer un nouveau connecteur d'impression réseau avec les options spécifiées
        $connector = new NetworkPrintConnector($printerIp, $printerPort);

        // Créer un nouveau profil de capacité (vous pouvez l'ajuster en fonction des capacités de votre imprimante)
        $profile = CapabilityProfile::load("simple");

        // Créer une nouvelle instance d'imprimante
        $printer = new Printer($connector, $profile);

        try {
            // Début de l'impression
            $printer->initialize();
            
            // Ajoutez vos commandes d'impression d'étiquettes ici
            $printer->text("Bonjour, ceci est une étiquette !");

            // Fin de l'impression
            $printer->finalize();

        } catch (\Exception $e) {
            // Gérer les exceptions qui surviennent pendant l'impression
            // Vous pouvez enregistrer ou afficher l'erreur selon vos besoins
            echo "Erreur : " . $e->getMessage();
        }

        // Fermer la connexion après l'impression
        $printer->close();
    } 

}


