<?php

namespace AppBundle\Services\Exporter\Output;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 *
 * @author sebastienhupin
 */
interface OutputInterface {
    /**
     * Output the given files
     * 
     * @param DocumentCollection<\AppBundle\Services\Exporter\Document> $documents
     * @param ExportParameters $exportParameters
     */
    public function output(DocumentCollection $documents, ExportParameters $exportParameters);
    
    /**
     * Checks whether the given class is supported to output the given type.
     *
     * @param ExportParameters $exportParameters
     *
     * @return bool
     */
    public function supportsOutput(ExportParameters $exportParameters);     
}
