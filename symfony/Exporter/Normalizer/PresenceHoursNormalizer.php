<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Exporter\ErrorCollection;
use Dunglas\ApiBundle\Util\ReflectionTrait;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Services\AccessService;
use Symfony\Component\Templating\EngineInterface;


/**
 * Description of LicenseNormalizer
 */
class PresenceHoursNormalizer implements NormalizerInterface {

    use ReflectionTrait;

    const FORMAT = 'presences_hours';

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
     * @param AccessService $accessService
     */
    public function __construct(
        EngineInterface $templating,
        AccessService $accessService
    ) {
        $this->templating = $templating;
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

        $html = $this->templating->render(('@template/Export/presences-hours/presences-hours.html.twig'),
            Array(
                'presences' => $data
            )
        );

        $domDocument = new \DOMDocument();
        $ret = $domDocument->loadXML($html);
        if (false === $ret) {
            throw new \RuntimeException('bad_html');
        }

        if (false === $domDocument) {
            throw new \RuntimeException('bad_document');
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
     *
     *  {@inheritdoc}
     */
    public function supportsNormalization(ExportParameters $exportParameters) {
        return self::FORMAT === $exportParameters->getView();
    }
}
