<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Model\Export\Parameters as ExportParameters;
use Dunglas\ApiBundle\Api\ResourceCollectionInterface;
use AppBundle\Services\Util\Entity as EntityUtil;
use AppBundle\Model\Export\PropertyInfo;
use Dunglas\ApiBundle\Util\ReflectionTrait;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Templating\EngineInterface;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Services\AccessService;
use Symfony\Component\Routing\RouterInterface;

/**
 * Description of CardNormalizer
 *
 * @author sebastienhupin
 */
class CardNormalizer extends AbstractNormalizerExporter implements NormalizerInterface {

    use ReflectionTrait;    
    
    const FORMAT = 'card';

    /**
     *
     * @var type 
     */
    private $resourceCollection;

    /**
     *
     * @var AppBundle\Services\Util\Entity
     */
    private $entityUtil;
    /**
     * The constructor
     * 
     * @param ResourceCollectionInterface $resourceCollection
     * @param EngineInterface $templating
     * @param EntityUtil $entityUtil
     * @param AccessService $accessService
     * @param RouterInterface $router
     */
    public function __construct(ResourceCollectionInterface $resourceCollection, EngineInterface $templating, EntityUtil $entityUtil, AccessService $accessService, RouterInterface $router) {
        parent::__construct($templating, $accessService, $router);

        $this->resourceCollection = $resourceCollection;
        $this->entityUtil = $entityUtil;
    }    
    
    /**
     * 
     *  {@inheritdoc}
     */    
    public function normalize(ExportParameters $exportParameters, Array $data) {
        $html = '';
        
        $resource = $this->resourceCollection->getResourceForShortName($exportParameters->getResourceName());
        $metaData = $exportParameters->getMetaData();

        $entityMetadata = new PropertyInfo();
        $entityMetadata->setName($resource->getShortName());
        $entityMetadata->setClass($resource->getEntityClass())
            ->setType('entity');               

        if (0 === $exportParameters->getFields()->count()) { 
            $fields = new ArrayCollection($this->flattenFields($data));
        }
        else {
            $fields = $exportParameters->getFields();
        }

        $this->parseFields($fields, $entityMetadata);
        $options = $exportParameters->getOptions();

        $html = $this->templating->render(sprintf('@template/Export/%s/basecard.html.twig', $exportParameters->getView()), Array(
            'templates' => Array(
                sprintf('@template/Export/card/%s/card.html.twig', $resource->getShortName()),
                '@template/Export/card/card.html.twig',
            ),
            'entity' => $data,
            'entityMetadata' => $entityMetadata,
            'options' => $options
        ));

        $header = $this->getHeader($options,$metaData,$this->accessService->getAccess()->getOrganization());
        $footer = $this->getFooter($options,$exportParameters->getFormat());

        $doc = new Document();
        $doc->setName(sprintf("%s.%s",$exportParameters->getName(), $exportParameters->getFormat()))
            ->setFolder('Exports')
            ->setContent($html)
            ->setAccess($this->accessService->getAccess())
            ->setOrganization($this->accessService->getAccess()->getOrganization())
            ->setHeader($header)
            ->setFooter($footer)
        ;

        return new DocumentCollection(Array($doc));
    }
    
    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalization(ExportParameters $exportParameters) {
        return self::FORMAT === $exportParameters->getView();
    }
    
    /**
     * Flatten the array
     * 
     * @param Array $data
     * @return Array
     */
    private function flattenFields(Array $data) {
        $fields = Array();
        foreach($data as $prop => $value) {
            if (0 === strpos($prop, '@')) continue;
            if (is_array($data[$prop])) {
                $f = $this->flattenFields($data[$prop]);                
                foreach($f as $p) {
                    if (!is_numeric($prop)) {
                        $p = $prop . '.' . $p;
                    }
                    if (!in_array($p, $fields)) {
                        $fields[] = $p;
                    }
                }
            }
            else {
                $fields[] = $prop;
            }
        }
        return $fields;
    }

    /**
     * Create the nested PropertyInfo relationship
     * 
     * @param Array $properties
     * @param PropertyInfo $propertyInfo
     */
    private function parseFields($properties, PropertyInfo $propertyInfo) {
        foreach($properties as $property) {
            $this->parseField($property, $propertyInfo);
        }
    }     
    
    /**
     * Create a property info from entity and field name
     * 
     * @param string $property
     * @param PropertyInfo $propertyInfo
     */
    private function parseField($property, PropertyInfo $propertyInfo) {

        $metadata = $this->entityUtil->getMetadataFor($propertyInfo->getClass());
                
        $fieldsName = explode('.', $property);
        $propertyName = $fieldsName[0];
        $restOfField = implode('.',array_slice($fieldsName, 1));
        
        if (!$propertyInfo->containsKey($propertyName)) {
            $propInfo = new PropertyInfo();        
            $propInfo->setName($propertyName);
            $propertyInfo->set($propertyName, $propInfo);
        }
        else {
           $this->parseField($restOfField, $propertyInfo->get($propertyName));
           return;
        }

        if ($metadata->hasAssociation($propertyName)) {
            $associationMapping = $metadata->getAssociationMapping($propertyName);
            $type = $associationMapping['type'];
            $propInfo->setClass($associationMapping['targetEntity']);
            if ($type & ClassMetadataInfo::TO_ONE) {
               $propInfo->setType('entity');
            }
            else if ($type & ClassMetadataInfo::TO_MANY) {
                $propInfo->setType('collection');
            }
            $this->parseField($restOfField, $propInfo);
        }
        else if ($metadata->hasField($propertyName)) {
            $fieldMapping = $metadata->getFieldMapping($propertyName);            
            $propInfo->setType($fieldMapping['type']);
        }
        else {
            if (!$this->getReflectionProperty($metadata->getReflectionClass(), $propertyName)) {
                $propertyInfo->remove($propertyName);
            }
        }
    }
}
