<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Model\Export\Parameters as ExportParameters;
use Dunglas\ApiBundle\Util\ReflectionTrait;
use libphonenumber\PhoneNumberUtil;
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
class PresenceExamenNormalizer extends AbstractNormalizerExporter implements NormalizerInterface {

    use ReflectionTrait;    
    
    const FORMAT = 'presence-examen';

    /**
     *
     * @var EntityUtil
     */
    private $entityUtil;

    /**
     * PresenceExamenNormalizer constructor.
     * 
     * @param EngineInterface $templating
     * @param AccessService $ac cessService
     * @param EntityUtil $entityUtil
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
            throw new \RuntimeException('no-examen');
        }

        $metaData = $exportParameters->getMetaData();
        $options = $exportParameters->getOptions();
        $params['options'] = $options;

        $documentCollection = new DocumentCollection();


        foreach($data as  $examen) {
            $id = $this->getIdFromIri($examen['@id']);
            foreach($metaData['dates'][$id] as $dateTime) {
                $examen['datetimeStart'] = new \DateTime($dateTime["start"]);
                $examen['datetimeEnd'] = new \DateTime($dateTime["end"]);

                $this->handleAttendance($examen, $examen['datetimeStart']);
                $params['examen'] = $examen;

                $params['name'] = $this->accessService->getAccess()->getPerson()->getGivenName() . ' ' . $this->accessService->getAccess()->getPerson()->getName();

                $html = $this->templating->render('@template/Examen/PresenceFormBase.html.twig',$params);

                $header = $this->getHeader($options,$metaData,$this->accessService->getAccess()->getOrganization());
                $footer = $this->getFooter($options,$exportParameters->getFormat());

                $domDocument = new \DOMDocument();

                $ret = $domDocument->loadXML($html);
                if (false === $ret) {
                    throw new \RuntimeException('bad_html');
                }

                // The first document contain all reports cards and it's accessible for the organization and the current user
                $doc = new Document();
                $doc->setName(sprintf("%s.%s",$exportParameters->getName(), $exportParameters->getFormat()))
                    ->setFolder('PresenceExamen')
                    ->setContent($html)
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
                $booking['convocation'] = array_map( function($convocation) use($attendanceBooking) {
                    if($convocation['student']['@id'] == $attendanceBooking['access']['@id']){
                        $convocation['attendance'] = true;
                        $convocation['attendance_justify'] = $attendanceBooking['justify'];
                    }
                    return $convocation;
                }, $booking['convocation']);
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
