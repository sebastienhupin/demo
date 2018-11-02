<?php

defined('TYPO3_MODE') or die();

$fields = [
    'tx_theme_gallery_theme_name' => [
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'readOnly' =>true
        ]
    ],
    'tx_theme_gallery_theme_style' => [
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'readOnly' =>true
        ]
    ],    
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $fields);