<?php

namespace AppBundle\Services\Messenger\Automatic\NormalizationGroup;
use AppBundle\Model\AutomaticMessenger\Parameters;

/**
 * Description of AccessLoginNormalizationGroup
 *
 * @author sebastienhupin
 */
class AccessLoginNormalizationGroup implements NormalizationGroupInterface{
    /**
     * 
     *  {@inheritdoc}
     */
    public function getGroups() {
        return Array(
            'access',
            'person'
        );
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(Parameters $parameters) {
        return ('Access' === $parameters->getResourceName() &&
            ('CreateAccounts' === $parameters->getAction() || 'DeleteAccounts' === $parameters->getAction())
        );
    }
}
