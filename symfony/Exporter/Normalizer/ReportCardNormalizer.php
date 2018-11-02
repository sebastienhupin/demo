<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Enum\Core\FileTypeEnum;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Util\StringUtilities;
use Dunglas\ApiBundle\Util\ReflectionTrait;
use Symfony\Component\Templating\EngineInterface;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Services\AccessService;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Description of ReportCardNormalizer
 *
 * @author sebastienhupin
 */
class ReportCardNormalizer extends AbstractNormalizerExporter implements NormalizerInterface {

    use ReflectionTrait;    
    
    const FORMAT = 'report-card';

    /**
     *
     * @var IriConverterInterface
     */
    private $iriConverter;

    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var
     */
    private $stringUtilities;

    /**
     * The constructor
     * 
     * @param EngineInterface $templating
     * @param IriConverterInterface $iriConverter
     * @param AccessService $accessService
     * @param RouterInterface $router
     */
    public function __construct(EngineInterface $templating,
                                IriConverterInterface $iriConverter,
                                AccessService $accessService,
                                RouterInterface $router,
                                TranslatorInterface $translator,
                                StringUtilities $stringUtilities) {
        parent::__construct($templating, $accessService, $router);
        $this->iriConverter = $iriConverter;
        $this->translator = $translator;
        $this->stringUtilities = $stringUtilities;
    }
    
    /**
     * 
     *  {@inheritdoc}
     */
    public function normalize(ExportParameters $exportParameters, Array $data) {

        if(count($data) === 0){
            throw new \RuntimeException('no-bulletin');
        }

        $config = $this->accessService->getOrganization()->getParameters();
        $metaData = $exportParameters->getMetaData();
        $options = $exportParameters->getOptions();

        $html = $this->templating->render(sprintf('@template/Export/%s/base.html.twig', $exportParameters->getView()),
            Array(
                'config' => $config,
                'students' => $data,
                'period' => $metaData['period'],
                'options' => $options
            )
        );

        $header = $this->getHeader($options,$metaData,$this->accessService->getAccess()->getOrganization());
        $footer = $this->getFooter($options,$exportParameters->getFormat());

        $domDocument = new \DOMDocument();
        $ret = $domDocument->loadXML(preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $html), LIBXML_PARSEHUGE);
        if (false === $ret) {
            throw new \RuntimeException('bad_html');
        }                
        
        $documentCollection = new DocumentCollection();

        $periodeTranslate = $this->translator->trans($metaData['period']['period'], array(), 'messages', 'fr');
        $name = '';
        if(count($data) === 1){
            $student = array_values($data)[0]['student_info'];
            $name = $student['name'].'_'.$student['givenName'].'_';
        }
        $name = $name.'bulletin_'.$periodeTranslate.'_'.implode('_',$metaData['period']['years']);
        $path = $this->stringUtilities->convertToPath($name);

        // The first document contain all reports cards and it's accessible for the organization and the current user        
        $doc = new Document();
        $doc->setName(sprintf("%s.%s",$path, $exportParameters->getFormat()))
            ->setFolder('ReportsCards')
            ->setType(FileTypeEnum::BULLETIN)
            ->setContent($html)
            ->setAccess($this->accessService->getAccess())
            ->setOrganization($this->accessService->getAccess()->getOrganization())
            ->setHeader($header)
            ->setFooter($footer)
            ->setReplace(true)
        ;
        
        $documentCollection->add($doc);

        if($metaData['period']['archived']){
            // Now we generate a report card document for each students
            // Get each <page data-iri='iri'>
            // For each page we generate a document
            $pages = $domDocument->getElementsByTagName('page');
            foreach ($pages as $page) {
                $access = $this->getItemFromIri($page->getAttribute('data-iri'));
                $name = '';
                if($access->getPerson()->getName() &&  $access->getPerson()->getGivenName()){
                    $name = $access->getPerson()->getName().'_'.$access->getPerson()->getGivenName().'_';
                }
                $name = $name.'bulletin_'.$periodeTranslate.'_'.implode('_',$metaData['period']['years']);
                $path = $this->stringUtilities->convertToPath($name);

                $dm = new \DOMDocument();
                $dm->loadXML($html);
                $body = $dm->getElementsByTagName('body')->item(0);
                $b = $body->cloneNode();
                $b->appendChild($dm->importNode($page, true));
                $body->parentNode->replaceChild(
                    $b,
                    $body
                );
                $d = new Document();
                // @todo: Set the name correctly report_card_period
                $d->setName(sprintf("%s.%s",$path, $exportParameters->getFormat()))
                    ->setFolder('ReportsCards')
                    ->setType(FileTypeEnum::BULLETIN)
                    ->setContent($dm->saveHTML())
                    ->setReplace(true)
                    ->setAccess($access)
                    ->setOrganization($this->accessService->getAccess()->getOrganization())
                    ->setHeader($header)
                    ->setFooter($footer)
                ;
                $documentCollection->add($d);
            }
        }

        return $documentCollection;
    }
        
    /**
     * Retrieves an item from its IRI.
     *
     * @param string $iri
     *
     * @return object|null
     */    
    protected function getItemFromIri($iri) {
        $item = $this->iriConverter->getItemFromIri($iri, true);
        return $item->getItem();
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalization(ExportParameters $exportParameters) {
        return self::FORMAT === $exportParameters->getView();
    }
}
