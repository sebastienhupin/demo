<?php

namespace AppBundle\Services\Messenger\Automatic\Filter;
use AppBundle\Model\AutomaticMessenger\Parameters;

/**
 *
 * @author sebastienhupin
 */
interface FilterInterface {
    
    /**
     * Filtering the data
     * 
     * @param Parameters $parameters
     * @param array $data
     * @return array;
     */
    public function filtering(Parameters $parameters, Array $data);

    /**
     * Checks whether the given class is supported to filter.
     * @param ExportParameters $parameters
     * 
     * @return bool
     */
    public function supportsFilter(Parameters $parameters);    
}
