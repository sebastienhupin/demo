<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Exporter\ErrorCollection;
use AppBundle\Services\PES\PesGenerator;
use Doctrine\ORM\EntityManager;
use Dunglas\ApiBundle\Util\ReflectionTrait;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Services\AccessService;
use Dunglas\ApiBundle\Api\IriConverterInterface;


/**
 * Description of PesNormalizer
 */
class PesNormalizer implements NormalizerInterface {

    use ReflectionTrait;    
    
    const FORMAT = 'pes';

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
     * @var PesGenerator
     */
    private $pesGenerator;
    /**
     * @var ErrorCollection
     */
    protected $errorCollection;
    /**
     * The constructor
     *
     * @param IriConverterInterface $iriConverter
     * @param AccessService $accessService
     */
    public function __construct(
        IriConverterInterface $iriConverter,
        AccessService $accessService,
        PesGenerator $pesGenerator
    ) {
        $this->iriConverter = $iriConverter;
        $this->accessService = $accessService;
        $this->pesGenerator = $pesGenerator;
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

        $roleNumber = array_key_exists('roleNumber',$metaData ) ? $metaData['roleNumber'] : null;
        $domDocument = $this->pesGenerator->process($data, $roleNumber);

        if (false === $domDocument) {
            throw new \RuntimeException('pes_error');
        }

        $pesEntity = null;
        if(!$metaData['proFormat']){
            $bill = $this->getItemFromIri($data[0]['@id']);
            $pesEntity = $bill->getPes();
        }

        $documentCollection = new DocumentCollection();
        
        // The first document contain all reports cards and it's accessible for the organization and the current user        
        $doc = new Document();
        $doc->setName(sprintf("%s_%s.%s",
            $exportParameters->getName(),
                $roleNumber,
            $exportParameters->getFormat())
        )
            ->setFolder('Pes')
            ->setContent($domDocument->saveXML())
            //->setAccess($this->accessService->getAccess())
            ->setOrganization($this->accessService->getAccess()->getOrganization())
        ;

        if(!$metaData['proFormat'] && !is_null($pesEntity)){
            $doc->setEntityLinkToFile($pesEntity);
        }
        
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
