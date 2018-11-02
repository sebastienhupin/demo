<?php

namespace AppBundle\Services\Exporter\Encoder;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Model\Export\Parameters as ExportParameters;

class ExcelExport implements EncoderInterface {

    const FORMAT = 'xlsx';

    private $phpexcel;

    public function __construct($phpexcel) {
        $this->phpexcel = $phpexcel;
    }
    
    /**
     * {@inheritdoc}
     */    
    public function encode(DocumentCollection $documentCollection, ExportParameters $exportParameters = null) {
        foreach($documentCollection as $document) {
            $content = $document->getContent();
        
            $inputFileType = 'HTML';
            $outputFileType = 'Excel2007';
            \PHPExcel_Settings::setZipClass(\PHPExcel_Settings::PCLZIP);

            //tempory html
            $tempFileHtml = tempnam(sys_get_temp_dir(), 'html');
            file_put_contents($tempFileHtml, $content, LOCK_EX);

            //tempory xlsx
            $tempFileXlsx = tempnam(sys_get_temp_dir(), 'xlsx');

            //phpexcel reader
            $objPHPExcelReader = $this->phpexcel->createReader($inputFileType);
            $objPHPExcel = $objPHPExcelReader->load($tempFileHtml);

            //delete tempory html file
            unlink($tempFileHtml);

            //phpexcel writer
            foreach(range('B','G') as $columnID) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }
            $objPHPExcelWriter = $this->phpexcel->createWriter($objPHPExcel, $outputFileType);
            $objPHPExcel = $objPHPExcelWriter->save($tempFileXlsx . '.xlsx');

            $fileContent = file_get_contents($tempFileXlsx . '.xlsx');

            unlink($tempFileXlsx);

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
