<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Enum\Core\FileTypeEnum;
use AppBundle\Services\Exporter\ErrorCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Templating\EngineInterface;
use AppBundle\Model\Export\Parameters as ExportParameters;
use Dunglas\ApiBundle\Util\ReflectionTrait;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Services\AccessService;

/**
 * Description of BillingNormalizer
 */
class BillCreditNormalizer implements NormalizerInterface {

    use ReflectionTrait;

    const FORMAT = 'billcredit';

    /**
     *
     * @var EngineInterface
     */
    private $templating;

    private $em;
    /**
     * @var ErrorCollection
     */
    protected $errorCollection;

    /**
     *
     * @var AccessService
     */
    private $accessService;

    public function __construct(EngineInterface $templating, AccessService $accessService, EntityManager $em) {
        $this->templating = $templating;
        $this->accessService = $accessService;
        $this->em = $em;
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
        $metaData = $exportParameters->getMetaData();
        $config = Array(
            'preview' => $metaData['preview']
        );

        $billCredit = count($data) === 1?$data[0]: $data;

        $organization = $this->accessService->getAccess()->getOrganization();

        $html = $this->templating->render(sprintf('@template/Export/%s/base.html.twig', $exportParameters->getView()),
            Array(
                'billCredit' => $billCredit,
                'config' => $config,
                'organization' => $organization
            )
        );

        $exportParameters->setOptions(
            [
                'footer' => true,
                'pdfExportOption' =>
                    ['margin-bottom' => 30]
            ]
        );
        $footer = $this->templating->render('@template/Export/billing/billing.footer.html.twig',
            Array('organization' => $organization));

        $domDocument = new \DOMDocument();
        $ret = $domDocument->loadXML(preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $html), LIBXML_PARSEHUGE);
        if (false === $ret) {
            throw new \RuntimeException('bad_html');
        }

        $documentCollection = new DocumentCollection();

        $doc = new Document();
        $doc->setName(sprintf("%s.%s",$exportParameters->getName().'_'.time(), $exportParameters->getFormat()))
            ->setFolder('BillCredits')
            ->setType(FileTypeEnum::BILL_CREDIT)
            ->setContent($html)
            ->setOrganization($this->accessService->getAccess()->getOrganization())
            ->setFooter($footer)
        ;

        if(!$config['preview']) {
            $doc->setAccess($billCredit->getBill()->getAccess())
                ->setEntityLinkToFile($billCredit);
        }

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
     *
     *  {@inheritdoc}
     */
    public function supportsNormalization(ExportParameters $exportParameters) {
        return self::FORMAT === $exportParameters->getView();
    }
}