<?php

namespace AppBundle\Services\Exporter\NormalizationGroup;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 * Description of ReportCardNormalizationGroup
 *
 * @author sebastienhupin
 */
class LicenceCmfNormalizationGroup implements NormalizationGroupInterface{
    /**
     * 
     *  {@inheritdoc}
     */
    public function getGroups() {
        return Array(
            "licence_cmf"
        );
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(ExportParameters $exportParameters = null) {
        return 'licence-cmf' === $exportParameters->getView();
    }
}
