<?php

/**
 * Description of StructureService
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\WebServices;

use Opentalent\OtWebservice\Domain\Model\Dto\ISearch;

/**
 * 
 */
class StructureService extends \Opentalent\OtWebservice\WebServices\OpentalentService {

    /**
     * Service name Structure
     * 
     * @var String 
     */
    protected $name = "organizations";

    /**
     * Return a filtered list structure
     * 
     * @param \Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search $search
     * @return Array
     */
    public function searchForStructure(\Opentalent\OtWebservice\Domain\Model\Dto\Structure\Search $search) {
        $structures = $this->cget($search);

        return $structures->{'hydra:member'};
        ;
    }

    /**
     * Return a structure
     * 
     * @param Integer $id
     * @return Object
     */
    public function getStructure($id) {
        $structure = $this->get($id);

        return $structure;
    }

    /**
     * Gets parameters for a collection
     * 
     * @param ISearch $search
     * @return array
     */
    protected function getColletionParams(ISearch $search) {        
        $params = parent::getColletionParams($search);

        if (null !== $search->latitude && null !== $search->longitude) {
            $params['latitude'] = $search->latitude;
            $params['longitude'] = $search->longitude;
        }
        if (!empty($search->where)) {
            $params['where'] = $search->where; 
        }
        if (!empty($search->what)) {
            $params['what'] = $search->what; 
        }
        
        return $params;
    }
}
