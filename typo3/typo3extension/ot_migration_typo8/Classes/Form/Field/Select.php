<?php

namespace Opentalent\OtMigrationTypo8\Form\Field;

/**
 * Description of Select
 *
 * @author sebastienhupin
 */
class Select implements FieldInterface {
    
    const TYPE = 'SELECT';

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
     * @var Array<\Opentalent\OtMigrationTypo8\Form\Field\Option> 
     */
    protected $options = array();
    
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
     * Gets options
     * 
     * @return Array<\Opentalent\OtMigrationTypo8\Form\Field\Option> 
     */
    public function getOptions() {
        return $this->options;
    }
    
    /**
     * Sets options
     * 
     * @param Array<\Opentalent\OtMigrationTypo8\Form\Field\Option> $options
     * @return $this
     */
    public function setOptions(array $options) {
        $this->options = $options;
        return $this;
    }

    /**
     * Add an option
     * 
     * @param \Opentalent\OtMigrationTypo8\Form\Field\Option $option
     * @return $this
     */
    public function addOption(\Opentalent\OtMigrationTypo8\Form\Field\Option $option) {
        $this->options[] = $option;
        return $this;
    }    
    
    public function getDefaultValue() {
        if (empty($this->options)) {
            return '';
        }
        return $this->options[0]->getValue();
    }
}
