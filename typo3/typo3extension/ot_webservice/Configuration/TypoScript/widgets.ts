/*
 *    Project:	typo3.localhost.fr - typo3.localhost.fr
 *    Version:	1.0.0
 *    Date:		May 3, 2015 9:30:30 AM
 *    Author:	Sébastien Hupin <sebastien.hupin at gmail.com> 
 *
 *    Coded with Netbeans!
 */

plugin.tx_otwebservice {
    widgets {

      eventDetail = USER
      eventDetail { 
        userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
        pluginName = Pi1
        extensionName = OtWebservice
        controller = Event
        vendorName = Opentalent
        action = detail
        switchableControllerActions {
          Event { 
            1 = detail
          }
        }
      }

      eventSearchForm = USER
      eventSearchForm { 
        userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
        pluginName = Pi1
        extensionName = OtWebservice
        controller = Event
        vendorName = Opentalent
        action = searchForm
        switchableControllerActions {
          Event { 
            1 = searchForm
          }
        }

        settings.limit = 5
      }

      eventSearchResult = USER
      eventSearchResult { 
        userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
        pluginName = Pi1
        extensionName = OtWebservice
        controller = Event
        vendorName = Opentalent
        action = searchResult
        switchableControllerActions {
          Event { 
            1 = searchResult
          }
        }
        settings.limit = 5
      }

      eventSearchResultStructure < plugin.tx_otwebservice.widgets.eventSearchResult
      eventSearchResultStructure {
          settings.structure.id = {$plugin.tx_otwebservice.settings.structure.id}
      }

      eventMap = USER
      eventMap { 
        userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
        pluginName = Pi1
        extensionName = OtWebservice
        controller = Event
        vendorName = Opentalent
        action = googleMap
        switchableControllerActions {
          Event { 
            1 = googleMap
          }
        }
      }

      eventStructure < plugin.tx_otwebservice.widgets.eventSearchResult
      eventStructure { 
        action = eventStructure
        switchableControllerActions {
          Event { 
            1 = eventStructure
          }
        }
        settings.structure.id = {$plugin.tx_otwebservice.settings.structure.id}
      }

      eventStructureChildren < plugin.tx_otwebservice.widgets.eventSearchResult
      eventStructureChildren { 
        action = eventStructureChildren
        switchableControllerActions {
          Event { 
            1 = eventStructureChildren
          }
        }
      }

      eventStructureParent < plugin.tx_otwebservice.widgets.eventSearchResult
      eventStructureParent { 
        action = eventStructureParent
        switchableControllerActions {
          Event { 
            1 = eventStructureParent
          }
        }
      }

      structureDetail = USER
      structureDetail { 
          userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
          pluginName = Pi1
          extensionName = OtWebservice
          controller = Structure
          vendorName = Opentalent
          action = detail
          switchableControllerActions {
            Structure { 
              1 = detail
            }
          }
      }

      structureSearchForm = USER
      structureSearchForm { 
          userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
          pluginName = Pi1
          extensionName = OtWebservice
          controller = Structure
          vendorName = Opentalent
          action = searchForm
          switchableControllerActions {
            Structure { 
              1 = searchForm
            }
          }
          settings.limit = 2
      }

      structureSearchResult = USER
      structureSearchResult { 
          userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
          pluginName = Pi1
          extensionName = OtWebservice
          controller = Structure
          vendorName = Opentalent
          action = searchResult
          switchableControllerActions {
            Structure { 
              1 = searchResult
            }
          }
          settings.limit = 2
      }

      structureMap = USER
      structureMap { 
          userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
          pluginName = Pi1
          extensionName = OtWebservice
          controller = Structure
          vendorName = Opentalent
          action = googleMap
          switchableControllerActions {
            Structure { 
              1 = googleMap
            }
          }
      }

      members = USER
      members { 
        userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
        pluginName = Pi1
        extensionName = OtWebservice
        controller = Member
        vendorName = Opentalent
        action = searchMembers
        switchableControllerActions {
          Member { 
            1 = searchMembers
          }
        }

        settings {
            structure {
                id = {$plugin.tx_otwebservice.settings.structure.id}
            }
            limit = ALL
            orderBy   {
                0  {
                    key = instrument
                    value = asc
                }
                1  {
                    key = name
                    value = asc
                }
                2  {
                    key = givenName
                    value = asc
                }
            }
        }
      }

      members_ca < plugin.tx_otwebservice.widgets.members
      members_ca {
          action = searchMembersCA
          switchableControllerActions {
            Member {
              1 = searchMembersCA
            }
          }

          settings {
            structure {
                id = {$plugin.tx_otwebservice.settings.structure.id}
            }
            roles {
                0 = ACTIVE_MEMBER_OF_THE_CA
                1 = HONORARY_PRESIDENT
                2 = PRESIDENT
                3 = YOUTH_REPRESENTATIVE
                4 = SECRETARY
                5 = ASSISTANT_SECRETARY
                6 = TREASURER
                7 = TREASURER_ASSISTANT
                8 = VICE_PRESIDENT
                9 = VICE_PRESIDENT_OF_HONOR
                10 = HOUR_PRESIDENT
                11 = PRESIDENT_ASSISTANT,
                12 = ACTIVE_COOPTED_BOARD_MEMBER
                13 = MEMBER_OF_THE_BOARD
                14 = MEMBER_OF_BOARD_OF_HONOR
                15 = HONORARY_MEMBER
            }
            orderBy   {
                0  {
                    key = name
                    value = asc
                }
                1  {
                    key = givenName
                    value = asc
                }
                2  {

                }
            }
          }
      }

      donors = USER
      donors { 
          userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
          pluginName = Pi1
          extensionName = OtWebservice
          controller = Donor
          vendorName = Opentalent
          action = listDonor
          switchableControllerActions {
            Donor { 
              1 = listDonor
            }
          }

          settings.structure.id = {$plugin.tx_otwebservice.settings.structure.id}
      }

      donorsFede < plugin.tx_otwebservice.widgets.donors
      donorsFede { 
          action = listDonorFede
          switchableControllerActions {
            Donor { 
              1 = listDonorFede
            }
          }

        settings.limit = 4
      }
    }
}