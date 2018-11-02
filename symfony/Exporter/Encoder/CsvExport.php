<?php

namespace AppBundle\Services\Exporter\Encoder;

use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Model\Export\Parameters as ExportParameters;

class CsvExport implements EncoderInterface {

    const FORMAT = 'csv';

    /**
     * {@inheritdoc}
     */
    public function encode(DocumentCollection $documentCollection, ExportParameters $exportParameters = null) {
        foreach($documentCollection as $document) {
            $content = $document->getContent();

            $dataRows = [];
            $doc = new \DOMDocument();
            $doc->loadHTML($content);

            $rows = $doc->getElementsByTagName('tr');
            foreach($rows as $row) {
                $values = array();
                foreach($row->childNodes as $cell) {
                    if($cell->nodeName === 'td'){
                        $values[] = $cell->textContent;
                    }
                }
                $dataRows[] = $values;
            }

            $fileTemp = tempnam(sys_get_temp_dir(), 'csv');

            $handle = fopen($fileTemp . '.csv', 'w+');
            fwrite( $handle, "\xEF\xBB\xBF", 3 ); //excel can read utf-8
            foreach ($dataRows as $row) {
                $values = [];
                foreach ($row as $value) {
                    $values[] = $value;
                }

                fputcsv($handle, $values, ';');
            }

            fclose($handle);

            $fileContent = file_get_contents($fileTemp . '.csv');
            unlink($fileTemp);
            
            $document->setContent($fileContent);
        }
    }

    /**
     * {@inheritdoc}
     */    
    public function supportsEncoding(ExportParameters $exportParameters) {
        return self::FORMAT === $exportParameters->getFormat();
    }

}
