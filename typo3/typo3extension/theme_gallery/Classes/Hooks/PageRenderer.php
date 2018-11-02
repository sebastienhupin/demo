<?php

/**
 * Description of PageRenderer
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\ThemeGallery\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class PageRenderer {

  public function renderPreProcess($parameters, $pageRenderer) {
    $this->addJS($parameters, $pageRenderer);
  }

  private function addJS($parameters, $pageRenderer) {
    $allowedCTypesByColPos = array();
    $maxElementByColPos = array();
    
    $layoutSetup = GeneralUtility::callUserFunction('TYPO3\\CMS\\Backend\\View\\BackendLayoutView->getSelectedBackendLayout', intval(GeneralUtility::_GP('id')), $this);
    if (is_array($layoutSetup) && !empty($layoutSetup['__config']['backend_layout.']['rows.'])) {
      foreach ($layoutSetup['__config']['backend_layout.']['rows.'] as $rows) {
        foreach ($rows as $row) {
          if (!empty($layoutSetup['__config']['backend_layout.']['rows.'])) {
            foreach ($row as $col) {
              $values = Array();
              // Add allowed cType
              if ($col['allowed']) {
                $allowed = explode(',', $col['allowed']);
                foreach ($allowed as $ctype) {
                  $ctype = trim($ctype);
                  if ($ctype === '*') {
                    $values[] = 'all';
                    break;
                  } else {
                    $values[] = $ctype;
                  }
                }
              } else {
                $values[] = 'all';
              }
              $allowedCTypesByColPos[$col['colPos']] = $values;

              $values = 'unlimited';

              if ($col['maxElement'] !== NULL && $col['maxElement'] !== 'unlimited') {
                $maxElement = (int) $col['maxElement'];
                $values = $maxElement;
              }
              
              $maxElementByColPos[$col['colPos']] = trim($values);
            }
          }
        }
      }
    }

    $pageRenderer->loadRequireJsModule(
        'TYPO3/CMS/ThemeGallery/Backend/PageRenderManager',
        'function(PageRenderManager) {
            PageRenderManager.setPageColumnsAllowedCTypes('.json_encode($allowedCTypesByColPos).');
            PageRenderManager.setPageColumnsMaxElement('. json_encode($maxElementByColPos).');
        }'
    );
  }

}

?>
