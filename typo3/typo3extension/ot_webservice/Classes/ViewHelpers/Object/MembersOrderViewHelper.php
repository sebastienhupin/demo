<?php

/**
 * Description of ordered the members list by roles or materials
 *
 * @author Sébastien Hupin <sebastien.hupin at 2iopenservice.fr>
 */

namespace Opentalent\OtWebservice\ViewHelpers\Object;

class MembersOrderViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

  /**
   * 
   * @param array $members
   * @param string $order (material or role)
   * @param string $sort (ASC/DESC or a list of ordered key)
   * 
   * @return array
   */
  public function render(array $members, $order = 'material', $sort = 'ASC') {
    $members_ordered = array();
   
    $sort_ordered_key = array();
    
    if (!('ASC' === strtoupper($sort) || 'DESC' === strtoupper($sort))) {
      $sort_ordered_key = array_map('trim', explode(',', $sort));
    }

    foreach ($members as $member) {
      if ('material' === $order) {
        $mat = strtolower($member->instrument);
          if (!is_array($members_ordered[$mat])) {
            $members_ordered[$mat] = array();
          }
          $members_ordered[$mat][] = $member;
      } else if ('role' === $order) {
        $function = strtolower($member->function);      
        if (empty($sort_ordered_key) || in_array($function, $sort_ordered_key)) {
            if (!is_array($members_ordered[$function])) {
                $members_ordered[$function] = array();
            }  
            $members_ordered[$function][] = $member;
        }
      }
    }

    if ('material' === $order) return $members_ordered;

    $members_ordered = array_merge(array_flip($sort_ordered_key), $members_ordered);
    $members_ordered = array_filter($members_ordered, 'is_array');

    return $members_ordered;
  }
}
