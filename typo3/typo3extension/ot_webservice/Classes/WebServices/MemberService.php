<?php

/**
 * Description of EventService
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\WebServices;

/**
 * 
 */
class MemberService extends \Opentalent\OtWebservice\WebServices\OpentalentService {

  /**
   * Service name Member
   * 
   * @var String 
   */
  protected $name;

  /**
   * Constructor
   */
  public function __construct($name) {
    $this->name = $name;
    parent::__construct();
  }

  /**
   * Return a filtered list of members
   * 
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Member\Search $search
   * @return Array
   */
  public function searchMembers(\Opentalent\OtWebservice\Domain\Model\Dto\Member\Search $search) {
    $members = $this->cget($search); 
    return $members->{'hydra:member'};
  }
}

?>
