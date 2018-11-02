<?php

namespace AppBundle\Services\Exporter\Output;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Model\Export\Parameters as ExportParameters;

/**
 * Description of DefaultOutput
 *
 * @author sebastienhupin
 */
class DefaultOutput implements OutputInterface {
    /**
     * 
     *  {@inheritdoc}
     */
    public function output(DocumentCollection $documents, ExportParameters $exportParameters) {
        // Nothing to do here
    }
    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsOutput(ExportParameters $exportParameters) {
        return true;
    }
}
