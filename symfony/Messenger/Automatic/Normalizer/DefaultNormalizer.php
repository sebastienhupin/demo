<?php

namespace AppBundle\Services\Messenger\Automatic\Normalizer;

use AppBundle\Services\AccessService;

/**
 * Description of DefaultNormalizer
 *
 * @author sebastienhupin
 */
class DefaultNormalizer implements NormalizerInterface {
   
    /**
     *
     * @var AccessService 
     */
    private $accessService;
    
    /**
     * The constructor
     * 
     * @param AccessService $accessService
     */
    public function __construct(AccessService $accessService) {
        $this->accessService = $accessService;
    }    
    
    /**
     * 
     *  {@inheritdoc}
     */    
    public function normalize(\AppBundle\Model\AutomaticMessenger\Parameters $exportParameters, Array $data) {

        return ;
    }
    
    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalization(\AppBundle\Model\AutomaticMessenger\Parameters $parameters) {
        return true;
    }
}
