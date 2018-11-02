<?php

/**
 * Description of IncludeStaticTypoScriptSourcesAtEnd
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\ThemeGallery\Hooks;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * 
 */
class IncludeStaticTypoScriptSources {
  /**
   * 
   * @param array $params
   * @param \TYPO3\CMS\Core\TypoScript\TemplateService $pObj
   * @return NULL
   */
  public function main(array &$params, \TYPO3\CMS\Core\TypoScript\TemplateService &$pObj) {
    $idList = $params['idList'];
    $templateId = $params['templateId'];
    $isPreviewTheme = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('ADMCMD_themeGallery_preview');
    
    $pageid = intval($GLOBALS['TSFE']->id);

    $pageRootId = \Opentalent\ThemeGallery\Helpers\ThemeGalleryUtility::retrieveTheRootPageUid($pageid);

    if ($isPreviewTheme && ($templateId === $idList) && ($params['pid'] == $pageRootId)) {

      $themeName = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('THEMEGALLERY_theme_name');
      $templateId = 'theme_gallery_' . strtolower($themeName);

      $templateRecord = $this->generateTempletRecord($pageRootId,$themeName, TRUE);

      $pObj->processTemplate($templateRecord, $idList . ',' . $templateId, $params['pid'], $templateId, $params['templateId']);
      $pObj->generateConfig();
    } else if (($templateId === $idList) && ($params['pid'] == $pageRootId)) {
            
      $tRow = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('tx_theme_gallery_theme_name', 'pages', 'uid=' . (int) $pageRootId);
      $themeName = $tRow['tx_theme_gallery_theme_name'];
      
      if (empty($themeName)) {
        return;
      }
      
      $templateId = 'theme_gallery_' . strtolower($themeName);
      
      $templateRecord = $this->generateTempletRecord($pageRootId, $themeName);

      $pObj->processTemplate(
              $templateRecord, 
              $idList . ',' . $templateId, 
              $params['pid'], 
              $templateId, 
              $params['templateId']
      );
    }
  }

  protected function generateTempletRecord($rootPid, $themeName, $isPreview = FALSE) {
    /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */  
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');
    $queryBuilder->select('tx_theme_gallery_theme_style')
            ->from('pages')
            ->where($queryBuilder->expr()->eq(
                    'uid',$queryBuilder->createNamedParameter((int)$rootPid, \PDO::PARAM_INT)
                )
            )
            ->setMaxResults(1);
    
    /** @var  \Doctrine\DBAL\Driver\Statement $statement */
    $statement = $queryBuilder->execute();
    $tRow = $statement->fetch();
    
    $contants_theme_style = $tRow ? $tRow['tx_theme_gallery_theme_style'] : '';
    
    $tsSetups = '<INCLUDE_TYPOSCRIPT: source="FILE:fileadmin/theme_gallery/' . $themeName . '/Templates/typoscript/'. ($isPreview ? "preview" : "setup") .'.ts">';
    
    $templateRecord = array(
        'constants' => sprintf('<INCLUDE_TYPOSCRIPT: source="FILE:fileadmin/theme_gallery/%s/Templates/typoscript/constants.ts">', $themeName),
        'config' => $tsSetups,
        'editorcfg' => '',
        'include_static' => '',
        'include_static_file' => '',
        'title' => 'Theme: ' . $themeName,
        'uid' => 'EXT:theme_gallery_:' . $themeName,
    );

    $templateRecord['constants'] .= LF . trim($contants_theme_style) . LF;

    return $templateRecord;
  }

}

?>
