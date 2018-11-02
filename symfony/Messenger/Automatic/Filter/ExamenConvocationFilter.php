<?php

namespace AppBundle\Services\Messenger\Automatic\Filter;

use AppBundle\Enum\Booking\ParticipationStatusEnum;
use AppBundle\Model\AutomaticMessenger\Parameters;
use AppBundle\Services\Util\Entity as EntityUtil;

/**
 * Description of EquipmentLoanFilter
 *
 */
class ExamenConvocationFilter implements FilterInterface {
    /**
     *
     * @var EntityUtil
     */
    private $entityUtil;

    public function __construct(
        EntityUtil $entityUtil) {
        $this->entityUtil = $entityUtil;
    }
    /**
     *
     *  {@inheritdoc}
     */
    public function filtering(Parameters $parameters, Array $data) {
        $examens = array();

        foreach($data as $examen){
            if(empty($examen['convocation']))
                continue;

            $examen['convocation'] = array_filter($examen['convocation'], function($convocation){
                return  $convocation['isConvocated'];
            });

            if(empty($examen['convocation']))
                continue;

            array_push($examens, $examen);
        }

        return $examens;
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function supportsFilter(Parameters $parameters) {
        return 'Examen' === $parameters->getResourceName();
    }
}
