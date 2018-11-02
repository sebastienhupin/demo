<?php

namespace AppBundle\Services\Messenger\Automatic\Filter;

use AppBundle\Model\AutomaticMessenger\Parameters;

/**
 * Description of DefaultFilter
 *
 * @author sebastienhupin
 */
class DefaultFilter implements FilterInterface{
    /**
     * 
     *  {@inheritdoc}
     */    
    public function filtering(Parameters $parameters, Array $data) {
        // Nothing to do here
        return $data;
    }
    
    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsFilter(Parameters $parameters) {
        return true;
    }

}
