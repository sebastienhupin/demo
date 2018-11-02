<?php

namespace AppBundle\Services\Exporter\Filter;

use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Util\Entity as EntityUtil;

/**
 * Description of EquipmentLoanFilter
 *
 */
class PresenceEventFilter implements FilterInterface {
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
    public function filtering(ExportParameters $exportParameters, Array $data) {
        $events = array();
        $meta = $exportParameters->getMetaData();

        foreach($data as $event){
            if(empty($event['eventUser']))
                continue;

            $id = $this->getIdFromIri($event['@id']);
            $event['eventUser'] = array_filter($event['eventUser'],function($eventUser)use($id, $meta){

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
    public function supportsFilter(ExportParameters $exportParameters) {
        return 'presence-event' === $exportParameters->getView();
    }
}
