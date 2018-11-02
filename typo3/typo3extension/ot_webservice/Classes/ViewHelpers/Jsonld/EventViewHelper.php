<?php

/**
 * Description of EventViewHelper
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\ViewHelpers\Jsonld;

class EventViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * 
     * @param \stdClass $object The object
     * @param string $property Property should be inspected
     * @return mixed
     */
    public function render($object, $property) {

        if ('ADDRESS_PRINCIPAL' === $property) {
            return $this->extractAddressPrincipal($object);
        }
        else if ('categories' === $property) {
            return $this->extractCategories($object);
        }
        
        return null;
    }
    
    /**
     * Extract the principal address
     * 
     * @param Object $event
     * @return AddressPostal
     */
    private function extractAddressPrincipal($event) {
        foreach ($event->organizationAddressPostal as $organizationAddressPostal) {
            if ('ADDRESS_PRINCIPAL' === $organizationAddressPostal->type) {
                return $organizationAddressPostal->addressPostal;
            }
        }
        return null;
    }
    
    private function extractCategories($event) {
        $categories = Array();
        foreach($event->categories as $category) {
            $categories[] = sprintf("$%s$|$%s$;$%s$|$%s$;$%s$|$%s$",
                $category->familly->code,$category->familly->name,
                $category->subfamilly->code,$category->subfamilly->name,
                $category->gender->code,$category->gender->name    
            );
        }

        return implode('#', $categories);
    }
}

?>
