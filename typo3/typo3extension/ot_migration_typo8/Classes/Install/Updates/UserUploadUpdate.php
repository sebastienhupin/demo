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
class UserUploadUpdate extends \TYPO3\CMS\Install\Updates\AbstractUpdate {

    const UPLAOD_FOLDER_USERS_UPLOAD = 'uploads/user_upload/';
    const FOLDER_ContentUploads = '_migrated/content_uploads';

    /**
     * @var string
     */
    protected $title = 'Opentalent : Migrate "_migrated" data to user upload';

    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceStorage
     */
    protected $storage;

    /**
     * @var \TYPO3\CMS\Core\Resource\Index\FileIndexRepository
     */
    protected $fileIndexRepository;

    /**
     * @var string
     */
    protected $targetDirectory;

    /**
     * Initialize all required repository and factory objects.
     *
     * @throws \RuntimeException
     */
    protected function init() {
        $fileadminDirectory = rtrim($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'], '/') . '/';
        /** @var $storageRepository \TYPO3\CMS\Core\Resource\StorageRepository */
        $storageRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\StorageRepository');
        $storages = $storageRepository->findAll();

        foreach ($storages as $storage) {
            $storageRecord = $storage->getStorageRecord();
            $configuration = $storage->getConfiguration();
            $isLocalDriver = $storageRecord['driver'] === 'Local';
            $isOnFileadmin = !empty($configuration['basePath']) && GeneralUtility::isFirstPartOfStr($configuration['basePath'], $fileadminDirectory);
            if ($isLocalDriver && $isOnFileadmin) {
                $this->storage = $storage;
                break;
            }
        }
        if (!isset($this->storage)) {
            throw new \RuntimeException('Local default storage could not be initialized - might be due to missing sys_file* tables.');
        }

        $this->fileIndexRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\Index\\FileIndexRepository');
        $this->targetDirectory = PATH_site . $fileadminDirectory . self::FOLDER_ContentUploads . '/';
        if (!is_dir($this->targetDirectory)) {
            GeneralUtility::mkdir_deep($this->targetDirectory);
        }
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
    public function performUpdate(array &$databaseQueries, &$customMessages) {

        $this->init();
        $query = $this->getMigratesQuery();

        $statement = $query->select('sys_file.*')->execute();

        while ($record = $statement->fetch()) {
            var_dump($record);
          //  $this->migrateRecord($record);
        }

        return false;
        //$this->markWizardAsDone();
        return true;
    }

    /**
     * Processes the actual transformation
     *
     * @param array $record
     * @return void
     */
    protected function migrateRecord(array $record) {
        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        $flexForm = $flexFormService->convertFlexFormContentToArray($record['pi_flexform']);
        if (null !== $flexForm) {             
            $files = explode(',',$flexForm['selectImages']);

            foreach($files as $file) {
                if (!empty($file) && file_exists(PATH_site . self::UPLAOD_FOLDER_RRGSMOOTHGALLERY . $file)) {
                    GeneralUtility::upload_copy_move(PATH_site . self::UPLAOD_FOLDER_RRGSMOOTHGALLERY . $file, $this->targetDirectory . $file);
                    $fileObject = $this->storage->getFile(self::FOLDER_ContentUploads . '/' . $file);
                    $this->fileIndexRepository->add($fileObject);
                    $dataArray = array(
                        'uid_local' => $fileObject->getUid(),
                        'tablenames' => 'tt_content',
                        'uid_foreign' => $record['uid'],
                        // the sys_file_reference record should always placed on the same page
                        // as the record to link to, see issue #46497
                        'pid' => $record['pid'],
                        'fieldname' => 'image',
                        'sorting_foreign' => 0,
                        'table_local' => 'sys_file',
                        'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN","focusArea":null}}',
                        'l10n_diffsource' => ''
                    );
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_file_reference', $dataArray);                
                }
            }
        }
        $this->cleanRecord($record, $fileObject);
    }

    /**
     * Removes the old fields from the database-record
     *
     * @param array $record
     * @param FileInterface $file
     * @return void
     */
    protected function cleanRecord(array $record) {
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'uid = ' . $record['uid'], array(
            'Ctype' => 'image',
            'media' => 0,
            'pi_flexform' => null,
            'list_type' => '',
            'file_collections' => null,
            'imagecols' => 1,
            'tx_opentalent_carousel' => 1
        ));
    }

    /**
     * Get query parts for generic workflow
     *
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected function getMigratesQuery() {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_file');
        $queryBuilder->getRestrictions()->removeAll();

        $queryBuilder->from('sys_file')
            ->where(
                $queryBuilder->expr()->like('sys_file.identifier', $queryBuilder->createNamedParameter('/_migrated/%'))
            )
        ;

        return $queryBuilder;
    }

}
