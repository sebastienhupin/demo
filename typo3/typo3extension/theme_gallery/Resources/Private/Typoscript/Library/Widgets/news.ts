/*
 *    Project:	opentalent - opentalent
 *    Version:	1.0.0
 *    Date:		Apr 13, 2015 4:17:43 PM
 *    Author:	Sébastien Hupin <sebastien.hupin at 2iopenservice.fr> 
 *
 *    Coded with Netbeans!
 */

# Desactivation de l'error handling
# Ce qui permet d'avoir la liste et le detail des news sur la même page, même si il n'y a aucun detail de news à afficher.
# Normalement pour ce genre de cas on doit utiliser la proprieté singleNews, mais dans notre cas cela n'est pas possible d'afficher 
# une news par defaut.
plugin.tx_news.settings {
  detail.errorHandling =
}

lib.tx_themegallery.widgets {
  listnews = USER
  listnews {
    userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
    extensionName = News
    pluginName = Pi1
    vendorName = GeorgRinger
    action = list
    switchableControllerActions {
          News {
            1 = list
          }
    }

    settings < plugin.tx_news.settings
    settings {
          limit = {$settings.news.limit}
          detailPid = {$settings.news.uid}
          startingpoint = {$settings.news.uid}
    }
  }

  detailnews < lib.tx_themegallery.widgets.listnews
  detailnews {
    action = detail
    switchableControllerActions.News.1 = detail
  }
}
