<?php

/**
 * Description of ThemeGalleryController
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\Controller;

use Opentalent\OtWebservice\WebServices\EventService;

class EventController extends \Opentalent\OtWebservice\Controller\OtWebserviceController {

  /**
   * The search
   * 
   * @var \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  protected $search;

  /**
   * Initializes the controller before invoking an action method.
   *
   * Override this method to solve tasks which all actions have in
   * common.
   *
   * @return void
   * @api
   */
  protected function initializeAction() {
    if ($this->arguments->hasArgument('search')) {
      $propertyMappingConfiguration = $this->arguments->getArgument('search')->getPropertyMappingConfiguration();
      $propertyMappingConfiguration->allowAllProperties();
      $propertyMappingConfiguration->setTypeConverterOption('TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter', \TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
      $propertyMappingConfiguration->skipProperties('uid');
    }
  }

  /**
   * 
   */
  public function listAction() {
    $eventWS = new EventService();
    $events = $eventWS->getLastEvents();

    $this->view->assign('events', $events);
  }

  /**
   * 
   * @param string $id
   */
  public function detailAction($id = NULL) {  
    if ($this->request->hasArgument('id')) {
      $id = $this->request->getArgument('id');
    }

    $event = NULL;
    if (!is_null($id)) {
      $eventWS = new EventService();
      $event = $eventWS->getEvent($id);
    }

    // Adding a control on the url for to not have a duplicate content.
    if (strpos($event->url, \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL')) === FALSE) {
      $GLOBALS['TSFE']->additionalHeaderData['ot_webservice'] = '<meta name="robots" content="noindex, nofollow">';
    }

    $this->view->assign('event', $event);
  }

  /**
   * 
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search
   * @ignorevalidation $search
   */
  public function searchFormAction(\Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search = NULL) {
    if (is_null($search)) {
      $this->search = $this->objectManager->get('Opentalent\\OtWebservice\\Domain\\Model\\Dto\\Event\\Search');
      $this->parseSettings();
    } else {
      $this->search = $search;
    }
  
    $this->view->assignMultiple(array(
        'ot_webservice' => 'form-event',
        'search' => $this->search,
    ));
  }

  /**
   * 
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search
   * @ignorevalidation $search
   */
  public function searchResultAction(\Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search = NULL) {

    $events = NULL;

    if (is_null($search)) {
      $this->search = $this->objectManager->get('Opentalent\\OtWebservice\\Domain\\Model\\Dto\\Event\\Search');
      $this->parseSettings();
    } else {
      $this->search = $search;
    }

    $eventWS = new EventService();
    $events = $eventWS->searchForEvent($this->search);

    $this->search->setPage($this->search->getPage() + $this->search->getLimit());
    
    // The IsSearch parameter comes from the searchForm action and can be used to know
    // if there is a search request that have been made.
    
    $this->view->assignMultiple(array(
        'ot_webservice' => 'events',
        'events' => $events,
        'search' => $this->search,
        'isSearch' => \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('isSearch') || null
    ));
  }

  /**
   * 
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search
   * @ignorevalidation $search
   */
  public function lastAction(\Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search = NULL) {

    $events = NULL;

    if (is_null($search)) {
      $this->search = $this->objectManager->get('Opentalent\\OtWebservice\\Domain\\Model\\Dto\\Event\\Search');
      $this->parseSettings();
    } else {
      $this->search = $search;
    }

    $eventWS = new EventService();
    $events = $eventWS->searchForEvent($this->search);

    $this->view->assignMultiple(array(
        'ot_webservice' => 'events-last',
        'events' => $events,
        'search' => $this->search,
    ));
  }

  /**
   * 
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search
   * @ignorevalidation $search
   */
  public function topAction(\Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search = NULL) {

    $events = NULL;

    if (is_null($search)) {
      $this->search = $this->objectManager->get('Opentalent\\OtWebservice\\Domain\\Model\\Dto\\Event\\Search');
      $this->parseSettings();
    } else {
      $this->search = $search;
    }


    $eventWS = new EventService();
    $events = $eventWS->topEvent($this->search);

    $this->view->assignMultiple(array(
        'ot_webservice' => 'events-top',
        'events' => $events,
        'search' => $this->search,
    ));
  }

  /**
   * 
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search
   * @ignorevalidation $search
   */
  public function eventStructureAction(\Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search = NULL) {

    $events = NULL;

    if (is_null($search)) {
      $this->search = $this->objectManager->get('Opentalent\\OtWebservice\\Domain\\Model\\Dto\\Event\\Search');
      $this->parseSettings();
      
    } else {
      $this->search = $search;
    }

    $eventWS = new EventService();
    $events = $eventWS->searchForEvent($this->search);

    $this->view->assignMultiple(array(
        'ot_webservice' => 'events_structure',
        'search' => $this->search,
        'events' => $events,
    ));
  }

  /**
   * 
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search
   * @ignorevalidation $search
   */
  public function eventStructureChildrenAction(\Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search = NULL) {

    $events = NULL;

    if (is_null($search)) {
      $this->search = $this->objectManager->get('Opentalent\\OtWebservice\\Domain\\Model\\Dto\\Event\\Search');
      $this->parseSettings();
    } else {
      $this->search = $search;
    }
    $this->search->setOnChildrenOnly(true);

    $eventWS = new EventService();
    $events = $eventWS->searchForStructureChildrenEvent($this->search);

    $this->view->assignMultiple(array(
        'ot_webservice' => 'events_structure',
        'search' => $this->search,
        'events' => $events,
    ));
  }

  /**
   * 
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search
   * @ignorevalidation $search
   */
  public function eventStructureParentAction(\Opentalent\OtWebservice\Domain\Model\Dto\Event\Search $search = NULL) {

    $events = NULL;

    if (is_null($search)) {
      $this->search = $this->objectManager->get('Opentalent\\OtWebservice\\Domain\\Model\\Dto\\Event\\Search');
      $this->parseSettings();
    } else {
      $this->search = $search;
    }
    
    $this->search->setOnParentOnly(true);
    
    $eventWS = new EventService();
    $events = $eventWS->searchForStructureParentEvent($this->search);

    $this->view->assignMultiple(array(
        'ot_webservice' => 'events_structure',
        'search' => $this->search,
        'events' => $events,
    ));
  }

  /**
   * 
   * Display the google map
   * 
   */
  public function googleMapAction() {
    $this->view->assignMultiple(array(
        'ot_webservice' => 'map-event',
    ));
  }

  protected function parseSettings() {
    if (isset($this->settings['structure']['id'])) {
      $id = $this->settings['structure']['id'];
      $this->search->setStructure_Id($id);
    }

    if (isset($this->settings['onChildrenOnly'])) {
      $onChildrenOnly = $this->settings['onChildrenOnly'];
      $this->search->setOnChildrenOnly($onChildrenOnly);
    }

    if (isset($this->settings['what'])) {
      $what = $this->settings['what'];
      $this->search->setWhat($what);
    }

    if (isset($this->settings['where'])) {
      $where = $this->settings['where'];
      $this->search->setWhere($where);
    }

    if (isset($this->settings['dtbegin'])) {
      $dtbegin = $this->settings['dtbegin'];
      $this->search->setDtbegin($dtbegin);
    }

    if (isset($this->settings['dtend'])) {
      $dtend = $this->settings['dtend'];
      $this->search->setDtend($dtend);
    }

    if (isset($this->settings['latitude'])) {
      $latitude = $this->settings['latitude'];
      $this->search->setLatitude($latitude);
    }

    if (isset($this->settings['longitude'])) {
      $longitude = $this->settings['longitude'];
      $this->search->setLongitude($longitude);
    }

    if (isset($this->settings['rayon'])) {
      $rayon = $this->settings['rayon'];
      $this->search->setRayon($rayon);
    }

    if (isset($this->settings['types'])) {
      $types = explode(',', $this->settings['types']);
      $this->search->setTypes($types);
    }

    if (isset($this->settings['limit'])) {
      $limit = $this->settings['limit'];
      $this->search->setLimit($limit);
    }

    if (isset($this->settings['orderBy'])) {
      $orderBy = explode(',', $this->settings['orderBy']);
      foreach ($orderBy as $orders) {
        $order = explode(':', $orders);
        $name = trim($order[0]);
        $sort = trim($order[1]);
        $this->search->addOrderBy($name, $sort);
      }
    }
  }

}
