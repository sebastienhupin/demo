<?php

/**
 * Description of EventService
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\WebServices;

use Opentalent\OtWebservice\Domain\Model\Dto\ISearch;

/**
 * 
 */
class DonorService extends \Opentalent\OtWebservice\WebServices\OpentalentService {

    /**
     * Service name Donor
     * 
     * @var String 
     */
    protected $name = "donors";

    /**
     * Gets parameters for a collection
     * 
     * @param ISearch $search
     * @return array
     */
    protected function getColletionParams(ISearch $search) {
        $params = parent::getColletionParams($search);

        if ($search->getOnParentOnly()) {
            $params['parent'] = $search->getOnParentOnly();
        }

        if (!empty($search->getOrganizationId())) {
            $params['organizationId'] = (int) $search->getOrganizationId();
        }        

        return $params;
    }

    /**
     * Return a filtered list of donors
     * 
     * @param \Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search $search
     * @return Array
     */
    public function searchForDonor(\Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search $search) {
        $donors = $this->cget($search);        
        return $donors->{'hydra:member'};
    }

    /**
     * Return a filtered list of network donors
     * 
     * @param \Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search $search
     * @return Array
     */
    public function searchForDonorFede(\Opentalent\OtWebservice\Domain\Model\Dto\Donor\Search $search) {
        $donors = $this->cget($search);
        return $donors->{'hydra:member'};
    }

}

?>
