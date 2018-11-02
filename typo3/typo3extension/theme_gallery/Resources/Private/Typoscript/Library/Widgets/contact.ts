/*
 *    Project:	opentalent - opentalent
 *    Version:	8.0.0
 *    Date:		Apr 14, 2015 10:27:01 AM
 *    Author:	Sébastien Hupin <sebastien.hupin at 2iopenservice.fr> 
 *
 *    Coded with Netbeans!
 */

plugin.tx_form {
    settings {
        formDefinitionOverrides {
            ThemeGalleryContact {
                finishers {
                    0 {
                        options {
                            recipientAddress = {$settings.structure.email}
                        }
                    }
                    2 {
                        options {
                            pageUid =  {$settings.contact_tks.uid}
                        }
                    }
                }
            }
        }
    }
}

lib.tx_themegallery.widgets.contact = USER_INT
lib.tx_themegallery.widgets.contact {
	userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
	extensionName = Form
	pluginName = Formframework
	vendorName = TYPO3\CMS
	switchableControllerActions {
		FormFrontend {
			1 = perform
		}
	}
	settings.persistenceIdentifier = EXT:theme_gallery/Resources/Private/Forms/Contact.yaml
}

