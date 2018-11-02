<?php

namespace AppBundle\Services\Messenger\Normalizer;

use AppBundle\Services\Messenger\MessageCollection;

/**
 * Description of NewsletterNormalizer
 *
 * @author sebastienhupin
 */
class NewsletterNormalizer extends AbstractNormalizer implements NormalizerInterface {
    /**
     * 
     * {@inheritdoc}
     */
    public function normalize(\AppBundle\Services\Messenger\Parameters $parameters) {
        
    }
    /**
     * 
     * {@inheritdoc}
     */
    public function supportsNormalization(\AppBundle\Services\Messenger\Parameters $parameters) {
         return 'newsletter' === $parameters->getData()['format'];
    }

}
