<?php

/**
 * Description of NewContentElementWizardHookInterface
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\ThemeGallery\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class NewContentElementWizardHook implements \TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface {

  public function manipulateWizardItems(&$wizardItems, &$parentObject) {
    //if (!GeneralUtility::inList($GLOBALS['BE_USER']->groupData['explicit_allowdeny'], 'tt_content:CType:theme_gallery_pi1:DENY')) {
    $allowed_GP = GeneralUtility::_GP('tx_theme_gallery_allowed');
    if (!empty($allowed_GP)) {
      $allowed = array_flip(explode(',', $allowed_GP));
      $this->removeDisallowedWizardItems($allowed, $wizardItems);
      $this->removeEmptyHeadersFromWizard($wizardItems);
    }
    //}
  }

  /**
   * remove disallowed content elements from wizard items
   *
   * @param array $allowed
   * @param array $wizardItems
   *
   * @return void
   */
  public function removeDisallowedWizardItems($allowed, &$wizardItems) {
    if (!isset($allowed['*'])) {
      foreach ($wizardItems as $key => $wizardItem) {
        if (!$wizardItems[$key]['header']) {
          if (count($allowed) && !isset($allowed[$wizardItems[$key]['tt_content_defValues']['CType']])) {
            unset($wizardItems[$key]);
          }
        }
      }
    }
  }

  /**
   * remove unneccessary headers from wizard items
   *
   * @param array $wizardItems
   *
   * @return void
   */
  public function removeEmptyHeadersFromWizard(&$wizardItems) {
    $headersWithElements = array();
    foreach ($wizardItems as $key => $wizardItem) {
      $isElement = strpos($key, '_', 1);
      if ($isElement) {
        $headersWithElements[] = substr($key, 0, $isElement);
      }
    }
    foreach ($wizardItems as $key => $wizardItem) {
      if ($wizardItems[$key]['header']) {
        if (!in_array($key, $headersWithElements)) {
          unset($wizardItems[$key]);
        }
      }
    }
  }

}
