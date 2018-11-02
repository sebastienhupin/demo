<?php

/**
 * Description of ThemeGalleryController
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\Controller;

use Opentalent\OtWebservice\WebServices\StructureService;

class StructureController extends \Opentalent\OtWebservice\Controller\OtWebserviceController {

  /**
   * The search
   * 
   * @var \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search
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
   * Return the detail structure
   */
  public function detailAction() {
    if ($this->request->hasArgument('id')) {
      $id = $this->request->getArgument('id');
    }

    $structure = NULL;
    if (!is_null($id)) {
      $structureWS = new StructureService();
      $structure = $structureWS->getStructure($id);
    }
    // Adding a control on the url to not have a duplicate content.
    if (strpos($structure->urlStr, \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL')) === FALSE) {
      $GLOBALS['TSFE']->additionalHeaderData['ot_webservice'] = '<meta name="robots" content="noindex, nofollow">';
    }
    $this->view->assign('structure', $structure);
  }

  /**
   * Return the form
   * 
   * @param \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search $search
   * @ignorevalidation $search
   */
  public function searchFormAction(\Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search $search = NULL) {

    if (is_null($search)) {
      $this->search = $this->objectManager->get(\Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search::class);
      $this->parseSettings();
    } else {
      $this->search = $search;
    }

    $this->view->assignMultiple(array(
        'ot_webservice' => 'form-structure',
        'search' => $this->search,
    ));
  }

  /**
   * Return the search form result
   * 
   * @param Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search $search
   * @ignorevalidation $search
   */
  public function searchResultAction(\Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search $search = NULL) {

    $structures = NULL;

    if (is_null($search)) {
      $this->search = $this->objectManager->get(\Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search::class);
      $this->parseSettings();
    } else {
      $this->search = $search;
    }

    $structureWS = new StructureService();
    $structures = $structureWS->searchForStructure($this->search);

    $this->search->setPage($this->search->getPage() + $this->search->getLimit());

    $this->view->assignMultiple(array(
        'ot_webservice' => 'structures',
        'structures' => $structures,
        'search' => $this->search,
        'isSearch' => \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('isSearch') || null
    ));
  }

  /**
   * 
   * Display the google map
   * 
   */
  public function googleMapAction() {
    $this->view->assignMultiple(array(
        'ot_webservice' => 'map-structure',
    ));
  }

  protected function parseSettings() {
//var_dump($this->settings);die();
    if (isset($this->settings['structure']['id'])) {
      $id = $this->settings['structure']['id'];
      $this->search->setId($id);
    }

    if (isset($this->settings['showInDirectory'])) {
      $showInDirectory = $this->settings['showInDirectory'];
      $this->search->setShowInDirectory($showInDirectory);
    }    
    
    if (isset($this->settings['what'])) {
      $what = $this->settings['what'];
      $this->search->setWhat($what);
    }

    if (isset($this->settings['where'])) {
      $where = $this->settings['where'];
      $this->search->setWhere($where);
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
