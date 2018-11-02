<?php

namespace AppBundle\Services\Exporter\NormalizationGroup;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 * Description of AttendanceRecordCourseNormalizationGroup
 *
 * @author sebastienhupin
 */
class AttendanceRecordCourseNormalizationGroup implements NormalizationGroupInterface{
    /**
     * 
     *  {@inheritdoc}
     */
    public function getGroups() {
        return Array(
            'access',
            'person',
            'person_contactpoint',
            'contactpoint',
            'access_practicalcourses',
            'course',
            'course_access',
            'course_educationcurriculum',
            'course_bookingrecur',                
            'booking',
            'booking_access',
            'bookingrecur',
            'education',
            'education_educationcurriculum',
            'education_cyclebyeducation',
            'educationcategory',
            'educationcomplement',
            'educationcurriculum',
            'cycle',
            'place',
            'addresspostal',
            'place_room',
            'room'
        );
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(ExportParameters $exportParameters = null) {
        return 'attendance-record' === $exportParameters->getView();
    }
}
