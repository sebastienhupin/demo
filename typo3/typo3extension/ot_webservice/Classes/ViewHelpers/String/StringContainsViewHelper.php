<?php

/**
 * Description of CategoriesViewHelper
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\ViewHelpers\String;

class StringContainsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

  /**
   * Check if the haystack string contains the needle string
   * 
   * @param string $haystack The string
   * @param string $needle The string to search
   * 
   * @return boolean 
   */
  public function render($haystack='', $needle='') {
    return (strpos($haystack, $needle) === FALSE) ? FALSE : TRUE;
  }
}

?>
