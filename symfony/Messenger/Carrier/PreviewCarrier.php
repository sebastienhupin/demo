<?php

namespace AppBundle\Services\Messenger\Carrier;

use Knp\Snappy\GeneratorInterface;
use Gaufrette\Filesystem;
use Doctrine\ORM\EntityManager;
use AppBundle\Services\Messenger\Message;
use AppBundle\Entity\Core\File;
use AppBundle\Enum\Core\FileFolderEnum;
use AppBundle\Enum\Core\FileVisibilityEnum;
use AppBundle\Services\Messenger\Parameters;
use AppBundle\Services\Messenger\MessageCollection;
use AppBundle\Services\Contactor\Contactor;

/**
 * Description of PreviewCarrier
 *
 * @author sebastienhupin
 */
class PreviewCarrier extends AbstractCarrier implements CarrierInterface {
    /**
     *  @var GeneratorInterface
     */
    private $knpSnappy;    
    /**
     *
     * @var Filesystem 
     */
    private $gaufretteFilesystem;    
    /**
     *
     * @var EntityManager 
     */
    private $em;

    private $file;
    
    /**
     * The constructor
     * 
     * @param Contactor $contactor
     * @param GeneratorInterface $knpSnappy
     * @param Filesystem $gaufretteFilesystem
     * @param EntityManager $em
     */
    public function __construct(Contactor $contactor, GeneratorInterface $knpSnappy, Filesystem $gaufretteFilesystem, EntityManager $em) {
        
        parent::__construct($contactor);
        
        $this->knpSnappy = $knpSnappy;
        $this->gaufretteFilesystem = $gaufretteFilesystem;
        $this->em = $em;
    }
    
    /*
     * {@inheritdoc}
     */
    public function send(MessageCollection $messages, Parameters $parameters, $delivery = true) {
        
        $message = $messages->first();
        $content = array();

        foreach($messages as $m) {            
            $content[] = $m->getContent();
        }

        $content = implode('<span style="page-break-after:always;" />', $content);
        
        $content = $this->knpSnappy->getOutputFromHtml($content, array('encoding' => 'utf-8'));

        $message->setContent($content);
        // Cleaning all messages
        $messages->clear();
        $messages->add($message);
        
        $this->store($message, $parameters);

        return $this->getFile();
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function check(\AppBundle\Services\Messenger\MessageCollection $messages, \AppBundle\Services\Messenger\Parameters $parameters) {
        
    }      
    
    /**
     * Store the message to a file.
     * 
     * @param Message $message
     * @params Parameters $parameters
     */
    private function store(Message $message, Parameters $parameters) {
        $fileName = $message->getAbout() . '.pdf';
        $path = $this->getPath($message, $parameters);
        $key = sprintf("%s/%s", $path, $fileName);

        try {
            $this->gaufretteFilesystem->write($key, $message->getContent(), true);
        }
        catch (\RuntimeException $e) {
            throw new \RuntimeException(sprintf('Could not write the "%s" key content.', $key));
        }
        
        $folder = null;
        
        if ('print' === $parameters->getAction()) {
           $folder =  FileFolderEnum::PRINTING;
        }
        else if ('preview' === $parameters->getAction()) {
            $folder =  FileFolderEnum::PREVIEW;
        }
        
        $file = new File();
        $file
            ->setSlug($key)
            ->setName($fileName)
            ->setPath($path)                
            ->setOrganization($message->getOrganization())
            ->setVisibility(FileVisibilityEnum::NOBODY)
            ->setFolder($folder)
            ->setMimeType($this->gaufretteFilesystem->mimeType($key))    
        ;
        
        if ($message->getAccess()) {
            $file->addAccessPerson($message->getAccess()->getPerson());
        }
        
        $this->em->persist($file);
        $this->em->flush($file);  
        
        $message->setFile($file);

        $this->setFile($file);
    }
    
    /**
     * Gets path
     * 
     * @param Message $message
     * @param Parameters $parameters
     * 
     * @return string
     */
    protected function getPath(Message $message, Parameters $parameters) {
        $paths = Array();
        if ($message->getOrganization()) {
            $paths[] = $message->getOrganization()->getId();
        }
        if ($message->getAccess()) {
            $paths = Array($message->getAccess()->getOrganization()->getId(), $message->getAccess()->getId());
        }
        if ('print' === $parameters->getAction()) {
           $paths[] = 'Printing';
        }
        else if ('preview' === $parameters->getAction()) {
            $paths[] = 'Preview';
        }        

        return implode('/', $paths);
    }

    private function setFile($file)
    {
        $this->file = $file;

    }

    public function getFile()
    {
        return $this->file;
    }


    /*
     * {@inheritdoc}
     */    
    public function supportsMessage(\AppBundle\Services\Messenger\Parameters $parameters) {
        return 'print' === $parameters->getAction() || 'preview' === $parameters->getAction();
    }

}
