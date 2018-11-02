<?php

namespace Opentalent\OtMigrationTypo8\Form\Field;

/**
 * Description of Hidden
 *
 * @author sebastienhupin
 */
class Hidden implements FieldInterface {
    
    const TYPE = 'HIDDEN';
    
    /**
     *
     * @var string
     */
    protected $type = 'hidden';
    /**
     *
     * @var string
     */    
    protected $name;
    /**
     *
     * @var string
     */    
    protected $value;

    /**
     * Gets the type
     * 
     * @return string
     */
    public function getType() {
        return $this->type;
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
     * Gets the value
     * 
     * @return string
     */
    public function getValue() {
        return $this->label;
    }
    
    /**
     * Sets the value
     * 
     * @param string $value
     * @return $this
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }   
}
