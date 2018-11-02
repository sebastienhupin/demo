<?php

/**
 * Description of EventService
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\WebServices;

use Opentalent\OtWebservice\Domain\Model\Dto\ISearch;

/**
 * 
 */
class EventService extends \Opentalent\OtWebservice\WebServices\OpentalentService {

    /**
     * Service name Event
     * 
     * @var String 
     */
    protected $name = "events";

    /**
     * Appel le webservice pour récupérer les événements en fonction des paramètres passés par le search
     * 
     * @param \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search
     */
    private function _listEventRequest(\Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search) {

        $events = $this->cget($search);

        return $events->{'hydra:member'};
    }

    /**
     * Return a filtered list event
     * 
     * @param \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search
     * @return Array
     */
    public function searchForEvent(\Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search) {

        $events = $this->_listEventRequest($search);

        return $events;
    }

    /**
     * Return a filtered top event
     * 
     * @param \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search
     * @return Array
     */
    public function searchForStructureChildrenEvent(\Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search) {
        $events = $this->_listEventRequest($search);

        return $events;
    }

    /**
     * Return a filtered top event
     * 
     * @param \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search
     * @return Array
     */
    public function searchForStructureParentEvent(\Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search) {
        $events = $this->_listEventRequest($search);

        return $events;
    }

    /**
     * Return a filtered top event
     * 
     * @param \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search
     * @return Array
     */
    public function topEvent(\Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search) {
        $events = $this->_listEventRequest($search);

        return $events;
    }

    /**
     * Return a event
     * 
     * @param string $id
     * @return Object
     */
    public function getEvent($id) {

        $event = $this->get($id);

        return $event;
    }

    /**
     * Gets parameters for a collection
     * 
     * @param ISearch $search
     * @return array
     */
    protected function getColletionParams(ISearch $search) {
        $params = parent::getColletionParams($search);

        if (null !== $search->latitude && null !== $search->longitude) {
            $params['latitude'] = $search->latitude;
            $params['longitude'] = $search->longitude;
            $params['rayon'] = $search->rayon;
        }
        if (!empty($search->where)) {
            $params['where'] = $search->where;
        }
        if (!empty($search->what)) {
            $params['what'] = $search->what;
        }
        if (null !== $search->structure_id) {
            $params['organizationId'] = (int) $search->structure_id;
        }
        
        if (null !== $search->dtbegin) {
            $params['datetimeStart'] = $search->dtbegin;
        }
        
        if (null !== $search->dtend) {
            $params['datetimeEnd'] = $search->dtend;
        }        
        
        if ($search->onChildrenOnly) {
            $params['children'] = $search->onChildrenOnly;
        }

        if ($search->onParentOnly) {
            $params['parent'] = $search->onParentOnly;
        }

        return $params;
    }

}
