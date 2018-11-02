<?php

/*
  Document   : ext_localconf
  Created on : Nov 9, 2014, 3:36:27 AM
  Author     : Sébastien Hupin <sebastien.hupin at gmail.com>
  Description: Typo3 theme gallery extension
 */

if (!defined('TYPO3_MODE')) {
  die('Access denied.');
}

if ('BE' === TYPO3_MODE) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1488914437] = [
        'nodeName' => 'belayoutwizard',
        'priority' => 50,
        'class' => \Opentalent\ThemeGallery\Wizard\ThemeGalleryBackendLayoutWizardElement::class,
    ];
    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)->registerIcon(
        'opentalent-themegallery-document-color',
         \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
        [
            'name' => 'paint-brush',
            'spinning' => false
        ]
    );
    
    //Xclass of the PageLayoutView to manage limiting elements in column 
    //and displayed unused elements when a backend layout have been switched to a previous one.
//    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\View\\PageLayoutView'] = array(
//     'className' => \Opentalent\ThemeGallery\Xclass\PageLayoutView::class  
//    );
}




$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSourcesAtEnd']['theme_gallery'] = 'Opentalent\\ThemeGallery\\Hooks\\IncludeStaticTypoScriptSources->main';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['BackendLayoutDataProvider']['theme_gallery'] = 'Opentalent\\ThemeGallery\\Provider\\BackendLayoutDataProvider';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['theme_gallery'] = 'Opentalent\\ThemeGallery\\Hooks\\DataHandling\\ProcessCmdmap';

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class)->loadRequireJsModule('TYPO3/CMS/ThemeGallery/Backend/FrameCommunication');