<?php

/**
 * Description of ArrayAccess
 *
 * @author Sébastien Hupin <sebastien.hupin at 2iopenservice.fr>
 */

namespace Opentalent\OtWebservice\ViewHelpers\Object;

class ArrayAccessViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

  /**
   * 
   * @param integer $index
   * @param array $array
   * @param boolean $previous
   * 
   * @return mixed (array or NULL)
   */
  public function render($index, array $array, $previous = FALSE) {
    return $previous ? (isset($array[$index-1]) ? $array[$index-1] : NULL) : $array[$index];
  }

}
