<?php

namespace AppBundle\Services\Exporter\NormalizationGroup;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 * Description of ReportCardNormalizationGroup
 *
 * @author sebastienhupin
 */
class PesNormalizationGroup implements NormalizationGroupInterface{
    /**
     * 
     *  {@inheritdoc}
     */
    public function getGroups() {
        return Array(
            "billline",
            "bill_billline",
            "access",
            "person",
            "person_bankaccount",
            "bankaccount",
            "person_personaddresspostal",
            "personaddresspostal",
            "addresspostal"
        );
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(ExportParameters $exportParameters = null) {
        return 'pes' === $exportParameters->getView();
    }
}
