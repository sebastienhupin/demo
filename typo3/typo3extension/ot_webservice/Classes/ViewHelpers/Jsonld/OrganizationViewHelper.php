<?php

/**
 * Description of OrganizationViewHelper
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\ViewHelpers\Jsonld;

class OrganizationViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {


  /**
   * 
   * @param \stdClass $object The object
   * @param string $property Property should be inspected
   * @return mixed
   */
  public function render($object, $property) {
    
    if ('ADDRESS_PRINCIPAL' === $property) {
        foreach($object->organizationAddressPostal as $organizationAddressPostal) {
            if ('ADDRESS_PRINCIPAL' === $organizationAddressPostal->type) {
                return $organizationAddressPostal->addressPostal;
            }
        }
    }

    return null;
  }

}
