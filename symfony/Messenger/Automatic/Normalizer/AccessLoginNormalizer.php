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
 * Description of AccessLoginNormalizer
 *
 * @author sebastienhupin
 */
class AccessLoginNormalizer implements NormalizerInterface {
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
    public function __construct(AccessService $accessService, EngineInterface $templating, EntityRepository $accessRespository, EntityUtil $entityUtil, array $frontendUrl) {
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
        $originator = null;

        if ($this->accessService->getAccess()->getOrganization()->getContactPointForType(ContactPointTypeEnum::PRINCIPAL)) {
            $originator = $this->accessService->getAccess()->getOrganization()->getContactPointForType(ContactPointTypeEnum::PRINCIPAL)->getEmail();
        } else {
            $originator = $this->accessService->getAccess()->getPerson()->getEmail();
        }
        
        if (empty($originator)) {
            throw new \Exception("No originator found");
        }

        foreach($data as $access) {
            $contact = $this->accessRespository->find($this->getIdFromIri($access['@id']));
            if('CreateAccounts' === $parameters->getAction() ){
                $email = $this->createEmail($contact, $originator);
            }
            else if('DeleteAccounts' === $parameters->getAction() ){
                $email = $this->removeEmail($contact, $originator);
            }

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
        $hasAlreadyAccount = empty($access->getPerson()->getUsername()) ? false : true;            
        if ($hasAlreadyAccount) {
            $link = $this->getFrontendUrl('login');
        }
        else {
            $link = sprintf('%s/%s/%s', $this->getFrontendUrl('account_create'), $this->accessService->getAccess()->getOrganization()->getId(), $access->getPerson()->getConfirmationToken());
        }

        $content = $this->templating->render('account_create.html.twig', Array(
            'organization' => $this->accessService->getAccess()->getOrganization(),
            'product' => $access->getOrganization()->getSettings()->getProduct(),
            'name' => $access->getPerson()->getGivenName(). ' ' . $access->getPerson()->getname(),
            'hasAlreadyAccount' => $hasAlreadyAccount,
            'link' => $link
        ));

        $email = new EmailMessage();                  
        $email->addContact($access);
        $email->setAbout('Accès à votre espace personnel de '.$this->accessService->getAccess()->getOrganization()->getName());
        $email->setOriginator($originator);
        $email->setOrganization($this->accessService->getAccess()->getOrganization());
        $email->setAccess($this->accessService->getAccess());
        $email->setContent($content);

        return $email;
    }

    /**
     * @param Access $access
     * @param $originator
     * @return EmailMessage
     */
    private function removeEmail(Access $access, $originator) {
        $content = $this->templating->render('account_remove.html.twig', Array(
            'organization' => $this->accessService->getAccess()->getOrganization(),
            'name' => $access->getPerson()->getGivenName(). ' ' . $access->getPerson()->getname(),
            'mail' => $originator
        ));

        $email = new EmailMessage();
        $email->addContact($access);
        $email->setAbout('Désactivation de votre compte Opentalent');
        $email->setOriginator($originator);
        $email->setOrganization($this->accessService->getAccess()->getOrganization());
        $email->setAccess($this->accessService->getAccess());
        $email->setContent($content);

        return $email;
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
    
    private function getFrontendUrl($key, $default = null) {
        if (isset($this->frontendUrl[$key])) {
            return $this->frontendUrl[$key];
        }

        return $default;        
    }


    /**
     *
     *  {@inheritdoc}
     */
    public function supportsNormalization(\AppBundle\Model\AutomaticMessenger\Parameters $parameters) {
        return ('CreateAccounts' === $parameters->getAction() || 'DeleteAccounts' === $parameters->getAction());
    }

}
