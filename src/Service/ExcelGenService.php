<?php

namespace App\Service;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;

class ExcelGenService
{
    private $pdf;

    public function __construct()
    {
        
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
}


