<?php

namespace AppBundle\Services\Messenger\Automatic\Filter;

use AppBundle\Enum\Booking\ParticipationStatusEnum;
use AppBundle\Model\AutomaticMessenger\Parameters;
use AppBundle\Services\Util\Entity as EntityUtil;

/**
 * Description of EquipmentLoanFilter
 *
 */
class EventInvitationFilter implements FilterInterface {
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

        $events = array();
        $meta = $parameters->getMetaData();

        foreach($data as $event){
            if(empty($event['eventUser']))
                continue;

            $id = $this->getIdFromIri($event['@id']);
            $event['eventUser'] = array_filter($event['eventUser'],function($eventUser)use($id, $meta){

                if($eventUser['participation'] != ParticipationStatusEnum::NOT_RESPONSE)
                    return false;

                    $startDateTimeEventUser = new \DateTime($eventUser['datetimeStart']);
                    $startDateTimeEventUser->setTimeZone(new \DateTimeZone('UTC'));
                    $startDateTimeEventUser = $startDateTimeEventUser->format('Y-m-d H:i:s');

                    $find = false;
                    $cmpt = 0;

                    while(!$find && count($meta['dates'][$id]) > $cmpt){
                        $startDateTime = new \DateTime($meta['dates'][$id][$cmpt]['start']);
                        $startDateTime->setTimeZone(new \DateTimeZone('UTC'));
                        $startDateTime = $startDateTime->format('Y-m-d H:i:s');

                        if($startDateTime == $startDateTimeEventUser)
                            $find = true;

                        $cmpt++;
                    }

                    return $find;
             });

            array_push($events, $event);
        }

        return $events;
    }

    /**
     * Get an id from an iri
     *
     * @param string $iri
     * @throws InvalidArgumentException
     *
     * @return integer
     */
    protected function getIdFromIri($iri) {
        try {
            return $this->entityUtil->getIdFromIri($iri);
        }
        catch(\Exception $e) {
            return null;
        }
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function supportsFilter(Parameters $parameters) {
        return 'Event' === $parameters->getResourceName();
    }
}
