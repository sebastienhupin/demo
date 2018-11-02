/**
 * Module: TYPO3/CMS/ThemeGallery/Backend/PageRenderManager
 * 
 */
define(['jquery'], function($) {
	'use strict';
        
    /**
     *
     * @type {{}}
     * @exports TYPO3/CMS/ThemeGallery/Backend/PageRenderManager
    */    
    var PageRenderManager = {
        pageColumnsAllowedCTypes: {},
        pageColumnsMaxElement: {}
    }        
    
    PageRenderManager.setPageColumnsAllowedCTypes = function (ctypes) {
        this.pageColumnsAllowedCTypes = ctypes;
    }
    
    PageRenderManager.setPageColumnsMaxElement = function (maxElement) {
        this.pageColumnsMaxElement = maxElement;
    }
    
    PageRenderManager.initialize = function() {
        $('.t3js-page-new-ce, .t3-page-ce-wrapper-new-ce').each(function () {
            var newCeWrapperLinkNew = $(this).find('a').first();
            if(newCeWrapperLinkNew !== null) {
                var id = $(this).attr('id');
                var colPos = id.replace(/^colpos-([0-9]?)-page-.*/,"$1")                      
                var href = $(newCeWrapperLinkNew).attr('href');
                // Manage allowed ctype per column
                if(!href.match(/tx_theme_gallery_allowed=/)) {                    
                    if (colPos in PageRenderManager.pageColumnsAllowedCTypes) {
                        var allowedCTypes = PageRenderManager.pageColumnsAllowedCTypes[colPos];
                        var addAllowedCTypes = true;
                        for (var i=0;i<allowedCTypes.length;i++) {                            
                            if ('all' === allowedCTypes[i]) {                                        
                                addAllowedCTypes = false;
                            }
                        }
                        if (addAllowedCTypes) {
                            href = href+'&tx_theme_gallery_allowed='+allowedCTypes.join(',');
                            newCeWrapperLinkNew.attr('href',href);
                        }
                    }
                }
                
                // Manage the max element allowed per column
                if(!href.match(/tx_theme_gallery_maxelement=/)) {                    
                    if (colPos in PageRenderManager.pageColumnsMaxElement) {
                        var maxElement = PageRenderManager.pageColumnsMaxElement[colPos];                        
                        var contentElments = $(this).closest('[data-colpos="'+colPos+'"]');
                        if (contentElments.length >= maxElement) {
                            newCeWrapperLinkNew.remove();
                        }
                        else {
                            href = href+'&tx_theme_gallery_maxelement='+maxElement;
                            newCeWrapperLinkNew.attr('href',href);
                        }
                    }
                }
            }
        });
    };    

    $(PageRenderManager.initialize);
    return PageRenderManager;        
});        