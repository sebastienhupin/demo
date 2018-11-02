<?php

namespace Opentalent\OtMigrationTypo8\Install\Updates;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use Symfony\Component\Yaml\Yaml;
use Opentalent\OtMigrationTypo8\Form\MailForm;
use Opentalent\OtMigrationTypo8\Form\Field\Hidden;
use Opentalent\OtMigrationTypo8\Form\Field\Input;
use Opentalent\OtMigrationTypo8\Form\Field\Password;
use Opentalent\OtMigrationTypo8\Form\Field\Textarea;
use Opentalent\OtMigrationTypo8\Form\Field\Select;
use Opentalent\OtMigrationTypo8\Form\Field\Option;
use Opentalent\OtMigrationTypo8\Form\Field\Radio;
use Opentalent\OtMigrationTypo8\Form\Field\File;
use Opentalent\OtMigrationTypo8\Form\Field\Checkbox;
use Opentalent\OtCms\Utility\OpentalentCMSUtility;

/**
 * Description of MailFormDataUpdate
 * Migrate "mailform" data to new 8.0 ext:form
 *  
 * @author sebastienhupin
 */
class MailFormDataUpdate extends \TYPO3\CMS\Install\Updates\AbstractUpdate {
    /**
     *
     * @var string
     */
    protected $currentIdentifier;
    /**
     * @var array
     */
    protected $currentRow = [];
    /**
     * @var string
     */
    protected $title = 'Opentalent : Migrate "mailform" data to new 7.6 ext:form';
    /**
     * Checks if an update is needed
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is needed (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        if ($this->isWizardDone()) {
            return false;
        }
        $description = 'As the old mailform is moved to compatibility6 (since 7), this migration is to move the data to the new mailform functionality.';
                
        $query = $this->getMigratesQuery();
        
        $count = $query->count('*')
            ->execute()
            ->fetchColumn(0);
        if ($count > 0) {
            return true;
        }
        
        return false;
    }
    /**
     * Performs the database migrations if requested
     *
     * @param array &$databaseQueries Queries done in this update
     * @param mixed &$customMessages Custom messages
     * @return boolean
     */
    public function performUpdate(array &$databaseQueries, &$customMessages)
    {

        $query = $this->getMigratesQuery();
        
        $statement = $query->select('tt_content.*')->execute();
        //$databaseQueries[] = $query->getSQL();    
        
        while ($row = $statement->fetch()) {
            $this->currentRow = $row;
            $rootPageUid = OpentalentCMSUtility::retrieveTheRootPageUid($this->currentRow['pid']);
            if (null === $rootPageUid) {
                $customMessages .= sprintf('<br /><br />The root page id can not be found for Page : %d ', $this->currentRow['uid']);
                continue;
                // Exception(sprintf('Opentalent structure id not found for Page: %d '), $rootPageUid);
            }            
            $structureId = OpentalentCMSUtility::getOpentalentStructureId($rootPageUid);

            if (null === $structureId) {
               $customMessages .= sprintf('<br /><br />Opentalent structure id can not be found for the root Page: %d ', $rootPageUid);
               continue;
                // Exception(sprintf('Opentalent structure id not found for Page: %d '), $rootPageUid);
            }
            
            $formConfiguration = $this->getNewFormSetup($row);
            
            $yaml = Yaml::dump($formConfiguration, 99, 2);            
            $this->save($row, $structureId, $yaml);
            
        }
        $this->markWizardAsDone();
        return true;
    }
    
    /**
     * 
     * @param array $row
     * @param int $structureId
     * @param string $yaml
     */
    protected function save(array $row, int $structureId, $yaml) {
        
        $this->saveToFile($structureId, $yaml);
        $this->saveToBdd($row, $structureId);
    }
    /**
     * 
     * @param int $structureId
     * @param string $yaml
     */
    protected function saveToFile(int $structureId, $yaml) {
        $fileadminDir = $GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'];
        $formDir = sprintf('%s%suser_upload/%d/Forms/', PATH_site, $fileadminDir,$structureId);
        GeneralUtility::mkdir_deep($formDir);
        $formFile = sprintf('%s%s.yaml', $formDir, $this->currentIdentifier);
        file_put_contents($formFile, $yaml);
    }

    /**
     * 
     * @param array $row
     * @param int $structureId
     */
    protected function saveToBdd(array $row, int $structureId) {
        // Remove and replace
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');        
        $queryBuilder->getRestrictions()->removeAll();
        // CType = form_formframework
        // bodytext = NULL
        // subheader = ''
        $formFileMount = sprintf('1:/user_upload/%d/Forms/%s.yaml', $structureId, $this->currentIdentifier);        
        $pi_flexform = <<<EOT
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
    <data>
        <sheet index="sDEF">
            <language index="lDEF">
                <field index="settings.persistenceIdentifier">
                    <value index="vDEF">{$formFileMount}</value>
                </field>
                <field index="settings.overrideFinishers">
                    <value index="vDEF">0</value>
                </field>
            </language>
        </sheet>
    </data>
</T3FlexForms>                
EOT;

        $queryBuilder
            ->update('tt_content')
            ->where(
                $queryBuilder->expr()->eq('tt_content.uid', $queryBuilder->createNamedParameter($row['uid'], \PDO::PARAM_INT))
            )
            ->set('tt_content.CType', 'form_formframework')
            ->set('tt_content.bodytext', NULL)
            ->set('tt_content.subheader', '')
            ->set('tt_content.pi_flexform', $pi_flexform)
            ->execute()
        ;    
        //debug($row['uid']);
    }


    /**
     * Generate an yaml array from the old form configuration
     *
     * @param array $row
     * @return array
     */
    protected function getNewFormSetup($row)
    {
        $mailForm = $this->parseForm($row);
        $mailForm->setSenderAddress(MailUtility::getSystemFromAddress());
        
        return $mailForm->toYamlArray();
    }
    
    /**
     * 
     * @param array $row
     * @return \Opentalent\OtMigrationTypo8\Install\Updates\MaifForm
     */
    protected function parseForm($row) {
        $mailForm = new MailForm();
        if (empty($row['header'])) {
            $label = sprintf('UndefinedForm %d', $row['uid']);
        }
        else {
            $label = $row['header'];            
        }
        
        $this->currentIdentifier = $this->slugify($label);
        $mailForm->setLabel($label);
        $mailForm->setIdentifier($this->currentIdentifier);
        $mailForm->setRecipientAddress($row['subheader']);
        $mailForm->setSenderAddress(MailUtility::getSystemFromAddress());
        
        $formFields = GeneralUtility::trimExplode(LF, $row['bodytext'], true);

        foreach ($formFields as $fieldDef) {
            $this->addFieldSetup($fieldDef, $mailForm);
        }        
        
        return $mailForm;
    }


    /**
     * Calculate field setup based on setup line
     *
     * @param string $setupLine
     * @param \Opentalent\OtMigrationTypo8\Form\MaifForm $mailForm
     * @throws \Exception
     */
    protected function addFieldSetup($setupLine, \Opentalent\OtMigrationTypo8\Form\MailForm $mailForm)
    {

        $configs = explode('|', $setupLine);
        $label = trim(array_shift($configs));
        list($name, $typeSetup) = explode('=', array_shift($configs));
        $name = trim($name);
        $required = false;
        if (strpos($name, '*') === 0) {
            $name = substr($name, 1);
            $required = true;
        }
        $typeSetup = explode(',', $typeSetup);
        $type = trim($typeSetup[0]);
        $options = trim(implode('|', $configs));
        unset($configs);
        switch ($type) {
            case 'input':
                $field = new Input();
                $field->setName($name)
                      ->setLabel($label)
                      ->setRequired($required)  
                ;
                $mailForm->addField($field);
                break;
            case 'password':
                $field = new Password();
                $field->setName($name)
                      ->setLabel($label)
                      ->setRequired($required)  
                ;
                $mailForm->addField($field);
                break;            
            case 'textarea':
                $field = new Textarea();
                $field->setName($name)
                      ->setLabel($label)
                      ->setCols(($typeSetup[1] ?: 40)) 
                      ->setRows(($typeSetup[2] ?: 5))
                      ->setRequired($required)  
                ;
                break;
            case 'select':
                $field = new Select();
                $field->setName($name)
                      ->setLabel($label)
                      ->setRequired($required);  
                $values = explode(',', $options);

                foreach ($values as $i => $opt) {
                    $option = new Option();
                    $option->setText(trim($opt))
                           ->setValue(trim($opt))
                    ;        

                    // Make sure first is selected
                    if (0 === $i) {$option->setSelected (true);}
                    $field->addOption($option);
                }
                $mailForm->addField($field);
                break;
            case 'radio':
                $field = new Radio();
                $field->setName($name)
                      ->setLabel($label)
                      ->setRequired($required);  
                $values = explode(',', $options);

                foreach ($values as $i => $opt) {
                    $option = new Option();
                    $option->setText(trim($opt))
                           ->setValue(trim($opt))
                    ;        

                    // Make sure first is selected
                    if (0 === $i) {$option->setSelected (true);}
                    $field->addOption($option);
                }
                $mailForm->addField($field);
                break;    
            case 'check':
                $field = new Checkbox();
                $field->setName($name)
                      ->setLabel($label)
                      ->setRequired($required)  
                ;
                $mailForm->addField($field);
                break;                
            case 'hidden':                
                if ($name === 'subject') {
                    $mailForm->setSubject($options);                    
                } elseif ($name === 'html_enabled') {
                    // Deprecated..
                } else {
                    $field = new Hidden();
                    $field->setName($name)
                          ->setValue($options)
                    ;
                    $mailForm->addField($field);
                }
                break;
            case 'file':
                $field = new File();
                $field->setName($name)
                      ->setLabel($label)
                      ->setRequired($required)  
                ;
                $mailForm->addField($field);                
                break;
            case 'submit':
                $mailForm->setSubmitLabel($options);
                // name = $name
                break;
            case '':
                // No form setup in this line, maybe a comment?
                break;
            default:
                echo "<pre>";
                var_dump($this->currentRow['bodytext']);
                echo '</pre>';
                echo 'Unknown type: ' . $type . ' - Page: ' . $this->currentRow['pid'];
                die();
                //throw new \Exception('Unknown type: ' . $type . ' - Page: ' . $this->currentRow['pid']);
        }
    }
    /**
     * Get query parts for generic workflow
     *
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected function getMigratesQuery()
    {        
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');        
        $queryBuilder->getRestrictions()->removeAll();  
        
        $queryBuilder->from('tt_content')
        ->join(
            'tt_content',
            'pages',
            'pages',
            $queryBuilder->expr()->eq('pages.uid', $queryBuilder->quoteIdentifier('tt_content.pid'))
        )
        ->where(
            $queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter('mailform'))        
        )
        ->andWhere(
            $queryBuilder->expr()->neq('tt_content.subheader', $queryBuilder->createNamedParameter(''))            
        ) 
        ->andWhere(
            $queryBuilder->expr()->like('tt_content.bodytext', $queryBuilder->createNamedParameter('%formtype_mail=submit%'))            
        )                
        ->andWhere(
            $queryBuilder->expr()->eq('tt_content.hidden', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))            
        )  
        ->andWhere(
            $queryBuilder->expr()->eq('tt_content.deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))            
        )  
        ->andWhere(
            $queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))            
        )  
        ->andWhere(
            $queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))            
        )                           
        ;        

        return $queryBuilder;
    }    
    
    /**
     * Return the slug of a string.
     * @param type $text
     * @return string
     */
    protected function slugify($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicated - symbols
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'Udefined';
        }

        return $text;
    }    
    
}
