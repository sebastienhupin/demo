<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Exporter\ErrorCollection;
use Dunglas\ApiBundle\Util\ReflectionTrait;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Services\AccessService;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Templating\EngineInterface;


/**
 * Description of LicenseNormalizer
 */
class LicenseNormalizer implements NormalizerInterface {

    use ReflectionTrait;

    const FORMAT = 'license';

    /**
     *
     * @var IriConverterInterface
     */
    private $iriConverter;
    /**
     *
     * @var AccessService
     */
    private $accessService;

    /**
     *
     * @var EngineInterface
     */
    private $templating;
    /**
     * @var ErrorCollection
     */
    protected $errorCollection;


    /**
     * UnpaidNormalizer constructor.
     * @param EngineInterface $templating
     * @param IriConverterInterface $iriConverter
     * @param AccessService $accessService
     */
    public function __construct(
        EngineInterface $templating,
        IriConverterInterface $iriConverter,
        AccessService $accessService
    ) {
        $this->templating = $templating;
        $this->iriConverter = $iriConverter;
        $this->accessService = $accessService;
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

        $html = $this->templating->render(sprintf('@template/Export/%s/base.html.twig', $exportParameters->getView()),
            Array(
                'accesses' => $data
            )
        );

        $domDocument = new \DOMDocument();
        $ret = $domDocument->loadXML($html);
        if (false === $ret) {
            throw new \RuntimeException('bad_html');
        }

        if (false === $domDocument) {
            throw new \RuntimeException('license_error');
        }

        $documentCollection = new DocumentCollection();

        // The first document contain all reports cards and it's accessible for the organization and the current user
        $doc = new Document();
        $doc->setName(sprintf("%s_%s.%s",
                $exportParameters->getName(),time(),
                $exportParameters->getFormat())
        )
            ->setFolder('Licenses')
            ->setContent($domDocument->saveXML())
            ->setAccess($this->accessService->getAccess())
            ->setOrganization($this->accessService->getAccess()->getOrganization())
        ;

        $documentCollection->add($doc);

        return $documentCollection;

    }

    /**
     * Get the name
     *
     * @param array $config
     * @return string
     */
    private function getName(Array $config) {
        return sprintf('pes_%s', $config['bulletinPeriodValue']);
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
