<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Utility
 *
 * @author Sébastien Hupin <sebastien.hupin at 2iopenservice.fr>
 */

namespace Opentalent\ThemeGallery\Helpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ThemeGalleryUtility {

  
  /**
   * Retrieve the Id of the root page.
   * @param int $pageUid
   * @return int
   */
  public static function retrieveTheRootPageUid($pageUid) {
    $sysPage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
    
    $page = $sysPage->getPage($pageUid);

    $uid = $page['uid'];

    if (!empty($page) && !$page['is_siteroot']) {
      $uid = self::retrieveTheRootPageUid($page['pid']);
    }

    return (int) $uid;
  }  
  
  
  /**
   * Determines the page id for a given record of a database table.
   *
   * @param string $tableName
   * @param array $data
   * @return NULL|integer
   */
  public static function determinePageId($tableName, array $data) {
    $pageId = NULL;

    if (strpos($data['uid'], 'NEW') === 0) {
      // negative uid_pid values of content elements indicate that the element has been inserted after an existing element
      // so there is no pid to get the backendLayout for and we have to get that first
      if ($data['pid'] < 0) {
        $existingElement = self::getDatabaseConnection()->exec_SELECTgetSingleRow(
                'pid', $tableName, 'uid=' . abs($data['pid'])
        );
        if ($existingElement !== NULL) {
          $pageId = $existingElement['pid'];
        }
      } else {
        $pageId = $data['pid'];
      }
    } elseif ($tableName === 'pages') {
      $pageId = $data['uid'];
    } else {
      $pageId = $data['pid'];
    }

    return $pageId;
  }

  /**
   * @return \TYPO3\CMS\Core\Database\DatabaseConnection
   */
  public static function getDatabaseConnection() {
    return $GLOBALS['TYPO3_DB'];
  }
}
