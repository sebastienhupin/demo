<?php

namespace AppBundle\Services\Exporter\NormalizationGroup;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 * Description of ReportCardNormalizationGroup
 *
 */
class BillCreditNormalizationGroup implements NormalizationGroupInterface{
    /**
     * 
     *  {@inheritdoc}
     */
    public function getGroups() {
        return Array(
            'bill',
            'bill_billline',
            'billline',
            'access',
            'person',
            'person_personaddresspostal',
            'personaddresspostal',
            'addresspostal',
            'billcredit',
            'billcredit_billine'
        );
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(ExportParameters $exportParameters = null) {
        return 'billcredit' === $exportParameters->getView();
    }
}
