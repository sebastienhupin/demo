/*
 *    Project:	opentalent - opentalent
 *    Version:	1.0.0
 *    Date:		Apr 10, 2015 1:36:39 PM
 *    Author:	Sébastien Hupin <sebastien.hupin at 2iopenservice.fr> 
 *
 *    Coded with Netbeans!
 */

lib.tx_themegallery.widgets.carousel = COA
lib.tx_themegallery.widgets.carousel {
  wrap (
  |
  )

  20 = FILES  
  20 {
    references {
      data = levelmedia: -1, slide
    }
    renderObj >
    renderObj = COA
    renderObj {
      10 = TEXT
      10.value = <div class="item active">
      10.if.value.data = register:FILE_NUM_CURRENT
      10.if.equals = 0
      
      15 = TEXT
      15.value = <div class="item">
      15.if.value = 0
      15.if.isGreaterThan.data = register:FILE_NUM_CURRENT
        
      20= IMAGE
      20 {
        file.import.data = file:current:publicUrl
        file.width = {$lib.carousel.width}
        file.height = {$lib.carousel.height}
        altText.data = file:current:alternative // file:current:title
        titleText.data = file:current:description // file:current:title
        stdWrap.typolink {
          parameter.data = file:current:link
          extTarget = _blank
        }
      }

      30 = TEXT
      30.value = </div>
    }

    stdWrap.wrap = <div class="carousel-inner">|</div>
  }    
}