<?php

namespace AppBundle\Services\Messenger\Automatic\NormalizationGroup;
use AppBundle\Model\AutomaticMessenger\Parameters;

/**
 * Description of ReportCardNormalizationGroup
 *
 * @author sebastienhupin
 */
class ExamenConvocationNormalizationGroup implements NormalizationGroupInterface{
    /**
     * 
     *  {@inheritdoc}
     */
    public function getGroups() {
        return Array(
            'booking',
            'jury',
            'jury_access',
            'access',
            'person',
            'place',
            'room',
            'education',
            'examen_educationcurriculum',
            'examen_examenconvocation',
            'examenconvocation',
            'educationcurriculum',
            'educationcategory',
            'educationcomplement',
            'cycle',
            'addresspostal',
            'place_contactpoint',
            'contactpoint',
            'examenconvocation_equipment',
            'equipment'
        );
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalizationGroup(Parameters $parameters) {
        return 'Examen' === $parameters->getResourceName();
    }
}
