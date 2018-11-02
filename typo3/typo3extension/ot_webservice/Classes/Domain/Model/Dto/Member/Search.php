<?php

namespace Opentalent\OtWebservice\Domain\Model\Dto\Member;

use Opentalent\OtWebservice\Domain\Model\Dto\ISearch;

/**
 * Member
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
   * @var array
   */
  protected $functions = array();
  
  /**
   *
   * @var int | string 'ALL'
   */
  protected $limit = 'ALL';

  /**
   * @var int
   *
   */
  protected $page = 1;

  /**
   * @var array
   *
   */
  protected $orderBy = array();

  /**
   * 
   * {@inheritdoc}
   */
  public function __get($name) {

    if ('filters' === $name) {
        $wheres = array();
        
        if (!empty($this->organizationId)) {
            $wheres['organizationId'] = (int)$this->organizationId;
        }        
        
        if (!empty($this->functions)) {
            $wheres['or'] = array(array());

            foreach ($this->functions as $function) {
                $wheres['or'][0][] = array('function' => $function);
            }
        }

        if (!empty($wheres)) {return $wheres;}
        
        return null;
    }
    else if ('orders' === $name) {
        if (!empty($this->orderBy)) {
            foreach ($this->orderBy as $order) {
                $orders[$order['key']] = $order['value'];
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
   * 
   * @param String $name
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Member\Search
   */
  public function addFunction($name) {
    $this->functions[] = $name;
    
    return $this;
  }  
  
  /**
   * 
   * @param array $functions
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Member\Search
   */
  public function setFunctions(array $functions) {
    $this->functions = $functions;

    return $this;
  }  
  
  /**
   * 
   * @return array
   */
  public function getFunctions() {
    return $this->functions;
  }
    
  /**
   * Set limit
   *
   * @param int| string (ALL) $limit
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Member\Search
   */
  public function setLimit($limit) {
    $this->limit = $limit;

    return $this;
  }

  /**
   * Get limit
   *
   * @return int | string (ALL)
   */
  public function getLimit() {
    return $this->limit;
  }

  /**
   * Set page
   *
   * @param int $page
   * @return \Opentalent\OtWebservice\Domain\Model\Dto\Member\Search
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

}
