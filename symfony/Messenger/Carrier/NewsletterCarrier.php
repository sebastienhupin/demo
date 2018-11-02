<?php

namespace AppBundle\Services\Messenger\Carrier;

use AppBundle\Services\Contactor\Contactor;

/**
 * Description of NewsletterCarrier
 *
 * @author sebastienhupin
 */
class NewsletterCarrier extends AbstractCarrier implements CarrierInterface {
    
    /**
     * 
     * {@inheritdoc}
     */    
    public function send(\AppBundle\Services\Messenger\MessageCollection $messages, \AppBundle\Services\Messenger\Parameters $parameters, $delivery = true) {
        
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function check(\AppBundle\Services\Messenger\MessageCollection $messages, \AppBundle\Services\Messenger\Parameters $parameters) {
        
    }      
    
    /**
     * 
     * {@inheritdoc}
     */
    public function supportsMessage(\AppBundle\Services\Messenger\Parameters $parameters) {
        return 'newsletter' === $parameters->getData()['format'];
    }

}
