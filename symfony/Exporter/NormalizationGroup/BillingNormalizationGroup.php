<?php

namespace AppBundle\Services\Exporter\NormalizationGroup;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 * Description of ReportCardNormalizationGroup
 *
 * @author sebastienhupin
 */
class BillingNormalizationGroup implements NormalizationGroupInterface{
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
            'bill_billpayment',
            'billpayment',
            'booking',
            'equipmentloan'
        );
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(ExportParameters $exportParameters = null) {
        return 'billing' === $exportParameters->getView() || 'unpaid-bill' === $exportParameters->getView();
    }
}
