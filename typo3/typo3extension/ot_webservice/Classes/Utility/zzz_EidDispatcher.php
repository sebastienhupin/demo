<?php

namespace Opentalent\OtWebservice\Utility;

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class could called via eID
 *
 * @package TYPO3
 * @subpackage ot_webservice
 */
class EidDispatcher {

  /** @var $objectManager \TYPO3\CMS\Extbase\Object\ObjectManager */
  private $objectManager;

  /**
   * @var \array
   */
  protected $configuration;

  /**
   * @var \array
   */
  protected $bootstrap;

  /**
   *
   * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface 
   */
  protected $configurationManager;

  /**
   *
   * @var \array
   */
  protected $settings;

  /**
   *
   * @var \string
   */
  protected $pluginName;

  /**
   * The main Method
   *
   * @return \string
   */
  public function run() {
    return $this->bootstrap->run('', $this->configuration);
  }

  /**
   * Initialize Extbase
   *
   * @param \array $TYPO3_CONF_VARS
   */
  public function __construct($TYPO3_CONF_VARS) {
    $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);

    $this->bootstrap = new \TYPO3\CMS\Extbase\Core\Bootstrap();

    $feUserObj = \TYPO3\CMS\Frontend\Utility\EidUtility::initFeUser();
    $pid = (GeneralUtility::_GET('id')) ? GeneralUtility::_GET('id') : 1;

    $settings = (GeneralUtility::_GET('tx_otwebservice_pi1')) ? GeneralUtility::_GET('tx_otwebservice_pi1') : NULL;

    $data = GeneralUtility::_GP('request');

    if (is_null($settings)) {
      throw new Exception('You need to set the url to mapping your controller action.');
    }

    $GLOBALS['TSFE'] = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $TYPO3_CONF_VARS, $pid, 0, TRUE);

    $GLOBALS['TSFE']->connectToDB();
    $GLOBALS['TSFE']->fe_user = $feUserObj;
    $GLOBALS['TSFE']->id = $pid;
    $GLOBALS['TSFE']->determineId();
    $GLOBALS['TSFE']->getCompressedTCarray();
    $GLOBALS['TSFE']->initTemplate();
    $GLOBALS['TSFE']->getConfigArray();
    $GLOBALS['TSFE']->includeTCA();

    $this->configuration = array(
        'pluginName' => 'Pi1',
        'vendorName' => 'Opentalent',
        'extensionName' => 'OtWebservice',
        'controller' => $data['controller'],
        'action' => $data['action'],
        'switchableControllerActions' => array(
            $data['controller'] => array($data['action'])
        ),
        'mvc' => array(
            'requestHandlers' => array(
                'TYPO3\CMS\Extbase\Mvc\Web\FrontendRequestHandler' => 'TYPO3\CMS\Extbase\Mvc\Web\FrontendRequestHandler'
            )
        ),
        'settings' => array(),
        'persistence' => array(
        //'storagePid' => $pluginConfiguration['persistence']['storagePid']
        )
    );

    // Add the controller arguments to the global $_GET var.
    /** @var $extensionService \TYPO3\CMS\Extbase\Service\ExtensionService */
    $extensionService = $this->objectManager->get('\\TYPO3\\CMS\\Extbase\\Service\\ExtensionService');
    $pluginNamespace = $extensionService->getPluginNamespace('OtWebservice', 'Pi1');

    GeneralUtility::_GETset($data['arguments'], $pluginNamespace);
  }

}

global $TYPO3_CONF_VARS;
$eid = GeneralUtility::makeInstance('Opentalent\OtWebservice\Utility\EidDispatcher', $TYPO3_CONF_VARS);
echo $eid->run();
?>