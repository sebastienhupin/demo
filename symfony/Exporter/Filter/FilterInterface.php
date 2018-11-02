<?php

namespace AppBundle\Services\Exporter\Filter;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 *
 * @author sebastienhupin
 */
interface FilterInterface {
    
    /**
     * Filtering the data
     * 
     * @param ExportParameters $exportParameters
     * @param array $data
     * @return array;
     */
    public function filtering(ExportParameters $exportParameters, Array $data);

    /**
     * Checks whether the given class is supported to filter.
     * @param ExportParameters $exportParameters
     * 
     * @return bool
     */
    public function supportsFilter(ExportParameters $exportParameters);    
}
