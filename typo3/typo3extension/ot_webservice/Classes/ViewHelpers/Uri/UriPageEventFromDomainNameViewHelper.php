<?php

namespace Opentalent\OtWebservice\ViewHelpers\Uri;

/**
 * A view helper for creating External URIs to extbase actions.
 *
 * = Examples =
 *
 * <code title="URI to the show-action of the current controller">
 * <otws:uri.UriPageEventFromDomainNameViewHelper action="show" />
 * </code>
 * <output>
 * http://opentalent.fr/index.php?id=123&tx_myextension_plugin[action]=show&tx_myextension_plugin[controller]=Standard&cHash=xyz
 * (depending on the current page and your TS configuration)
 * </output>
 */
class UriPageEventFromDomainNameViewHelper extends \Opentalent\OtWebservice\ViewHelpers\Uri\ExternalActionViewHelper {

  /**
   * @param string $domainName The domain name
   * @param string $id The id
   *          
   * @return string Rendered link
   */
  public function render($domainName, $id) {

    $res = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('uid','pages',"
      pid = (
          SELECT uid
          FROM pages
          WHERE 
              pid = (
                  SELECT pages.uid
                  FROM pages
                  INNER JOIN sys_domain ON (sys_domain.pid = pages.uid) AND sys_domain.domainName = '".$domainName."'
              )
          AND tx_opentalent_pagename = 'ON_GOING_SEASON'
      )
      AND tx_opentalent_pagename = 'EVENTS'      
            ");
    

    $pageUid = null;
    if ($res) {
      $pageUid = $res['uid'];
    }

    return parent::render('detail', array('id' => $id), 'Event', NULL, NULL, $pageUid, 0, FALSE, FALSE, '', '', FALSE, array(), FALSE, FALSE, array(), NULL, $domainName, 'http');
  }

}
