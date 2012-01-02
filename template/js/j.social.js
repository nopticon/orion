/* ------------------------------------------------------------------------
 * prettySociable plugin.
 * Version: 1.2.1
 * Description: Include this plugin in your webpage and let people
 * share your content like never before.
 * Website: http://no-margin-for-errors.com/projects/prettySociable/
 * 						
 * Thank You: 
 * Chris Wallace, for the nice icons
 * http://www.chris-wallace.com/2009/05/28/free-social-media-icons-socialize/
 * ------------------------------------------------------------------------- */

(function($){$.prettySociable = {version: 1.21};

var pretty_domain = '//assets.orion.com/';

$.prettySociable = function(settings) {
	$.prettySociable.settings = jQuery.extend({
		animationSpeed: 'fast',
		opacity: 0.90,
		share_label: 'Drag to share',
		label_position: 'top',
		share_on_label: 'Share on ',
		hideflash: false,
		hover_padding: 0,
		websites: {
			facebook: {
				'active': true,
				'encode': true,
				'title': 'Facebook',
				'url': 'http://www.facebook.com/share.php?u=',
				'icon': pretty_domain + 'style/facebook.png',
				'sizes': {'width': 70, 'height': 70}
			},
			twitter: {
				'active': true,
				'encode': true,
				'title': 'Twitter',
				'url': 'http://twitter.com/home?status=',
				'icon': pretty_domain + 'style/twitter.png',
				'sizes': {'width':70, 'height':70}
			},
			tumblr:{
				'active': true,
				'encode': true,
				'title': 'Tumblr',
				'url': 'http://www.tumblr.com/share?v=3&u=',
				'icon': pretty_domain + 'style/tumblr.png',
				'sizes': {'width':70, 'height':70}
			},
			delicious: {
				'active': true,
				'encode': true,
				'title': 'Delicious',
				'url': 'http://del.icio.us/post?url=',
				'icon': pretty_domain + 'style/delicious.png',
				'sizes': {'width':70, 'height':70}
			},
			digg: {
				'active': true,
				'encode': true,
				'title': 'Digg',
				'url': 'http://digg.com/submit?phase=2&url=',
				'icon': pretty_domain + 'style/digg.png',
				'sizes': {'width':70, 'height':70}
			},
			linkedin: {
				'active': true,
				'encode': true,
				'title': 'LinkedIn',
				'url': 'http://www.linkedin.com/shareArticle?mini=true&ro=true&url=',
				'icon': pretty_domain + 'style/linkedin.png',
				'sizes': {'width':70, 'height':70}
			},
			reddit:{
				'active': true,
				'encode': true,
				'title': 'Reddit',
				'url': 'http://reddit.com/submit?url=',
				'icon': pretty_domain + 'style/reddit.png',
				'sizes': {'width':70, 'height':70}
			},
			stumbleupon: {
				'active': true,
				'encode': false,
				'title': 'StumbleUpon',
				'url': 'http://stumbleupon.com/submit?url=',
				'icon': pretty_domain + 'style/stumbleupon.png',
				'sizes': {'width':70, 'height':70}
			}
		},
		urlshortener: {
			bitly: {'active':false}
		},
		tooltip: {
			offsetTop:0,
			offsetLeft:15
		},
		popup: {width:900, height:500},
		callback: function(){}
	},settings);
	
	var websites, settings = $.prettySociable.settings, show_timer, ps_hover;
	
	$.each(settings.websites, function(i) {
		var preload = new Image();
		preload.src = this.icon;
	});
	
	$('a[rel^=prettySociable]').hover(function() {
		_self = this;
		_container = this;
		
		if ($(_self).find('img').size() > 0) {
			_self = $(_self).find('img');
		} else if ($.browser.msie) {
			if ($(_self).find('embed').size() > 0) {
				_self = $(_self).find('embed');
				$(_self).css({'display':'block'});
			}
		} else {
			if ($(_self).find('object').size() > 0) {
				_self = $(_self).find('object');
				$(_self).css({'display':'block'});
			}
		}
		
		$(_self).css({'cursor':'move','position':'relative','z-index':1005});
		
		offsetLeft = (parseFloat($(_self).css('borderLeftWidth'))) ? parseFloat($(_self).css('borderLeftWidth')) : 0;
		offsetTop = (parseFloat($(_self).css('borderTopWidth'))) ? parseFloat($(_self).css('borderTopWidth')) : 0;
		offsetLeft += (parseFloat($(_self).css('paddingLeft'))) ? parseFloat($(_self).css('paddingLeft')) : 0;
		offsetTop += (parseFloat($(_self).css('paddingTop'))) ? parseFloat($(_self).css('paddingTop')): 0 ;
		ps_hover = $('<div id="ps_hover"> \
        <div class="ps_hd"> \
         <div class="ps_c"></div> \
        </div> \
        <div class="ps_bd"> \
         <div class="ps_c"> \
          <div class="ps_s"> \
          </div> \
         </div> \
        </div> \
        <div class="ps_ft"> \
         <div class="ps_c"></div> \
        </div> \
        <div id="ps_title"> \
         <div class="ps_tt_l"> \
          '+settings.share_label+' \
         </div> \
        </div> \
       </div>').css({'width':$(_self).width()+(settings.hover_padding+8)*2,'top':$(_self).position().top-settings.hover_padding-8+parseFloat($(_self).css('marginTop'))+offsetTop,'left':$(_self).position().left-settings.hover_padding-8+parseFloat($(_self).css('marginLeft'))+offsetLeft}).hide().insertAfter(_container).fadeIn(settings.animationSpeed);$('#ps_title').animate({top:-15},settings.animationSpeed);$(ps_hover).find('>.ps_bd .ps_s').height($(_self).height()+settings.hover_padding*2);fixCrappyBrowser('ps_hover',this);DragHandler.attach($(this)[0]);$(this)[0].dragBegin=function(e){_self=this;show_timer=window.setTimeout(function(){$('object,embed').css('visibility','hidden');$(_self).animate({'opacity':0},settings.animationSpeed);$(ps_hover).remove();overlay.show();tooltip.show(_self);tooltip.follow(e.mouseX,e.mouseY);sharing.show();},200);};$(this)[0].drag=function(e){tooltip.follow(e.mouseX,e.mouseY);}
$(this)[0].dragEnd=function(element,x,y){$('object,embed').css('visibility','visible');$(this).attr('style',0);overlay.hide();tooltip.checkCollision(element.mouseX,element.mouseY);};},function(){$(ps_hover).fadeOut(settings.animationSpeed,function(){$(this).remove()});}).click(function(){clearTimeout(show_timer);});var tooltip={show:function(caller){tooltip.link_to_share=($(caller).attr('href')!="#")?$(caller).attr('href'):location.href;if(settings.urlshortener.bitly.active){if(window.BitlyCB){BitlyCB.myShortenCallback=function(data){var result;for(var r in data.results){result=data.results[r];result['longUrl']=r;break;};tooltip.link_to_share=result['shortUrl'];};BitlyClient.shorten(tooltip.link_to_share,'BitlyCB.myShortenCallback');};};attributes=$(caller).attr('rel').split(';');for(var i=1;i<attributes.length;i++){attributes[i]=attributes[i].split(':');};desc=($('meta[name=Description]').attr('content'))?$('meta[name=Description]').attr('content'):"";if(attributes.length==1){attributes[1]=['title',document.title];attributes[2]=['excerpt',desc];}
ps_tooltip=$('<div id="ps_tooltip"> \
         <div class="ps_hd"> \
          <div class="ps_c"></div> \
         </div> \
         <div class="ps_bd"> \
          <div class="ps_c"> \
           <div class="ps_s"> \
           </div> \
          </div> \
         </div> \
         <div class="ps_ft"> \
          <div class="ps_c"></div> \
         </div> \
            </div>').appendTo('body');$(ps_tooltip).find('.ps_s').html("<p><strong>"+attributes[1][1]+"</strong><br />"+attributes[2][1]+"</p>");fixCrappyBrowser('ps_tooltip');},checkCollision:function(x,y){collision="";scrollPos=_getScroll();$.each(websites,function(i){if((x+scrollPos.scrollLeft>$(this).offset().left&&x+scrollPos.scrollLeft<$(this).offset().left+$(this).width())&&(y+scrollPos.scrollTop>$(this).offset().top&&y+scrollPos.scrollTop<$(this).offset().top+$(this).height())){collision=$(this).find('a');}});if(collision!=""){$(collision).click();}
sharing.hide();$('#ps_tooltip').remove();},follow:function(x,y){scrollPos=_getScroll();settings.tooltip.offsetTop=(settings.tooltip.offsetTop)?settings.tooltip.offsetTop:0;settings.tooltip.offsetLeft=(settings.tooltip.offsetLeft)?settings.tooltip.offsetLeft:0;$('#ps_tooltip').css({'top':y+settings.tooltip.offsetTop+scrollPos.scrollTop,'left':x+settings.tooltip.offsetLeft+scrollPos.scrollLeft});}}
var sharing={show:function(){websites_container=$('<ul />');$.each(settings.websites,function(i){var _self=this;if(_self.active){link=$('<a />').attr({'href':'#'}).html('<img src="'+_self.icon+'" alt="'+_self.title+'" width="'+_self.sizes.width+'" height="'+_self.sizes.height+'" />').hover(function(){sharing.showTitle(_self.title,$(this).width(),$(this).position().left,$(this).height(),$(this).position().top);},function(){sharing.hideTitle();}).click(function(){shareURL=(_self.encode)?encodeURIComponent(tooltip.link_to_share):tooltip.link_to_share;popup=window.open(_self.url+shareURL,"prettySociable","location=0,status=0,scrollbars=1,width="+settings.popup.width+",height="+settings.popup.height);});$('<li>').append(link).appendTo(websites_container);};});$('<div id="ps_websites"><p class="ps_label"></p></div>').append(websites_container).appendTo('body');fixCrappyBrowser('ps_websites');scrollPos=_getScroll();$('#ps_websites').css({'top':$(window).height()/2-$('#ps_websites').height()/2+scrollPos.scrollTop,'left':$(window).width()/2-$('#ps_websites').width()/2+scrollPos.scrollLeft});websites=$.makeArray($('#ps_websites li'));},hide:function(){$('#ps_websites').fadeOut(settings.animationSpeed,function(){$(this).remove()});},showTitle:function(title,width,left,height,top){$label=$('#ps_websites .ps_label');$label.text(settings.share_on_label+title)
$label.css({'left':left-$label.width()/2+width/2,'opacity':0,'display':'block'}).stop().animate({'opacity':1,'top':top-height+45},settings.animationSpeed);},hideTitle:function(){$('#ps_websites .ps_label').stop().animate({'opacity':0,'top':10},settings.animationSpeed);}};var overlay={show:function(){$('<div id="ps_overlay" />').css('opacity',0).appendTo('body').height($(document).height()).fadeTo(settings.animationSpeed,settings.opacity);},hide:function(){$('#ps_overlay').fadeOut(settings.animationSpeed,function(){$(this).remove();});}}
var DragHandler={_oElem:null,attach:function(oElem){oElem.onmousedown=DragHandler._dragBegin;oElem.dragBegin=new Function();oElem.drag=new Function();oElem.dragEnd=new Function();return oElem;},_dragBegin:function(e){var oElem=DragHandler._oElem=this;if(isNaN(parseInt(oElem.style.left))){oElem.style.left='0px';}
if(isNaN(parseInt(oElem.style.top))){oElem.style.top='0px';}
var x=parseInt(oElem.style.left);var y=parseInt(oElem.style.top);e=e?e:window.event;oElem.mouseX=e.clientX;oElem.mouseY=e.clientY;oElem.dragBegin(oElem,x,y);document.onmousemove=DragHandler._drag;document.onmouseup=DragHandler._dragEnd;return false;},_drag:function(e){var oElem=DragHandler._oElem;var x=parseInt(oElem.style.left);var y=parseInt(oElem.style.top);e=e?e:window.event;oElem.style.left=x+(e.clientX-oElem.mouseX)+'px';oElem.style.top=y+(e.clientY-oElem.mouseY)+'px';oElem.mouseX=e.clientX;oElem.mouseY=e.clientY;oElem.drag(oElem,x,y);return false;},_dragEnd:function(){var oElem=DragHandler._oElem;var x=parseInt(oElem.style.left);var y=parseInt(oElem.style.top);oElem.dragEnd(oElem,x,y);document.onmousemove=null;document.onmouseup=null;DragHandler._oElem=null;}};function _getScroll(){if(self.pageYOffset){scrollTop=self.pageYOffset;scrollLeft=self.pageXOffset;}else if(document.documentElement&&document.documentElement.scrollTop){scrollTop=document.documentElement.scrollTop;scrollLeft=document.documentElement.scrollLeft;}else if(document.body){scrollTop=document.body.scrollTop;scrollLeft=document.body.scrollLeft;}
return{scrollTop:scrollTop,scrollLeft:scrollLeft};};function fixCrappyBrowser(element,caller){if($.browser.msie&&$.browser.version==6){if(typeof DD_belatedPNG!='undefined'){if(element=='ps_websites'){$('#'+element+' img').each(function(){DD_belatedPNG.fixPng($(this)[0]);});}else{DD_belatedPNG.fixPng($('#'+element+' .ps_hd .ps_c')[0]);DD_belatedPNG.fixPng($('#'+element+' .ps_hd')[0]);DD_belatedPNG.fixPng($('#'+element+' .ps_bd .ps_c')[0]);DD_belatedPNG.fixPng($('#'+element+' .ps_bd')[0]);DD_belatedPNG.fixPng($('#'+element+' .ps_ft .ps_c')[0]);DD_belatedPNG.fixPng($('#'+element+' .ps_ft')[0]);}};};}};})(jQuery);
