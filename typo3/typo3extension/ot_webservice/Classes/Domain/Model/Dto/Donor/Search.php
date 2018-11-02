<?php

namespace Opentalent\OtWebservice\Domain\Model\Dto\Donor;

use Opentalent\OtWebservice\Domain\Model\Dto\ISearch;

/**
 * Donor
 *
 */
class Search extends \TYPO3\CMS\Extbase\DomainObject\AbstractValueObject implements ISearch {

  /**
   * The structure id of the search donor.
   * It can be set to search only donors from the structure with this pid.
   * @var int 
   */
  protected $organizationId;
  /**
   *
   * @var boolean
   */
  public $onParentOnly = false;
  /**
   *
   * @var string
   */
  public $type;

  /**
   *
   * @var int
   */
  public $visibility;

  /**
   * @var int
   *
   */
  public $limit = 0;

  /**
   * @var int
   *
   */
  public $page = 1;

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
   * Return the Organization Id
   * @return int
   */
  public function getOrganizationId() {
    return $this->organizationId;
  }
  
  /**
   * Set the Organization Id
   * @param int $id
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Member\Search
   */
  public function setOrganizationId($id) {
    $this->organizationId = $id;
    return $this;
  }

  /**
   * Return the type
   * 
   * @return string
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Return the visibility
   * 
   * @return int
   */
  public function getVisibility() {
    return $this->visibility;
  }

  /**
   * Set the type
   * 
   * @param type $type
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search
   */
  public function setType($type) {
    $this->type = $type;
    return $this;
  }

  /**
   * Set the visibility
   * 
   * @param type $visibility
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search
   */
  public function setVisibility($visibility) {
    $this->visibility = $visibility;
    return $this;
  }

  /**
   * Set limit
   *
   * @param int $limit
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search
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
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search
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
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Member\Search
   */
  public function setOrderBy(array $orderBy) {
    $this->orderBy = $orderBy;
    return $this;
  }   
    
  /**
   * 
   * @param array $orderBy
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Member\Search
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
   * @return type
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
