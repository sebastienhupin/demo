/**
 * Module: TYPO3/CMS/ThemeGallery/Backend/FrameCommunication
 * 
 */
define(['jquery'], function($) {
	'use strict';
    /**
     *
     * @type {{}}
     * @exports TYPO3/CMS/ThemeGallery/Backend/FrameCommunication
    */    
    var FrameCommunication= {
        iFrame: null,
        css: null,
        hostname: null
    };
    /**
     * 
     * @returns {Void}
     */
    FrameCommunication.initialize = function() {
        var $iFrame = $('#tx_viewpage_iframe');
        if ($iFrame.length) {
            FrameCommunication.iFrame = $iFrame[0].contentWindow;
            FrameCommunication.hostname = FrameCommunication.getHostName($iFrame[0].src);
        }
        else {
            var $body = $(document).find('body');
            var $css = $body.find('#theme_gallery_preview_css');
            if (!$css.length) {
              // Add a the style tag
              $body.append('<style type="text/css" id="theme_gallery_preview_css" />');
              FrameCommunication.css = $body.find('#theme_gallery_preview_css');
            }
            else {
                FrameCommunication.css = $css;
            }

            FrameCommunication.hostname = window.location.origin;
            window.addEventListener("message", FrameCommunication.receiveMessage, false);
        }
    };
    /**
     * 
     * @param {String} data css
     * @param {String} url
     * @returns {Void}
     */
    FrameCommunication.postMessage = function (data, url) {
        if (null === this.iFrame) return;
        this.iFrame.postMessage(data, this.hostname);
    };
    /**
     * 
     * @param {Event} event
     * @returns {Void}
     */
    FrameCommunication.receiveMessage = function (event) {
        if (null === FrameCommunication.css) return;
        if (!FrameCommunication.sameDomain(event.origin)) return;
        // Replace the style tag by the text style parsed.        
        FrameCommunication.css.text(event.data);
    };
    /**
     * 
     * @param {String} url
     * @returns {String} the hostname from the url
     */
    FrameCommunication.getHostName = function (url) {
        var match = url.match(/(https{0,1}):\/\/(www[0-9]?\.)?(.[^/:]+)/i);
        if (match !== null && match.length > 2 && typeof match[0] === 'string' && match[0].length > 0) {
        return match[0];
        }
        else {
            return null;
        }
    }
    /**
     * 
     * @param {String} url
     * @returns {String}
     */
    FrameCommunication.getDomain = function (url) {
        var hostName = this.getHostName(url);
        var domain = hostName;

        if (hostName != null) {
            var parts = hostName.split('.').reverse();

            if (parts != null && parts.length > 1) {
                domain = parts[1] + '.' + parts[0];

                if (hostName.toLowerCase().indexOf('.co.uk') != -1 && parts.length > 2) {
                  domain = parts[2] + '.' + domain;
                }
            }
        }

        return domain;
    }
    /**
     * 
     * @param {String} url
     * @returns {Boolean}
     */
    FrameCommunication.sameDomain = function (url) {
        return (this.getDomain(url) === this.getDomain(this.hostname));
    }
    
    $(FrameCommunication.initialize);
    return FrameCommunication;    
});        



