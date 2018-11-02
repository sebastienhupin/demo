<?php

namespace Opentalent\OtMigrationTypo8\Form;

/**
 * Description of MailForm
 *
 * @author sebastienhupin
 */
class MailForm {
    /**
     *
     * @var string 
     */
    protected $label;
    /**
     *
     * @var string 
     */
    protected $identifier;            
    /**
     *
     * @var string 
     */
    protected $subject;
    /**
     *
     * @var string 
     */
    protected $recipientAddress;
    /**
     *
     * @var string 
     */
    protected $senderAddress;
    /**
     *
     * @var string 
     */
    protected $submitLabel;
    /**
     *
     * @var Array<\Opentalent\OtMigrationTypo8\Form\Field\FieldInterface> 
     */
    protected $fields;
    
    /**
     * The constructor
     */
    public function __construct() {
        $this->fields = array();
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
     * Gets the identifier
     * 
     * @return string
     */
    public function getIdentifier() {
        return $this->identifier;
    }
    
    /**
     * Sets the identifier
     * 
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier) {
        $this->identifier = $identifier;
        return $this;
    }
        
    /**
     * Gets the subject
     * 
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }
    
    /**
     * Sets the subject
     * 
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }
    
    /**
     * Gets the recipient email
     * 
     * @return string
     */
    public function getRecipientAddress() {
        return $this->recipientAddress;
    }
    
    /**
     * Sets the recipient email
     * 
     * @param string $recipientAddress
     * @return $this
     */
    public function setRecipientAddress($recipientAddress) {
        $this->recipientAddress = $recipientAddress;
        return $this;
    }
    
    /**
     * Gets the sender email
     * 
     * @return string
     */
    public function getSenderAddress() {
        return $this->senderAddress;
    }

    /**
     * Sets the sender email
     * 
     * @param  steing $senderAddress
     * @return $this
     */
    public function setSenderAddress($senderAddress) {
        $this->senderAddress = $senderAddress;
        return $this;
    }    
    
    /**
     * Gets the submit label
     * 
     * @return string
     */
    public function getSubmitLabel() {
        return $this->submitLabel;
    }
    
    /**
     * Sets the submit label
     * 
     * @param string $submitLabel
     * @return $this
     */
    public function setSubmitLabel($submitLabel) {
        $this->submitLabel = $submitLabel;
        return $this;
    }
            
    /**
     * Gets fields
     * 
     * @return Array<\Opentalent\OtMigrationTypo8\Form\Field\FieldInterface>
     */
    public function getFields() {
        return $this->fields;
    }
    
    /**
     * Sets fields
     * 
     * @param Array<\Opentalent\OtMigrationTypo8\Form\Field\FieldInterface> $fields
     * @return $this
     */
    public function setFields($fields) {
        $this->fields = $fields;
        return $this;
    }
    
    /**
     * Add a field
     * 
     * @param \Opentalent\OtMigrationTypo8\Form\Field\FieldInterface $field
     * @return $this
     */
    public function addField(\Opentalent\OtMigrationTypo8\Form\Field\FieldInterface $field) {
        $this->fields[] = $field;
        return $this;
    }
    
    /**
     * Return the object parsed to a yaml array
     * 
     * @return array
     */
    public function toYamlArray() {
        $mailformSetup = array(
            'renderingOptions' => array(
                'submitButtonLabel' => $this->getSubmitLabel()   
            ),
            'type' => 'Form',
            'identifier'=> $this->getIdentifier(),
            'label'=> $this->getLabel(),
            'prototypeName'=> 'standard',
            'finishers' => array(
                array(
                    'options' => array(
                        'subject' => $this->getSubject(),
                        'recipientAddress' => $this->getRecipientAddress(),
                        'recipientName' => '',
                        'senderAddress' => $this->getSenderAddress(),
                        'senderName' => '',
                        'replyToAddress' => '',
                        'carbonCopyAddress' => '',
                        'blindCarbonCopyAddress' => '',
                        'format' => 'html',
                        'attachUploads' => true,
                        'translation' => array('language' => ''),                        
                    ),
                    'identifier' => 'EmailToReceiver'
                )
            ),
            'renderables' => array(
                array(
                    'renderingOptions' => array(
                        'previousButtonLabel' => 'Previous step',
                        'nextButtonLabel' => 'Next step'                                                                        
                    ),
                    'type' => 'Page',
                    'identifier' => 'page-1',
                    'label' => 'Step',
                    'renderables' => $this->fieldsToYamlArray()
                )
            )
        );

        return $mailformSetup;
    }
    
    /**
     * Parse fields to a yaml array
     * 
     * @return array
     */
    protected function fieldsToYamlArray() {
        $fields = array();
        foreach($this->fields as $index => $field) {

            $fields[] = $this->fieldToYamlArray($field, $index);
        }
        return $fields;
    }
    
    /**
     * Parse field to a yaml array
     * 
     * @param \Opentalent\OtMigrationTypo8\Form\Field\FieldInterface $field
     * @param int $index
     */    
    protected function fieldToYamlArray(\Opentalent\OtMigrationTypo8\Form\Field\FieldInterface $field, int $index) {
        $f = array();
        switch ($field::TYPE) {
            case 'HIDDEN':
                $f = array(
                    'defaultValue' => sprintf("'%s'", $field->getValue()),
                    'type' => ucfirst($field->getType()),
                    'identifier' => sprintf('hidden-%d',$index),
                    'label' => $field->getName()                    
                );
                break;
            case 'INPUT':
                $f = array(
                    'defaultValue' => '',
                    'type' => ucfirst($field->getType()),
                    'identifier' => sprintf('text-%d',$index),
                    'label' => $field->getLabel()
                );
                $this->setRequiredField($field, $f);    
                break;   
            case 'PASSWORD':
                $f = array(
                    'defaultValue' => '',
                    'type' => ucfirst($field->getType()),
                    'identifier' => sprintf('password-%d',$index),
                    'label' => $field->getLabel()
                );
                $this->setRequiredField($field, $f);    
                break;               
            case 'SELECT':
                $options = array();
                foreach($field->getOptions() as $option) {
                   $options[$option->getText()] = $option->getValue(); 
                }
                $f = array(
                    'properties' => array(
                        'options' => $options
                    ),    
                    'type' => 'SingleSelect',
                    'identifier' => sprintf('singleselect-%d',$index),
                    'label' => $field->getLabel(),
                    'defaultValue' => $field->getDefaultValue()                
                );
                $this->setRequiredField($field, $f);                
                break; 
            case 'RADIO':
                $options = array();
                foreach($field->getOptions() as $option) {
                   $options[$option->getText()] = $option->getValue(); 
                }
                $f = array(
                    'properties' => array(
                        'options' => $options
                    ),    
                    'type' => 'RadioButton',
                    'identifier' => sprintf('radiobutton-%d',$index),
                    'label' => $field->getLabel(),
                    'defaultValue' => $field->getDefaultValue()                    
                );
                $this->setRequiredField($field, $f);              
                break;  
            case 'CHECKBOX':
                $f = array(
                    'type' => ucfirst($field->getType()),
                    'identifier' => sprintf('checkbox-%d',$index),
                    'label' => $field->getLabel()                    
                );
                $this->setRequiredField($field, $f);               
                break;                
            case 'FILE':
                $f = array(
                    'properties' => array(
                      'saveToFileMount' =>  '1:/user_upload/',
                      'allowedMimeTypes' => array(
                        'application/pdf'
                      ),    
                    ),    
                    'type'=> 'FileUpload',
                    'identifier' => sprintf('fileupload-%d', $index),
                    'label' => $field->getLabel()                    
                );
                $this->setRequiredField($field, $f);
                break;
            case 'TEXTAREA':
                $f = array(
                    'defaultValue' => '',
                    'type' => ucfirst($field->getType()),
                    'identifier' => sprintf('textarea-%d',$index),
                    'label' => $field->getLabel()                 
                );         
                $this->setRequiredField($field, $f);
                break;            
        }
        return $f;
    }
    
    /**
     * Set definition for field has required
     * 
     * @param \Opentalent\OtMigrationTypo8\Form\Field\FieldInterface $field
     * @param array $def
     */
    protected function setRequiredField($field, &$def) {
        if ($field->isRequired()) {
            $def['properties'] = array(
                'fluidAdditionalAttributes' => array(
                    'required' => 'required'
                )
            );
            $def['validators'] = array(
                array('identifier' => 'NotEmpty')
            );
        }            
    }
}
