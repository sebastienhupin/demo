/**
 * jQuery Colors Picker: HSL Circle
 * @license Copyright 2010 Enideo. Released under dual MIT and GPL licenses.
*/

define(['jquery','TYPO3/CMS/ThemeGallery/Backend/jquery.colors/Colors'], function(jQuery){
(function($,undefined){   

  var ini, settings = {},
    hsl, disabled, onStartChange, onChange, onEndChange,
    container, wrapper, scales, currentId, scriptSrc;


function setuphslCircleColorPicker(){

  var scaleTemplate = $('<div/>').addClass('scale'),
    overlayTemplate =   $('<div/>').css({zIndex:1,width:160,height:160}),
    handleTemplate = $('<span/>').css({position:'absolute',zIndex:3}),
    handleOutlineTemplate = handleTemplate.clone().addClass('outline').css({zIndex:2}),
    handleWrapper = $('<span/>').addClass('handle');

  overlayTemplate.add(handleWrapper).css({position:'absolute',top:0,left:0});

  wrapper = $('<div/>').addClass('hslCircleColorPicker').css({position:'relative',width:160,height:185});
  wrapper
    .append(  scaleTemplate.clone().addClass('h').data('arrayIndex',0)
      .addClass('hSprite').css({width:160,height:160})
      .append( overlayTemplate.clone().addClass('hSprite').css({backgroundPosition:"-320px 0"}) )
      .append( overlayTemplate.clone().addClass('hSprite').css({backgroundPosition:"-160px 0"}) ) );

  scaleTemplate.css({overflow:'hidden'});
  overlayTemplate.css({backgroundRepeat:'repeat-x',width:20,height:100});

  wrapper.append(  scaleTemplate.clone().addClass('s').data('arrayIndex',1)
    .css({width:20,height:100,position:'absolute',top:30,left:70})
    .append( overlayTemplate.clone().addClass('sSprite').css({backgroundPosition:"0 -200px"}) )
    .append( overlayTemplate.clone().addClass('sSprite') )
    .append( overlayTemplate.clone().addClass('sSprite').css({backgroundPosition:"0 -100px"}) ) );

  wrapper.append(  scaleTemplate.clone().addClass('l').data('arrayIndex',2)
    .css({height:20,position:'absolute',top:165,left:0,right:0}) );

  container.append( wrapper );

  scales = wrapper.children('div');

  scales.each(function(i){

    var self = $(this);

    if( i===0 ){

      self.append( handleWrapper.clone().append( handleTemplate.clone().text('+') ).append( handleOutlineTemplate.clone().text('+') ) );

      self.children('span').children().each(function(j){

        var self = $(this);
        self.css({top:-self.height()/2,left:-self.width()/2 });

      });

    }else if( i==1 ){

      self.append( handleWrapper.clone()
        .append( handleTemplate.clone().text('▶') ).append( handleTemplate.clone().text('◀') )
        .append( handleOutlineTemplate.clone().text('▶') ).append( handleOutlineTemplate.clone().text('◀') ) );
      self.children('span').css({right:2}).children().each(function(j){

        var self = $(this);

        leftOrRight = 'left';

        if(j%2!==0){
          leftOrRight = 'right';
        }

        self.css({top:-self.height()/2}).css( leftOrRight , -self.width()/2 );

      });

    }else if( i==2 ){

      self.append( handleWrapper.clone()
        .append( handleTemplate.clone().text('▼') ).append( handleTemplate.clone().text('▲') )
        .append( handleOutlineTemplate.clone().text('▼') ).append( handleOutlineTemplate.clone().text('▲') ) );

      self.children('span').css({bottom:2}).children().each(function(j){

        var self = $(this),
          topOrBottom = 'top';

        if(j%2!==0){
          topOrBottom = 'bottom';
        }

        // -1 ?
        self.css({left:-self.width()/2 -1}).css( topOrBottom , -self.height()/2 );

      });
    }

  });

  hsl = [ Math.random()*360 , Math.random()*100 , Math.random()*100 ];
  disabled = false;
  onStartChange = onChange = onEndChange = null;

  scales.mousedown(mousedown);

}

function mousedown(event) {
  var scale = $(event.currentTarget),
    offset = scale.offset(),
    whichCase = scale.data('arrayIndex');

  function mousemove(event) {

    var factor = 100, value;

    if ( whichCase===0 ) {

      value = Math.atan2( (event.pageX - offset.left), (offset.top-event.pageY) ) / 6.28;
      if (value < 0) { value += 1; }
      factor = 360;

    }else if ( whichCase==1 ) {
      value = (100 - (event.pageY - offset.top))/100;
    }else{
      value = (event.pageX - offset.left)/160;
    }

    value = value > 1 ? 1 : value < 0 ? 0 : value;



    hsl[whichCase] = value*factor;

    refresh();

    if( $.isFunction(onChange) ){
      onChange( $.colors( hsl , 'array1Circle2Percentage','HSL') );
    }

    return false;
  }

  function mouseup(event){

    if( $.isFunction(onEndChange) ){
      onEndChange( $.colors( hsl , 'array1Circle2Percentage','HSL') );
    }

    $(document).unbind('mousemove', mousemove).unbind('mouseup', mouseup);
    wrapper.data('dragging',false);
  }


  if(whichCase===0){
    offset.top+=80;
    offset.left+=80;
  }

  setGlobals( $(this).closest('.hslCircleColorPicker').parent() );

  if(disabled){
    return true;
  }

  if( $.isFunction(onStartChange) ){
    onStartChange( $.colors( hsl , 'array1Circle2Percentage','HSL') );
  }

  if ( !wrapper.data('dragging') ) {
    $(document).bind('mousemove', mousemove).bind('mouseup', mouseup);
    wrapper.data('dragging',true);
  }

  mousemove(event);

  return false;

} /// mousedown


function refresh() {

  var opacityValuesArray, opacityValue,
    grayValue = (hsl[2])/100;

  scales.each(function(){
    var self = $(this),
      whichCase = self.data('arrayIndex'),
      angle = hsl[ whichCase ]/360 * 6.28;

    self = self.children('span');

    if( whichCase===0 ){

      self.css({
        top: -Math.cos(angle) * 70 + 80 ,
        left: Math.sin(angle) * 70 + 80
      });

    }else if( whichCase==1 ){
      self.css({
        top: 98 - hsl[ whichCase ]*100/100
      });
    }else{
      self.css({
        left: hsl[ whichCase ]*160/100
      });
    }


  });


  opacityValue = (100-hsl[1])/100;
  opacityValuesArray = [grayValue*opacityValue, (1-grayValue)*opacityValue ];

  if( hsl[2] > 50 ){
    opacityValue = (hsl[2]-50)*2/100;
    opacityValuesArray[0] = Math.max( opacityValuesArray[0], opacityValue );
  }else{
    opacityValue = (50-hsl[2])*2/100;
    opacityValuesArray[1] = Math.max( opacityValuesArray[1], opacityValue );
  }

  scales.filter('.h').children('div').each(function(i){
    $(this).css('opacity',opacityValuesArray[i]);
  });

  if( hsl[2] > 50 ){
    opacityValuesArray = [opacityValue, 1-opacityValue, 0];
  }else{
    opacityValuesArray = [0, 1-opacityValue, opacityValue];
  }
  /// gray is never 100%: otherwise background hue doesnt come through on IE7
  opacityValuesArray[1] *= 0.8;


  scales.filter('.s').css({
    backgroundColor: $.colors( [ hsl[0], 100, 50 ], 'array1Circle2Percentage','HSL').toString('rgb')
  }).children('div').each(function(i){
    $(this).css('opacity',opacityValuesArray[i]);
  });

  scales.filter('.l').css({
    backgroundColor: $.colors( [ hsl[0], hsl[1], 50 ], 'array1Circle2Percentage','HSL').toString('rgb')
  });

}



function setGlobals(thisContainer,options){

  container = thisContainer;

  /// create it if it doesn't exist
  if( !container.data('hslCircleColorPickerId') ){
    currentId = new Date().getTime();
    container.data('hslCircleColorPickerId',currentId);
    setuphslCircleColorPicker();

    settings[ currentId ] = {
      hsl:hsl,
      disabled:disabled,
      onStartChange:onStartChange,
      onChange:onChange,
      onEndChange:onEndChange
    };

  }else{

    wrapper = container.children();
    scales = wrapper.children('div.scale');
    currentId = container.data('hslCircleColorPickerId');
    hsl = settings[ currentId ].hsl;
    disabled = settings[ currentId ].disabled;

    onStartChange = settings[ currentId ].onStartChange;
    onChange = settings[ currentId ].onChange;
    onEndChange = settings[ currentId ].onEndChange;

  }
}


$.fn.hslCircleColorPicker = function(options){

  var returnedElements, resultsArray = [];

  if (typeof options == 'string' && this.data('hslCircleColorPickerId') ){
    if(options==='color'){
      return $.colors( settings[ this.data('hslCircleColorPickerId') ].hsl , 'array1Circle2Percentage','HSL');
    }else{
      return settings[ this.data('hslCircleColorPickerId') ][options];
    }
  }

  if(!ini){
    initiate();
  }

  returnedElements = this.each(function(){

    setGlobals($(this));

    if (typeof options == 'object') {

      applyOptions(options);

    }else if(options === 'hsl'){
      resultsArray.push( hsl );
    }

    refresh();

  });


  if( resultsArray.length>1 ){

    return resultsArray;

  }else if( resultsArray.length==1 ){

    return resultsArray[0];

  }else{

    return returnedElements;

  }

};


function applyOptions(options){

  var hslParameterIndexes = $.colors.models.HSL.parameterIndexes, optionName;

  for ( optionName in options ){

    if(optionName in hslParameterIndexes){
      settings[currentId].hsl[ hslParameterIndexes[ optionName ] ] = options[ optionName ];
      delete options[ optionName ];
    }

    if( optionName==='color' ){
      if( options[ optionName ] instanceof $.colors === false ){
        options[ optionName ] = $.colors( options[ optionName ] );
      }
      settings[currentId].hsl = options[ optionName ].model('HSL').format('array1Circle2Percentage');
      delete options[ optionName ];
    }

  }


  $.extend( settings[currentId], options);

  settings[currentId].hsl = $.colors.models.HSL.sanitize( settings[currentId].hsl ).slice(0,3);

  setGlobals(container); /// called twice: but then fast and logical

  if( 'disabled' in options ){

    if( options.disabled ){
      wrapper.addClass('disabled');
    }else{
      wrapper.removeClass('disabled');
    }

  }

}

  if( $.colors.pickers === undefined ){
    $.colors.pickers = {};
  }

  scriptSrc = $('script[src]'); // :last-child fails on chrome?
  if( scriptSrc.length>0 ){
    scriptSrc = scriptSrc.eq( scriptSrc.length -1).attr('src').split('/');
    scriptSrc.pop();
    scriptSrc = scriptSrc.join('/')+'/';
  }else{
    scriptSrc = '';
  }


  $.colors.pickers.hslCircle = {
    pathToImages : scriptSrc
  };



function initiate(){


  function injectStyle(e){

    var a, aMax, b, bMax,
      style=[
        /// wrapper
        ['.hslCircleColorPicker',
          ['display','inline-block']
        ], /// scale
        ['.hslCircleColorPicker>div.scale',
          ['border','1px solid #888'],
          ['cursor','crosshair']
        ], /// hue scale
        ['.hslCircleColorPicker>div.scale.h',
          ['border-width','0 !important']
        ], /// saturation scale
        ['.hslCircleColorPicker>div.scale.s',
          ['cursor','NS-resize'],
          ['cursor','row-resize']
        ], /// lightness scale
        ['.hslCircleColorPicker>div.scale.l',
          ['cursor','EW-resize'],
          ['cursor','col-resize']
        ], /// handles
        ['.hslCircleColorPicker>div.scale>span.handle>span',
          ['color','#eee'],
          ['font-size','16px']
        ], /// handle outline
        ['.hslCircleColorPicker>div.scale>span.handle>span.outline',
          ['color','#333'],
          ['font-size','18px']
        ], /// disabled scale
        ['.hslCircleColorPicker.disabled>div.scale',
          ['cursor','default']
        ], /// disabled handle
        ['.hslCircleColorPicker.disabled>div.scale>span.handle>span',
          ['opacity','0.5'],
          ['filter','alpha(opacity=50)']
        ]
      ],
      styleElement,
      thisStyle,
      beforeThis = document.getElementsByTagName('link');

    if(beforeThis.length===0){
      beforeThis = document.getElementsByTagName('style');
    }


    if( e===true || this.width != 1 || this.height != 1 ){

      style = style.concat([
        /// hue sprite
        ['.hslCircleColorPicker .hSprite',
          ['background-image',"url('"+$.colors.pickers.hslCircle.pathToImages+"jquery.colors.pickers.hslCircle.sprite.h.png')"]
        ], /// saturation sprite
        ['.hslCircleColorPicker .sSprite',
          ['background-image',"url('"+$.colors.pickers.hslCircle.pathToImages+"jquery.colors.pickers.hslCircle.sprite.s.png')"]
        ], /// lightness sprite
        ['.hslCircleColorPicker>div.scale.l',
          ['background-image',"url('"+$.colors.pickers.hslCircle.pathToImages+"jquery.colors.pickers.hslCircle.l.png')"]
        ]
      ]);

    }else{

      styleElement = document.createElement('link');
      styleElement.type = 'text/css';
      styleElement.charset = 'utf-8';
      styleElement.rel = 'stylesheet';

      styleElement.href = $.colors.pickers.hslCircle.pathToImages+'jquery.colors.pickers.hslCircle.css';

      if(beforeThis.length!==0){
        document.getElementsByTagName('head')[0].insertBefore( styleElement, beforeThis[0] );
      }else{
        document.getElementsByTagName('head')[0].appendChild( styleElement);
      }


    }


    styleElement = document.createElement('style');
    styleElement.type = 'text/css';
    styleElement.charset = 'utf-8';
    styleElement.rel = 'stylesheet';

    for( a=0, aMax=style.length; a<aMax; a++ ){

      thisStyle = [ style[a][0] , '{' ];

      for( b=1, bMax=style[a].length; b<bMax; b++ ){
        thisStyle.push( style[a][b].join(':') , ';' );
      }

      thisStyle.push('}');

      style[a] = thisStyle.join('');
    }

    style = style.join('');


    if(beforeThis.length!==0){
      document.getElementsByTagName('head')[0].insertBefore( styleElement, beforeThis[0] );
    }else{
      document.getElementsByTagName('head')[0].appendChild( styleElement);
    }

    /// http://www.mail-archive.com/jquery-en@googlegroups.com/msg16487.html
    if( styleElement.styleSheet ) {  // IE
      styleElement.styleSheet.cssText = style;
    }else{  // other browsers
      $(styleElement).text( style );
    }

    if(e!==true){
      $(this).remove();
    }

  }


  /// confirm initiation
  ini = true;


  /// IE wont allow dynamic data uris so detection wont work (and no error called)
  /// http://msdn.microsoft.com/en-us/library/cc848897%28VS.85%29.aspx
  if( /MSIE/.test(navigator.appVersion) ){
    injectStyle(true);
  }else{
    $('<img/>').attr('src','data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==')
      .one('error load',injectStyle)
      .appendTo('body');
  }
}
 })(jQuery);
});