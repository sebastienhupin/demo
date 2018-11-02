lib.themegallery.navigation.menu = COA
lib.themegallery.navigation.menu {
  20 = HMENU
  20 {

    // The number (1) it's a requirement, do not change it
    // Level 1
    1 = TMENU
    1 {
      NO {
        linkWrap =|
        wrapItemAndSub.insertData = 1        
        doNotLinkIt = 1
        stdWrap.typolink.parameter.data = field:uid
        stdWrap.typolink.addQueryString = 1
        stdWrap.typolink.addQueryString.exclude = L,id,cHash
        stdWrap.typolink.addQueryString.method = GET
        wrapItemAndSub = <li class="item first">|</li>|*|<li class="item">|</li>|*|<li class="item last">|</li>
      }

      ACT < .NO
      ACT.wrapItemAndSub = <li class="item first selected">|</li>|*|<li class="item selected">|</li>|*|<li class="item last selected">|</li>
      ACT = 1

      CUR < .NO
      CUR.wrapItemAndSub = <li class="item first current">|</li>|*|<li class="item current">|</li>|*|<li class="item last current">|</li>
      CUR = 1
      
      wrap = <ul class="menu level1">|</ul>
    }

    2 < .1
    2.wrap = <ul class="menu level2">|</ul>

  }
}

lib.themegallery.navigation.menu.level2 < lib.themegallery.navigation.menu
lib.themegallery.navigation.menu.level2 {
  20 {
    1.expAll = 1
    2 < .1
    2.wrap = <ul class="menu level2">|</ul>

  }
}

lib.themegallery.navigation.menu.level3 < lib.themegallery.navigation.menu.level2
lib.themegallery.navigation.menu.level3 {
  20 {
    3 < .2
    3.wrap = <ul class="menu level3">|</ul>

  }
}

#Pages spécifiques au footer
lib.themegallery.navigation.footer = HMENU
lib.themegallery.navigation.footer {
  special = directory
  special.value.cObject = COA
  special.value.cObject {
    10 < lib.opentalent.page.footer.uid
  }

  1 = TMENU
  1 {
    wrap = <ul>|</ul>
    noBlur = 1
    expAll = 1
    NO = 1
    NO {
      ATagTitle.field = abstract // description // title
      wrapItemAndSub  = <li>|</li>
    }
  }
}