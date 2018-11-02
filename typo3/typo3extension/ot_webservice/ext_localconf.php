<?php

if (!defined('TYPO3_MODE')) {
  die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Opentalent.' . $_EXTKEY, 
        'Pi1', 
        array(
            'Event' => 'list,detail,searchForm,searchResult,last,top,googleMap,eventStructure,eventStructureChildren,eventStructureParent',
            'Structure' => 'detail,searchForm,searchResult',
            'Donor' => 'listDonor,listDonorFede',
            'Member' => 'searchMembers,searchMembersCA'
        ),
        // non-cacheable actions
        array(
            'Event' => 'list,detail,searchForm,searchResult,last,top,googleMap,eventStructure,eventStructureChildren,eventStructureParent',
            'Structure' => 'detail,searchForm,searchResult',
            'Donor' => 'listDonor,listDonorFede',
            'Member' => 'searchMembers,searchMembersCA'
        )
);

// Ajax dispatcher
//$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Utility/EidDispatcher.php';

