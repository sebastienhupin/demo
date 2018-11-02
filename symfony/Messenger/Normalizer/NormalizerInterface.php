<?php

namespace AppBundle\Services\Messenger\Normalizer;

use AppBundle\Services\Messenger\Parameters;

/**
 *
 * @author sebastienhupin
 */
interface NormalizerInterface {
    /**
     * Normalizes an object into a set of arrays/scalars.
     * 
     * @param Parameters $parameters
     *
     * @return \AppBundle\Services\Messenger\MessageCollection
     */
    public function normalize(Parameters $parameters);
    /**
     * Checks whether the given message is supported for normalization by this normalizer.
     *
     * @param Parameters $parameters
     *
     * @return bool
     */
    public function supportsNormalization(Parameters $parameters);
}
