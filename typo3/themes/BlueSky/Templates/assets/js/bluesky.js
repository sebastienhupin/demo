
jQuery(document).ready(function() {
    jQuery('#navstatic').affix({
        offset: {
            top: jQuery('.header').height()
        }
    }).on('affix.bs.affix', function(e) {
        //In affix
        jQuery('.navbar-user').children('a').addClass('in_affix');

    }).on('affix-top.bs.affix', function(e) {
        //Out affix
        jQuery('.navbar-user').children('a').removeClass('in_affix');
    });

    jQuery("img.lazy").lazyload({
        placeholder: '/fileadmin/theme_gallery/BlueSky/Templates/assets/img/default-small.jpg',
        threshold: 200,
        container: jQuery(".ggmaps-list-results")
    });
    
    jQuery(document).on('click', '.caret', function(e){
        $(this).parent().parent('li').toggleClass('open');
        if($(this).parent().siblings('ul').is(':visible')){
            $(this).parent().siblings('ul').hide();
        }
        return false;
    })
});
