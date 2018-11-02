<?php

namespace AppBundle\Services\Exporter\Encoder;

use AppBundle\Services\Elastica\ElasticaBookingSearch;
use Sabre\VObject\Component\VCalendar;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 * Ics Export Service service
 *
 * @author Vincent GUFFON
 */
class IcsExport implements EncoderInterface
{
    const FORMAT = 'csv';
    
    /**
     * @var
     */
    private $vCalendar;

    private $elasticaBookingSearch;

    /**
     * Holidays constructor.
     * @param VCalendar $vCalendar
     */
    public function __construct(VCalendar $vCalendar, ElasticaBookingSearch $elasticaBookingSearch)
    {
        $this->vCalendar = $vCalendar;
        $this->elasticaBookingSearch = $elasticaBookingSearch;
    }

    /**
     *
     */
    public function export($request, $dateTimeStart, $dateTimeEnd, $type)
    {
        $bookings = $this->elasticaBookingSearch->getBooking($request, $dateTimeStart, $dateTimeEnd, $type, null, false);
        array_shift($bookings);

        foreach ($bookings as $booking) {
            $this->vCalendar->add(
                'VEVENT',
                [
                    'SUMMARY' => $booking->getName(),
                    'DTSTART' => $booking->getDatetimeStart(),
                    'DTEND' => $booking->getDatetimeEnd(),
                ]
            );
        }

        return $this->vCalendar->serialize();
    }
    
    /**
     * {@inheritdoc}
     */ 
    public function encode(DocumentCollection $documentCollection, ExportParameters $exportParameters = null) {
        foreach($documentCollection as $document) {
            $content = $document->getContent();
            
            $document->setContent($content);
        }    
    }
    
    /**
     * {@inheritdoc}
     */     
    public function supportsEncoding(ExportParameters $exportParameters) {
        return self::FORMAT === $exportParameters->getFormat();
    }

}

