<?php

namespace AppBundle\Services\Exporter\Encoder;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Model\Export\Parameters as ExportParameters;
use Symfony\Component\HttpKernel\Kernel;

class WordExport implements EncoderInterface {

    const FORMAT = 'docx';

    const VALID_OPTIONS = [
        'orientation' =>
            [
                'optionLabel' => 'orientation',
                'values'=>['landscape','portrait']
            ],
        'format'=> [
            'optionLabel' => 'page-size',
            'values'=>['A4','A3','letter']
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function encode(DocumentCollection $documentCollection, ExportParameters $exportParameters = null) {
        foreach($documentCollection as $document) {
            $content = $document->getContent();
            $options = $exportParameters->getOptions();

            //Cover Case
            if(!empty($options['cover']) && $options['cover']){
                $fileCoverTemp = tempnam(sys_get_temp_dir(), 'cover');
                $docxCover = new \Phpdocx\Create\CreateDocx();
                $docxCover->modifyPageLayout('A4',
                    array(
                        'marginTop' => 0,
                        'marginRight' => 0,
                        'marginBottom' => 0,
                        'marginLeft' => 0
                    ));
                $contentCover = $document->getCover();
                $docxCover->embedHTML($contentCover,array('downloadImages' => true));
                $docxCover->addBreak(array('type'=>'page'));
                $docxCover->createDocx($fileCoverTemp);
                $filePathCover =  $fileCoverTemp . '.docx';
            }

            $fileTemp = tempnam(sys_get_temp_dir(), 'docx');
            $docx = new \Phpdocx\Create\CreateDocx();

            if(!empty($parameters['toc'])){
                $legend = array('text' => 'Faites un clic droit "actualiser la table des matières"');
                $docx->addTableContents(array('autoUpdate' => true), $legend);
                $docx->addBreak(array('type'=>'page'));
            }

            $docx->embedHTML($content,array('downloadImages' => true));

            $paperType = $this->getPaperType($options);
            if($paperType){
                $docx->modifyPageLayout($paperType);
            }

            if($options['header']){
                $docx = $this->setHeader($docx,$document);
            }
            if($options['footer']){
                $docx = $this->setFooter($docx,$document,$options['numbering']);
            }

            $docx->createDocx($fileTemp);

            if(!empty($options['cover']) && $options['cover']){
                //Merging part (for cover case)
                $merge = new \Phpdocx\Utilities\MultiMerge();
                $fileTempFinal = tempnam(sys_get_temp_dir(), 'merging');
                $merge->mergeDocx($filePathCover, array($fileTemp. '.docx'), $fileTempFinal. '.docx', array());

                //Get file content
                $fileContent = file_get_contents($fileTempFinal . '.docx');
                $document->setContent($fileContent);

                unlink($fileTemp . '.docx');
                unlink($filePathCover);
                unlink($fileTempFinal. '.docx');
            }else{
                //Get file content
                $fileContent = file_get_contents($fileTemp . '.docx');
                $document->setContent($fileContent);
                unlink($fileTemp . '.docx');
            }
        }
    }

    /**
     * {@inheritdoc}
     */    
    public function supportsEncoding(ExportParameters $exportParameters) {
        return self::FORMAT === $exportParameters->getFormat();
    }

    /**
     * @param $options
     * @return null|string
     */
    private function getPaperType($options){
        $paperType = null;
        $format = $options['format'];
        $orientation = $options['orientation'];

        $validOrientationValues = self::VALID_OPTIONS['orientation']['values'];
        $validformatValues = self::VALID_OPTIONS['format']['values'];

        if (in_array($format, $validformatValues)) {
            $paperType = $format;
            if(in_array($orientation, $validOrientationValues) && $orientation==='landscape'){
                $paperType = $paperType.'-'.'landscape';
            }
        }
        return $paperType;
    }

    /**
     * @param $docx
     * @param $document
     * @return mixed
     */
    private function setHeader($docx,$document){
        $header =new \Phpdocx\Elements\WordFragment($docx,'defaultHeader');

        $headerHtml = $document->getHeader();

        $header->embedHTML($headerHtml,array('downloadImages' => true));
        $docx->addHeader(array('default' => $header));

        return $docx;
    }

    /**
     * @param $docx
     * @param $document
     * @param $numbering
     * @return mixed
     */
    private function setFooter($docx,$document,$numbering){
        $footer =new \Phpdocx\Elements\WordFragment($docx,'defaultFooter');

        if($numbering){
            $footer->addPageNumber('numerical',array('textAlign'=>'center'));
        }

        $footerHtml = $document->getFooter();
        $footer->embedHTML($footerHtml);

        $docx->addFooter(array('default' => $footer));

        return $docx;
    }
}
