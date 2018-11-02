<?php

/**
 * Description of BackendLayoutDataProvider
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\ThemeGallery\Provider;

use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayoutCollection;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderContext;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendLayoutDataProvider implements DataProviderInterface {

  const FILE_TYPES_LAYOUT = 'ts,txt';
  const FILE_TYPES_ICON = 'png,gif,jpg';
  const FILE_TYPES_TRANSLATION = 'xlf,xml';

  protected $themesGalleryPath;
  protected $themeName;

  public function __construct() {
    $this->themesGalleryPath = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['theme_gallery']['themeGallery']['folder'];    
  }

  /**
   * Adds backend layouts to the given backend layout collection.
   *
   * @param DataProviderContext $dataProviderContext
   * @param BackendLayoutCollection $backendLayoutCollection
   * @return void
   */
  public function addBackendLayouts(DataProviderContext $dataProviderContext, BackendLayoutCollection $backendLayoutCollection) {
    $this->themeName = $this->retrieveThemeName($dataProviderContext->getPageId());

    $files = $this->getLayoutFiles();
    foreach ($files as $file) {
      $backendLayout = $this->createBackendLayout($file);      
      $backendLayoutCollection->add($backendLayout);
    }

  }

  /**
   * Gets a backend layout by (regular) identifier.
   *
   * @param string $identifier
   * @param integer $pageUid
   * @return void|BackendLayout
   */
  public function getBackendLayout($identifier, $pageUid) {
    
    // Get the current theme name
    $this->themeName = $this->retrieveThemeName($pageUid);
    
    $files = $this->getLayoutFiles();
    
    if (array_key_exists($identifier, $files)) {
      return $this->createBackendLayout($files[$identifier]);
    }
    else {
      return $this->createBackendLayout($files['page']);
    }
    
  }
  
  /**
   * Retrieve the theme name,iterate until to found the root page.
   * @param int $pageUid
   * @return string
   */
  protected function retrieveThemeName($pageUid) {
    $sysPage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
    
    $page = $sysPage->getPage($pageUid);

    $themeName = $page['tx_theme_gallery_theme_name'];
    
    if (!$page['is_siteroot']) {
      $themeName = $this->retrieveThemeName($page['pid']);
    }

    return $themeName;
  }

  /**
   * Creates a new backend layout using the given record data.
   *
   * @param string $file
   * @return BackendLayout
   */
  protected function createBackendLayout($file) {
    $fileInformation = pathinfo($file);
    $backendLayout = BackendLayout::create(
                    $fileInformation['filename'], $this->getTitle($fileInformation), GeneralUtility::getUrl($file)
    );

    return $backendLayout;
  }

  /**
   * Get all files
   *
   * @return array
   * @throws \UnexpectedValueException
   */
  protected function getLayoutFiles() {
    $fileCollection = array();
    $directory = $this->themesGalleryPath . DIRECTORY_SEPARATOR . $this->themeName . DIRECTORY_SEPARATOR . 'BackendLayout';

    $directory = GeneralUtility::getFileAbsFileName($directory);
    $filesOfDirectory = GeneralUtility::getFilesInDir($directory, self::FILE_TYPES_LAYOUT, TRUE, 1);
    foreach ($filesOfDirectory as $file) {
      $this->addFileToCollection($file, $fileCollection);
    }
    return $fileCollection;
  }

  /**
   * @param array $fileInformation pathinfo() information of the given file
   * @return string
   */
  protected function getTitle(array $fileInformation) {
    $title = $fileInformation['filename'];
    $translationFileEndings = explode(',', self::FILE_TYPES_TRANSLATION);
    $filePath = $fileInformation['dirname'] . '/locallang.';
    foreach ($translationFileEndings as $extension) {
      $file = $filePath . $extension;
      if (is_file($file)) {
        $file = str_replace(PATH_site, '', $file);
        $translatedTitle = $GLOBALS['LANG']->sL('LLL:' . $file . ':' . $fileInformation['filename']);
        if ($translatedTitle) {
          $title = $translatedTitle;
          break;
        }
      }
    }
    return $title;
  }

  /**
   * @param $file
   * @param $fileCollection
   * @return array
   * @throws \UnexpectedValueException
   */
  protected function addFileToCollection($file, array &$fileCollection) {
    //$key = sha1($file);
    $fileInformation = pathinfo($file);
    $key = $fileInformation['filename'];
    if (isset($fileCollection[$key])) {
      throw new \UnexpectedValueException(sprintf('The file "%s" exists already, see "%s"', $file, $fileCollection[$key]));
    }
    $fileCollection[$key] = $file;
  }

}

?>
