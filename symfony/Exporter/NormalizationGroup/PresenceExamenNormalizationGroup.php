<?php

namespace AppBundle\Services\Exporter\NormalizationGroup;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 * Description of ReportCardNormalizationGroup
 *
 * @author sebastienhupin
 */
class PresenceExamenNormalizationGroup implements NormalizationGroupInterface{
    /**
     * 
     *  {@inheritdoc}
     */
    public function getGroups() {
        return Array(
            'booking',
            'access',
            'person',
            'education',
            'examen_educationcurriculum',
            'examen_examenconvocation',
            'examenconvocation',
            'educationcurriculum',
            'educationcategory',
            'educationcomplement',
            'cycle',
            'contactpoint',
            'person_contactpoint',
            'attendancebooking',
            'examen_attendancebooking'
        );
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(ExportParameters $exportParameters = null) {
        return 'presence-examen' === $exportParameters->getView();
    }
}
