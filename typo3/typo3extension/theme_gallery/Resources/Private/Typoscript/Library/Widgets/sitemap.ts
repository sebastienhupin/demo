/*
 *    Project:	opentalent - opentalent
 *    Version:	1.0.0
 *    Date:		Apr 14, 2015 3:41:07 PM
 *    Author:	Sébastien Hupin <sebastien.hupin at 2iopenservice.fr> 
 *
 *    Coded with Netbeans!
 */

lib.tx_themegallery.widgets.sitemap = COA
lib.tx_themegallery.widgets.sitemap {

	2 = HMENU
	2{
		entryLevel = 0

		1 = TMENU
		1{
		 	expAll = 1
			wrap = <ul class="map-first-level">|</ul>

			NO = 1
			NO {
				wrapItemAndSub = <li>|</li>
				stdWrap.htmlSpecialChars = 1
				}

			CUR = 1
			CUR < .NO
			CUR.ATagParams = class=first-level-current

		}

		2 = TMENU
		2{
		 	expAll = 1
			wrap = <ul class="map-second-level">|</ul>

			NO = 1
			NO {
				wrapItemAndSub = <li>|</li>
				stdWrap.htmlSpecialChars = 1
				}

			CUR = 1
			CUR < .NO
			CUR.ATagParams = class=second-level-current

		}

		3 < .2

	}
}
