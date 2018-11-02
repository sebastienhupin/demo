<?php

namespace AppBundle\Services\Messenger\Automatic\NormalizationGroup;
use AppBundle\Model\AutomaticMessenger\Parameters;

/**
 * Description of EquipmentLoanNormalizationGroup
 *
 */
class EquipmentLoanNormalizationGroup implements NormalizationGroupInterface{
    /**
     * 
     *  {@inheritdoc}
     */
    public function getGroups() {
        return Array(
            "equipmentrent_list"
        );
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(Parameters $parameters) {
        return 'EquipmentLoan' === $parameters->getResourceName();
    }
}
