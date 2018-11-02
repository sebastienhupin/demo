<?php

namespace AppBundle\Services\Exporter\Normalizer;

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
class PresenceCourseNormalizer extends AbstractNormalizerExporter implements NormalizerInterface
{

    use ReflectionTrait;

    const FORMAT = 'presence-course';

    /**
     *
     * @var EntityUtil
     */
    private $entityUtil;

    /**
     * PresenceExamenNormalizer constructor.
     *
     * @param EngineInterface $templating
     * @param AccessService $accessService
     * @param EntityUtil $entityUtil
     * @param RouterInterface $router
     */
    public function __construct(
        EngineInterface $templating,
        AccessService $accessService,
        EntityUtil $entityUtil,
        RouterInterface $router)
    {

        parent::__construct($templating, $accessService, $router);
        $this->entityUtil = $entityUtil;
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function normalize(ExportParameters $exportParameters, Array $data)
    {

        if (count($data) === 0) {
            throw new \RuntimeException('no-course');
        }

        $metaData = $exportParameters->getMetaData();
        $options = $exportParameters->getOptions();
        $params['options'] = $options;
        $params['note'] = $metaData['note'];
        $params['name'] = $this->accessService->getAccess()->getPerson()->getGivenName() . ' ' . $this->accessService->getAccess()->getPerson()->getName();

        $documentCollection = new DocumentCollection();


        foreach ($data as $course) {
            $paramsCourse = array();
            $id = $this->getIdFromIri($course['@id']);
            $paramsCourse['dates'] = $metaData['dates'][$id];

            $this->handleAttendance($course, $paramsCourse['dates']);
            $paramsCourse['course'] = $course;

            $params['courses'][] = $paramsCourse;
        }
        $html = $this->templating->render('@template/Course/PresenceFormBase.html.twig', $params);

        $header = $this->getHeader($options, $metaData, $this->accessService->getAccess()->getOrganization());
        $footer = $this->getFooter($options, $exportParameters->getFormat());

        $domDocument = new \DOMDocument();

        $ret = $domDocument->loadXML($html);
        if (false === $ret) {
            throw new \RuntimeException('bad_html');
        }

        // The first document contain all reports cards and it's accessible for the organization and the current user
        $doc = new Document();
        $doc->setName(sprintf("%s.%s", $exportParameters->getName(), $exportParameters->getFormat()))
            ->setFolder('PresenceCourse')
            ->setContent($html)
            ->setOrganization($this->accessService->getAccess()->getOrganization())
            ->setHeader($header)
            ->setFooter($footer);

        $documentCollection->add($doc);


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
    protected function getIdFromIri($iri)
    {
        try {
            return $this->entityUtil->getIdFromIri($iri);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function handleAttendance(&$booking, $dates)
    {
        foreach ($booking['attendanceBooking'] as $attendanceBooking) {
            $attendanceStart = new \DateTime($attendanceBooking['datetimeStart']);
            foreach ($dates as $d) {
                $startDate = new \DateTime($d['start']);
                if ($attendanceStart == $startDate) {
                    $booking['students'] = array_map(function ($student) use ($attendanceBooking, $startDate) {
                        if ($student['@id'] == $attendanceBooking['access']['@id']) {
                            $student['attendance'][$startDate->format('y-m-dH:i')] = true;
                            $student['attendance_justify'][$startDate->format('y-m-dH:i')] = $attendanceBooking['justify'];
                        }
                        return $student;
                    }, $booking['students']);
                }
            }
        }
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function supportsNormalization(ExportParameters $exportParameters)
    {
        return self::FORMAT === $exportParameters->getView();
    }
}
