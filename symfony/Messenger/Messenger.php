<?php

namespace AppBundle\Services\Messenger;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use AppBundle\Services\AccessService;
use AppBundle\Services\Messenger\Normalizer\NormalizerInterface;
use AppBundle\Services\Messenger\Carrier\CarrierInterface;
use AppBundle\Services\Messenger\Reporter\ReportingInterface;
use AppBundle\Services\Messenger\Notifier\NotifierInterface;

/**
 * Description of Messenger
 *
 * @author sebastienhupin
 */
class Messenger {
    /**
     *
     * @var Parameters 
     */
    private $parameters;  
    /**
     *
     * @var boolean 
     */
    private $detachedProcess;     
    /**
     *
     * @var boolean 
     */
    private $delivery;     
    /**
     *
     * @var AccessService 
     */
    private $accessService;    
    /**
     *
     * @var Producer 
     */
    private $messageProducer;
    /**    
    /**
     *
     * @var Array 
     */
    private $carriers = [];        
    /**
     *
     * @var Array 
     */
    private $normalizers = [];
    /**
     *
     * @var Array 
     */
    private $reporters = [];
    /**
     *
     * @var Array 
     */
    private $notifiers;

    private $file;
    
    /**
     * The constructor
     * 
     * @param AccessService $accessService
     * @param Producer $messageProducer
     */
    public function __construct(AccessService $accessService, Producer $messageProducer)
    {
        $this->accessService = $accessService;
        $this->messageProducer = $messageProducer;      
    }

    /**
     * Start to sending message
     * 
     * @param array $data
     */
    public function dispatch(Array $data) {
        $this->parameters->setData($data);
        if ($this->isDetachedProcess()) {
             $this->detachedDispatcher();
        }
        else {
            $this->process();
        }
    }    
    
    /**
     * Check message
     * 
     * @param array $data
     */
    public function check(Array $data) {
        $this->parameters->setData($data);
        /*
         * @var MessageCollection<Message>
         */
        $messages = $this->normalize($data);

        // Get the carrier        
        $carrier = $this->getCarrier();

        if (null === $carrier) {
            throw new \RuntimeException('No carrier found');
        }
        // Check messages
        return $carrier->check($messages, $this->parameters);        
    }    
        
    /**
     * Is detachedProcess
     * 
     * @return boolean
     */
    public function isDetachedProcess() {
        return $this->detachedProcess;
    }

    /**
     * Sets the process run in background is used.
     * 
     * @param boolean $detachedProcess
     * @return $this
     */
    public function setDetachedProcess($detachedProcess) {
        $this->detachedProcess = $detachedProcess;
        return $this;
    }
    
    /**
     * Detached processing
     */
    public function detachedProcess() {
        $this->exportProducer->publish(serialize($this->exportParameters));        
    }    
            
    /**
     * Process the data
     */
    public function process() {
        /**
         * @var Array
         */
        $data = $this->parameters->getData();        
        /*
         * @var MessageCollection<Message>
         */
        $messages = $this->normalize($data);

        $this->send($messages);
        
        $this->reporting($messages);
        
        $this->notify($messages);
    }    
    
    /**
     * Gets the messenger definition.
     * 
     * @return Parameters 
     */
    public function getParameters() {
        return $this->parameters;
    }
    
    /**
     * Sets the messenger definition.
     * 
     * @param Parameters  $parameters
     * @return $this
     */
    public function setParameters(Parameters $parameters) {
        $this->parameters = $parameters;
        if (null === $this->parameters->getAccessId()) {
            $this->parameters->setAccessId($this->accessService->getAccess()->getId());
        }
        if (null === $this->parameters->getOrganizationId()) {
            $this->parameters->setOrganizationId($this->accessService->getAccess()->getOrganization()->getId());
        }
        return $this;
    }    

    /**
     * Normalize
     * 
     * @param array $data
     * @return MessageCollection<Message>
     * @throws \RuntimeException
     */
    public function normalize(Array $data) {
        // Normalize data.        
        $normalizer = $this->getNormalizer();
        if (null === $normalizer) {
            throw new \RuntimeException('No normalizer found');
        }
        
        $messages = $normalizer->normalize($this->parameters);   
        
        return $messages;
    }    
    
    /**
     * Send
     * 
     * @param MessageCollection<Message> $messages
     * @throws \RuntimeException
     */
    public function send(MessageCollection $messages) {
        // Get the carrier        
        $carrier = $this->getCarrier();
        if (null === $carrier) {
            throw new \RuntimeException('No carrier found');
        }
        // Send messages
        $file = $carrier->send($messages, $this->parameters, $this->getDelivery());
        if($file){
            $this->setFile($file);
        }
    }    
    
    /**
     * Reporting
     * 
     * @param MessageCollection<Message> $messages
     * @throws \RuntimeException
     */
    public function reporting(MessageCollection $messages) {
        // Call the reporter        
        $reporter = $this->getReporter();
        if (null !== $reporter) {
            $reporter->reporting($messages, $this->parameters);
        }
    }    
    
    /**
     * Notify
     * 
     * @param MessageCollection<Message> $messageCollection
     */
    public function notify(MessageCollection $messageCollection) {
        
        // Call the notifier
        $notifier = $this->getNotifier();    
        if (null === $notifier) {
            throw new \RuntimeException('No notifier found');
        }        
        
        $notifier->notify($messageCollection, $this->parameters);
    }    
    
    /**
     * Returns a matching carrier.
     * 
     * @return CarrierInterface|null
     */
    private function getCarrier() {
        foreach ($this->carriers as $carrier) {
            if ($carrier instanceof CarrierInterface && $carrier->supportsMessage($this->parameters)) {
                return $carrier;
            }
        }        
    }
    
    /**
     * Gets encoders
     * 
     * @return array
     */
    public function getCarriers() {
        return $this->carriers;
    }
    
    /**
     * Sets carriers.
     * 
     * @param Array $carriers
     * @return $this
     */
    public function setCarriers(Array $carriers) {
        $this->carriers = $carriers;
        return $this;
    }

    /**
     * Returns a matching normalizer.
     * 
     * @return NormalizerInterface|null
     */
    private function getNormalizer() {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof NormalizerInterface && $normalizer->supportsNormalization($this->parameters)) {
                return $normalizer;
            }
        }        
    }    
        
    /**
     * Gets normalizers
     * 
     * @return Array
     */
    public function getNormalizers() {
        return $this->normalizers;
    }
    
    /**
     * Sets normalizers
     * 
     * @param array $normalizers
     * @return $this
     */
    public function setNormalizers(Array $normalizers) {
        $this->normalizers = $normalizers;
        return $this;
    }    
    /**
     * Gets delivery
     * 
     * @return boolean
     */
    public function getDelivery() {
        return $this->delivery;
    }
    
    /**
     * Sets delivery
     * 
     * @param boolean $delivery
     * @return $this
     */
    public function setDelivery($delivery) {
        $this->delivery = $delivery;
        return $this;
    }
    
    /**
     * Gets reporters
     * 
     * @return Array
     */
    public function getReporters() {
        return $this->reporters;
    }
    
    /**
     * Sets reporters
     * 
     * @param array $reporters
     * @return $this
     */
    public function setReporters(Array $reporters) {
        $this->reporters = $reporters;
        return $this;
    }

    /**
     * Returns a matching reporter.
     * 
     * @return ReportingInterface|null
     */
    private function getReporter() {
        foreach ($this->reporters as $reporter) {
            if ($reporter instanceof ReportingInterface && $reporter->supportsMessage($this->parameters)) {
                return $reporter;
            }
        }        
    }    

    /**
     * Gets notifiers
     * 
     * @return Array
     */
    public function getNotifiers() {
        return $this->notifiers;
    }
    
    /**
     * Returns a matching output.
     * 
     * @return NotifierInterface|null
     */
    private function getNotifier() {
        foreach ($this->notifiers as $notifier) {
            if ($notifier instanceof NotifierInterface && $notifier->supportsNotification($this->parameters)) {
                return $notifier;
            }
        }        
    }    
    
    /**
     * Sets notifiers
     * 
     * @param array $notifiers
     * @return $this
     */
    public function setNotifiers(Array $notifiers) {
        $this->notifiers = $notifiers;
        return $this;
    }

    private function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }
    
}
