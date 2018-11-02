<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Enum\Core\FileTypeEnum;
use AppBundle\Model\Export\Parameters as ExportParameters;
use Dunglas\ApiBundle\Util\ReflectionTrait;
use Symfony\Component\Templating\EngineInterface;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Services\AccessService;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use AppBundle\Services\Util\Entity as EntityUtil;
use Symfony\Component\Routing\RouterInterface;

/**
 * Description of ReportCardNormalizer
 *
 * @author sebastienhupin
 */
class PresenceEventNormalizer extends AbstractNormalizerExporter implements NormalizerInterface {

    use ReflectionTrait;    
    
    const FORMAT = 'presence-event';

    /**
     *
     * @var EntityUtil
     */
    private $entityUtil;

    /**
     * The constructor
     * 
     * @param EngineInterface $templating
     * @param IriConverterInterface $iriConverter
     * @param AccessService $accessService
     * @param RouterInterface $router
     */
    public function __construct(
        EngineInterface $templating,
        AccessService $accessService,
        EntityUtil $entityUtil,
        RouterInterface $router) {

        parent::__construct($templating, $accessService, $router);
        $this->entityUtil = $entityUtil;
    }
    
    /**
     * 
     *  {@inheritdoc}
     */
    public function normalize(ExportParameters $exportParameters, Array $data) {

        if(count($data) === 0){
            throw new \RuntimeException('no-event');
        }

        $metaData = $exportParameters->getMetaData();
        $options = $exportParameters->getOptions();
        $params['options'] = $options;

        $documentCollection = new DocumentCollection();


        foreach($data as  $event) {
            $id = $this->getIdFromIri($event['@id']);
            foreach($metaData['dates'][$id] as $dateTime) {
                $event['datetimeStart'] = new \DateTime($dateTime["start"]);
                $event['datetimeEnd'] = new \DateTime($dateTime["end"]);

                $this->handleAttendance($event, $event['datetimeStart']);

                $params['event'] = $event;

                $params['name'] = $this->accessService->getAccess()->getPerson()->getGivenName() . ' ' . $this->accessService->getAccess()->getPerson()->getName();

                $html = $this->templating->render('@template/Event/PresenceFormBase.html.twig',$params);

                $header = $this->getHeader($options,$metaData,$this->accessService->getAccess()->getOrganization());
                $footer = $this->getFooter($options,$exportParameters->getFormat());

                $domDocument = new \DOMDocument();
                $ret = $domDocument->loadXML($html);
                if (false === $ret) {
                    throw new \RuntimeException('bad_html');
                }

                $doc = new Document();
                $doc->setName(sprintf("%s.%s",$exportParameters->getName(), $exportParameters->getFormat()))
                    ->setFolder('PresenceEvent')
                    ->setType(FileTypeEnum::PRESENCE_ATTENDANCE)
                    ->setContent($html)
                    ->setAccess($this->accessService->getAccess())
                    ->setOrganization($this->accessService->getAccess()->getOrganization())
                    ->setHeader($header)
                    ->setFooter($footer)
                ;

                $documentCollection->add($doc);
            }
        }

        return $documentCollection;
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

    public function handleAttendance(&$booking, $startDate){

        foreach ($booking['attendanceBooking'] as $attendanceBooking) {
            $attendanceStart = new \DateTime($attendanceBooking['datetimeStart']);
            if($attendanceStart == $startDate){
                $booking['eventUser'] = array_map( function($eventUser) use($attendanceBooking) {
                    if($eventUser['guest']['@id'] == $attendanceBooking['access']['@id']){
                        $eventUser['attendance'] = true;
                        $eventUser['attendance_justify'] = $attendanceBooking['justify'];
                    }
                    return $eventUser;
                }, $booking['eventUser']);
            }
        }
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsNormalization(ExportParameters $exportParameters) {
        return self::FORMAT === $exportParameters->getView();
    }
}
