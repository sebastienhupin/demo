page {
  # Include all css
  includeCSS {
    bootstrap1 = /fileadmin/theme_gallery/BlueSky/Templates/assets/css/bootstrap.min.css
    bootstrap2 = /fileadmin/theme_gallery/BlueSky/Templates/assets/css/bootstrap-theme.min.css
    fontAwesome = /fileadmin/theme_gallery/BlueSky/Templates/assets/css/font-awesome.min.css
    sm = /fileadmin/theme_gallery/BlueSky/Templates/assets/css/jquery.smartmenus.bootstrap.css
    #See Bug 46123 from typo3
    rtehtmlarea = EXT:rtehtmlarea/res/contentcss/default.css
  }
  includeJS {
    jquery = https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js
    jquery.external = 1  
  }
  includeJSFooter {    
    bootstrap = /fileadmin/theme_gallery/BlueSky/Templates/assets/js/bootstrap.min.js
    ie10 = /fileadmin/theme_gallery/BlueSky/Templates/assets/js/ie10-viewport-bug-workaround.js
    bluesky = /fileadmin/theme_gallery/BlueSky/Templates/assets/js/bluesky.js
    infinite-scroll = /fileadmin/theme_gallery/BlueSky/Templates/assets/js/jquery-ias.min.js
    lazyload = /fileadmin/theme_gallery/BlueSky/Templates/assets/js/jquery.lazyload.min.js    
    markerclusterer = /fileadmin/theme_gallery/BlueSky/Templates/assets/js/markerclusterer.min.js
    smartmenus = /fileadmin/theme_gallery/BlueSky/Templates/assets/js/jquery.smartmenus.min.js
    smartmenus_boot = /fileadmin/theme_gallery/BlueSky/Templates/assets/js/jquery.smartmenus.bootstrap.min.js
    simple_pagination = /fileadmin/theme_gallery/BlueSky/Templates/assets/js/simplepagination.js
  }

  headerData.10=TEXT
  headerData.10.value (
    <meta name="viewport" content="width=device-width, initial-scale=1" />
  )

}

[browser = msie] && [version = 9]
  page.includeJS.ie9 {
    html5shiv = https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js
    html5shiv.external = 1
    respond = https://oss.maxcdn.com/respond/1.4.2/respond.min.js
    respond.external = 1
  } 
[global]

page.10 {
    partialRootPath = fileadmin/theme_gallery/BlueSky/Templates/Partials/
    layoutRootPath = fileadmin/theme_gallery/BlueSky/Templates/Layouts/

    # Suivant la valeur du backend layout, on sert un template different.
    file.stdWrap.cObject {
        # Par défaut on sert le fichier index.
        default = TEXT
        default.value = fileadmin/theme_gallery/BlueSky/Templates/Default.html

        home = TEXT
        home.value = fileadmin/theme_gallery/BlueSky/Templates/Home.html

        login = TEXT
        login.value = fileadmin/theme_gallery/BlueSky/Templates/Login.html

        news = TEXT
        news.value = fileadmin/theme_gallery/BlueSky/Templates/News.html

        events = TEXT
        events.value = fileadmin/theme_gallery/BlueSky/Templates/Events.html

        association_spectacle = TEXT
        association_spectacle.value = fileadmin/theme_gallery/BlueSky/Templates/Association_spectacle.html

        members_list_fede = TEXT
        members_list_fede.value = fileadmin/theme_gallery/BlueSky/Templates/Structures.html

        contact = TEXT
        contact.value = fileadmin/theme_gallery/BlueSky/Templates/Contact.html  

        ca_members = TEXT
        ca_members.value = fileadmin/theme_gallery/BlueSky/Templates/Ca_members.html

        members_list = TEXT
        members_list.value = fileadmin/theme_gallery/BlueSky/Templates/Members_list.html

        info_association = TEXT
        info_association.value = fileadmin/theme_gallery/BlueSky/Templates/Info_association.html

        site_map = TEXT
        site_map.value = fileadmin/theme_gallery/BlueSky/Templates/Site_map.html
    }

    variables {

      # Bloc de contenu central
      colContent < styles.content.get
      colContent.select.where = colPos = 0

      # Colonne de gauche
      colLeft < styles.content.get
      colLeft.select.where = colPos = 1

      # Colonne de droite
      colRight < styles.content.get
      colRight.select.where = colPos = 2

    }
}

