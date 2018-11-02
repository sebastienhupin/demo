<?php

namespace AppBundle\Services\Messenger\Automatic\Normalizer;

use AppBundle\Enum\Organization\CategoryEnum;
use AppBundle\Services\AccessService;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityRepository;
use AppBundle\Services\Util\Entity as EntityUtil;
use AppBundle\Services\Messenger\MessageCollection;
use AppBundle\Services\Messenger\EmailMessage;
use AppBundle\Enum\Core\ContactPointTypeEnum;
use AppBundle\Entity\AccessAndFunction\Access;

/**
 * Description of SharingContactNormalizer
 *
 */
class SharingContactNormalizer implements NormalizerInterface {
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
     *
     * @var array 
     */
    private $frontendUrl;
    
    /**
     * The constructor
     * 
     * @param AccessService $accessService
     */
    public function __construct(AccessService $accessService, EngineInterface $templating, EntityRepository $accessRespository,EntityUtil $entityUtil, array $frontendUrl) {
        $this->accessService = $accessService;
        $this->templating = $templating;
        $this->accessRespository = $accessRespository;
        $this->entityUtil = $entityUtil;
        $this->frontendUrl = $frontendUrl;
    }
    
    /**
     * 
     *  {@inheritdoc}
     */
    public function normalize(\AppBundle\Model\AutomaticMessenger\Parameters $parameters, Array $data) {


        $messages = new MessageCollection();
        $originator = 'no-reply@opentalent.fr';

        foreach($data as $access) {
            $contact = $this->accessRespository->find($this->getIdFromIri($access['@id']));
            $email = $this->createEmail($contact, $originator);

            $messages->add($email);
        }

        return $messages;
    }

    /**
     * @param Access $access
     * @param $originator
     * @return EmailMessage
     */
    private function createEmail(Access $access, $originator) {


        $link = sprintf('%s/%s/%s', $this->getFrontendUrl('sharing_contact'), $access->getPerson()->getConfirmationToken(),$this->accessService->getAccess()->getOrganization()->getId());

        $content = $this->templating->render('@template/account/sharing_contact.html.twig', Array(
            'organization_requester' => $this->accessService->getAccess()->getOrganization(),
            'organization_origin' => $access->getOrganization(),
            'name' => $access->getPerson()->getGivenName(). ' ' . $access->getPerson()->getname(),
            'link' => $link
        ));


        $email = new EmailMessage();                  
        $email->addContact($access);
        $email->setAbout('Demande de partage de vos données personnelles');
        $email->setOriginator($originator);
        $email->setOrganization($this->accessService->getAccess()->getOrganization());
        $email->setAccess($this->accessService->getAccess());
        $email->setContent($content);

        return $email;
    }

    private function getFrontendUrl($key, $default = null) {
        if (isset($this->frontendUrl[$key])) {
            return $this->frontendUrl[$key];
        }

        return $default;        
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
        return ('SharingContact' === $parameters->getAction());
    }

}
