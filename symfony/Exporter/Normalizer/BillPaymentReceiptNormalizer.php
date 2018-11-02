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
 * Description of PesNormalizer
 */
class BillPaymentReceiptNormalizer implements NormalizerInterface {

    use ReflectionTrait;

    const FORMAT = 'bill-payment-receipt';

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

        $acquiredPayments = [];
        foreach($data as $billPayment){
            if($billPayment["isAcquired"]){
                $acquiredPayments[] = $billPayment;
            }
        }

        if(count($acquiredPayments) === 0){
            throw new \RuntimeException('no-acquired-payments');
        }

        $organization = $this->accessService->getAccess()->getOrganization();

        $html = $this->templating->render(sprintf('@template/Export/%s/base.html.twig', $exportParameters->getView()),
            Array(
                'payments' => $acquiredPayments,
                'organization' => $organization,
                'config' => null
            )
        );

        $domDocument = new \DOMDocument();
        $ret = $domDocument->loadXML(preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $html), LIBXML_PARSEHUGE);
        if (false === $ret) {
            throw new \RuntimeException('bad_html');
        }

        if (false === $domDocument) {
            throw new \RuntimeException('bill_payment_detail_error');
        }

        $documentCollection = new DocumentCollection();

        $doc = new Document();
        $doc->setName(sprintf("%s_%s.%s",
                $exportParameters->getName(),time(),
                $exportParameters->getFormat())
        )
            ->setFolder('BillPaymentReceipt')
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
