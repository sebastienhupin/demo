<?php

namespace AppBundle\Services\Messenger\Automatic\Normalizer;

use AppBundle\Model\AutomaticMessenger\Parameters;

/**
 *
 * @author sebastienhupin
 */
interface NormalizerInterface {
    /**
     * Normalizes an object into a set of arrays/scalars.
     * 
     * @param Parameters $parameters
     * @param Array $data
     *
     * @return \AppBundle\Services\Messenger\MessageCollection;
     */
    public function normalize(Parameters $parameters, Array $data);
    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param Parameters $parameters
     *
     * @return bool
     */
    public function supportsNormalization(Parameters $parameters);
}
