<?php

namespace AppBundle\Services\Exporter\Filter;

use AppBundle\Enum\Education\PeriodEnum;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Util\PeriodDates;
use AppBundle\Services\Util\PeriodMonths;
use Dunglas\ApiBundle\Exception\InvalidArgumentException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use AppBundle\Services\AccessService;
use AppBundle\Services\Util\Entity as EntityUtil;

/**
 * Description of ReportCardFilter
 *
 * @author sebastienhupin
 */
class ReportCardFilter implements FilterInterface
{

    const SCHOOLYEAR = 'SCHOOLYEAR';

    /**
     *
     * @var AccessService
     */
    private $accessService;
    /**
     *
     * @var EntityUtil
     */
    private $entityUtil;

    /**
     *
     * @var PeriodDates
     */
    private $periodDates;

    /**
     * ReportCardFilter constructor.
     * @param AccessService $accessService
     * @param EntityUtil $entityUtil
     * @param PeriodDates $periodDates
     */
    public function __construct(AccessService $accessService, EntityUtil $entityUtil, PeriodDates $periodDates)
    {
        $this->accessService = $accessService;
        $this->entityUtil = $entityUtil;
        $this->periodDates = $periodDates;
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function filtering(ExportParameters $exportParameters, Array $data)
    {
        $metaData = $exportParameters->getMetaData();
        $parameters = $this->accessService->getOrganization()->getParameters();
        $trackingValidation = $parameters->getTrackingValidation();

        $period = $metaData['period'];
        $years = $period['years'];

        if (PeriodEnum::isValid($period['period'])) {
            $datesPeriodByYears = $this->periodDates->getDatesFromPeriodAndYears($period['period'], $period['years']);
        } else if ($period['period'] == self::SCHOOLYEAR) {
            $datesPeriodByYears = $this->periodDates->getDatesFromSchoolYears($period['years'], $this->accessService->getAccess());
        }

        $response = [];
        foreach ($data as $item) {
            $atLeastOneNotation = false;
            $student_id = $this->entityUtil->getIdFromIri($item['@id']);
            $response[$student_id]['access_iri'] = $item['@id'];
            $response[$student_id]['student_info'] = $item['person'];
            $response[$student_id]['guardians_info'] = $item['guardians'];

            foreach ($years as $year) {
                $datesPeriod = $datesPeriodByYears[$year];

                //get the rights education student
                $educationStudents = $this->cleanEducationStudent($item['educationStudent'], $year, $trackingValidation);

                //get the rights education notations for each education students
                foreach ($educationStudents as $key => $educationStudent) {
                    $educationNotations = $this->cleanEducationNotation($educationStudent['educationNotations'], $period, $trackingValidation);
                    $educationStudents[$key]['educationNotations'] = $this->transformNote($educationNotations);
                }

                //Excluded education students if there are no education notation for this period and this year
                if (!$parameters->getBulletinShowEducationWithoutEvaluation()) {
                    $educationStudents = array_filter($educationStudents, function ($educationStudent) {
                        return count($educationStudent['educationNotations']) > 0;
                    });
                }


                if(count(array_filter($educationStudents, function ($educationStudent) {
                    return count($educationStudent['educationNotations']) > 0;
                })) > 0){
                    $atLeastOneNotation = true;
                }

                //Excluded years if there is no education students
                if (!$parameters->getBulletinEditWithoutEvaluation()) {
                    if (count($educationStudents) == 0) continue;
                }

                //Average calculating
                $educationStudentAverageArray = [];
                if(empty($educationStudentAverageArray))
                    $response[$student_id][$year]['averageByPeriod'] = null;

                foreach ($educationStudents as $key => $educationStudent) {
                    //Group all notation by period
                    $educationNotationByPeriod = $this->getEducationNotationByPeriod($educationStudent, $period);

                    //If there is no notation by period, so the average for the period is null AND average for the education student is null too
                    if (empty($educationNotationByPeriod)) {
                        $response[$student_id][$year]['averageByPeriod'] = null;
                        $educationStudents[$key]['average'] = null;
                        continue;
                    }

                    //For each notation by period, calculating the average for the period
                    $averageByPeriodArray = [];
                    foreach ($educationNotationByPeriod as $p => $educationNotation) {
                        $averageByPeriod = $this->averageCalculating($educationNotation);
                        $averageByPeriodArray[] = $averageByPeriod;
                        $response[$student_id][$year]['averageByPeriod'][$p][$educationStudent['@id']] = $averageByPeriod;
                    }

                    //Calculating average for the education student for all period
                    $educationStudentAverage = $this->averageCalculating($averageByPeriodArray);

                    $educationStudentAverageArray[] = $educationStudentAverage;
                    $educationStudents[$key]['average'] = $educationStudentAverage;
                }
                //Calculating average general for education student
                $educationGeneralAverage = $this->averageCalculating($educationStudentAverageArray);
                $response[$student_id][$year]['educationGeneralAverage'] = $educationGeneralAverage;

                //We save education students definition
                $response[$student_id][$year]['educationStudent'] = $educationStudents;

                //Exam convocation
                if ($period['period'] == self::SCHOOLYEAR && $parameters->getBulletinViewTestResults()) {
                    $examConvocations = $this->cleanExamConvocations($item['examenConvocations'], $datesPeriod);
                    $examConvocations = $this->transformNote($examConvocations);

                    $examsNotesArray = array_map(function ($examConvocation) {
                        return $examConvocation['note'];
                    }, $examConvocations);
                    $examAverage = $this->averageCalculating($examsNotesArray);
                } else {
                    $examConvocations = null;
                    $examAverage = null;
                }

                if(!empty($response[$student_id][$year]['averageByPeriod'])){
                    foreach ($response[$student_id][$year]['averageByPeriod'] as $averagePeriod => $averagesByEducation){
                        $average = $this->averageCalculating($averagesByEducation);
                        $response[$student_id][$year]['averageByPeriod'][$averagePeriod]['average'] = $average;
                    }
                }

                //Save exam general average
                $response[$student_id][$year]['examGeneralAverage'] = $examAverage;

                //We save exam convocations definition
                $response[$student_id][$year]['examConvocations'] = $examConvocations;

                //Calculating average general
                $generalAverage = $this->averageCalculating([$educationGeneralAverage, $examAverage]);
                $response[$student_id][$year]['generalAverage'] = $generalAverage;

                //Get non attendance counting
                if ($parameters->getBulletinShowAbsences()) {
                    $response[$student_id][$year]['attendance'] = $this->getAttendanceNumber($item['attendanceBookings'], $datesPeriod);
                }
            }

            if (!$parameters->getBulletinEditWithoutEvaluation() && !$atLeastOneNotation) {
                unset($response[$student_id]);
            }
        }

        return $response;
    }

    /**
     * Keep just education student who have the right years and, if organization doing the tracking validation,
     * being acquired
     * @param $educationStudent
     * @param $year
     * @param $trackingValidation
     * @return array
     */
    private function cleanEducationStudent($educationStudent, $year, $trackingValidation)
    {
        return array_filter($educationStudent, function ($educationStudent) use ($year, $trackingValidation) {
            return $trackingValidation && $educationStudent['acquired'] && $educationStudent['startYear'] == $year
            || !$trackingValidation && $educationStudent['startYear'] == $year;
        });
    }

    /**
     * Keep just education notation who have the right period and, if organization doing the tracking validation,
     * being validated
     * @param $educationNotations
     * @param $period
     * @param $trackingValidation
     * @return array
     */
    private function cleanEducationNotation($educationNotations, $period, $trackingValidation)
    {
        return array_filter($educationNotations, function ($educationNotation) use ($period, $trackingValidation) {
            if ($period['period'] == self::SCHOOLYEAR) {
                return !$trackingValidation || $trackingValidation && $educationNotation['isValid'];
            } else {
                return !$trackingValidation && $period['period'] == $educationNotation['period']
                || $trackingValidation && $educationNotation['isValid'] && $period['period'] == $educationNotation['period'];
            }
        });
    }

    /**
     * Keep just examen convocations who start between the right range period
     * @param array $examConvocations
     * @param $datesPeriod
     * @return array
     */
    private function cleanExamConvocations($examConvocations = array(), $datesPeriod)
    {
        return array_filter($examConvocations, function ($examConvocation) use ($datesPeriod) {
            $examDate = new \DateTime($examConvocation['examen']['datetimeStart']);
            return $this->checkIfDateIsInDateRange($examDate, $datesPeriod['dateStart'], $datesPeriod['dateEnd']);
        });
    }

    /**
     * @param $educationNotations
     * @return mixed
     */
    private function transformNote($educationNotations)
    {
        foreach ($educationNotations as $key => $educationNotation) {
            $educationNotations[$key]['note'] = $educationNotation['note'] / 5;
        }
        return $educationNotations;
    }

    /**
     * Group all notation by period
     * @param $educationStudent
     * @param $period
     * @return array
     */
    private function getEducationNotationByPeriod($educationStudent, $period)
    {
        $educationNotationByPeriod = [];
        //Group all notation by period
        if ($period['period'] == self::SCHOOLYEAR) {
            foreach ($educationStudent['educationNotations'] as $educationNotation) {
                $educationNotationByPeriod[$educationNotation['period']][] = $educationNotation['note'];
            }
        } else {
            $educationNotationByPeriod[$period['period']] = array_map(function ($educationNotation) {
                return $educationNotation['note'];
            }, $educationStudent['educationNotations']);
        }
        return $educationNotationByPeriod;
    }

    /**
     * @param $notations
     * @return float|int
     */
    private function averageCalculating($notations)
    {
        $notations = array_filter($notations, function ($notation) {
            return !is_null($notation);
        });

        if (empty($notations))
            return null;

        $notes = 0;
        foreach ($notations as $notation) {
            $notes += $notation;
        }
        return $notes / count($notations);
    }

    /**
     * Get the number of non attendance during period
     * @param array $attendancesBookings
     * @param $datesPeriod
     * @return int
     */
    private function getAttendanceNumber($attendancesBookings = array(), $datesPeriod)
    {
        $attendancesBookings = array_filter($attendancesBookings, function ($attendanceBooking) use ($datesPeriod) {
            $attendanceDate = new \DateTime($attendanceBooking['datetimeStart']);
            return $this->checkIfDateIsInDateRange($attendanceDate, $datesPeriod['dateStart'], $datesPeriod['dateEnd']);
        });
        return count($attendancesBookings);
    }

    /**
     * Check if the date is in between range
     * @param $date
     * @param $startDateRange
     * @param $endDateRange
     * @return bool
     */
    public function checkIfDateIsInDateRange($date, $startDateRange, $endDateRange)
    {
        if (($date >= $startDateRange && $date <= $endDateRange)) {
            return true;
        }
        return false;
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
            $parameters = $this->router->match($iri);
        } catch (ExceptionInterface $e) {
            throw new InvalidArgumentException(sprintf('No route matches "%s".', $iri), $e->getCode(), $e);
        }

        if (!isset($parameters['id'])) {
            throw new InvalidArgumentException(sprintf('No route matches "%s".', $iri), $e->getCode(), $e);
        }

        return $parameters['id'];
    }

    /**
     *
     *  {@inheritdoc}
     */
    public function supportsFilter(ExportParameters $exportParameters)
    {
        return 'report-card' === $exportParameters->getView();
    }
}
