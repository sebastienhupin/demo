<?php

/**
 * Description of CategoriesViewHelper
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\ViewHelpers\Format;

class CategoriesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {


    const CATEGORIES = array(
        "1MC" => "Musique",
        "OTCI" => "Cirque",
        "2TH" => "Théâtre & Humour",
        "3DA" => "Danse",
        "OTAR" => "Arts de la rue",
        "5FA" => "Spectacles",
        "6AR" => "Arts & Musées",
        "OTAU" => "Loisirs",
        "8CI" => "Cinéma"
    );        

  /**
   * @var boolean
   */
  protected $escapingInterceptorEnabled = FALSE;

  /**
   * 
   * @param string $categories The categories at parsed
   * @param string $format Format String which is taken to format the Categories like $code$|$name$;$code$|$name$;...
   * @return string Formatted categories
   */
  public function render($categories = NULL, $format=' ') {

    if ($categories === NULL) {
      $categories = $this->renderChildren();
      if ($categories === NULL) {
        return '';
      }
    }
    
    $cats = array_intersect_key(self::CATEGORIES, array_flip(explode(',',$categories)));

    $cat = implode($format, $cats);

    return $cat;
  }

}

?>
