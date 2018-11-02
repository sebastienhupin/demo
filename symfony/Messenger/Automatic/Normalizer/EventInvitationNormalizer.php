<?php

namespace AppBundle\Services\Messenger\Automatic\Normalizer;

use AppBundle\Enum\Message\MessageStatusEnum;
use AppBundle\Services\AccessService;
use Doctrine\ORM\EntityManager;
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
class EventInvitationNormalizer implements NormalizerInterface {
    const ACTION = 'EventInvitation';

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
     * @var
     */
    private $em;

    /**
     * The constructor
     * 
     * @param AccessService $accessService
     */
    public function __construct(
        AccessService $accessService,
        EngineInterface $templating,
        EntityRepository $accessRespository,
        EntityUtil $entityUtil,
        EntityManager $em) {
        $this->accessService = $accessService;
        $this->templating = $templating;
        $this->accessRespository = $accessRespository;
        $this->entityUtil = $entityUtil;
        $this->em = $em;
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function normalize(\AppBundle\Model\AutomaticMessenger\Parameters $parameters, Array $data) {

        if(count($data) === 0){
            throw new \Exception("no_invitations_to_send");
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

        $repo = $this->em->getRepository('AppBundle:Booking\EventUser');
        $meta = $parameters->getMetaData();
        $sendMessage = false;
        foreach($data as  $event) {

            $id = $this->getIdFromIri($event['@id']);
            foreach($meta['dates'][$id] as $dateTime) {
                $event['datetimeStart'] = new \DateTime($dateTime["start"]);
                $event['datetimeEnd'] = new \DateTime($dateTime["end"]);

                $params['event'] = $event;
                $params['reminder'] = array_key_exists('_reminder', $meta) ? $meta['_reminder'] : false;
                $params['organization'] = $this->accessService->getAccess()->getOrganization();
                $params['contactPoint'] = $originator;

                foreach($event['eventUser'] as  $eventUser) {
                    $params['access'] = $eventUser['guest'];

                    $contact = $this->accessRespository->find($this->getIdFromIri($eventUser['guest']['@id']));

                    $params['name'] = $eventUser['guest']['person']['givenName'] . ' ' . $eventUser['guest']['person']['name'];

                    $content = $this->templating->render('@template/Event/EventUserInvitationBase.html.twig', $params);

                    $email = new EmailMessage();
                    $email->addContact($contact);

                    if($eventUser['statusMail'] == MessageStatusEnum::SEND)
                        $email->setAbout("En attente d'une confirmation de votre participation à l'événement : " . $params['event']['name']);
                    else{
                        $email->setAbout("Invitation à l'événement : ". $params['event']['name']);
                        $id_invit = $this->getIdFromIri($eventUser['@id']);
                        $eventUser_obj = $repo->find($id_invit);
                        $eventUser_obj->setStatusMail(MessageStatusEnum::SEND);
                    }

                    $email->setOriginator($originator);
                    $email->setOrganization($this->accessService->getAccess()->getOrganization());
                    $email->setAccess($this->accessService->getAccess());
                    $email->setContent($content);

                    $messages->add($email);

                    $sendMessage = true;
                }
            }
        }

        if(!$sendMessage)
            throw new \Exception("no_invitations_to_send");

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
