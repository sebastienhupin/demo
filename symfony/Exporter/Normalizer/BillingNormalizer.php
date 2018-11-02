<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Enum\Core\FileTypeEnum;
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
 * Description of BillingNormalizer
 */
class BillingNormalizer implements NormalizerInterface {

    use ReflectionTrait;

    const FORMAT = 'billing';

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

        $data = $data["hydra:member"];
        $metaData = $exportParameters->getMetaData();
        $config = Array(
            'proFormat' => $metaData['proFormat']
        );
        $organization = $this->accessService->getAccess()->getOrganization();
        $withPes = $organization->getBillingSetting() && $this->testOrganizationWithPes($organization) && $organization->getPesSetting() && array_key_exists('roleNumber', $metaData);
        if($withPes){
            $config['roleNumber'] = $metaData['roleNumber'];
        }

        foreach($data as $key => $d){
            $data[$key]['comments'] = html_entity_decode($d['comments']);
        }

        usort($data, function($a, $b) {
            //Spaceship Operator <=>
            return $a['access']['person']['name'] <=> $b['access']['person']['name'];
        });

        $html = $this->templating->render(sprintf('@template/Export/%s/base.html.twig', $exportParameters->getView()),
            Array(
                'bills' => $data,
                'config' => $config,
                'organization' => $organization,
                'pes' => $withPes
            )
        );

        $exportParameters->setOptions(
            [
                'footer' => true,
                'pdfExportOption' =>
                    ['margin-bottom' => $withPes ? 10 : 30]
            ]
        );

        $footer = null;
        if(!$withPes){
            $footer = $this->templating->render('@template/Export/billing/billing.footer.html.twig',
                Array('organization' => $organization));
        }

        $domDocument = new \DOMDocument();
        $ret = $domDocument->loadXML(preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $html), LIBXML_PARSEHUGE);
        if (false === $ret) {
            throw new \RuntimeException('bad_html');
        }

        $documentCollection = new DocumentCollection();

        $doc = new Document();
        $doc->setName(sprintf("%s.%s",$exportParameters->getName().'_'.time(), $exportParameters->getFormat()))
            ->setFolder('Bills')
            ->setType(FileTypeEnum::BILL)
            ->setContent($html)
            ->setOrganization($this->accessService->getAccess()->getOrganization())
            ->setFooter($footer)
        ;

        $documentCollection->add($doc);

        if(!$config['proFormat']) {

            $pages = $domDocument->getElementsByTagName('page');
            foreach ($pages as $page) {
                $bill = $this->getItemFromIri($page->getAttribute('data-bill'));
                $access = $this->getItemFromIri($page->getAttribute('data-iri'));
                $billNumber = $page->getAttribute('data-ref');
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
                $d->setName(sprintf("%s.%s", $this->getName($billNumber), $exportParameters->getFormat()))
                    ->setFolder('Bills')
                    ->setType(FileTypeEnum::BILL)
                    ->setContent($dm->saveHTML())
                    ->setAccess($access)
                    ->setOrganization($this->accessService->getAccess()->getOrganization())
                    ->setFooter($footer)
                    ->setEntityLinkToFile($bill);
                $documentCollection->add($d);
            }
        }
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

    private function testOrganizationWithPes($organization){
        return $organization->getSettings() && array_key_exists('Pes', $organization->getSettings()->getModules()) ? $organization->getSettings()->getModules()['Pes'] : false;
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function supportsNormalization(ExportParameters $exportParameters) {
        return self::FORMAT === $exportParameters->getView();
    }
}