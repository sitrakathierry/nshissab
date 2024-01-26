<?php

namespace App\Service;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ExcelGenService extends AbstractController
{
    private $pdf;
    private $urlGenerator ;
    
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator ;
    }

    public function generateExcelFile($entete,$params,$filename)
    {
        // Créez un écrivain pour générer le fichier Excel
        $writer = WriterEntityFactory::createXLSXWriter();

        // Ouvrez le fichier de sortie
        $writer->openToFile($filename);

        /** Create a style with the StyleBuilder */
        $style = (new StyleBuilder())
        ->setFontBold()
        ->setFontColor(Color::BLACK)
        ->setFontName('Times New Roman')
        ->setFontSize(12)
        ->setBackgroundColor(Color::ORANGE)
        ->build();

        $fontFamily = (new StyleBuilder())
                ->setFontName('Times New Roman')
                ->setFontSize(12)
                ->build();
                
        // Créez une ligne d'en-tête
        $headerRow = WriterEntityFactory::createRowFromArray([""],$style);
        $writer->addRow($headerRow);

        // Créez une ligne d'en-tête
        $headerRow = WriterEntityFactory::createRowFromArray($entete,$style);
        $writer->addRow($headerRow);

        foreach ($params as $rowData) {
            $dataRow = WriterEntityFactory::createRowFromArray($rowData,$fontFamily);
            $writer->addRow($dataRow);
        }
        
        // Fermez le fichier de sortie
        $writer->close();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($filename);

        unlink($filename) ;
    }

    public function generateFileExcel($entete,$params,$nameFilename,$route)
    {
        // Create a spreadsheet
        $spreadsheet = new Spreadsheet();

        // Set the document properties
        $spreadsheet->getProperties()->setTitle('Fichier Excel')
            ->setSubject('Fichier Excel')
            ->setCreator('RANDRIANIRINARISOA Sitraka Thierry')
            ->setLastModifiedBy('Your Last Modified By : Sitraka Thierry');

        // Add some data to the spreadsheet
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();

        // Add header row
        $sheet->fromArray($entete, NULL, 'B2');

        // Add data rows
        $row = 3;
        foreach ($params as $rowData) {
            $sheet->fromArray($rowData, NULL, 'B' . $row);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($nameFilename);

        if (file_exists($nameFilename)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($nameFilename));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($nameFilename));
        
            readfile($nameFilename);
            unlink($nameFilename) ;

            $url = $this->urlGenerator->generate($route);
            header('location:'.$url) ;
            exit(); 

        } else {
            return false ;
        }
    }
}


