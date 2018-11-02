<?php

namespace AppBundle\Services\Exporter\Storage;

use AppBundle\Enum\Core\FileTypeEnum;
use Gaufrette\Filesystem;
use AppBundle\Entity\Core\File;
use AppBundle\Enum\Core\FileVisibilityEnum;
use AppBundle\Enum\Core\FileFolderEnum;
use AppBundle\Services\AccessService;
use Doctrine\ORM\EntityManager;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Description of DefaultStorage
 *
 * @author sebastienhupin
 */
class DefaultStorage implements StorageInterface {

    /**
     *
     * @var AccessService 
     */
    private $accessService;
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
    
    /**
     * The constructor
     * 
     * @param AccessService $accessService
     * @param Filesystem $gaufretteFilesystem
     * @param EntityManager $em
     */
    public function __construct(AccessService $accessService, Filesystem $gaufretteFilesystem, EntityManager $em) {
        $this->accessService = $accessService;
        $this->gaufretteFilesystem = $gaufretteFilesystem;
        $this->em = $em;
    }

    /**
     * Store a document
     * 
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    public function store(DocumentCollection $documentCollection, ExportParameters $exportParameters = null) {
        foreach($documentCollection as $document) {
            $fileName = $document->getName();
            $path = $this->getPath($document);
            $key = sprintf("%s/%s", $path, $fileName);
            try {
                $this->gaufretteFilesystem->write($key, $document->getContent(), true);
            }
            catch (\RuntimeException $e) {
                throw new \RuntimeException(sprintf('Could not write the "%s" key content.', $key));
            }

            if($document->getReplace()){
                $fileRepo = $this->em->getRepository('AppBundle:Core\File');
                $messageRepo = $this->em->getRepository('AppBundle:Message\Message');
                $reportMessageRepo = $this->em->getRepository('AppBundle:Message\ReportMessage');
                $files = $fileRepo->findByDocument($document);
                foreach ($files as $file){
                    $messages = $messageRepo->findByFiles($file);
                    foreach ($messages as $message){
                        $reportMessages = $reportMessageRepo->findBy(array('message' => $message));
                        foreach ($reportMessages as $reportMessage){
                            $this->em->remove($reportMessage);
                        }
                        $this->em->remove($message);
                    }
                    $this->em->remove($file);
                }
            }

            $file = new File();
            $file
                ->setSlug($key)
                ->setName($fileName)
                ->setPath($path)
                ->setOrganization($document->getOrganization())
                ->setVisibility(FileVisibilityEnum::NOBODY)
                ->setFolder(FileFolderEnum::DOCUMENTS)
                ->setMimeType($this->gaufretteFilesystem->mimeType($key))
            ;

            if($document->getType() && FileTypeEnum::isValid($document->getType())){
                $file->setType($document->getType());
            }

            if ($document->getAccess()) {
                $file->addAccessPerson($document->getAccess()->getPerson());
            }

            if($document->getEntityLinkToFile()){
                $document->getEntityLinkToFile()->setFile($file);
                if ($document->getAccess()) {
                    $file->setPerson($document->getAccess()->getPerson());
                }
            }

            $this->em->persist($file);
            $this->em->flush();
            $document->setFile($file);
        }
    }    
    
    /**
     * Gets path
     * 
     * @param Document $document
     * @return string
     */
    protected function getPath(Document $document) {
        $paths = Array();
        if ($document->getOrganization()) {
            $paths[] = $document->getOrganization()->getId();
        }
        if ($document->getAccess()) {
            $paths = Array($document->getAccess()->getOrganization()->getId(), $document->getAccess()->getId());
        }
        $paths[] = $document->getFolder();
        
        return implode('/', $paths);
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsStorage(ExportParameters $exportParameters) {
        return true;
    }
}
