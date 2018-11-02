<?php

namespace Opentalent\OtMigrationTypo8\Install\Updates;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Description of BeUserUpdate
 *
 * @author sebastienhupin
 */
class BeUserUpdate extends \TYPO3\CMS\Install\Updates\AbstractUpdate {
    /**
     * @var string
     */
    protected $title = 'Opentalent : Add editing frontend for all be users with the "Association writer group"';    
    
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
        $statement = $this->getBeUsers();
        
        while ($row = $statement->fetch()) {
            $id = (int)$row['uid'];
			$BE_USER = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');
			$user = $BE_USER->getRawUserByUid($id);
                        $BE_USER->user = $user;
                        $BE_USER->backendSetUC();
                        $BE_USER->uc['frontend_editing'] = 1;
			$BE_USER->writeUC($BE_USER->uc); 

        }
        $this->markWizardAsDone();
        return true;
    }       
    
    /**
     * Gets all root pages
     * 
     * @return Statement
     */
    protected function getBeUsers() {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('be_users');        
        $queryBuilder->getRestrictions()->removeAll();  
        
         return $queryBuilder
            ->select('be_users.*')    
            ->from('be_users')
            ->where(
               $queryBuilder->expr()->eq('be_users.usergroup', $queryBuilder->createNamedParameter(3, \PDO::PARAM_INT))         
            ) 
            ->execute()     
        ;        
    }    
    
}
