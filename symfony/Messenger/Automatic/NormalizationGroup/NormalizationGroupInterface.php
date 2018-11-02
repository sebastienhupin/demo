<?php

namespace AppBundle\Services\Messenger\Automatic\NormalizationGroup;
use AppBundle\Model\AutomaticMessenger\Parameters;

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
     * @param Parameters $parameters
     *
     * @return bool
     */
    public function supportsNormalizationGroup(Parameters $parameters);
}
