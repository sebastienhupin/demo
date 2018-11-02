<?php

namespace Opentalent\OtMigrationTypo8\Install\Updates;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Service\FlexFormService;
use TYPO3\CMS\Core\Resource\FileInterface;
use Opentalent\OtCms\Utility\OpentalentCMSUtility;

/**
 * Description of User Upload
 * Migrate _migrated pics and media files to users upload folder
 *  
 * @author vincent guffon
 */
class RteProcessedFile extends \TYPO3\CMS\Install\Updates\AbstractUpdate {

    /**
     * @var string
     */
    protected $title = 'Opentalent : update bodytext of tt content who have _processed_ file link inside them';


    /**
     * Initialize all required repository and factory objects.
     *
     * @throws \RuntimeException
     */
    protected function init() {
    }

    /**
     * Checks if an update is needed
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is needed (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description) {
        if ($this->isWizardDone()) {
            return false;
        }
        $description = 'move the file inside _migrated folder inside the new user upload folder.';

        $query = $this->getProcessedQuery();

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
    public function performUpdate(array &$databaseQueries, &$customMessages) {

        $this->init();
        $query = $this->getProcessedQuery();

        $statement = $query->select('tt_content.*')->execute();

        while ($record = $statement->fetch()) {
            $this->migrateRecord($record);
        }

        $this->markWizardAsDone();
        return true;
    }

    /**
     * Processes the actual transformation
     *
     * @param array $record
     * @return void
     */
    protected function migrateRecord(array $record) {
        $subject = $record['bodytext'];
        $pattern = '@fileadmin/_processed_/[a-zA-Z0-9._\-]+@i';
        preg_match_all($pattern, $subject, $matches);
        foreach ($matches[0] as $match){
            $matchArray = explode('/', $match);
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_file_processedfile');
            $queryBuilder->getRestrictions()->removeAll();

            $statement = $queryBuilder
                ->select('file.identifier')
                ->from('sys_file_processedfile')
                ->join(
                    'sys_file_processedfile',
                    'sys_file',
                    'file',
                    $queryBuilder->expr()->eq('sys_file_processedfile.original', $queryBuilder->quoteIdentifier('file.uid'))
                    )
                ->where(
                    $queryBuilder->expr()->eq('sys_file_processedfile.name', $queryBuilder->createNamedParameter(array_pop($matchArray)))
                )
                ->execute();

            while ($recordSys = $statement->fetch()) {
                $subject = str_replace($match, 'fileadmin'.$recordSys['identifier'], $subject);
            }
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->update('tt_content')
            ->where(
                $queryBuilder->expr()->eq('tt_content.uid', $queryBuilder->createNamedParameter($record['uid']))
            )
            ->set('bodytext', $subject)
            ->execute();
    }

    /**
     * Get query parts for generic workflow
     *
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected function getProcessedQuery() {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();

        $queryBuilder->from('tt_content')
            ->where(
                $queryBuilder->expr()->like('tt_content.bodytext', $queryBuilder->createNamedParameter('%/_processed_/%'))
            )
        ;

        return $queryBuilder;
    }

}
