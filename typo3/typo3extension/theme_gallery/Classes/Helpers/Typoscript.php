<?php

/**
 * Description of Typoscript
 *
 * @author Sébastien Hupin <sebastien.hupin at 2iopenservice.fr>
 */

namespace Opentalent\ThemeGallery\Helpers;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Typoscript {

  /**
   * 
   */
  static function getTypoScriptFromFile($file) {

    $typoScriptFileName = GeneralUtility::getFileAbsFileName($file);

    $typoScriptFile = file_get_contents($typoScriptFileName);
    
    $TSparserObject = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\Parser\\TypoScriptParser');
    $TSparserObject->parse($typoScriptFile);
    
    $TypoScriptService = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\TypoScriptService');
    $conf = $TypoScriptService->convertTypoScriptArrayToPlainArray($TSparserObject->setup);

    return $conf;

  }
  /**
   * 
   */
  static function getTypoScriptFromString($string) {

    
    $TSparserObject = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\Parser\\TypoScriptParser');
    $TSparserObject->parse($string);
    
    $TypoScriptService = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\TypoScriptService');
    $conf = $TypoScriptService->convertTypoScriptArrayToPlainArray($TSparserObject->setup);

    return $conf;

  }
}
