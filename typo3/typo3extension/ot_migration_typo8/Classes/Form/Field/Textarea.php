<?php

namespace Opentalent\OtMigrationTypo8\Form\Field;

/**
 * Description of Textarea
 *
 * @author sebastienhupin
 */
class Textarea implements FieldInterface {
    
    const TYPE = 'TEXTAREA';
    
    /**
     *
     * @var string
     */
    protected $type = 'textarea';    
    
    /**
     *
     * @var string
     */    
    protected $name;
    /**
     *
     * @var string
     */    
    protected $label;
    /**
     *
     * @var bool 
     */
    protected $required = false;
    /**
     *
     * @var int 
     */
    protected $cols = 40;
    /**
     *
     * @var int 
     */
    protected $rows = 5;
        
    /**
     * Gets the type
     * 
     * @return string
     */
    public function getType() {
        return $this->type;
    }
    
    /**
     * Sets the type
     * 
     * @param type $type
     * @return $this
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }    
    
    /**
     * Gets the name
     * 
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Sets the name
     * 
     * @param string $name
     * @return $this
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Gets the label
     * 
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }
    
    /**
     * Sets the label
     * 
     * @param string $label
     * @return $this
     */
    public function setLabel($label) {
        $this->label = $label;
        return $this;
    }
    
    /**
     * Get is required
     * 
     * @return bool
     */
    public function getRequired() {
        return $this->required;
    }
    
    /**
     * Sets is required
     * 
     * @param bool $required
     * @return $this
     */
    public function setRequired(bool $required) {
        $this->required = $required;
        return $this;
    }
    
    /**
     * Is required
     * 
     * @return bool
     */
    public function isRequired() {
        return $this->required;
    }        
    /**
     * Gets column width
     * 
     * @return int
     */
    public function getCols() {
        return $this->cols;
    }
    
    /**
     * Sets column width
     * 
     * @param int $cols
     * @return $this
     */
    public function setCols($cols) {
        $this->cols = $cols;
        return $this;
    }
    
    /**
     * Gets row height
     * 
     * @return int
     */
    public function getRows() {
        return $this->rows;
    }
    
    /**
     * Set row height
     * 
     * @param int $rows
     * @return $this
     */
    public function setRows(int $rows) {
        $this->rows = $rows;
        return $this;
    }

}
