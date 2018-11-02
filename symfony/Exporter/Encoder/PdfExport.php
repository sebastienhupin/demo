<?php

namespace AppBundle\Services\Exporter\Encoder;

use Knp\Snappy\GeneratorInterface;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Model\Export\Parameters as ExportParameters;

class PdfExport implements EncoderInterface {

    const FORMAT = 'pdf';

    const VALID_OPTIONS = [
        'orientation' =>
        [
            'optionLabel' => 'orientation',
            'values'=>['landscape','portrait']
        ],
        'format'=> [
            'optionLabel' => 'page-size',
            'values'=>['A4','A3','Letter']
        ]
    ];

    /** @var array */
    private $options = array(
        'margin-top'    => 15,
        'margin-right'  => 10,
        'margin-bottom' => 10,
        'margin-left'   => 15,
        'enable-local-file-access' => true
    );

    /** @var GeneratorInterface */
    private $knpSnappy;

    /** \Symfony\Component\HttpFoundation\Request */
    private $request;
    
    /**
     * The constructor
     * 
     * @param GeneratorInterface $knpSnappy
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(GeneratorInterface $knpSnappy, \Symfony\Component\HttpFoundation\RequestStack $requestStack) {
        $this->knpSnappy = $knpSnappy;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */    
    public function encode(DocumentCollection $documentCollection, ExportParameters $exportParameters = null) {
        $exportParametersOptions = $exportParameters->getOptions();
        if(array_key_exists('pdfExportOption', $exportParametersOptions)){
            $this->options = array_merge($this->options, $exportParametersOptions['pdfExportOption']);
        }

        $pdf = new \Jurosh\PDFMerge\PDFMerger;
        foreach($documentCollection as $document) {
            $filePaths = array();
            if(!empty($exportParametersOptions['cover']) && $exportParametersOptions['cover']){
                $content = $document->getCover();
                $exportParametersOptionsCover = $exportParametersOptions;
                $exportParametersOptionsCover['pdfExportOption'] = array(
                    'margin-top'    => 0,
                    'margin-right'  => 0,
                    'margin-bottom' => 0,
                    'margin-left'   => 0
                );
                $exportParametersOptionsCover['toc'] = false;
                $exportParametersOptionsCover['header'] = false;
                $exportParametersOptionsCover['footer'] = false;

                $this->setParameters($exportParametersOptionsCover,$document);
                $coverContent = $this->knpSnappy->getOutputFromHtml(
                    $content, $exportParametersOptionsCover['pdfExportOption']
                );

                $coverPath = sys_get_temp_dir().'/cover_'.time().'.pdf';
                file_put_contents($coverPath, $coverContent, LOCK_EX);
                $filePaths[] = $coverPath;
            }

            $content = $document->getContent();
            $this->setParameters($exportParameters->getOptions(),$document);
            $content = $this->knpSnappy->getOutputFromHtml(
                    $content, $this->options
            );

            if(!empty($exportParametersOptions['cover']) && $exportParametersOptions['cover']){
                $documentPath = sys_get_temp_dir().'/export_'.time().'.pdf';
                file_put_contents($documentPath, $content, LOCK_EX);
                $filePaths[] = $documentPath;

                foreach ($filePaths as $f){
                    $pdf->addPDF($f, 'all');
                }

                $content = $pdf->merge('string');

                foreach ($filePaths as $f){
                    unlink($f);
                }
            }

            $document->setContent($content);
        }
    }

    /**
     * Sets an array of options
     *
     * @param array $options An associative array of options as name/value
     */
    public function setOptions(array $options) {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    /**
     * Sets an option. Be aware that option values are NOT validated and that
     * it is your responsibility to validate user inputs
     *
     * @param string $name  The option to set
     * @param mixed  $value The value (NULL to unset)
     *
     */
    public function setOption($name, $value) {
        $this->options[$name] = $value;
    }

    /**
     * Returns all the options
     *
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * Adds an option
     *
     * @param string $name    The name
     * @param mixed  $default An optional default value
     *
     */
    protected function addOption($name, $default = null) {
        $this->options[$name] = $default;
    }

    /**
     * Adds an array of options
     *
     * @param array $options
     */
    protected function addOptions(array $options) {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * {@inheritdoc}
     */    
    public function supportsEncoding(ExportParameters $exportParameters) {
        return self::FORMAT === $exportParameters->getFormat();
    }

    public function setParameters($parameters, $document){
        $this->addOption('images', true);
        $validOptions = array_keys(self::VALID_OPTIONS);
        foreach($parameters as $keyOption => $parameter) {
            if (!in_array($keyOption, $validOptions)) {
                continue;
            }
            $currentValidOption = self::VALID_OPTIONS[$keyOption];
            foreach($currentValidOption['values'] as $value) {
                if (strtolower($value) === strtolower($parameter)) {
                    $this->addOption($currentValidOption['optionLabel'],$value);
                }
            }
        }

        if(!empty($parameters['header']) && $parameters['header']){
            $this->addOption('header-html', $document->getHeader());
        }
        if(!empty($parameters['footer']) && $parameters['footer']){
            $this->addOption('footer-html', $document->getFooter());
        }

        if(!empty($parameters['toc'])){
            $this->addOption('toc', true);
            $this->addOption('toc-header-text', 'Sommaire');
        }
    }
}
