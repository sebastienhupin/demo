<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "ot_webservice"
 *
 * Auto generated by Extension Builder 2015-01-14
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Webservice',
	'description' => 'Interrogation des webservices Opentalent ',
	'category' => 'plugin',
	'author' => 'Sébastien hupin',
	'author_email' => 'sebastien.hupin@2iopenservice.fr',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2',
                        'ot_cms' => '1.0'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);