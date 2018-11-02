<?php
use Opentalent\ThemeGallery\Wizard\ThemeGalleryBackendLayoutWizardElement;

return [  
    /** Wizards */
    // Register backend_layout wizard
    'wizard_gridelements_backend_layout' => [
        'path' => '/wizard',
        'target' => ThemeGalleryBackendLayoutWizardElement::class . '::mainAction'
    ]
];