/**
 * Module: TYPO3/CMS/ThemeGallery/Backend/ThemeGallery
 * 
 */
define(['require', 'jquery', 'TYPO3/CMS/ThemeGallery/Backend/FrameCommunication', 'TYPO3/CMS/ThemeGallery/Backend/jquery.colors/Picker'], function(require, $, FrameCommunication) {
	'use strict';

    /**
     *
     * @type {{}}
     * @exports TYPO3/CMS/ThemeGallery/Backend/ThemeGallery
    */    
    var ThemeGallery= {
        name: '',
        style: '',
        config: {
          colors: {
            color1: null,
            color2: null,
            color3: null,
            color4: null,
            color5: null
          }
        }        
    };
    /**
     * Set colors
     * 
     * @param {Array} colors
     * @returns {Void}
     */
    ThemeGallery.setColors = function(colors) {
        this.config.colors = {
          color1: colors.color1.toString('hex'),
          color2: colors.color2.toString('hex'),
          color3: colors.color3.toString('hex'),
          color4: colors.color4.toString('hex'),
          color5: colors.color5.toString('hex')
        };        
    };
    /**
     * Preview the style with the new colors
     * 
     * @returns {undefined}
     */
    ThemeGallery.preview = function() {        
        var style = this.style;
        // Parse the style.
        for (var color in this.config.colors) { 
          var sColor = '###' + color.toUpperCase() + '###';
          var reg = new RegExp(sColor, "g");
          style = style.replace(reg, this.config.colors[color]);
          $('input[name="tx_themegallery_web_themegallerythemegallery[themeConfig][theme][colors]['+color+']"]').val(this.config.colors[color])
        }

        // Get the preview iframe document object.
        FrameCommunication.postMessage(style, "*");
    };
    /**
     * Alogo to choose colors from one color
     * 
     * @param {Array} color
     * @returns {ThemeGalleryL#5.ThemeGallery.algo2.colors}
     */
    ThemeGallery.algo2 = function (color) {
      var colors = {
        color1: null,
        color2: null,
        color3: null,
        color4: null,
        color5: null
      };

      colors.color1 = $.colors([color[0], color[1], color[2]], 'array3Normalized', 'HSL');

      var h = color[0];
      // complementary
      h = (h + 180) % 360;
      colors.color2 = $.colors([h, color[1], color[2]], 'array3Normalized', 'HSL');

      // split
      h = (h + 30) % 360;
      colors.color3 = $.colors([h, color[1], color[2]], 'array3Normalized', 'HSL');

      // split
      h = (h + 30) % 360;
      colors.color4 = $.colors([h, color[1], color[2]], 'array3Normalized', 'HSL');

      // complementary
      h = (h + 180) % 360;
      colors.color5 = $.colors([h, color[1], color[2]], 'array3Normalized', 'HSL');

      return colors;
    }    
    /**
     * Open the color picker
     * 
     * @returns {Void}
     */
    ThemeGallery.openColor = function() {
        var $colorPickerWrapper =  $('#colorPickerWrapper');
        if ($colorPickerWrapper.is(":visible")) { 
            $colorPickerWrapper.hide();
        }
        else {
            $colorPickerWrapper.show();
        }
    }
    /**
     * Initialize the module
     * 
     * @returns {Void}
     */
    ThemeGallery.initialize = function() {
        
        this.name = $('input[name="tx_themegallery_web_themegallerythemegallery[themeConfig][theme][name]"]').val();

        // Load the theme style.
        $.when($.get("/fileadmin/theme_gallery/" + this.name + "/Templates/assets/css/style.css")).done(function(response) {
          ThemeGallery.style = response;
          // Init the color scheme.
          // Get the primary color (color1)
          var $color = $('input[name="tx_themegallery_web_themegallerythemegallery[themeConfig][theme][colors][color1]"]');
          if ($color.length) {
            var initColor = $.colors($color.val());
            $('#colorPickerWrapper').hslCircleColorPicker({
              color: initColor,
              onChange: function(color) {
                ThemeGallery.setColors(ThemeGallery.algo2(color.model('HSL').get()));
                ThemeGallery.preview();
              }
            }).hide();
          }     
        });
                
        $(function() {
            ThemeGallery.initializeEvents();
        });                                        
    };
    /**
     * Initialize module 
     * 
     * @returns {Void}
     */
    ThemeGallery.initializeEvents = function() {
        $(document).on('submit', 'form.theme-gallery-document-save',function() {
          // Save colors
          for (var color in this.config.colors) {
            var $color = jQuery('input[name="tx_themegallery_web_themegallerythemegallery[themeConfig][theme][colors][' + color + ']"');
            if ($color.length) {
              $color.val(this.config.colors[color]);
            }
          }
        })
        .on('click', '.t3js-themegallery-open-color', function (evt) {
            ThemeGallery.openColor();
        });        
        ;
    }
    
    $(ThemeGallery.initialize);
    return ThemeGallery;
});        