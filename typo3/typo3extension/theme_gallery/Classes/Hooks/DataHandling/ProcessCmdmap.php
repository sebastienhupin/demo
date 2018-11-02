<?php

namespace Opentalent\ThemeGallery\Hooks\DataHandling;

/**
 * Hook on Core/DataHandling/DataHandler to manage Process Cmdmap
 */
class ProcessCmdmap {

  var $debug = TRUE;

  /*   * ******************************************
   *
   * Public API (called by hook handler)
   *
   * ****************************************** */

  /**
   * This function is called by TCEmain before a new record has been inserted into the database.
   * 
   * @param \String $status The status new,update,...
   * @param \String $table The Table
   * @param \Integer $id The id
   * @param \Array $fieldArray The fields to be saved
   * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler The DataHandler
   */
  public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $dataHandler) {
    if ($this->debug) {
      \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('processDatamap_postProcessFieldArray', 'opentalent', 0, array($status, $table, $id, $fieldArray));
    }

    // Setting the backend_layout to theme_gallery__page for standard page (doktype=1)
    if ($table == 'pages' && $status == 'new' && $fieldArray['doktype'] == 1) {
      $fieldArray['backend_layout'] = 'theme_gallery__page';
    }
  }

}

?>
