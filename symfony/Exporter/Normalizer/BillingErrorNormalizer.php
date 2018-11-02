<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Services\Exporter\ErrorCollection;
use Doctrine\ORM\EntityManager;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use Symfony\Component\Templating\EngineInterface;
use AppBundle\Model\Export\Parameters as ExportParameters;
use Dunglas\ApiBundle\Util\ReflectionTrait;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Services\AccessService;

/**
 * Description of BillingErrorNormalizer
 */
class BillingErrorNormalizer implements NormalizerInterface {

    use ReflectionTrait;

    const FORMAT = 'billing-error';

    /**
     *
     * @var EngineInterface
     */
    private $templating;

    private $iriConverter;

    /**
     *
     * @var AccessService
     */
    private $accessService;
    /**
     * @var ErrorCollection
     */
    protected $errorCollection;

    public function __construct(EngineInterface $templating, AccessService $accessService, IriConverterInterface $iriConverter) {
        $this->templating = $templating;
        $this->accessService = $accessService;
        $this->iriConverter = $iriConverter;
        $this->errorCollection = new ErrorCollection();
    }

    public function getErrors(){
        return $this->errorCollection;
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function normalize(ExportParameters $exportParameters, Array $data) {

        $errors = $data;

        $html = $this->templating->render(sprintf('@template/Export/%s/base.html.twig', $exportParameters->getView()),
            Array(
                'errors' => $errors
            )
        );

        $domDocument = new \DOMDocument();
        $ret = $domDocument->loadXML(preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $html), LIBXML_PARSEHUGE);
        if (false === $ret) {
            throw new \RuntimeException('bad_html');
        }

        $documentCollection = new DocumentCollection();

        $doc = new Document();
        $doc->setName(sprintf("%s.%s",$exportParameters->getName().'_'.time(), $exportParameters->getFormat()))
            ->setFolder('BillErrors')
            ->setContent($html)
            ->setOrganization($this->accessService->getAccess()->getOrganization())
        ;

        $documentCollection->add($doc);

        return $documentCollection;
    }

    /**
     * Get the name
     *
     * @param $billNumber
     * @return string
     */
    private function getName($billNumber) {
        return sprintf('facture_ref_%s', $billNumber);
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