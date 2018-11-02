<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Enum\AccessAndFunction\FunctionEnum;
use AppBundle\Enum\Core\FileTypeEnum;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Util\StringUtilities;
use Doctrine\ORM\EntityManager;
use Dunglas\ApiBundle\Util\ReflectionTrait;
use Symfony\Component\Templating\EngineInterface;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\ErrorCollection;
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
class LicenceCmfNormalizer extends AbstractNormalizerExporter implements NormalizerInterface
{

    use ReflectionTrait;

    const FORMAT = 'licence-cmf';

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
     *
     * @var EntityManager
     */
    private $em;


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
                                StringUtilities $stringUtilities,
                                EntityManager $em
    )
    {
        parent::__construct($templating, $accessService, $router);
        $this->iriConverter = $iriConverter;
        $this->translator = $translator;
        $this->stringUtilities = $stringUtilities;
        $this->em = $em;
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function normalize(ExportParameters $exportParameters, Array $response)
    {
        $documentCollection = new DocumentCollection();
        $data = $response[0];
        $errors = $response[1];

        if (count($data) > 0) {
            $cmfOrganization = $this->em->getRepository('AppBundle:Organization\Organization')->find(12097);
            $year = date('Y');

            $config = $this->accessService->getOrganization()->getParameters();
            $metaData = $exportParameters->getMetaData();
            $options = $exportParameters->getOptions();

            $organizationLicence = false;
            if (array_key_exists('organization', $metaData) && $metaData['organization']) {
                $organizationLicence = true;
                $data[0]['logo'] = $this->accessService->getOrganization()->getLogo() ? $this->router->generate('opentalent_internal_secure_file_donwload', array('id' => $this->accessService->getOrganization()->getLogo()->getId())) : null;
                $presidents = $this->em->getRepository('AppBundle:AccessAndFunction\Access')->findByOrganizationAndMission($this->accessService->getOrganization(), FunctionEnum::PRESIDENT);

                $data[0]['person']['gender'] = $presidents[0]->getPerson()->getGender();
                $data[0]['person']['name'] = $presidents[0]->getPerson()->getName();
                $data[0]['person']['givenName'] = $presidents[0]->getPerson()->getGivenName();

                $data[0]['fede'] = $this->accessService->getOrganization()->getParent()[0]->getName();
            }

            $qrCode = $cmfOrganization->getParameters()->getQrCode() ? $this->router->generate('opentalent_internal_secure_file_donwload', array('id' => $cmfOrganization->getParameters()->getQrCode()->getId())) : null;

            $html = $this->templating->render(sprintf('@template/Export/%s/base.html.twig', $exportParameters->getView()),
                Array(
                    'organization' => $this->accessService->getOrganization(),
                    'qrCode' => $qrCode,
                    'config' => $config,
                    'year' => $year,
                    'datas' => $data,
                    'options' => $options,
                    'organizationLicence' => $organizationLicence
                )
            );

//        echo $html; die;

            $domDocument = new \DOMDocument();
            $ret = $domDocument->loadXML(preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $html), LIBXML_PARSEHUGE);
            if (false === $ret) {
                throw new \RuntimeException('bad_html');
            }


            $name = '';
            if (count($data) === 1 && !$organizationLicence) {
                $person = array_values($data)[0]['person'];
                $name = $person['name'] . '_' . $person['givenName'] . '_';
            }
            $name = $name . 'cmf_licence_' . $year;
            $path = $this->stringUtilities->convertToPath($name);

            // The first document contain all reports cards and it's accessible for the organization and the current user
            $doc = new Document();
            $doc->setName(sprintf("%s.%s", $path, $exportParameters->getFormat()))
                ->setFolder('LicenceCMF')
                ->setType(FileTypeEnum::LICENCE_CMF)
                ->setContent($html)
                ->setOrganization($this->accessService->getAccess()->getOrganization())
                ->setReplace(true);

            $documentCollection->add($doc);

            if (!$organizationLicence) {
                // Now we generate a report card document for each students
                // Get each <page data-iri='iri'>
                // For each page we generate a document
                $pages = $domDocument->getElementsByTagName('page');
                foreach ($pages as $page) {
                    $access = $this->getItemFromIri($page->getAttribute('data-iri'));
                    $name = '';
                    if ($access->getPerson()->getName() && $access->getPerson()->getGivenName()) {
                        $name = $access->getPerson()->getName() . '_' . $access->getPerson()->getGivenName() . '_';
                    }
                    $name = $name . 'cmf_licence_' . $year;
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
                    $d->setName(sprintf("%s.%s", $path, $exportParameters->getFormat()))
                        ->setFolder('LicenceCMF')
                        ->setType(FileTypeEnum::LICENCE_CMF)
                        ->setContent($dm->saveHTML())
                        ->setReplace(true)
                        ->setAccess($access)
                        ->setOrganization($this->accessService->getAccess()->getOrganization());
                    $documentCollection->add($d);
                }
            }
        }

        if ($errors) {
            $users = array();
            foreach ($errors as $error) {
                $users[] = $error['person']['name'] . ' ' . $error['person']['givenName'];
            }

            $messageError = array();
            $messageError['msg'] = 'error_licence_msg';
            $messageError['params']['users'] = $users;

            $this->errorCollection->add(
                [
                    "name" => 'error_licence_cmf',
                    "message" => $messageError
                ]
            );
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
    protected function getItemFromIri($iri)
    {
        $item = $this->iriConverter->getItemFromIri($iri, true);
        return $item->getItem();
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function supportsNormalization(ExportParameters $exportParameters)
    {
        return self::FORMAT === $exportParameters->getView();
    }
}
