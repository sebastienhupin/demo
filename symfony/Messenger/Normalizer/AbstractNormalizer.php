<?php

namespace AppBundle\Services\Messenger\Normalizer;

use AppBundle\Services\RulerzService;
use Symfony\Component\Templating\EngineInterface;
use AppBundle\Services\AccessService;
use Dunglas\ApiBundle\Exception\InvalidArgumentException;
use AppBundle\Entity\AccessAndFunction\Access;
use AppBundle\Services\Util\Entity as EntityUtil;
use Symfony\Component\Serializer\Serializer;
use Dunglas\ApiBundle\Api\ResourceCollectionInterface;
use AppBundle\Services\Exporter\Exporter;
use AppBundle\Entity\Core\ContactInterface;

/**
 * Description of AbstractNormalizer
 *
 * @author sebastienhupin
 */
abstract class AbstractNormalizer {
    const FORBIDDEN_PATH = [
      'person.username',
      'person.password'
    ];

    /** 
     * @var RulerzService 
     */
    protected $ruler;
    /**
     *
     * @var AccessService 
     */
    protected $accessService;    
    /**
     *
     * @var EngineInterface 
     */
    protected $templating;
    /**
     *
     * @var EntityUtil 
     */
    protected $entityUtil;
    /**
     *
     * @var Serializer 
     */
    protected $serializer;
    /**
     * @var ResourceCollectionInterface
     */
    protected $resourceCollection;
    /**
     *
     * @var Exporter 
     */
    protected $exporter;
    /**
     * The constructor
     * 
     * @param RulerzService $ruler
     * @param AccessService $accessService
     * @param EngineInterface $templating
     * @param EntityUtil $entityUtil
     * @param Serializer $serializer
     */
    public function __construct(RulerzService $ruler, AccessService $accessService, EngineInterface $templating, EntityUtil $entityUtil, Serializer $serializer, ResourceCollectionInterface $resourceCollection, Exporter $exporter) {        
        $this->ruler = $ruler;
        $this->accessService = $accessService;
        $this->templating = $templating;
        $this->entityUtil = $entityUtil;
        $this->serializer = $serializer;
        $this->resourceCollection = $resourceCollection;
        $this->exporter = $exporter;
    }

    public function cleanTemplate($text){
        foreach (self::FORBIDDEN_PATH as $forbidden){
            $text = str_replace($forbidden, '', $text);
        }

        $text = html_entity_decode(html_entity_decode($text));
        return $text;
    }
    
    /**
     * Get all people from the rule
     * 
     * @param String $rule
     * @return array<ContactInterface>
     */
    public function getContactsFromRule($rule) {

        $contacts = array();
        if (!empty($rule)) {
            $this->ruler->setRule($rule);
            $contacts = $this->ruler->filter();
        }
        return $contacts;
    }
        
    /**
     * Generate the body message
     * 
     * @param ContactInterface $contact
     * @param String $id
     * 
     * @return string
     */
    public function generateContent(ContactInterface $contact, $id) {
        return $this->templating->render(sprintf( __DIR__."/template_tmp/%s.html.twig", $id),
            Array(
                'organization' => $this->getAccess()->getOrganization(),
                'contact' => $contact
            )
        );
    }
    
    /**
     * Get Access
     * 
     * @return AccessService
     */
    public function getAccess() {
        return $this->accessService->getAccess();
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
}
