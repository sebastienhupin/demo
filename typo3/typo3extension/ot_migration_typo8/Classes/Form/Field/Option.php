<?php

namespace Opentalent\OtMigrationTypo8\Form\Field;

/**
 * Description of Option
 *
 * @author sebastienhupin
 */
class Option implements FieldInterface {
    
    const TYPE = 'OPTION';
    
    /**
     *
     * @var string
     */    
    protected $text;
    /**
     *
     * @var string
     */    
    protected $value;
    /**
     *
     * @var bool 
     */
    protected $selected = false;
    
    /**
     * Gets the text
     * 
     * @return string
     */
    public function getText() {
        return $this->text;
    }
    
    /**
     * Sets the text
     * 
     * @param string $text
     * @return $this
     */
    public function setText($text) {
        $this->text = $text;
        return $this;
    }
    
    /**
     * Gets the value
     * 
     * @return string
     */
    public function getValue() {
        return $this->value;
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
    
    /**
     * Gets is selected
     * 
     * @return bool
     */
    public function getSelected() {
        return $this->selected;
    }
    
    /**
     * Sets is selected
     * 
     * @param bool $selected
     * @return $this
     */
    public function setSelected(bool $selected) {
        $this->selected = $selected;
        return $this;
    }

    /**
     * Is selected
     * 
     * @return bool
     */
    public function isSelected() {
        return $this->selected;
    }    

}
