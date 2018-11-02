<?php

namespace AppBundle\Services\Exporter\NormalizationGroup;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 * Description of ReportCardNormalizationGroup
 *
 * @author sebastienhupin
 */
class PresenceEventNormalizationGroup implements NormalizationGroupInterface{
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
            'person_contactpoint',
            'contactpoint',
            'attendancebooking',
            'event_attendancebooking'
        );
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(ExportParameters $exportParameters = null) {
        return 'presence-event' === $exportParameters->getView();
    }
}
