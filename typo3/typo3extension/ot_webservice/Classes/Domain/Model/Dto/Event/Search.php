<?php

namespace Opentalent\OtWebservice\Domain\Model\Dto\Event;

use Opentalent\OtWebservice\Domain\Model\Dto\ISearch;

/**
 * Event
 *
 */
class Search extends \TYPO3\CMS\Extbase\DomainObject\AbstractValueObject implements ISearch {

  /**
   * The structure id of the search event.
   * It can be set to search only events from the structure with this pid.
   * @var int 
   */
  public $structure_id;
  
  /**
   * Search only on all structure children
   * @var boolean 
   */
  public $onChildrenOnly = FALSE;
  /**
   * Search only on all structure parent
   * @var boolean 
   */
  public $onParentOnly = FALSE;
  /**
   *
   * @var array
   */
  public $types = array();


  /**
   * @var string
   * 
   */
  public $where;

  /**
   * @var string
   * 
   */
  public $when;

  /**
   * @var string
   * 
   */
  public $what;

  /**
   * @var string
   *
   */
  public $dtbegin;

  /**
   * @var string
   *
   */
  public $dtend;

  /**
   * @var float
   *
   */
  public $latitude;

  /**
   * @var float
   *
   */
  public $longitude;

  /**
   * @var int
   *
   */
  public $rayon = 20;

  /**
   * @var int
   *
   */
  public $limit = 20;

  /**
   * @var int
   *
   */
  public $page = 1;

  /**
   * @var array
   *
   */
  public $orderBy = array(array('key' => 'datetimeStart' , 'value' => 'ASC'));

  
  /**
   * 
   * {@inheritdoc}
   */
  public function __get($name) {
    if ('filters' === $name) {

        return null;
    }
    else if ('orders' === $name) {
        $orders = array();
        if (!empty($this->orderBy)) {            
            foreach ($this->orderBy as $order) {
                $orders[] = array($order['key'] => $order['value']);
            }
        }

        if (!empty($orders)) {return $orders;}

        return null;
    }
    else if('itemsPerPage' === $name) {
        return $this->limit;
    }      
  }
  /**
   * Return the Structure Id
   * @return int
   */
  public function getStructure_Id() {
    return $this->structure_id;
  }
  
  /**
   * Set the Structure Id
   * @param int $id
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setStructure_Id($id) {
    $this->structure_id = $id;
    return $this;
  }
  
  /**
   * Return search only on children structure
   * @return boolean
   */
  public function getOnChildrenOnly() {
    return $this->onChildrenOnly;
  }
  
  /**
   * Set a search only on structure children
   * 
   * @param boolean $onChildrenOnly
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setOnChildrenOnly($onChildrenOnly) {
    $this->onChildrenOnly = $onChildrenOnly;
    return $this;
  }

    
  
  /**
   * Return all type needed for the search request
   * @return array
   */
  public function getTypes() {
    return $this->types;
  }
  
  /**
   * Set all type needed for the search request
   * @param array $types
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setTypes($types) {
    if(!is_array($types)) {
      throw new Exception('Only array of type is allowed !!!');
    }
    $this->types = $types;
    return $this;
  }  
  
  /**
   * 
   * Add a type to search
   * 
   * @param string $name
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function addType($name) {
    if (!is_array($this->types)) {
      $this->types = array();
    }
    $this->types[] = $name;

    return $this;
  }  
  

  /**
   * Set where
   * 
   * 
   * @param string $where
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setWhere($where) {
    $this->where = $where;
    return $this;
  }

  /**
   * Get where
   *
   * @return string 
   */
  public function getWhere() {
    return $this->where;
  }

  /**
   * Set when
   * 
   * @param string $when
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setWhen($when) {
    $this->when = $when;

    $daterange = explode('-', str_replace(' ', '', $when), 2);

    if (!empty($daterange[0])) {
      $dtBegin = \DateTime::createFromFormat('d/m/Y', $daterange[0]);
      // ISO 8601
      $this->setDtbegin($dtBegin->format('d-m-Y'));
    }

    // Si un date de fin a été donnée et que celle ci est différente de la date de début.
    if (count($daterange) > 1) {
      $dtEnd = \DateTime::createFromFormat('d/m/Y', $daterange[1]);
      // ISO 8601
      $this->setDtend($dtEnd->format('d-m-Y'));
    }

    return $this;
  }

  /**
   * Get when
   *
   * @return string 
   */
  public function getWhen() {
    return $this->when;
  }

  /**
   * Set what
   * 
   * @param string $when
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setWhat($what) {
    $this->what = $what;
    return $this;
  }

  /**
   * Get what
   *
   * @return string 
   */
  public function getWhat() {
    return $this->what;
  }

  /**
   * Set dtbegin
   *
   * @param string $dtbegin
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setDtbegin($dtbegin) {
    $this->dtbegin = $dtbegin;

    return $this;
  }

  /**
   * Get dtbegin
   *
   * @return string 
   */
  public function getDtbegin() {
    return $this->dtbegin;
  }

  /**
   * Set dtend
   *
   * @param string $dtend
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setDtend($dtend) {
    $this->dtend = $dtend;

    return $this;
  }

  /**
   * Get dtend
   *
   * @return string 
   */
  public function getDtend() {
    return $this->dtend;
  }

  /**
   * Set latitude
   *
   * @param float $latitude
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setLatitude($latitude) {
    $this->latitude = $latitude;

    return $this;
  }

  /**
   * Get latitude
   *
   * @return float 
   */
  public function getLatitude() {
    return $this->latitude;
  }

  /**
   * Set longitude
   *
   * @param float $longitude
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setLongitude($longitude) {
    $this->longitude = $longitude;

    return $this;
  }

  /**
   * Get longitude
   *
   * @return float 
   */
  public function getLongitude() {
    return $this->longitude;
  }

  /**
   * Set rayon
   *
   * @param int $rayon
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setRayon($rayon) {
    $this->rayon = $rayon;

    return $this;
  }

  /**
   * Get rayon
   *
   * @return int 
   */
  public function getRayon() {
    return $this->rayon;
  }

  /**
   * Set limit
   *
   * @param int $limit
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setLimit($limit) {
    $this->limit = $limit;

    return $this;
  }

  /**
   * Get limit
   *
   * @return int 
   */
  public function getLimit() {
    return $this->limit;
  }

  /**
   * Set page
   *
   * @param int $page
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setPage($page) {
    $this->page = $page;

    return $this;
  }

  /**
   * Get page
   *
   * @return int 
   */
  public function getPage() {
    return $this->page;
  }

  /**
   * Set the Order By
   * 
   * @param array $orderBy
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function setOrderBy(array $orderBy) {
    $this->orderBy = $orderBy;

    return $this;
  }  
  
  /**
   * 
   * @param array $orderBy
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Event\Search
   */
  public function addOrderBy($name, $sort = 'ASC') {
    $this->orderBy[] = array('key'=>$name,'value'=>$sort);

    return $this;
  }

  /**
   * 
   * @return array
   */
  public function getOrderBy() {
    return $this->orderBy;
  }
  
  /**
   * Gets parent only
   * 
   * @return boolean
   */
  function getOnParentOnly() {
      return $this->onParentOnly;
  }
  
  /**
   * Sets parent only
   * 
   * @param boolean $onParentOnly
   * @return $this
   */
  function setOnParentOnly($onParentOnly) {
      $this->onParentOnly = $onParentOnly;
      
      return $this;
  }
}
