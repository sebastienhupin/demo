<?php

/**
 * Description of ThemeGalleryController
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\Controller;

use Opentalent\OtWebservice\WebServices\MemberService;

class MemberController extends \Opentalent\OtWebservice\Controller\OtWebserviceController {
  
  /**
   * The search
   * 
   * @var \Opentalent\OtWebservice\Domain\Model\Dto\Member\Search
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
   * Call the web service to retriev members of the current selected organization
   * 
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Member\Search $search
   * @return array<members>
   */
  protected function getMembers(\Opentalent\OtWebservice\Domain\Model\Dto\Member\Search $search = NULL, $ca = false) {
    $members = NULL;

    if (is_null($search)) {
      $this->search = $this->objectManager->get('Opentalent\\OtWebservice\\Domain\\Model\\Dto\\Member\\Search');
      $this->parseSettings();
    } else {
      $this->search = $search;
    }

    if($ca)
        $memberWS = new MemberService('members_ca');
    else
        $memberWS = new MemberService('members');

    return $memberWS->searchMembers($this->search);

  }

  /**
   * Display all Members for the selected organization
   * 
   * @param Opentalent\OtWebservice\Domain\Model\Dto\Member\Search $search
   * @ignorevalidation $search
   */
  public function searchMembersAction(\Opentalent\OtWebservice\Domain\Model\Dto\Member\Search $search = NULL) {
    $members = $this->getMembers($search);
    
    $this->view->assignMultiple(array(
        'ot_webservice' => 'members-list',
        'members' => $members,
        'search' => $this->search,
    ));
  }

  /**
   * Display all CA Members for the selected organization
   *
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Member\Search $search
   */
  public function searchMembersCAAction(\Opentalent\OtWebservice\Domain\Model\Dto\Member\Search $search = NULL) {
    $members = $this->getMembers($search, true);

    $this->view->assignMultiple(array(
        'ot_webservice' => 'members-list',
        'members' => $members,
        'search' => $this->search,
    ));
  }
  
  protected function parseSettings() {
    if (isset($this->settings['structure']['id'])) {
      $this->search->setOrganizationId((int)$this->settings['structure']['id']);
    }

    if (isset($this->settings['roles'])) {
      $this->search->setFunctions($this->settings['roles']);
    }

    if (isset($this->settings['visibility'])) {
      $visibility = $this->settings['visibility'];
    }

    if (isset($this->settings['limit'])) {
      $this->search->setLimit($this->settings['limit']);      
    }

    if (isset($this->settings['orderBy'])) {
        $this->search->setOrderBy($this->settings['orderBy']);
    }
  }    
}
