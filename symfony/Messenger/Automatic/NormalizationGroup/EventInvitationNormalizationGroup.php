<?php

namespace AppBundle\Services\Messenger\Automatic\NormalizationGroup;
use AppBundle\Model\AutomaticMessenger\Parameters;

/**
 * Description of ReportCardNormalizationGroup
 *
 * @author sebastienhupin
 */
class EventInvitationNormalizationGroup implements NormalizationGroupInterface{
    /**
     * 
     *  {@inheritdoc}
     */
    public function getGroups() {
        return Array(
            'booking',
            'event_eventuser',
            'eventuser',
            'access',
            'person',
            'place',
            'addresspostal',
            'place_contactpoint',
            'contactpoint'
        );
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(Parameters $parameters) {
        return 'Event' === $parameters->getResourceName();
    }
}
