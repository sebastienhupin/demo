<?php

namespace Opentalent\OtMigrationTypo8\Install\Updates;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Description of FormFolderUpdate
 * Create all folder needed by the new form ext
 * 
 * @author sebastienhupin
 */
class FormFolderUpdate extends \TYPO3\CMS\Install\Updates\AbstractUpdate {
    /**
     * @var string
     */
    protected $title = 'Opentalent :  Create all folder needed by the form extension';    
    
    /**
     * Checks if an update is needed
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is needed (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        return true;
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
        $statement = $this->getRootPages();
        
        while ($row = $statement->fetch()) {
            $structureId = (int)$row['tx_opentalent_structure_id'];
            if (0 === $structureId) {
                continue;
            }
            $this->createFormFolder($structureId);
        }
        $this->markWizardAsDone();
        return true;
    }   
    
    /**
     * Create the form folder for the structure
     * 
     * @param int $structureId
     */
    protected function createFormFolder(int $structureId) {
        GeneralUtility::mkdir_deep(sprintf('%s%suser_upload/%d/Forms/', PATH_site, $GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'],$structureId));        
    }
    
    /**
     * Gets all root pages
     * 
     * @return Statement
     */
    protected function getRootPages() {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');        
        $queryBuilder->getRestrictions()->removeAll();  
        
         return $queryBuilder
            ->select('pages.*')    
            ->from('pages')
            ->where(
               $queryBuilder->expr()->eq('pages.is_siteroot', $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT))         
            ) 
            ->execute()     
        ;        
    }
}
