<?php

namespace AppBundle\Services\Exporter\Filter;

use AppBundle\Entity\AccessAndFunction\FunctionType;
use AppBundle\Enum\AccessAndFunction\FunctionEnum;
use AppBundle\Enum\AccessAndFunction\TypeFunctionEnum;
use AppBundle\Enum\Core\AddressPostalTypeEnum;
use AppBundle\Enum\Core\ContactPointTypeEnum;
use AppBundle\Enum\Network\NetworkEnum;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Util\OrganizationFunctionUtils;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use Dunglas\ApiBundle\Exception\InvalidArgumentException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use AppBundle\Services\AccessService;
use AppBundle\Services\Util\Entity as EntityUtil;

/**
 * Description of LicenseFilter
 *
 */
class LicenceCmfFilter implements FilterInterface {
    const FUNCTION_TO_TEST = [
        FunctionEnum::ADHERENT,
        FunctionEnum::STUDENT,
        FunctionEnum::TEACHER
    ];
    const FUNCTIONTYPE_TO_TEST = [
        TypeFunctionEnum::ADMINISTRATIVES_FUNCTION
    ];

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
     * @var OrganizationFunctionUtils
     */
    private $organizationFunctionUtils;
    /**
     *
     * @var IriConverterInterface
     */
    private $iriConverter;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * LicenceCmfFilter constructor.
     * @param AccessService $accessService
     * @param EntityUtil $entityUtil
     */
    public function __construct(AccessService $accessService,
                                EntityUtil $entityUtil,
                                OrganizationFunctionUtils $organizationFunctionUtils,
                                IriConverterInterface $iriConverter,
                                RouterInterface $router) {
        $this->accessService = $accessService;
        $this->entityUtil = $entityUtil;
        $this->organizationFunctionUtils = $organizationFunctionUtils;
        $this->iriConverter = $iriConverter;
        $this->router = $router;
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function filtering(ExportParameters $exportParameters, Array $data) {
        if(array_key_exists('organization', $exportParameters->getMetaData()) && $exportParameters->getMetaData()['organization']){
            $networks = $this->accessService->getOrganization()->getNetwork();
            $isCMF = false;
            foreach($networks as $networkOrganization){
                if(!$isCMF)
                    $isCMF = $networkOrganization->getNetwork()->getId() == NetworkEnum::CMF;
            }
            if(!$isCMF) throw new \RuntimeException('not-a-cmf-organization');

            return [$data, []];
        }

        $accesses = [];
        $errors = [];
        foreach($data as $access){
            $isValid = false;
            $access['function_prio'] = null;
            foreach ($access['organizationFunction'] as $function){
                if(!$isValid && (in_array($function['functionType']['mission'], self::FUNCTION_TO_TEST) ||
                    in_array($function['functionType']['functionType'], self::FUNCTIONTYPE_TO_TEST)))
                {
                    $dateEndToTest = $function['endDate'] ? new \DateTime($function['endDate']) : null;
                    $dateStartToTest = $function['startDate'] ? new \DateTime($function['startDate']) : null;

                    $isValid = $this->organizationFunctionUtils->testValidity($dateEndToTest, $dateStartToTest, false);

                    if($isValid){
                        if(
                                !$access['function_prio']
                            || $access['function_prio'] == FunctionEnum::ADHERENT
                            || $function['functionType']['mission'] == FunctionEnum::TEACHER
                        )
                            $access['function_prio'] = $function['functionType']['mission'];
                    }
                }
            }

            if(!$isValid){
                foreach ($access['personActivity'] as $pa){
                    if(!$isValid)
                    {
                        $dateEndToTest = $pa['endDate'] ? new \DateTime($pa['endDate']) : null;
                        $dateStartToTest = $pa['startDate'] ? new \DateTime($pa['startDate']) : null;

                        $isValid = $this->organizationFunctionUtils->testValidity($dateEndToTest, $dateStartToTest, false);
                    }
                }
            }

            if($isValid){
                $person = $this->getItemFromIri($access['person']['@id']);
                $access['person']['id'] = $person->getId();
                $access['person']['avatar'] = $person->getImage() ? $this->router->generate('opentalent_internal_secure_file_donwload', array('id' => $person->getImage()->getId())) : null;
                $accesses[] = $access;
            }

            else
                $errors[] = $access;
        }

        return [$accesses, $errors];
    }

    /**
     * Retrieves an item from its IRI.
     *
     * @param string $iri
     *
     * @return object|null
     */
    protected function getItemFromIri($iri)
    {
        $item = $this->iriConverter->getItemFromIri($iri, true);
        return $item->getItem();
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function supportsFilter(ExportParameters $exportParameters) {
        return 'licence-cmf' === $exportParameters->getView();
    }
}
