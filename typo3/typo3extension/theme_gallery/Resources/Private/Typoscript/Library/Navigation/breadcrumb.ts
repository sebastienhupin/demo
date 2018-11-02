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
     1.wrap = <div class="breadcrumb"> | </div>
     1.NO {
         stdWrap.field = title
         ATagTitle.field = nav_title // title
         linkWrap = ||*|  > |*|
         }
     # Current menu item is unlinked
     1.CUR {
         stdWrap.field = title
         linkWrap = ||*|  > |*|
         doNotLinkIt = 1
         }
    }
}