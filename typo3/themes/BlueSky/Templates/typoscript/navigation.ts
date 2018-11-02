/*
 *    Project:	BlueSky Theme
 *    Version:	1.0.0
 *    Date:		Apr 14, 2015 8:56:09 AM
 *    Author:	Sébastien Hupin <sebastien.hupin at 2iopenservice.fr> 
 *
 *    Coded with Netbeans!
 */

lib.navigation.breadcrumb=COA
lib.navigation.breadcrumb {
10 = HMENU
10 {
 special = rootline
 special.range = 0|-1
 # "not in menu pages" should show up in the breadcrumbs menu
 includeNotInMenu = 1
 1 = TMENU
     # no unneccessary scripting.
     1.noBlur = 1
     # Current item should be unlinked
     1.CUR = 1
     1.target = _self
     1.wrap = <div class="row breadcrumb"><div class="col-sm-12"><ul> | </ul></div></div>
     1.NO {
         stdWrap.field = title
         ATagTitle.field = nav_title // title
         #linkWrap = ||*|  > |*|
         linkWrap = <li> | </li>
         }
     # Current menu item is unlinked
     1.CUR {
         stdWrap.field = title
         linkWrap = <li> | </li>
         doNotLinkIt = 1
         }
    }
}

lib.menu.navigation.bar = HMENU
lib.menu.navigation.bar {
  1 = TMENU
  1 {
    wrap = <ul class="nav navbar-nav">|</ul>
    noBlur = 1
    expAll = 1
    NO = 1
    NO {
      ATagTitle.field = abstract // description // title
      wrapItemAndSub  = <li>|</li>
    }
    ACT < .NO
    ACT.wrapItemAndSub = <li class="active">|</li>
    CUR < .ACT

    IFSUB = 1
    IFSUB {
      wrapItemAndSub = <li>|</li>
      stdWrap.wrap = | <b class="caret"></b>
     #ATagParams = class="dropdown-toggle" data-toggle="dropdown"
    }
    ACTIFSUB < .IFSUB
    ACTIFSUB {
      wrapItemAndSub = <li class="active">|</li>
    }
    CURIFSUB < .ACTIFSUB
    
    SPC = 1
    SPC {
      wrapItemAndSub = <li class="divider-vertical">|</li>
      doNotShowLink = 1
    }
  }

  2 < .1
  2 {
    wrap = <ul class="dropdown-menu">|</ul>
    IFSUB = 1
    IFSUB {
      wrapItemAndSub = <li>| </li>
      stdWrap.wrap = | <b class="right-caret"></b>
      #ATagParams = class="dropdown-toggle" data-toggle="dropdown"
    }
    ACTIFSUB < .IFSUB
    ACTIFSUB {
      wrapItemAndSub = <li class="active">|</li>
    }
    SPC.wrapItemAndSub = <li class="divider">|</li>
  }

  3 < .2
  3 {
    IFSUB >
    ACTIFSUB >
    CURIFSUB >    
  }
}

lib.sitetitle = TEXT
lib.sitetitle {
   data = levelfield:0,title
   insertData = 1 
}

lib.logo = IMAGE
lib.logo {
    # path to your logo file
    file={$settings.structure.logo}
    # Set the max height for the image (until the typo3 bug is solved @see : 46020)
    file.height = 75m

    # combining alt text from root page title and it's tq_seo extension prefix
    altText.cObject = COA
    altText.cObject {
      # getting root page title adding it to title prefix
      10 = TEXT
      10.data = levelfield:0,title
      # using notrimwrap to add space before root page title
      10.noTrimWrap = | |
    }
    imageLinkWrap = 1
    imageLinkWrap {
      # Activate ImageLinkWrap.
      enable = 1
      typolink {
        # links logo to the root page
        parameter.data = leveluid:0
        # getting link title text from nav_title field or page title if empty
        title.data = levelfield:0,nav_title // levelfield:0,title
        ATagParams = class="navbar-brand"
      }
    }    
    # wrap it all as you like
    wrap =|
}

lib.link.home = TEXT
lib.link.home {
  stdWrap.typolink {
    parameter.data = leveluid:0
  }
  value.current = 1    
}

lib.opentalent.page.login.uid = CONTENT
lib.opentalent.page.login.uid {
  table = pages
  select {
        pidInList.data = fullRootLine : 0, uid
        recursive = 1
        selectFields = uid
        where = tx_opentalent_pagename = "LOGIN"
  }
  
  renderObj = TEXT
  renderObj {
    field = uid 
    required = 1    
    wrap = |
    #stdWrap.debugData = 1
  }
  wrap =  |
}
