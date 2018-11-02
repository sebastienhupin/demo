<?php

defined('TYPO3_MODE') or die();

$fields = [
    'tx_theme_gallery_slide' => [
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'readOnly' =>true
        ]
    ]   
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $fields);