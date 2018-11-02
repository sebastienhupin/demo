<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Enum\Core\FileTypeEnum;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Util\SchoolingYearCalculating;
use DoctrineExtensions\Query\Mysql\Date;
use Dunglas\ApiBundle\Util\ReflectionTrait;
use Symfony\Component\Templating\EngineInterface;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Services\AccessService;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use AppBundle\Services\Util\Entity as EntityUtil;
use Symfony\Component\Routing\RouterInterface;
use AppBundle\Enum\Core\ContactPointTypeEnum;
use AppBundle\Enum\Core\AddressPostalTypeEnum;

/**
 * Description of ReportCardNormalizer
 *
 * @author sebastienhupin
 */
class ReportActivityNormalizer extends AbstractNormalizerExporter implements NormalizerInterface {

    use ReflectionTrait;    
    
    const FORMAT = 'report-activity';

    /**
     *
     * @var EntityUtil
     */
    private $entityUtil;

    /**
     * @var SchoolingYearCalculating
     */
    private $schoolingYearCalculating;

    /**
     * PresenceExamenNormalizer constructor.
     * 
     * @param EngineInterface $templating
     * @param AccessService $accessService
     * @param EntityUtil $entityUtil
     * @param RouterInterface $router
     */
    public function __construct(
        EngineInterface $templating,
        AccessService $accessService,
        EntityUtil $entityUtil,
        RouterInterface $router,
        SchoolingYearCalculating $schoolingYearCalculating
    ) {

        parent::__construct($templating, $accessService, $router);
        $this->entityUtil = $entityUtil;
        $this->schoolingYearCalculating = $schoolingYearCalculating;
    }
    
    /**
     * 
     *  {@inheritdoc}
     */
    public function normalize(ExportParameters $exportParameters, Array $data) {
        $metaData = $exportParameters->getMetaData();
        $options = $exportParameters->getOptions();
        $params['options'] = $options;

        $documentCollection = new DocumentCollection();
        $address = $this->accessService->getAccess()->getOrganization()->getAddressPostalForType(AddressPostalTypeEnum::ADDRESS_PRINCIPAL);
        $contactPoint = $this->accessService->getAccess()->getOrganization()->getContactPointForType(ContactPointTypeEnum::PRINCIPAL);
        $schoolYear = $this->schoolingYearCalculating->calculating($this->accessService->getAccess(), new \DateTime($metaData['dateStart']));

        $html = $this->templating->render('@template/report/activity.html.twig',
            array(
                "params"=>$params,
                "structureName"=>$this->accessService->getAccess()->getOrganization()->getName(),
                "creationDate"=>$this->accessService->getAccess()->getOrganization()->getCreationDate(),
                "legalStatut"=>$this->accessService->getAccess()->getOrganization()->getLegalStatus(),
                "structureName"=>$this->accessService->getAccess()->getOrganization()->getName(),
                "dateStart" => $metaData["dateStart"],
                "dateEnd" => $metaData["dateEnd"],
                "schoolYear" => $schoolYear
            ));

        $cover = $this->templating->render('@template/report/activity.cover.html.twig',
            Array(
                "structureName"=>$this->accessService->getAccess()->getOrganization()->getName(),
                "dateStart" => $metaData["dateStart"],
                "dateEnd" => $metaData["dateEnd"],
                "website"=>$this->accessService->getAccess()->getOrganization()->getParameters()->getWebsite(),
                "format" => $exportParameters->getFormat(),
                "address" => $address,
                "contactPoint" => $contactPoint
            )
        );

        $header = $this->templating->render('@template/report/activity.header.html.twig',
            Array(
                "dateStart" => $metaData["dateStart"],
                "dateEnd" => $metaData["dateEnd"])
        );

        $footer = $this->templating->render('@template/report/activity.footer.html.twig',
            Array(
                "structureName"=>$this->accessService->getAccess()->getOrganization()->getName(),
                "website"=>$this->accessService->getAccess()->getOrganization()->getParameters()->getWebsite(),
                "format" => $exportParameters->getFormat(),
                "address" => $address,
                "contactPoint" => $contactPoint)
        );

//        echo $footer; die;

        $exportParameters->setOptions(
            [
                'footer' => true,
                'header' => true,
                'cover' => true,
                'toc' => true,
                'pdfExportOption' =>
                    ['margin-bottom' => 22,'margin-top' => 20]
            ]
        );

        $domDocument = new \DOMDocument();

        $ret = $domDocument->loadXML($html, LIBXML_PARSEHUGE);
        if (false === $ret) {
            throw new \RuntimeException('bad_html');
        }

        // The first document contain all reports cards and it's accessible for the organization and the current user
        $doc = new Document();
        $doc->setName(sprintf("%s.%s",$exportParameters->getName(), $exportParameters->getFormat()))
            ->setFolder('activityReport')
            ->setContent($html)
            ->setType(FileTypeEnum::ACTIVITY_REPORT)
            ->setOrganization($this->accessService->getAccess()->getOrganization())
            ->setCover($cover)
            ->setHeader($header)
            ->setFooter($footer)
        ;

        $documentCollection->add($doc);

        return $documentCollection;
    }

    /**
     * Get an id from an iri
     *
     * @param string $iri
     * @throws InvalidArgumentException
     *
     * @return integer
     */
    protected function getIdFromIri($iri) {
        try {
            return $this->entityUtil->getIdFromIri($iri);
        }
        catch(\Exception $e) {
            return null;
        }
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalization(ExportParameters $exportParameters) {
        return self::FORMAT === $exportParameters->getView();
    }
}
