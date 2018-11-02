<?php

namespace AppBundle\Services\Exporter\Filter;

use AppBundle\Model\Export\Parameters as ExportParameters;
use Dunglas\ApiBundle\Exception\InvalidArgumentException;
use AppBundle\Services\Util\Event\EventRecurUtils;
use AppBundle\Services\Util\Entity as EntityUtil;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use AppBundle\Services\Util\PeriodDates;

/**
 * Description of AttendanceRecordCourseFilter
 *
 */
class AttendanceRecordCourseFilter implements FilterInterface {

    /**
     *
     * @var EventRecurUtils
     */
    private $eventRecurUtils;

    /**
     *
     * @var EntityUtil
     */
    private $entityUtil;

    /**
     *
     * @var IriConverterInterface 
     */
    private $iriConverter;

    /**
     *
     * @var PeriodDates
     */
    private $periodDates;

    /**
     * The constructor
     *
     * @param EventRecurUtils $eventRecurUtils
     * @param EntityUtil $entityUtil
     * @param IriConverterInterface $iriConverter
     * @param PeriodDates $periodDates
     */
    public function __construct(EventRecurUtils $eventRecurUtils, EntityUtil $entityUtil, IriConverterInterface $iriConverter, PeriodDates $periodDates) {
        $this->eventRecurUtils = $eventRecurUtils;
        $this->iriConverter = $iriConverter;
        $this->entityUtil = $entityUtil;
        $this->periodDates = $periodDates;
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function filtering(ExportParameters $exportParameters, Array $data) {

        $courses = [];
        $metaData = $exportParameters->getMetaData();
        
        foreach ($data as $access) {
            $c = array(
                'teacher' => $access['person'],
                'courses' => array()
            );
            foreach ($access['practicalCourses'] as $course) {
                
                if (!in_array($course['@id'], $metaData['courses'])) {
                    continue;
                }
                
                $eventRecurs = $course['eventRecur'];
                $dates = array();
                
                if (empty($eventRecurs)) {
                    // 
                } else {
                    foreach ($eventRecurs as $er) {
                        $eventRecur = $this->getItemFromIri($er['@id']);
                        $constraint = $this->getConstraint($exportParameters);
                        $recurrs = $this->eventRecurUtils->getDates($eventRecur, $constraint);
                        foreach ($recurrs as $recurr) {
                            $dates[] = $recurr->getStart();
                        }
                    }
                }

                if (!empty($dates)) {
                    $c['courses'][] = array(
                        'course' => $course,
                        'dates' => $dates
                    );                    
                }
            }
            
            if (!empty($c['courses'])) {
                $courses[$this->getIdFromIri($access['@id'])] = $c;
            }            
        }
        return $courses;
    }

    /**
     * Get the constraint
     * 
     * @param ExportParameters $exportParameters
     */
    protected function getConstraint(ExportParameters $exportParameters) {
        $metaData = $exportParameters->getMetaData();
        $period = $metaData['period'];
        $year = (int) date("Y");

        $dates = $this->periodDates->getDatesFromPeriodAndYears($period);

        $constraint = new \Recurr\Transformer\Constraint\BetweenConstraint($dates[$year]['dateStart'], $dates[$year]['dateEnd'], true);

        return $constraint;
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
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Retrieves an item from its IRI.
     *
     * @param string $iri
     *
     * @return object|null
     */
    protected function getItemFromIri($iri) {
        $item = $this->iriConverter->getItemFromIri($iri, true);
        return $item->getItem();
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function supportsFilter(ExportParameters $exportParameters) {
        return 'attendance-record' === $exportParameters->getView();
    }

}
