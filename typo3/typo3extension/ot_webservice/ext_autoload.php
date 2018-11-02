<?php

$extensionClassPath = TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('ot_webservice') . 'Classes/';
return array(
    'Opentalent\\OtWebservice\\Utility\\EidDispatcher' => $extensionClassPath . 'Utility/EidDispatcher.php',
    'Opentalent\\OtWebservice\\WebServices\\OpentalentService' => $extensionClassPath . 'WebServices/OpentalentService.php',
    'Opentalent\\OtWebservice\\WebServices\\EventService' => $extensionClassPath . 'WebServices/EventService.php',
    'Opentalent\\OtWebservice\\WebServices\\StructureService' => $extensionClassPath . 'WebServices/StructureService.php',
    'Opentalent\\OtWebservice\\WebServices\\MemberService' => $extensionClassPath . 'WebServicesMemberService.php',  
    'Opentalent\\OtWebservice\\WebServices\\DonorService' => $extensionClassPath . 'WebServices/DonorService.php',  
    'Opentalent\\OtWebservice\\Controller\\EventController' => $extensionClassPath . 'Controller/EventController.php',
    'Opentalent\\OtWebservice\\Controller\\StructureController' => $extensionClassPath . 'Controller/StructureController.php',
    'Opentalent\\OtWebservice\\Controller\\MemberController' => $extensionClassPath . 'Controller/MemberController.php',
    'Opentalent\\OtWebservice\\Controller\\DonorController' => $extensionClassPath . 'Controller/DonorController.php',
    'Opentalent\\OtWebservice\\Property\\TypeConverter\\OrderByConverter' => $extensionClassPath . 'Property/TypeConverter/OrderByConverter.php',
    'Opentalent\\OtWebservice\\ViewHelpers\\Format\\CategoriesViewHelper' => $extensionClassPath . 'ViewHelpers/Format/CategoriesViewHelper.php',
);
