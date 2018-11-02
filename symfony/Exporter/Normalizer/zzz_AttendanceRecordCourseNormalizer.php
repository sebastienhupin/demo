<?php

namespace AppBundle\Services\Exporter\Normalizer;

use Dunglas\ApiBundle\Api\IriConverterInterface;
use Symfony\Component\Templating\EngineInterface;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Services\AccessService;
use Symfony\Component\Routing\RouterInterface;

/**
 * Description of AttendanceRecordCourseNormalizer
 */
class AttendanceRecordCourseNormalizer extends AbstractNormalizerExporter implements NormalizerInterface {

    const VIEW = 'attendance-record';

    /**
     *
     * @var IriConverterInterface 
     */
    private $iriConverter;
    
    /**
     * The constructor
     * 
     * @param EngineInterface $templating
     * @param AccessService $accessService
     * @param IriConverterInterface $iriConverter
     * @param RouterInterface $router
     */
    public function __construct(EngineInterface $templating, AccessService $accessService, IriConverterInterface $iriConverter, RouterInterface $router) {
        parent::__construct($templating, $accessService, $router);
        $this->iriConverter = $iriConverter;
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function normalize(ExportParameters $exportParameters, Array $data) {

        $options = $exportParameters->getOptions();
        $metaData = $exportParameters->getMetaData();
        
        $organization = $this->accessService->getAccess()->getOrganization();
                        
        $html = $this->templating->render(sprintf('@template/Export/%s/base.course.html.twig', $exportParameters->getView()), Array(
            'data' => $data,
            'organization' => $organization,
            'options' => $options,
            'withNote' => $metaData['note'],
            'withAbsenceJustified' => $metaData['absenceJustified']
        ));
        
        $domDocument = new \DOMDocument();
        $ret = $domDocument->loadXML($html);
        if (false === $ret) {
            throw new \RuntimeException('bad_html');
        }

        $documentCollection = new DocumentCollection();

        $header = $this->getHeader($options, $metaData, $this->accessService->getAccess()->getOrganization());
        $footer = $this->getFooter($options, $exportParameters->getFormat());        
        
        $doc = new Document();
        $doc->setName(sprintf("%s.%s", $exportParameters->getName() . '_' . time(), $exportParameters->getFormat()))
            ->setFolder('Attendance')
            ->setContent($html)
            ->setOrganization($this->accessService->getAccess()->getOrganization())
            ->setHeader($header)
            ->setFooter($footer)                
        ;

        $documentCollection->add($doc);

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
        return self::VIEW === $exportParameters->getView();
    }

}
