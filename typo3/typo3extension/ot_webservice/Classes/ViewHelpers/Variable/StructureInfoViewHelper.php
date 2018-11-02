<?php

namespace Opentalent\OtWebservice\ViewHelpers\Variable;

use Opentalent\OtWebservice\WebServices\StructureService;

class StructureInfoViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

  /**
   * 
   * @param int $id
   * @return null
   */
  public function render($id) {

    $structure = NULL;

    $structureWS = new StructureService();
    $structure = $structureWS->getStructure($id);    

    $this->templateVariableContainer->add('structureInfo',  $structure);
    
    return NULL;
  }

}

?>
