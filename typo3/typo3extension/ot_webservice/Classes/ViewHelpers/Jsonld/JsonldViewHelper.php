<?php

/**
 * Description of JsonldViewHelper
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\ViewHelpers\Jsonld;

class JsonldViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

  /**
   * 
   * @param \stdClass $object The object
   * @param string $property Property should be inspected
   * @return mixed
   */
  public function render(\stdClass $object, $property='id') {
    $value = NULL;

    if ('id' === $property) {
        $value = end(explode('/',$object->{'@id'}));
    }

    if (is_array($object->$property)) {
        $value = implode(' ', $object->$property);
    }
    
    return $value;
  }

}

?>
