<?php

namespace AppBundle\Services\Exporter;
use AppBundle\Entity\AccessAndFunction\Access;
use AppBundle\Entity\Organization\Organization;
use AppBundle\Entity\Core\File;

/**
 * Description of Document
 *
 * @author sebastienhupin
 */
class Document {
    /**
     *
     * @var Access 
     */
    protected $access;
    /**
     *
     * @var Organization 
     */
    protected $organization;
    /**
     *
     * @var string 
     */
    protected $name;
    /**
     *
     * @var string 
     */
    protected $folder;
    /**
     *
     * @var string 
     */
    protected $content;
    /**
     *
     * @var File 
     */
    protected $file;

    /**
     *
     * @var File
     */
    protected $entityLinkToFile;

    /**
     *
     * @var string
     */
    protected $cover;

    /**
     *
     * @var string
     */
    protected $header;

    /**
     *
     * @var string
     */
    protected $footer;

    /**
     *
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $replace = false;

    /**
     * Gets access
     * 
     * @return Access
     */
    public function getAccess() {
        return $this->access;
    }
    
    /**
     * Sets access
     * 
     * @param Access $access
     * @return $this
     */
    public function setAccess(Access $access) {
        $this->access = $access;
        return $this;
    }
    
    /**
     * Gets organization
     * 
     * @return Organization
     */
    public function getOrganization() {
        return $this->organization;
    }
    
    /**
     * Sets organization
     * 
     * @param Organization $organization
     * @return $this
     */
    public function setOrganization(Organization $organization) {
        $this->organization = $organization;
        return $this;
    }
    /**
     * Gets name
     * 
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Sets name
     * 
     * @param string $name
     * @return $this
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Gets folder
     * 
     * @return string
     */
    public function getFolder() {
        return $this->folder;
    }

    /**
     * Sets folder
     * 
     * @param string $folder
     * @return $this
     */
    public function setFolder($folder) {
        $this->folder = $folder;
        return $this;
    }

    /**
     * Gets content
     * 
     * @return string
     */
    public function getContent() {
        return $this->content;
    }
    
    /**
     * Sets content
     * 
     * @param string $content
     * @return $this
     */
    public function setContent($content) {
        $this->content = $content;
        return $this;
    }
    
    /**
     * Gets the file
     * 
     * @return File
     */
    public function getFile() {
        return $this->file;
    }
    
    /**
     * Sets the file
     * 
     * @param File $file
     * @return $this
     */
    public function setFile(File $file) {
        $this->file = $file;
        return $this;
    }

    /**
     * Gets the entityLinkToFile
     *
     */
    public function getEntityLinkToFile() {
        return $this->entityLinkToFile;
    }

    /**
     * Sets the file
     *
     * @param $entityLinkToFile
     * @return $this
     */
    public function setEntityLinkToFile($entityLinkToFile) {
        $this->entityLinkToFile = $entityLinkToFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getCover() {
        return $this->cover;
    }

    /**
     * @param $cover
     * @return $this
     */
    public function setCover($cover) {
        $this->cover = $cover;
        return $this;
    }


    /**
     * @return string
     */
    public function getHeader() {
        return $this->header;
    }

    /**
     * @param $header
     * @return $this
     */
    public function setHeader($header) {
        $this->header = $header;
        return $this;
    }

    /**
     * @return string
     */
    public function getFooter() {
        return $this->footer;
    }

    /**
     * @param $footer
     * @return $this
     */
    public function setFooter($footer) {
        $this->footer = $footer;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function getReplace() {
        return $this->replace;
    }

    /**
     * @param $replace
     * @return $this
     */
    public function setReplace($replace) {
        $this->replace = $replace;
        return $this;
    }
}
