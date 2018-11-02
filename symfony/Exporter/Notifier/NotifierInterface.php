<?php

namespace AppBundle\Services\Exporter\Notifier;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\ErrorCollection;

/**
 *
 * @author sebastienhupin
 */
interface NotifierInterface {
    /**
     * Send notification
     *
     * @param DocumentCollection $documents
     * @param ExportParameters|null $exportParameters
     * @param ErrorCollection|null $errors
     * @return mixed
     */
    public function notify(DocumentCollection $documents, ExportParameters $exportParameters = null, ErrorCollection $errors);
    /**
     * Checks whether the given class is supported to notify the given type.
     *
     * @param ExportParameters $exportParameters
     *
     * @return bool
     */
    public function supportsNotification(ExportParameters $exportParameters);
}
