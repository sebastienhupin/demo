<?php

namespace AppBundle\Services\Messenger\Automatic\NormalizationGroup;
use AppBundle\Model\AutomaticMessenger\Parameters;

/**
 * Description of ReportCardNormalizationGroup
 *
 * @author sebastienhupin
 */
class DefaultNormalizationGroup implements NormalizationGroupInterface{
    /**
     * 
     *  {@inheritdoc}
     */
    public function getGroups() {
        return array();
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(Parameters $parameters) {
        return true;
    }
}
