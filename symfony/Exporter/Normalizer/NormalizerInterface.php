<?php

namespace AppBundle\Services\Exporter\Normalizer;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Exporter\ErrorCollection;

/**
 *
 * @author sebastienhupin
 */
interface NormalizerInterface {
    /**
     * Normalizes an object into a set of arrays/scalars.
     * 
     * @param ExportParameters $exportParameters
     * @param Array $data
     *
     * @return \AppBundle\Services\Exporter\DocumentCollection
     */
    public function normalize(ExportParameters $exportParameters, Array $data);
    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param ExportParameters $exportParameters
     *
     * @return bool
     */
    public function supportsNormalization(ExportParameters $exportParameters);

    /**
     * @return mixed
     */
    public function getErrors();
}
