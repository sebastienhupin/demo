<?php

namespace Opentalent\ThemeGallery\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use Opentalent\ThemeGallery\Helpers\Typoscript;

/**
 * Description of ThemeGalleryController
 *
 * @author sebastienhupin
 */
class ThemeGalleryController extends ActionController {
    /**
     *
     * @var int 
     */
    protected  $pageRootId;
    /**
     *
     * @var string 
     */
    protected $themesGalleryPath;
    /**
     * @var string
     */
    protected $themeName;
    /**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;
    /**
     * @var BackendTemplateView
     */
    protected $view;
    
    /**
     * 
     */
    public function __construct() {
        $this->themesGalleryPath = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['theme_gallery']['themeGallery']['folder'];
        $this->pageRootId = (int) GeneralUtility::_GP('id');
    }    
    
    /**
     * Index action
     */
    public function indexAction() {
        $pageIdToShow = (int) GeneralUtility::_GP('id');

        $sysPage = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');

        // Get the current page
        $page = $sysPage->getPage($pageIdToShow);

        // If the current page is not a root page, nothing to do.
        if (!$page['is_siteroot']) {
            $this->view->assign('notavailable', 1);
            return;
        }

        // get all sub folders in the themes folder
        $themes = $this->getThemes();

        $this->view->assign('themes', $themes);        
    }
    
    /**
     * Activate action
     */
    public function activateAction() {
        $themeConfig = $this->request->getArgument('themeConfig');
        $this->themeName = $themeConfig['theme']['name'];
        $themeConfig = array_merge($themeConfig, $this->getThemeConfig());

        $this->save($themeConfig);
                       
        $this->preview();
    }

    /**
     * Preview action
     */
    public function previewAction() {
        $themeConfig = $this->request->getArgument('themeConfig');
        $this->themeName = $themeConfig['theme']['name'];
        $this->preview();
    }    
    
    /**
     * Update action
     */
    public function updateAction() {
        $themeConfig = $this->request->getArgument('themeConfig');
        $this->themeName = $themeConfig['theme']['name'];

        $this->save($themeConfig);
        $this->preview();
    }    
    
    /**
     * Save the theme for the current page root
     * 
     * @param array $themeConfig
     */
    protected function save(Array $themeConfig) {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages'); 
        $queryBuilder->update('pages')
            ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int)$this->pageRootId, \PDO::PARAM_INT))
            )
            ->set('tx_theme_gallery_theme_name', $themeConfig['theme']['name'])
            ->set('tx_theme_gallery_theme_style', $this->getThemeStyle($themeConfig))
            ->execute()    
        ;    
        // Clear the page cache
        $this->cacheService->clearPageCache();         
    }
    
    /**
     * Get the theme style from the theme configuration
     * 
     * @param array $themeConfig
     * @return string
     */
    protected function getThemeStyle(Array $themeConfig) {
        $themStyle = "";

        foreach ($themeConfig['theme']['colors'] as $name => $value) {
            $themStyle .= sprintf("%s=%s\n", $name, $value);
        }
        
        return $themStyle;        
    }

    /**
     * Gets the theme configuration
     * 
     * @return Array
     */
    protected function getThemeConfig() {
        // Load the the configuration.
        $themeConfig = Typoscript::getTypoScriptFromFile('fileadmin/theme_gallery/' . $this->themeName . '/config.ts');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */  
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('pages');
        $queryBuilder->select('tx_theme_gallery_theme_style')
                ->from('pages')
                ->where($queryBuilder->expr()->eq(
                        'uid',$queryBuilder->createNamedParameter((int)$this->pageRootId, \PDO::PARAM_INT)
                    )
                )
                ->setMaxResults(1);

        /** @var  \Doctrine\DBAL\Driver\Statement $statement */
        $statement = $queryBuilder->execute();
        $tRow = $statement->fetch();

        $contants_theme_style = $tRow ? $tRow['tx_theme_gallery_theme_style'] : '';
        $contants_theme_style = Typoscript::getTypoScriptFromString($contants_theme_style);
        $themeConfig['theme']['colors'] = array_merge($themeConfig['theme']['colors'], $contants_theme_style);

        return $themeConfig;
    }
    
    /**
     * Set preview view
     * 
     */
    protected function preview() {
        $GLOBALS['LANG']->includeLLFile('EXT:theme_gallery/Resources/Private/Language/locallang.xlf');
        $moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
        $pageRenderer = $moduleTemplate->getPageRenderer();
        $pageRenderer->addInlineSettingArray('web_view', array(
            'States' => $GLOBALS['BE_USER']->uc['moduleData']['web_view']['States'],
        ));

        $pageRenderer->addInlineLanguageLabelFile('EXT:theme_gallery/Resources/Private/Language/locallang.xlf');

        $themeConfig = $this->getThemeConfig();

        $this->view->assignMultiple(
                array(
                    'widths' => $this->getPreviewFrameWidths(),
                    'url' => $this->getTargetUrl(),
                    'theme' => $themeConfig['theme'],
                    'languages' => $this->getPreviewLanguages()
                )
        );        
    }
    
    /**
     * Clear cache 
     * 
     * @param int $id
     */
    protected function clearCache(int $id) {
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->start([], []);
            $dataHandler->clear_cacheCmd('pages');        
    }

    /**
     * Gets all themes
     * 
     * @return Array
     */
    protected function getThemes() {
        $themes = array();
        $path = PATH_site . $this->themesGalleryPath;
        if (is_dir($path)) {
            $dir = scandir($path);
            foreach ($dir as $entry) {
                $fullPath = $path . '/' . $entry;
                if (is_dir($fullPath) && $entry != '..' && $entry != '.' && $entry != 'nbproject') {
                    $properties = $this->getThemeProperties($entry, $path);
                    $themes[$entry] = $properties;
                }
            }
        }

        return $themes;
    }    
    
    /**
     * Gets theme properties
     * 
     * @param String $name
     * @param String $path
     * @return Array
     */
    protected function getThemeProperties($name, $path) {
        $properties = null;
        if (is_dir($path)) {
            // Load the locallang
            //$GLOBALS['LANG']->includeLLFile(PATH_site . $this->themesGalleryPath . '/' . $name .'/locallang.xlf');

            $properties = array(
                'name' => $name,
                'path' => $path,
                'screenshot' => 'screenshot.png'
            );
        }
        return $properties;
    }    
    
    /**
     * Get available widths for preview frame
     *
     * @return array
     */
    protected function getPreviewFrameWidths() {
        $pageId = (int) GeneralUtility::_GP('id');
        $modTSconfig = BackendUtility::getModTSconfig($pageId, 'mod.web_view');
        $widths = [
            '100%|100%' => $this->getLanguageService()->getLL('autoSize')
        ];
        if (is_array($modTSconfig['properties']['previewFrameWidths.'])) {
            foreach ($modTSconfig['properties']['previewFrameWidths.'] as $item => $conf) {
                $label = '';

                $width = substr($item, 0, -1);
                $data = ['width' => $width];
                $label .= $width . 'px ';

                //if height is set
                if (isset($conf['height'])) {
                    $label .= ' × ' . $conf['height'] . 'px ';
                    $data['height'] = $conf['height'];
                }

                if (substr($conf['label'], 0, 4) !== 'LLL:') {
                    $label .= $conf['label'];
                } else {
                    $label .= $this->getLanguageService()->sL(trim($conf['label']));
                }
                $value = ($data['width'] ?: '100%') . '|' . ($data['height'] ?: '100%');
                $widths[$value] = $label;
            }
        }
        return $widths;
    }    
        
    /**
     * Determine the url to view
     *
     * @return string
     */
    protected function getTargetUrl() {
        $pageIdToShow = (int) GeneralUtility::_GP('id');

        $permissionClause = $this->getBackendUser()->getPagePermsClause(1);
        $pageRecord = BackendUtility::readPageAccess($pageIdToShow, $permissionClause);
        if ($pageRecord) {
            $this->view->getModuleTemplate()->getDocHeaderComponent()->setMetaInformation($pageRecord);
            
            $adminCommand = $this->getAdminCommand($pageIdToShow);
            $domainName = $this->getDomainName($pageIdToShow);
            $languageParameter = $this->getLanguageParameter();
            // Mount point overlay: Set new target page id and mp parameter
            /** @var \TYPO3\CMS\Frontend\Page\PageRepository $sysPage */
            $sysPage = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
            $sysPage->init(false);
            $mountPointMpParameter = '';
            $finalPageIdToShow = $pageIdToShow;
            $mountPointInformation = $sysPage->getMountPointInfo($pageIdToShow);
            if ($mountPointInformation && $mountPointInformation['overlay']) {
                // New page id
                $finalPageIdToShow = $mountPointInformation['mount_pid'];
                $mountPointMpParameter = '&MP=' . $mountPointInformation['MPvar'];
            }
            // Modify relative path to protocol with host if domain record is given
            $protocolAndHost = '..';
            if ($domainName) {
                // TCEMAIN.previewDomain can contain the protocol, check prevents double protocol URLs
                if (strpos($domainName, '://') !== false) {
                    $protocolAndHost = $domainName;
                } else {
                    $protocol = GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https' : 'http';
                    $protocolAndHost = $protocol . '://' . $domainName;
                }
            }
            return $protocolAndHost . '/index.php?no_cache=1&id=' . $finalPageIdToShow . $this->getTypeParameterIfSet($finalPageIdToShow) . $mountPointMpParameter . $adminCommand . $languageParameter;
        }
        return '#';
    }    
    
    /**
     * Get admin command
     *
     * @param integer $pageId
     * @return string
     */
    protected function getAdminCommand($pageId) {
        // The page will show only if there is a valid page and if this page may be viewed by the user
        $pageinfo = BackendUtility::readPageAccess($pageId, $GLOBALS['BE_USER']->getPagePermsClause(1));
        $addCommand = '';
        if (is_array($pageinfo)) {
            $addCommand = '&no_cache=1&ADMCMD_themeGallery_preview=1&THEMEGALLERY_theme_name=' . $this->themeName;
        }
        return $addCommand;
    }
    
    /**
     * Get domain name for requested page id
     *
     * @param int $pageId
     * @return string|NULL Domain name from first sys_domains-Record or from TCEMAIN.previewDomain, NULL if neither is configured
     */
    protected function getDomainName($pageId) {
        $previewDomainConfig = $this->getBackendUser()->getTSConfig('TCEMAIN.previewDomain', BackendUtility::getPagesTSconfig($pageId));
        if ($previewDomainConfig['value']) {
            $domain = $previewDomainConfig['value'];
        } else {
            $domain = BackendUtility::firstDomainRecord(BackendUtility::BEgetRootLine($pageId));
        }
        return $domain;
    }

    /**
     * Gets the L parameter from the user session
     *
     * @return string
     */
    protected function getLanguageParameter() {
        $states = $this->getBackendUser()->uc['moduleData']['web_view']['States'];
        $languages = $this->getPreviewLanguages();
        $languageParameter = '';
        if (isset($states['languageSelectorValue']) && isset($languages[$states['languageSelectorValue']])) {
            $languageParameter = '&L=' . (int) $states['languageSelectorValue'];
        }
        return $languageParameter;
    }
    
    /**
     * Returns the preview languages
     *
     * @return array
     */
    protected function getPreviewLanguages() {
        $pageIdToShow = (int) GeneralUtility::_GP('id');
        $modSharedTSconfig = BackendUtility::getModTSconfig($pageIdToShow, 'mod.SHARED');
        if ($modSharedTSconfig['properties']['view.']['disableLanguageSelector'] === '1') {
            return [];
        }
        $languages = [
            0 => isset($modSharedTSconfig['properties']['defaultLanguageLabel']) ? $modSharedTSconfig['properties']['defaultLanguageLabel'] . ' (' . $this->getLanguageService()->sL('LLL:EXT:lang/Resources/Private/Language/locallang_mod_web_list.xlf:defaultLanguage') . ')' : $this->getLanguageService()->sL('LLL:EXT:lang/Resources/Private/Language/locallang_mod_web_list.xlf:defaultLanguage')
        ];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_language');
        $queryBuilder->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        if (!$this->getBackendUser()->isAdmin()) {
            $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(HiddenRestriction::class));
        }

        $result = $queryBuilder->select('sys_language.uid', 'sys_language.title')
                ->from('sys_language')
                ->join(
                        'sys_language', 'pages_language_overlay', 'o', $queryBuilder->expr()->eq('o.sys_language_uid', $queryBuilder->quoteIdentifier('sys_language.uid'))
                )
                ->where(
                        $queryBuilder->expr()->eq(
                                'o.pid', $queryBuilder->createNamedParameter($pageIdToShow, \PDO::PARAM_INT)
                        )
                )
                ->groupBy('sys_language.uid', 'sys_language.title', 'sys_language.sorting')
                ->orderBy('sys_language.sorting')
                ->execute();

        while ($row = $result->fetch()) {
            if ($this->getBackendUser()->checkLanguageAccess($row['uid'])) {
                $languages[$row['uid']] = $row['title'];
            }
        }
        return $languages;
    }    
    
    /**
     * With page TS config it is possible to force a specific type id via mod.web_view.type
     * for a page id or a page tree.
     * The method checks if a type is set for the given id and returns the additional GET string.
     *
     * @param int $pageId
     * @return string
     */
    protected function getTypeParameterIfSet($pageId) {
        $typeParameter = '';
        $modTSconfig = BackendUtility::getModTSconfig($pageId, 'mod.web_view');
        $typeId = (int) $modTSconfig['properties']['type'];
        if ($typeId > 0) {
            $typeParameter = '&type=' . $typeId;
        }
        return $typeParameter;
    }    
    
    /**
     * Set up the doc header properly here
     *
     * @param ViewInterface $view
     * @return void
     */
    protected function initializeView(ViewInterface $view) {
        /** @var BackendTemplateView $view */
        parent::initializeView($view);

        if ($view instanceof BackendTemplateView && $this->actionMethodName == 'activateAction') {
            $this->registerDocheaderButtons();
            $this->view->getModuleTemplate()->setFlashMessageQueue($this->controllerContext->getFlashMessageQueue());
            $update = Array();
            $this->view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule(
                'TYPO3/CMS/ThemeGallery/Backend/ThemeGallery'                    
            );                     
        }
    }

    /**
     * Registers the Icons into the docheader
     *
     * @throws \InvalidArgumentException
     */
    protected function registerDocHeaderButtons() {
        /** @var ButtonBar $buttonBar */
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();
        $lang = $this->getLanguageService();
        
        $colorButton = $buttonBar->makeLinkButton()
                ->setHref('#')
                //->setOnClick('ThemeGallery.openColor();return false;')
                ->setClasses('t3js-themegallery-open-color')
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:labels.showPage'))
                ->setIcon($this->view->getModuleTemplate()->getIconFactory()->getIcon('opentalent-themegallery-document-color', Icon::SIZE_SMALL));
        
        $buttonBar->addButton($colorButton);        
        
        // SAVE button:
        $saveButton = $buttonBar->makeInputButton()
                ->setTitle($lang->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:rm.saveDoc'))
                ->setName('_savetheme')
                ->setValue('Save')
                ->setForm('ThemeGalleryControllerActivate')
                ->setIcon($this->view->getModuleTemplate()->getIconFactory()->getIcon(
                                'actions-document-save', Icon::SIZE_SMALL
                ))
                ->setShowLabelText(true);

        $buttonBar->addButton($saveButton);
    }

    /**
     * Returns LanguageService
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService() {
        return $GLOBALS['LANG'];
    }
    
    /**
     * Gets the backend user
     * 
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser() {
        return $GLOBALS['BE_USER'];
    }
}
