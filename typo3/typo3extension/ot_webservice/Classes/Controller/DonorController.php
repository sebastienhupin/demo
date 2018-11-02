<?php

/**
 * Description of ThemeGalleryController
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\Controller;

use Opentalent\OtWebservice\WebServices\DonorService;

class DonorController extends \Opentalent\OtWebservice\Controller\OtWebserviceController {
  
  /**
   * The search
   * 
   * @var \Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search
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
   * Return a list of donors
   * 
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search $search
   */
  public function listDonorAction(\Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search $search = NULL) {
    
    if (is_null($search)) {
      $this->search = $this->objectManager->get('Opentalent\\OtWebservice\\Domain\\Model\\Dto\\Donor\\Search');
      $this->parseSettings();
    } else {
      $this->search = $search;
    }

    $donorWS = new DonorService();
    $donors = $donorWS->searchForDonor($this->search);

    $this->view->assignMultiple(array(
        'search' => $this->search,
        'donors' => $donors
    ));
  }

  /**
   * Return a list of network donors
   * 
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search $search
   */
  public function listDonorFedeAction(\Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search $search = NULL) {

    if (is_null($search)) {
      $this->search = $this->objectManager->get('Opentalent\\OtWebservice\\Domain\\Model\\Dto\\Donor\\Search');
      $this->parseSettings();
    } else {
      $this->search = $search;
    }
    
    $this->search->setOnParentOnly(true);
    
    $donorWS = new DonorService();
    $donors = $donorWS->searchForDonorFede($this->search);

    $this->view->assign('donors', $donors);
  }  

  protected function parseSettings() {
    if (isset($this->settings['structure']['id'])) {        
      $this->search->setOrganizationId((int)$this->settings['structure']['id']);
    }

    if (isset($this->settings['type'])) {
      $type = $this->settings['type'];
      $this->search->setType($type);
    }

    if (isset($this->settings['visibility'])) {
      $visibility = $this->settings['visibility'];
      $this->search->setVisibility($visibility);
    }

    if (isset($this->settings['limit'])) {
      $limit = $this->settings['limit'];
      $this->search->setLimit($limit);
    }

    if (isset($this->settings['orderBy'])) {
        $this->search->setOrderBy($this->settings['orderBy']);
    }    
  }
}

?>
