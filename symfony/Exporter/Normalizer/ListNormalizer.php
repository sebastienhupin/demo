<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Enum\Core\FileTypeEnum;
use AppBundle\Services\Exporter\ErrorCollection;
use Symfony\Component\Templating\EngineInterface;
use Dunglas\ApiBundle\Api\ResourceCollectionInterface;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Model\Export\PropertyInfo;
use Dunglas\ApiBundle\Util\ReflectionTrait;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use AppBundle\Services\Util\Entity as EntityUtil;
use Symfony\Component\PropertyAccess\PropertyAccess;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Services\AccessService;
use Symfony\Component\Routing\RouterInterface;

/**
 * Description of ListNormalizer
 *
 * @author sebastienhupin
 */
class ListNormalizer extends AbstractNormalizerExporter implements NormalizerInterface {
    
    use ReflectionTrait;
    
    const FORMAT = 'list';
    /**
     *
     * @var ResourceCollectionInterface 
     */
    private $resourceCollection;

    /**
     *
     * @var AppBundle\Services\Util\Entity
     */
    private $entityUtil;
    /**
     * @var ErrorCollection
     */
    protected $errorCollection;
    
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
        $this->errorCollection = new ErrorCollection();
    }

    public function getErrors(){
        return $this->errorCollection;
    }
    /**
     * 
     *  {@inheritdoc}
     */
    public function normalize(ExportParameters $exportParameters, Array $data) {

        $html = '';
        $resource = $this->resourceCollection->getResourceForShortName($exportParameters->getResourceName());
        $metaData = $exportParameters->getMetaData();
        $data = $this->orderData($data,$exportParameters);

        $entityMetadata = new PropertyInfo();
        $entityMetadata->setClass($resource->getEntityClass())
            ->setType('entity');
        $this->parseFields($exportParameters->getFields(), $entityMetadata);

        $grid = new \stdClass();
        $grid->rows = Array();

        $grid->header = $entityMetadata->flatten();
        $this->prepareListData($entityMetadata, $data, $grid);

        $options = $exportParameters->getOptions();
        $html = $this->templating->render(sprintf('@template/Export/list/%s.html.twig', $exportParameters->getView()),
            Array('grid' => $grid, 'options' => $options));

        $header = $this->getHeader($options,$metaData,$this->accessService->getAccess()->getOrganization());
        $footer = $this->getFooter($options,$exportParameters->getFormat());

        $doc = new \DOMDocument();

        //delete Invalid char in CDATA 0xC
        $cleanHtml = preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $html);

        $ret = $doc->loadHTML($cleanHtml, LIBXML_PARSEHUGE);
        if ($ret === false) {
            throw new \RuntimeException('bad_html');
        }

        $doc = new Document();
        $doc->setName(sprintf("%s.%s",$exportParameters->getName(), $exportParameters->getFormat()))
            ->setFolder('Exports')
            ->setType(FileTypeEnum::LISTING)
            ->setContent($html)
            ->setAccess($this->accessService->getAccess())
            ->setOrganization($this->accessService->getAccess()->getOrganization())
            ->setHeader($header)
            ->setFooter($footer);
        ;
        return new DocumentCollection(Array($doc));
    }
    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalization(ExportParameters $exportParameters) {
        return self::FORMAT === $exportParameters->getView() && is_array($exportParameters->getData());
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
    private function parseField($property, PropertyInfo $propertyInfo)
    {
        $metadata = $this->entityUtil->getMetadataFor($propertyInfo->getClass());
        $fieldsName = explode('.', $property);
        $propertyName = $fieldsName[0];
        $restOfField = implode('.', array_slice($fieldsName, 1));

        if (!$propertyInfo->containsKey($propertyName)) {
            $propInfo = new PropertyInfo();
            $propInfo->setName($propertyName);
            $propertyInfo->set($propertyName, $propInfo);
        } else {
            $this->parseField($restOfField, $propertyInfo->get($propertyName));
            return;
        }

        if ($metadata->hasAssociation($propertyName)) {
            $associationMapping = $metadata->getAssociationMapping($propertyName);
            $type = $associationMapping['type'];
            $propInfo->setClass($associationMapping['targetEntity']);
            if ($type & ClassMetadataInfo::TO_ONE) {
                $propInfo->setType('entity');
            } else if ($type & ClassMetadataInfo::TO_MANY) {
                $propInfo->setType('collection');
            }
            $this->parseField($restOfField, $propInfo);
        } else if ($metadata->hasField($propertyName)) {
                $fieldMapping = $metadata->getFieldMapping($propertyName);
                $propInfo->setType($fieldMapping['type']);

        }
        else {
            if (!$this->getReflectionProperty($metadata->getReflectionClass(), $propertyName)) {
//                $propertyInfo->remove($propertyName);

            }
        }
    }

    /**
     * Prepare the data
     * 
     * @param PropertyInfo $propertyField
     * @param Object $data
     * @param Object $grid
     */
    private function prepareListData(PropertyInfo $propertyField, $data, &$grid) {
        foreach($data as $item) {
            $rowspan = 1;
            $colunms = Array();

            foreach ($propertyField as $prop) {
                $this->extractValue($prop, $item, $colunms, $rowspan);
            }

            $rows = Array();
            // Here we know the max rowspan, so we create all rows needed to load the data.
            // fill rows with a row class
            for($i=0;$i<$rowspan; $i++) {
               $rowClass = new \stdClass();
               $rowClass->columns = Array();
               $rows[] = $rowClass; 
            }
            // Put all values in the right place in rows
            foreach($colunms as $name => $columnsValues) {
                  $values = $columnsValues['value'];
                  if (0 === count($values)) {
                      $values = array('');
                  }
                  // Allways put the first value in the first row and indicate the rowspan value
                  $row = $rows[0];
                  $col = new \stdClass();
                  $col->name = $name;
                  $col->value = $values[0];
                  $col->type = $columnsValues['type'];
                  $col->rowspan = ($rowspan - count($values)) + 1;
                  $row->columns[] = $col;
                  // All values are added to last row and before
                  $i = count($rows)-1;
                  for ($x=1; $x<count($values); $x++) {
                      $row = $rows[$i];
                      $col = new \stdClass();
                      $col->name = $name;
                      $col->value = $values[$x];
                      $col->type = $columnsValues['type'];
                      $col->rowspan = null;
                      $row->columns[] = $col;                      
                      $i--;
                  }
            }
            // Merge rows from the rows grid
            $grid->rows = array_merge($grid->rows, $rows);
        }
    }    
    
   /**
     * Extract the value from the property info
     * 
     * @param PropertyInfo $propertyField
     * @param Object $item
     * @param Array $colunms
     * @param int $rowspan
     * @param string $path 
     */
    private function extractValue(PropertyInfo $propertyField, $item, &$colunms, &$rowspan, $path = null) {
        $accessor = PropertyAccess::createPropertyAccessor();
        $value = null;

        if(!empty($item)){
            $value = $accessor->getValue($item, '['.$propertyField->getName().']');

            if($propertyField->getName() == '@type')
                $value = str_replace('http://schema.org/', '', $value);
        }

        $path = ($path ? $path . '.' : '') . $propertyField->getName();

        // It's a relation to many
        if ($propertyField->isCollection()) {
            // Set rowspan
            if (count($value) > $rowspan) {
                $rowspan = count($value);
            }
            // Now for each value from the collection, we extract all rest values from the propertyInfo children
            if(is_array($value)){
                foreach ($value as $data) {
                    foreach($propertyField as $prop) {
                        $this->extractValue($prop, $data, $colunms, $rowspan, $path);
                    }
                }
            }
            if(empty($value)){
                foreach($propertyField as $prop) {
                    $this->extractValue($prop, null, $colunms, $rowspan, $path);
                }
            }
        }
        // It's an entity relation
        else if ($propertyField->isEntity()) {
            // For each propertyInfo children we extract the value
            foreach($propertyField as $prop) {
                $this->extractValue($prop, $value, $colunms, $rowspan, $path);
            }
        }
        // It's time to set the value for the path
        else {
           if (!array_key_exists($path, $colunms)) {
               $colunms[$path] = Array();
           }elseif(is_null($value) || $value === ""){
                return;
           }
            $replaceValue = false;
            if (array_key_exists('value', $colunms[$path]) && count($colunms[$path]['value']) === 1 && !is_array($colunms[$path]['value'][0])) {
                $firstValue = $colunms[$path]['value'][0];
                if(is_null($firstValue) || $firstValue === ""){
                    $replaceValue = true;
                    $colunms[$path]['value'][0] = $value;
                }
            }
            $colunms[$path]['type'] = $propertyField->getType();
            if(!$replaceValue){
                $colunms[$path]['value'][] = $value;
            }
        }
    }

    /**
     * order data with selectables ids
     * @param $data
     * @param $exportParameters
     * @return array
     */
    private function orderData($data, $exportParameters){
        $selectable = $exportParameters->getSelectable();
        if(count($selectable) === 0){
            return $data;
        }

        $dataWithId =  $this->getDataWithId($data);

        $dataOrder = [];

        foreach($selectable as $id) {
            $key = array_search($id, array_column($dataWithId, 'id'));
            $dataOrder[] = $dataWithId[$key];
        }

        return $dataOrder;
    }

    /**
     * @param $data
     * @return array
     */
    private function getDataWithId($data)
    {
        $dataWithId =  array_map(function ($dataLine) {
            $idExplode = explode('/', $dataLine['@id']);
            $id = $idExplode[count($idExplode) - 1];
            $dataLine['id'] = $id;
            return $dataLine;
        },$data);

        return $dataWithId;
    }

}
