/*
 *    Project:	opentalent - opentalent
 *    Version:	1.0.0
 *    Date:		Mar 19, 2015 4:17:56 PM
 *    Author:	Sébastien Hupin <sebastien.hupin at 2iopenservice.fr> 
 *
 *    Coded with Netbeans!
 */

config.doctype = html5

page = PAGE
page.typeNum = 0

# Si la variable get typeNum = 107, on renvoi le fichier style.css avec son contenu parsé.
css_file = PAGE
css_file {
  typeNum = 107
  config{
    additionalHeaders {
        10 {
            header = Content-type: text/css
            replace = 1
        }
    }
    disableAllHeaderCode = 1    
  }
  10 = TEMPLATE
  10 {
    template = FILE
    template.file = {$theme_gallery_css_file}
    marks.COLOR1 = TEXT
    marks.COLOR1.value = {$color1}
    marks.COLOR2 = TEXT
    marks.COLOR2.value = {$color2}
    marks.COLOR3 = TEXT
    marks.COLOR3.value = {$color3}
    marks.COLOR4 = TEXT
    marks.COLOR4.value = {$color3}
    marks.COLOR5 = TEXT
    marks.COLOR5.value = {$color4}
  }
}

// On charge le fichier style.
page.headerData.809 = TEXT
page.headerData.809.dataWrap = <link rel="stylesheet" type="text/css" href="/index.php?id={field:uid}&type=107&no_cache=1" />

lib.domain = TEXT
lib.domain.data = getEnv:HTTP_HOST

page.10 = FLUIDTEMPLATE
page.10 {
    extbase.pluginName = Pi1
    extbase.controllerExtensionName = OtWebservice

    format = html

    # En fonction de la valeur du champs backend_layout, on peut switcher sur un template différent.
    # Chaque theme peut définir un template différent en fct du backend layout,
    # Par defaut le template est index.html
    file.stdWrap.cObject = CASE
    file.stdWrap.cObject {
      key.stdWrap.cObject = TEXT
      key.stdWrap.cObject {
        data = levelfield:-2,backend_layout_next_level,slide
        override.field = backend_layout
        # On recupére la partie droite
        split {
          token = theme_gallery__
          1.current = 1
          1.wrap = |
        }
      }
##########################################################################################################
# EXEMPLE
##########################################################################################################
#        # Par défaut on sert le fichier index.
#        default = TEXT
#        default.value = fileadmin/theme_gallery/Blank/Templates/index.html  
#        
#        # Sert le template pour la page contact.
#        contact = TEXT
#        contact.value = fileadmin/theme_gallery/Blank/Templates/contact.html
#
#        # Sert le template pour la page info_association.
#        info_association = TEXT
#        info_association.value = fileadmin/theme_gallery/Blank/Templates/info_association.html 
########################################################################################################### 
    }

    settings {
        sitetitle < sitetitle

        structure {
            id = {$settings.structure.id}
            is_network = {$settings.structure.is_network}
            logo = {$settings.structure.logo}
        }
        network {
          logo = {$settings.network.logo}
          name = {$settings.network.name}
          url = {$settings.network.url}
        }
    }    
}
