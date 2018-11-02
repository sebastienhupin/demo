<?php

if (!defined('TYPO3_MODE')) {
  die('Access denied.');
}

if ('BE' === TYPO3_MODE) {


  //$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\NullBackend';
  //$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_classes']['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\NullBackend';

  $TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['themeGallery']['folder'] = 'fileadmin/theme_gallery';

  /**
   * Registers a Backend Module
   * // Module ThemeGallery->View
   */
  \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
          'Opentalent.' . $_EXTKEY, 
          'web', // Make module a submodule of 'web'
          'themegallery', // Submodule key
          '', // Position
          array(
            'ThemeGallery' => 'index, preview, activate, update',
          ), 
          array(
            'access' => 'user,group',
            'icon' => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_themegallery.xml',
          )
  );  
  
  // Add a select box for content can be displayed on any pages or subpages
  $tempColumns = array(
      'tx_theme_gallery_slide' => array(
          'exclude' => true,
          'label' => 'LLL:EXT:theme_gallery/locallang_db.xml:theme_gallery_slide',
          'config' => array(
              'type' => 'select',
              'renderType' => 'selectSingle',
              'items' => array(
                  array('', 0),
                  array('LLL:EXT:theme_gallery/locallang_db.xml:theme_gallery_slide.1', 1),
                  array('LLL:EXT:theme_gallery/locallang_db.xml:theme_gallery_slide.2', 2),
              ),
          ),
      ),
  );

  \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $tempColumns, 1);  
  
  if (isset($TCA['tt_content']['palettes']['visibility'])) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tt_content', 'visibility', 'tx_theme_gallery_slide', 'after:linkToTop');
  } else {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_theme_gallery_slide', '', 'after:linkToTop');
  }

  $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['itemsProcFunc'] = 'Opentalent\\ThemeGallery\\Hooks\\ColPosList->itemsProcFunc';
  
  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook'][] = \Opentalent\ThemeGallery\Hooks\NewContentElementWizardHook::class;  
  
  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] =
          \Opentalent\ThemeGallery\Hooks\PageRenderer::class . '->renderPreProcess';
    
  }

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Theme Gallery');  

