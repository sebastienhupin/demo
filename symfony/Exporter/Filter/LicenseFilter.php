<?php

namespace AppBundle\Services\Exporter\Filter;

use AppBundle\Enum\Core\AddressPostalTypeEnum;
use AppBundle\Enum\Core\ContactPointTypeEnum;
use AppBundle\Model\Export\Parameters as ExportParameters;
use Dunglas\ApiBundle\Exception\InvalidArgumentException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use AppBundle\Services\AccessService;
use AppBundle\Services\Util\Entity as EntityUtil;

/**
 * Description of LicenseFilter
 *
 */
class LicenseFilter implements FilterInterface {
    /**
     *
     * @var AccessService
     */
    private $accessService;
    /**
     *
     * @var EntityUtil
     */
    private $entityUtil;
    /**
     * The constructor
     *
     * @param AccessService $accessService
     * @param RouterInterface $router
     */
    public function __construct(AccessService $accessService, EntityUtil $entityUtil) {
        $this->accessService = $accessService;
        $this->entityUtil = $entityUtil;
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function filtering(ExportParameters $exportParameters, Array $data) {

        $accesses = [];
        foreach($data as $access){

            $accessEdited = [
                'person' => [],
                'organization' => []
            ];

            $accessEdited["person"]["name"] = $access["person"]["name"];
            $accessEdited["person"]["givenName"] = $access["person"]["givenName"];
            $accessEdited["organization"]["name"] = $access["organization"]["name"];

            //keep max one personAddressPostal
            foreach( $access["person"]["personAddressPostal"] as $personAddressPostal){
                if($personAddressPostal["type"] === AddressPostalTypeEnum::ADDRESS_PRINCIPAL){
                    $accessEdited["person"]["personAddressPostal"] = $personAddressPostal;
                }
            }
            //keep max one contactPoint
            foreach( $access["person"]["contactPoint"] as $contactPoint){
                if($contactPoint["contactType"] === ContactPointTypeEnum::PRINCIPAL){
                    $accessEdited["person"]["contactPoint"] = $contactPoint;
                }
            }

            $accessEdited["person"]["email"] = $access["person"]["email"];
            $accessEdited["person"]["birthDate"] = $access["person"]["birthDate"];
            $accessEdited["person"]["gender"] = $access["person"]["gender"];

            $accessEdited["personLicenses"]["licenseType"] = !empty($access["personLicenses"]) ? $access["personLicenses"]["licenseType"]: null;
            $accessEdited["personLicenses"]["licenseRate"] = !empty($access["personLicenses"]) ? $access["licenseRate"] : null;

            $accesses[] = $accessEdited;
        }
        return $accesses;
    }

    /**
     * Get an id from an iri
     *
     * @param string $iri
     * @throws InvalidArgumentException
     *
     * @return integer
     */
    protected function getIdFromIri($iri) {
        try {
            $parameters = $this->router->match($iri);
        } catch (ExceptionInterface $e) {
            throw new InvalidArgumentException(sprintf('No route matches "%s".', $iri), $e->getCode(), $e);
        }

        if (!isset($parameters['id'])) {
            throw new InvalidArgumentException(sprintf('No route matches "%s".', $iri), $e->getCode(), $e);
        }

        return $parameters['id'];
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function supportsFilter(ExportParameters $exportParameters) {
        return 'license' === $exportParameters->getView();
    }
}
