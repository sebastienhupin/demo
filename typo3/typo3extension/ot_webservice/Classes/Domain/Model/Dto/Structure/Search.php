<?php

namespace Opentalent\OtWebservice\Domain\Model\Dto\Structure;

use Opentalent\OtWebservice\Domain\Model\Dto\ISearch;

/**
 * Structure
 *
 */
class Search extends \TYPO3\CMS\Extbase\DomainObject\AbstractValueObject implements ISearch {

  /**
   *
   * @var integer 
   */
  public $id;

  /**
   * The structure can be displayed into the opentalent directory.
   * @var boolean 
   */
  public $showInDirectory = FALSE;

  /**
   * @var string
   * 
   */
  public $where;
  
  /**
   *
   * @var string
   */
  public $what;

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
  public $rayon = 15;

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
   * @var int
   *
   */
  public $count = 0;  
  
  /**
   * @var array
   *
   */
  public $orderBy = array();  
    
  /**
   * 
   * {@inheritdoc}
   */
  public function __get($name) {

    if ('filters' === $name) {
        $wheres = array();
        
        if (!empty($this->id)) {
            $wheres['id'] = (int)$this->id;
        }        

        if (!empty($wheres)) {return $wheres;}
        
        return null;
    }
    else if ('orders' === $name) {
        return null;
    }
    else if('itemsPerPage' === $name) {
        return $this->limit;
    }
  }

  /**
   * Return the Structure Id
   * 
   * @return integer
   */
  public function getId() {
    return $this->id;
  }
  
  /**
   * Set the Structure Id
   * 
   * @param integer $id
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
   */
  public function setId($id) {
    $this->id = $id;
    return $this;
  }
  
  /**
   * Return if the structure can be displayed into the opentalent directory.
   * 
   * @return boolean
   */
  public function getShowInDirectory() {
    return $this->showInDirectory;
  }
  
  /**
   * Set if the structure can be displayed into the opentalent directory.
   * 
   * @param type $showInDirectory
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
   */
  public function setShowInDirectory($showInDirectory) {
    $this->showInDirectory = $showInDirectory;
    return $this;
  }

  /**
   * Set where
   * 
   * 
   * @param string $where
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
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
   * Set what
   *  
   * @param string $when
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
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
   * Set latitude
   *
   * @param float $latitude
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
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
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
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
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
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
   * @param string $limit
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
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
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
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
   * Get count
   *
   * @return int 
   */
  public function getCount() {
    return $this->count;
  }  
  
  /**
   * Set count
   * 
   * @param int $count
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
   */
  public function setCount($count) {
    $this->count = $count;
    return $this;
  }  
  
  /**
   * Set the Order By
   * 
   * @param array $orderBy
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
   */
  public function setOrderBy(array $orderBy) {
    $this->orderBy = $orderBy;
    return $this;
  }   
  
  /**
   * 
   * @param array $orderBy
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
   */
  public function addOrderBy($name, $sort = 'ASC') {
    $this->orderBy[] = array('key'=>$name,'value'=>$sort);

    return $this;
  }
  
  /**
   * 
   * @return Array
   */
  public function getOrderBy() {
    return $this->orderBy;
  }

}
