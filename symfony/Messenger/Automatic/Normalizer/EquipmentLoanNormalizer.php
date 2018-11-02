<?php

namespace AppBundle\Services\Messenger\Automatic\Normalizer;

use AppBundle\Services\AccessService;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityRepository;
use AppBundle\Services\Util\Entity as EntityUtil;
use AppBundle\Services\Messenger\MessageCollection;
use AppBundle\Services\Messenger\EmailMessage;
use AppBundle\Enum\Core\ContactPointTypeEnum;

/**
 * Description of EquipmentLoanNormalizer
 *
 * @author sebastienhupin
 */
class EquipmentLoanNormalizer implements NormalizerInterface {
    const ACTION = 'EquipmentLoanReminder';

    /**
     *
     * @var AccessService 
     */
    private $accessService;
    /**
     *
     * @var EngineInterface 
     */
    private $templating;
    /**
     *
     * @var EntityRepository 
     */
    private $accessRespository;
    /**
     *
     * @var EntityUtil 
     */
    private $entityUtil;    
    /**
     * The constructor
     * 
     * @param AccessService $accessService
     */
    public function __construct(AccessService $accessService, EngineInterface $templating, EntityRepository $accessRespository, EntityUtil $entityUtil) {
        $this->accessService = $accessService;
        $this->templating = $templating;
        $this->accessRespository = $accessRespository;
        $this->entityUtil = $entityUtil;
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function normalize(\AppBundle\Model\AutomaticMessenger\Parameters $parameters, Array $data) {

        if(count($data) === 0){
            throw new \RuntimeException('no-equipment-loan-reminder');
        }

        $messages = new MessageCollection();
        $originator = null;
        
        if ($this->accessService->getAccess()->getOrganization()->getContactPointForType(ContactPointTypeEnum::PRINCIPAL)) {
            $originator = $this->accessService->getAccess()->getOrganization()->getContactPointForType(ContactPointTypeEnum::PRINCIPAL)->getEmail();
        } else {
            $originator = $this->accessService->getAccess()->getPerson()->getEmail();
        }
        
        if (empty($originator)) {
            throw new \Exception("No originator found");
        }

        foreach($data as $equipmentRent) {
            $contact = $this->accessRespository->find($this->getIdFromIri($equipmentRent['borrower']['@id']));

            $content = $this->templating->render(sprintf('reminder_%s.html.twig', strtolower($parameters->getResourceName())), Array(
                'organization' => $this->accessService->getAccess()->getOrganization(),
                'loggedInUser' => $this->accessService->getAccess(),
                'borrower' => $contact,
                'data' => $equipmentRent,
                'name' => $contact->getPerson()->getGivenName(). ' ' . $contact->getPerson()->getName()
            ));
            $email = new EmailMessage();
            $email->addContact($contact);
            $email->setAbout('Relance des emprunts/locations');
            $email->setOriginator($originator);
            $email->setOrganization($this->accessService->getAccess()->getOrganization());
            $email->setAccess($this->accessService->getAccess());
            $email->setContent($content);
            
            $messages->add($email);               
        }

        return $messages;
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
            return $this->entityUtil->getIdFromIri($iri);
        }
        catch(\Exception $e) {
            return null;
        }
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function supportsNormalization(\AppBundle\Model\AutomaticMessenger\Parameters $parameters) {
        return self::ACTION === $parameters->getAction();
    }

}
