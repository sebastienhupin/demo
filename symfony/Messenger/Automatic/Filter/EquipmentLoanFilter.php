<?php

namespace AppBundle\Services\Messenger\Automatic\Filter;

use AppBundle\Model\AutomaticMessenger\Parameters;

/**
 * Description of EquipmentLoanFilter
 *
 */
class EquipmentLoanFilter implements FilterInterface {

    /**
     *
     *  {@inheritdoc}
     */
    public function filtering(Parameters $parameters, Array $data) {

        $equipmentRents = [];
        $dateNow = new \DateTime('NOW');
        foreach($data as $equipmentRent){
            if (empty($equipmentRent['endDate'])) {
              $dateEnd = \DateTime::createFromFormat('Y-m-d', $equipmentRent['endDateTheorical']);
              if ($dateNow>=$dateEnd) {
                  $equipmentRents[] = $equipmentRent;  
              }              
            }
        }
        return $equipmentRents;
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function supportsFilter(Parameters $parameters) {
        return 'EquipmentLoan' === $parameters->getResourceName();
    }
}
