<?php

namespace AppBundle\Services\Exporter\NormalizationGroup;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 * Description of ReportCardNormalizationGroup
 *
 * @author sebastienhupin
 */
class PresenceCourseNormalizationGroup implements NormalizationGroupInterface{
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
            'course_educationcurriculum',
            'course_access',
            'educationcurriculum',
            'educationcategory',
            'educationcomplement',
            'cycle',
            'place',
            'room',
            'contactpoint',
            'person_contactpoint',
            'attendancebooking',
            'course_attendancebooking'
        );
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(ExportParameters $exportParameters = null) {
        return 'presence-course' === $exportParameters->getView();
    }
}
