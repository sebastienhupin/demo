<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Services\AccessService;
use AppBundle\Services\Exporter\ErrorCollection;

/**
 * Description of DefaultNormalizer
 *
 * @author sebastienhupin
 */
class DefaultNormalizer implements NormalizerInterface {
   const VIEW = 'document';
   
    /**
     *
     * @var AccessService 
     */
    private $accessService;
    /**
     * @var ErrorCollection
     */
    protected $errorCollection;
    
    /**
     * The constructor
     * 
     * @param AccessService $accessService
     */
    public function __construct(AccessService $accessService) {
        $this->accessService = $accessService;
        $this->errorCollection = new ErrorCollection();
    }    
    
    /**
     * 
     *  {@inheritdoc}
     */    
    public function normalize(\AppBundle\Model\Export\Parameters $exportParameters, Array $data) {
        $doc = new Document();
        $doc->setName(sprintf("%s.%s",$exportParameters->getName(), $exportParameters->getFormat()))
            ->setFolder('Documents')
            ->setContent($data['text'])
            ->setAccess($this->accessService->getAccess())
            ->setOrganization($this->accessService->getAccess()->getOrganization());

        return new DocumentCollection(Array($doc));
    }

    public function getErrors(){
        return $this->errorCollection;
    }
    
    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalization(\AppBundle\Model\Export\Parameters $exportParameters) {
        $data = $exportParameters->getData();
        return self::VIEW === $exportParameters->getView() && isset($data['text']);
    }
}
