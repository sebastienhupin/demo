<?php

namespace AppBundle\Services\Exporter\NormalizationGroup;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 *
 * @author sebastienhupin
 */
interface NormalizationGroupInterface {
    /**
     * Gets groups needed for the normalization
     * 
     * @return Array
     */
    public function getGroups();
    /**
     * Checks whether the given class is supported for normalization group.
     *
     * @param ExportParameters $exportParameters
     *
     * @return bool
     */
    public function supportsNormalizationGroup(ExportParameters $exportParameters);    
}
