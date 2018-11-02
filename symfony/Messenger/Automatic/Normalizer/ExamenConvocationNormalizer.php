<?php

namespace AppBundle\Services\Messenger\Automatic\Normalizer;

use AppBundle\Enum\Booking\ExamenConvocationStatusEnum;
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
class ExamenConvocationNormalizer implements NormalizerInterface {
    const ACTION = 'ExamenConvocation';

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

        $meta = $parameters->getMetaData();
        $repo = $this->em->getRepository('AppBundle:Booking\ExamenConvocation');
        $sendMessage = false;

        foreach($data as  $examen) {
            $id = $this->getIdFromIri($examen['@id']);

            foreach($meta['dates'][$id] as $dateTime){
                $examen['datetimeStart'] = new \DateTime($dateTime["start"]);
                $examen['datetimeEnd'] = new \DateTime($dateTime["end"]);

                $params['examen'] = $examen;
                $params['contactPoint'] = $originator;
                $params['organization'] = $this->accessService->getAccess()->getOrganization();

                if(!empty($examen['convocation'])){
                    foreach($examen['convocation'] as  $convocation) {
                        $params['convocation'] = $convocation;

                        $id_convocation = $this->getIdFromIri($convocation['@id']);
                        $convocation_obj = $repo->find($id_convocation);
                        $convocation_obj->setStatus(ExamenConvocationStatusEnum::SEND);

                        $contact = $this->accessRespository->find($this->getIdFromIri($convocation['student']['@id']));

                        $params['name'] = $convocation['student']['person']['givenName'] . ' ' . $convocation['student']['person']['name'];
                        $content = $this->templating->render('@template/Examen/convocation.html.twig', $params);

                        $email = new EmailMessage();
                        $email->addContact($contact);

                        $email->setAbout("Convocation à l'examen : ". $params['examen']['name']);

                        $email->setOriginator($originator);
                        $email->setOrganization($this->accessService->getAccess()->getOrganization());
                        $email->setAccess($this->accessService->getAccess());
                        $email->setContent($content);

                        $messages->add($email);

                        $sendMessage = true;
                    }
                }
            }
        }

        if(!$sendMessage)
            throw new \Exception("no_convocation_to_send");

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
