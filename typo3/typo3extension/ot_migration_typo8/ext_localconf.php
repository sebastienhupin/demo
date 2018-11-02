<?php

/*
  Document   : ext_localconf
  Created on : Nov 9, 2014, 3:36:27 AM
  Author     : Sébastien Hupin <sebastien.hupin at 2iopenservice.fr>
 */


if (!defined('TYPO3_MODE')) {
  die('Access denied.');
}

/******* Migrate form to the new form extension **/
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['opentalent_form_folder'] = \Opentalent\OtMigrationTypo8\Install\Updates\FormFolderUpdate::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['opentalent_deprecated_mailform'] = \Opentalent\OtMigrationTypo8\Install\Updates\MailFormDataUpdate::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['opentalent_be_user'] = \Opentalent\OtMigrationTypo8\Install\Updates\BeUserUpdate::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['opentalent_c1x1flashplayer'] = \Opentalent\OtMigrationTypo8\Install\Updates\C1x1FlashplayerUpdate::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['opentalent_rrgsmoothgallery'] = \Opentalent\OtMigrationTypo8\Install\Updates\RrgSmoothGalleryUpdate::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['opentalent_preprodmailformdata'] = \Opentalent\OtMigrationTypo8\Install\Updates\PreprodMailFormDataUpdate::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['opentalent_rteprocessedfile'] = \Opentalent\OtMigrationTypo8\Install\Updates\RteProcessedFile::class;
//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['opentalent_userupload'] = \Opentalent\OtMigrationTypo8\Install\Updates\UserUploadUpdate::class;
/******* End Migrate form to the new form extension **/

