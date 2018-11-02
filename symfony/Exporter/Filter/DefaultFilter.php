<?php

namespace AppBundle\Services\Exporter\Filter;

use AppBundle\Model\Export\Parameters as ExportParameters;

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
    public function filtering(ExportParameters $exportParameters, Array $data) {
        // Nothing to do here
        return $data;
    }
    
    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsFilter(ExportParameters $exportParameters) {
        return true;
    }

}
