<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColPosList
 *
 * @author Sébastien Hupin <sebastien.hupin at 2iopenservice.fr>
 */

namespace Opentalent\ThemeGallery\Hooks;

use Opentalent\ThemeGallery\Helpers\ThemeGalleryUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ColPosList {
  
  /**
   * ItemProcFunc for colpos items
   * control the number element allowed for a column and which type is allowed
   * 
   * @param Array $params
   * @param Object $pObj
   */  
  public function itemsProcFunc(&$params, $pObj) {

    $tt_content = $params['row'];
    $CType = $tt_content['CType'];

    $pageId = ThemeGalleryUtility::determinePageId($params['table'], $params['row']);
    $columnDef = $this->getMaxElementAndAllowedItemsPerColumn($pageId);
    $layout = GeneralUtility::callUserFunction('TYPO3\\CMS\\Backend\\View\\BackendLayoutView->getSelectedBackendLayout', intval($pageId), $this);

    if ($layout && $layout['__items']) {
      $params['items'] = $layout['__items'];
    }
    
    // The params item it defined as :
    // an array of array with the index 0 is the column name, 1 is the colPos, 2 is unused
    // $params['items'] = array(0 => array(O=>'column name', 1 => 'colPos', 2=>null), 1=>array(O=>'column name', 1 => 'colPos', 2=>null), ...)

    // For each column found all content.
    foreach ($columnDef as $colPos => $conf) {
      $where = 'pid=' . (int) $pageId . ' AND colPos =' . $colPos . BackendUtility::deleteClause('tt_content');
      $contentCount = ThemeGalleryUtility::getDatabaseConnection()->exec_SELECTcountRows('uid', 'tt_content', $where);
      $colShouldBeRemoved = FALSE;

      $allowed = $conf['allowed'];
      // If there is a specific content defined for this column, we need to check which type can be added to the column.
      if ($allowed !== 'all') {
        $allowed = explode(',', $allowed);
        // If the CType is not found on the allowed list, the new content can not be added to the column.
        if (array_search($CType, $allowed) === FALSE) {
          $colShouldBeRemoved = TRUE;
        }
      }
      
      $maxElement = $conf['maxElement'];

      // We need to check if the limit of max element.
      if ($maxElement !== 'unlimited' && $contentCount >= intval($maxElement)) {
        $colShouldBeRemoved = TRUE;
      }
      
      // We check if the column should be removed or not.
      if ($colShouldBeRemoved) {
        // Found the approriate colPos into params.
        foreach ($params['items'] as $key => $colDef) {
          if ($colDef[1] == $colPos) {
            // Removing the column to the list.
            unset($params['items'][$key]);
          }
        }
      }
    }
  }
  
  /**
   * Return an array of the max and allowed element per column.
   * @param int $pageId
   * @return array
   */
  protected function getMaxElementAndAllowedItemsPerColumn($pageId) {

    $columnDef = array();

    $layoutSetup = GeneralUtility::callUserFunction('TYPO3\\CMS\\Backend\\View\\BackendLayoutView->getSelectedBackendLayout', intval($pageId), $this);
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

              $allowedCTypesByColPos = implode(',', $values);

              $values = 'unlimited';

              if ($col['maxElement'] !== NULL && $col['maxElement'] !== 'unlimited') {
                $maxElement = (int) $col['maxElement'];
                $values = $maxElement;
              }

              $maxElementByColPos = trim($values);

              $columnDef[$col['colPos']] = array(
                  'allowed' => $allowedCTypesByColPos,
                  'maxElement' => $maxElementByColPos
              );
            }
          }
        }
      }
    }

    return $columnDef;
  }  
  
}
