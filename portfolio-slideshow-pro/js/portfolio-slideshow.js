/*!
 * touchSwipe - jQuery Plugin
 * http://plugins.jquery.com/project/touchSwipe
 * http://labs.skinkers.com/touchSwipe/
 *
 * Copyright (c) 2010 Matt Bryson (www.skinkers.com)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * $version: 1.2.3
 */
 /* 
 */

(function($) 
{
	
	
	
	$.fn.swipe = function(options) 
	{
		if (!this) return false;
		
		// Default thresholds & swipe functions
		var defaults = {
					
			fingers 		: 1,								// int - The number of fingers to trigger the swipe, 1 or 2. Default is 1.
			threshold 		: 75,								// int - The number of pixels that the user must move their finger by before it is considered a swipe. Default is 75.
			
			swipe 			: null,		// Function - A catch all handler that is triggered for all swipe directions. Accepts 2 arguments, the original event object and the direction of the swipe : "left", "right", "up", "down".
			swipeLeft		: null,		// Function - A handler that is triggered for "left" swipes. Accepts 3 arguments, the original event object, the direction of the swipe : "left", "right", "up", "down" and the distance of the swipe.
			swipeRight		: null,		// Function - A handler that is triggered for "right" swipes. Accepts 3 arguments, the original event object, the direction of the swipe : "left", "right", "up", "down" and the distance of the swipe.
			swipeUp			: null,		// Function - A handler that is triggered for "up" swipes. Accepts 3 arguments, the original event object, the direction of the swipe : "left", "right", "up", "down" and the distance of the swipe.
			swipeDown		: null,		// Function - A handler that is triggered for "down" swipes. Accepts 3 arguments, the original event object, the direction of the swipe : "left", "right", "up", "down" and the distance of the swipe.
			swipeStatus		: null,		// Function - A handler triggered for every phase of the swipe. Handler is passed 4 arguments: event : The original event object, phase:The current swipe face, either "start�, "move�, "end� or "cancel�. direction : The swipe direction, either "up�, "down�, "left " or "right�.distance : The distance of the swipe.
			click			: null,		// Function	- A handler triggered when a user just clicks on the item, rather than swipes it. If they do not move, click is triggered, if they do move, it is not.
			
			triggerOnTouchEnd : true,	// Boolean, if true, the swipe events are triggered when the touch end event is received (user releases finger).  If false, it will be triggered on reaching the threshold, and then cancel the touch event automatically.
			allowPageScroll : "auto" 	/* How the browser handles page scrolls when the user is swiping on a touchSwipe object. 
											"auto" : all undefined swipes will cause the page to scroll in that direction.
 											"none" : the page will not scroll when user swipes.
 											"horizontal" : will force page to scroll on horizontal swipes.
 											"vertical" : will force page to scroll on vertical swipes.
										*/
		};
		
		
		//Constants
		var LEFT = "left";
		var RIGHT = "right";
		var UP = "up";
		var DOWN = "down";
		var NONE = "none";
		var HORIZONTAL = "horizontal";
		var VERTICAL = "vertical";
		var AUTO = "auto";
		
		var PHASE_START="start";
		var PHASE_MOVE="move";
		var PHASE_END="end";
		var PHASE_CANCEL="cancel";
		
		
		
		var phase="start";
		
		if (options.allowPageScroll==undefined && (options.swipe!=undefined || options.swipeStatus!=undefined))
			options.allowPageScroll=NONE;
		
		if (options)
			$.extend(defaults, options);
		
		
		/**
		 * Setup each object to detect swipe gestures
		 */
		return this.each(function() 
		{
			var $this = $(this);
			
			var triggerElementID = null; 	// this variable is used to identity the triggering element
			var fingerCount = 0;			// the current number of fingers being used.	
			
			//track mouse points / delta
			var start={x:0, y:0};
			var end={x:0, y:0};
			var delta={x:0, y:0};
			
			
			/**
			* Event handler for a touch start event. 
			* Stops the default click event from triggering and stores where we touched
			*/
			function touchStart(event) 
			{
				phase = PHASE_START;
		
				// get the total number of fingers touching the screen
				fingerCount = event.touches.length;
				
				//clear vars..
				distance=0;
				direction=null;
				
				// check the number of fingers is what we are looking for
				if ( fingerCount == defaults.fingers ) 
				{
					// get the coordinates of the touch
					start.x = end.x = event.touches[0].pageX;
					start.y = end.y = event.touches[0].pageY;
					
					if (defaults.swipeStatus)
						triggerHandler(event, phase);
				} 
				else 
				{
					//touch with more/less than the fingers we are looking for
					touchCancel(event);
				}
			}

			/**
			* Event handler for a touch move event. 
			* If we change fingers during move, then cancel the event
			*/
			function touchMove(event) 
			{
				if (phase == PHASE_END || phase == PHASE_CANCEL)
					return;
				
				end.x = event.touches[0].pageX;
				end.y = event.touches[0].pageY;
					
				direction = caluculateDirection();
				fingerCount = event.touches.length;
				
				phase = PHASE_MOVE
				
				//Check if we need to prevent default evnet (page scroll) or not
				validateDefaultEvent(event, direction);
		
				if ( fingerCount == defaults.fingers ) 
				{
					distance = caluculateDistance();
					
					if (defaults.swipeStatus)
						triggerHandler(event, phase, direction, distance);
					
					//If we trigger whilst dragging, not on touch end, then calculate now...
					if (!defaults.triggerOnTouchEnd)
					{
						// if the user swiped more than the minimum length, perform the appropriate action
						if ( distance >= defaults.threshold ) 
						{
							phase = PHASE_END;
							triggerHandler(event, phase);
							touchCancel(event); // reset the variables
						}
					}
				} 
				else 
				{
					phase = PHASE_CANCEL;
					triggerHandler(event, phase); 
					touchCancel(event);
				}
			}
			
			/**
			* Event handler for a touch end event. 
			* Calculate the direction and trigger events
			*/
			function touchEnd(event) 
			{
				event.preventDefault();
				
				distance = caluculateDistance();
				direction = caluculateDirection();
						
				if (defaults.triggerOnTouchEnd)
				{
					phase = PHASE_END;
					// check to see if more than one finger was used and that there is an ending coordinate
					if ( fingerCount == defaults.fingers && end.x != 0 ) 
					{
						// if the user swiped more than the minimum length, perform the appropriate action
						if ( distance >= defaults.threshold ) 
						{
							triggerHandler(event, phase);
							touchCancel(event); // reset the variables
						} 
						else 
						{
							phase = PHASE_CANCEL;
							triggerHandler(event, phase); 
							touchCancel(event);
						}	
					} 
					else 
					{
						phase = PHASE_CANCEL;
						triggerHandler(event, phase); 
						touchCancel(event);
					}
				}
				else if (phase == PHASE_MOVE)
				{
					phase = PHASE_CANCEL;
					triggerHandler(event, phase); 
					touchCancel(event);
				}
			}
			
			/**
			* Event handler for a touch cancel event. 
			* Clears current vars
			*/
			function touchCancel(event) 
			{
				// reset the variables back to default values
				fingerCount = 0;
				
				start.x = 0;
				start.y = 0;
				end.x = 0;
				end.y = 0;
				delta.x = 0;
				delta.y = 0;
			}
			
			
			/**
			* Trigger the relevant event handler
			* The handlers are passed the original event, the element that was swiped, and in the case of the catch all handler, the direction that was swiped, "left", "right", "up", or "down"
			*/
			function triggerHandler(event, phase) 
			{
				//update status
				if (defaults.swipeStatus)
					defaults.swipeStatus.call($this,event, phase, direction || null, distance || 0);
				
				
				if (phase == PHASE_CANCEL)
				{
					if (defaults.click && fingerCount==1 && (isNaN(distance) || distance==0))
						defaults.click.call($this,event, event.target);
				}
				
				if (phase == PHASE_END)
				{
					//trigger catch all event handler
					if (defaults.swipe)
				{
						
						defaults.swipe.call($this,event, direction, distance);
						
				}
					//trigger direction specific event handlers	
					switch(direction)
					{
						case LEFT :
							if (defaults.swipeLeft)
								defaults.swipeLeft.call($this,event, direction, distance);
							break;
						
						case RIGHT :
							if (defaults.swipeRight)
								defaults.swipeRight.call($this,event, direction, distance);
							break;

						case UP :
							if (defaults.swipeUp)
								defaults.swipeUp.call($this,event, direction, distance);
							break;
						
						case DOWN :	
							if (defaults.swipeDown)
								defaults.swipeDown.call($this,event, direction, distance);
							break;
					}
				}
			}
			
			
			/**
			 * Checks direction of the swipe and the value allowPageScroll to see if we should allow or prevent the default behaviour from occurring.
			 * This will essentially allow page scrolling or not when the user is swiping on a touchSwipe object.
			 */
			function validateDefaultEvent(event, direction)
			{
				if( defaults.allowPageScroll==NONE )
				{
					event.preventDefault();
				}
				else 
				{
					var auto=defaults.allowPageScroll==AUTO;
					
					switch(direction)
					{
						case LEFT :
							if ( (defaults.swipeLeft && auto) || (!auto && defaults.allowPageScroll!=HORIZONTAL))
								event.preventDefault();
							break;
						
						case RIGHT :
							if ( (defaults.swipeRight && auto) || (!auto && defaults.allowPageScroll!=HORIZONTAL))
								event.preventDefault();
							break;

						case UP :
							if ( (defaults.swipeUp && auto) || (!auto && defaults.allowPageScroll!=VERTICAL))
								event.preventDefault();
							break;
						
						case DOWN :	
							if ( (defaults.swipeDown && auto) || (!auto && defaults.allowPageScroll!=VERTICAL))
								event.preventDefault();
							break;
					}
				}
				
			}
			
			
			
			/**
			* Calcualte the length / distance of the swipe
			*/
			function caluculateDistance()
			{
				return Math.round(Math.sqrt(Math.pow(end.x - start.x,2) + Math.pow(end.y - start.y,2)));
			}
			
			/**
			* Calcualte the angle of the swipe
			*/
			function caluculateAngle() 
			{
				var X = start.x-end.x;
				var Y = end.y-start.y;
				var r = Math.atan2(Y,X); //radians
				var angle = Math.round(r*180/Math.PI); //degrees
				
				//ensure value is positive
				if (angle < 0) 
					angle = 360 - Math.abs(angle);
					
				return angle;
			}
			
			/**
			* Calcualte the direction of the swipe
			* This will also call caluculateAngle to get the latest angle of swipe
			*/
			function caluculateDirection() 
			{
				var angle = caluculateAngle();
				
				if ( (angle <= 45) && (angle >= 0) ) 
					return LEFT;
				
				else if ( (angle <= 360) && (angle >= 315) )
					return LEFT;
				
				else if ( (angle >= 135) && (angle <= 225) )
					return RIGHT;
				
				else if ( (angle > 45) && (angle < 135) )
					return DOWN;
				
				else
					return UP;
			}
			
			

			// Add gestures to all swipable areas if supported
			try
			{
				this.addEventListener("touchstart", touchStart, false);
				this.addEventListener("touchmove", touchMove, false);
				this.addEventListener("touchend", touchEnd, false);
				this.addEventListener("touchcancel", touchCancel, false);
			}
			catch(e)
			{
				//touch not supported
			}
				
		});
	};
	
	
	
	
})(jQuery);

/*!
 * jQuery imagesLoaded plugin v1.0.3
 * http://github.com/desandro/imagesloaded
 *
 * MIT License. by Paul Irish et al.
 */

(function($, undefined) {

  // $('#my-container').imagesLoaded(myFunction)
  // or
  // $('img').imagesLoaded(myFunction)

  // execute a callback when all images have loaded.
  // needed because .load() doesn't work on cached images

  // callback function gets image collection as argument
  //  `this` is the container

  $.fn.imagesLoaded = function( callback ) {
    var $this = this,
        $images = $this.find('img').add( $this.filter('img') ),
        len = $images.length,
        blank = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

    function triggerCallback() {
      callback.call( $this, $images );
    }

    function imgLoaded() {
      if ( --len <= 0 && this.src !== blank ){
        setTimeout( triggerCallback );
        $images.unbind( 'load error', imgLoaded );
      }
    }

    if ( !len ) {
      triggerCallback();
    }

    $images.bind( 'load error',  imgLoaded ).each( function() {
      // cached images don't fire load sometimes, so we reset src.
      if (this.complete || this.complete === undefined){
        var src = this.src;
        // webkit hack from http://groups.google.com/group/jquery-dev/browse_thread/thread/eee6ab7b2da50e1f
        // data uri bypasses webkit log warning (thx doug jones)
        this.src = blank;
        this.src = src;
      }
    });

    return $this;
  };
})(jQuery);

/*!
 * Portfolio Slideshow Pro
 */
 
(function ($) {
	$(window).load(function() {
	
		var psLoader, psHash, psFancyBox, psInfoTxt, psFluid, psTouchSwipe, psKeyboarNav, disableAutoScroll;
		currSlide = new Array(); tabSlide = new Array();
		psLoader = portfolioSlideshowOptions.psLoader;
		psFluid = portfolioSlideshowOptions.psFluid;
		psHash = portfolioSlideshowOptions.psHash;
		psFancyBox = portfolioSlideshowOptions.psFancyBox;
		psTouchSwipe = portfolioSlideshowOptions.psTouchSwipe;
		psKeyboardNav = portfolioSlideshowOptions.psKeyboardNav;
		psInfoTxt = portfolioSlideshowOptions.psInfoTxt;
		disableAutoScroll = false;
	
		if ( jQuery.browser.msie && parseInt( jQuery.browser.version ) < 8 ) { //sets ie var to true if IE is 7 or below. Necessary for some style & functionality issues.
			ie = true; 
		} else { 
			ie = false 
		}
			
		//load our Fancybox scripts
		if (psFancyBox === true) {
			function formatTitle (title, currentArray, currentIndex, currentOpts) {
				return '<div id="image-caption">' + (title && title.length ? '<b>' + title + '</b>' : '' ) + ' Image ' + (currentIndex + 1) + ' of ' + currentArray.length + '</div>';
				}
			
				$("a.fancybox, .gallery-item 'a[href$='.gif'], .gallery-item 'a[href$='.jpg'], .gallery-item 'a[href$='.png'], .gallery-item 'a[href$='.jpeg']").fancybox({
				'titlePosition' : 'inside',
				'titleFormat' : formatTitle
			});
	
		}
		
		if (psLoader === true) { //if we're supposed to show a loader
			$('.slideshow-wrapper').delay(1000).queue(function() {
				$('.portfolio-slideshow, .slideshow-nav, .pager').css('visibility', 'visible');
				$(this).removeClass("showloader");
			});	
		} else {
			$('.portfolio-slideshow, .slideshow-nav, .pager').css('visibility', 'visible');
		}
										
		$("div[id^=portfolio-slideshow]").each(function () {
				
			var num = this.id.match(/portfolio-slideshow(\d+)/)[1];
			
			/* Cache our jQuery objects*/
			
			/* Main slideshow elements */
			var slideshowwrapper = $('#slideshow-wrapper' + num);
			var slideshownav = $('#slideshow-nav' + num);
			var slideshow = $('#portfolio-slideshow' + num);
			var slideimage = slideshow.find('.slideshow-content img');
			var fancygrid = $('#slideshow-wrapper'+num+'.fancygrid');
			
			/*Toggles*/
			var toggleshow = slideshowwrapper.find('.show');
			var togglehide = slideshowwrapper.find('.hide')
			var carousel = slideshowwrapper.find('.pscarousel');
			var thumbs = slideshowwrapper.find('.psthumbs'); 
			
			/*Nav elements*/
			var playbutton = slideshownav.find('.play');
			var pausebutton = slideshownav.find('.pause');
			var restartbutton = slideshownav.find('.restart');
				
			if ( ie === true ) {
				slideshowwrapper.addClass('ie');
			}

			//load our scrollable carousel
			$('#scrollable' + num).scrollable({keyboard:false}).navigator({navi: "#carouselnav" + num, naviItem: 'a'});
			$('.prev.browse').removeClass('hidden');
			
			/* Pause the carousel autoscroll function for 15 seconds if the carousel nav is clicked */
			
			slideshowwrapper.find('.browse').click(function() {
				if ( disableAutoScroll != true ) {
					disableAutoScroll = true;
					setTimeout( function() { disableAutoScroll=false;
					} , 15000 );	
				}		
			});
									
			/* Toggle actions */

			toggleshow.click(function() {
				$(this).hide();
				togglehide.addClass('active');
				thumbs.fadeIn('fast');
			});		
			
			togglehide.click(function() {
				$(this).removeClass('active');
				toggleshow.show();
				thumbs.fadeOut('fast');
			});		
			
			/* End toggles */
			
			/* Fancygrid */

			fancygrid.find('.portfolio-slideshow').hide();
				fancygrid.find('.psthumbs').show();
		
				fancygrid.find('.pager img').click(function() {
						slideshow.fadeIn('fast', function() {
							slideshow.css('width','').css('height','');	
							var $h, $w;
							$h = slideshow.find('.slideshow-content').eq(currSlide[num]).outerHeight();
							$w = slideshow.find('.slideshow-content').eq(currSlide[num]).width();
							slideshow.css("height", $h).css("width", $w);
							slideshow.fadeTo('fast',1);
						});
								
					fancygrid.find('.slideshow-nav').show();
					fancygrid.find('.pager').hide();
				});		
				
				fancygrid.find('.thumb-toggles a').click(function() {
					fancygrid.find('.portfolio-slideshow').hide();
					fancygrid.find('.slideshow-nav').hide();
					fancygrid.find('.pager').fadeIn('fast');
					fancygrid.find('.psthumbs').show();	
					fancygrid.find('a.hide').removeClass('active');
					fancygrid.find('a.show').show();
				});			
		
			/* End Fancygrid */
				
				$(function () {
					var index = 0, hash = window.location.hash;
					if (/\d+/.exec(hash)) {
					index = /\d+/.exec(hash)[0];
					index = (parseInt(index) || 1) - 1; // slides are zero-based
				}
	
				// Set up active pager links for the two slideshow configurations
				if ( psPagerStyle[num] === 'thumbs' || psPagerStyle[num] === 'carousel') {
					$.fn.cycle.updateActivePagerLink = function(pager, currSlideIndex) { 
					$(pager).find('img').removeClass('activeSlide') 
					.filter('#pager' + num + ' img:eq('+currSlideIndex+')').addClass('activeSlide'); 
					};
				} else {
					$.fn.cycle.updateActivePagerLink = function(pager, currSlideIndex) { 
					$(pager).find('a').removeClass('activeSlide') 
					.filter('#pager' + num + ' a:eq('+currSlideIndex+')').addClass('activeSlide'); 
					};
				}	
	
				//two different Cycle configs, depending on the pager option
				function cyclePager() {
					slideshow.cycle({
						fx: psTrans[num],
						speed: psSpeed[num],
						timeout: psTimeout[num],
						delay: psDelay[num],
						random: psRandom[num],
						nowrap: psNoWrap[num],
						next: '#slideshow-wrapper' + num + ' a.slideshow-next, #slideshow-wrapper' + num + ' #psnext' + num,
						startingSlide: index,
						prev: '#slideshow-wrapper' + num + ' a.slideshow-prev , #slideshow-wrapper' + num + ' #psprev' + num,
						before:	onBefore,
						after:	onAfter,
						end:	onEnd,
						slideExpr:	'.slideshow-content',
						manualTrump: true,
						slideResize: false,
						containerResize: false,
						pager:  '#pager' + num,
						cleartypeNoBg: true,
						pagerAnchorBuilder: buildAnchors
					});
				}	
				
				function cycleNumbersPager() {
					slideshow.cycle({
						fx: psTrans[num],
						speed: psSpeed[num],
						timeout: psTimeout[num],
						delay: psDelay[num],
						random: psRandom[num],
						nowrap: psNoWrap[num],
						next: '#slideshow-wrapper' + num + ' a.slideshow-next, #psnext' + num,
						startingSlide: index,
						prev: '#slideshow-wrapper' + num + ' a.slideshow-prev, #psprev' + num,
						before:     onBefore,
						after:	onAfter,
						slideExpr:	'.slideshow-content',
						end:	onEnd,
						manualTrump: true,
						slideResize: false,
						containerResize: false,
						pager:  '#pager' + num + ' .numbers', 
						cleartypeNoBg: true
					});
				}
			
				if (psPagerStyle[num] === "numbers") {
					cycleNumbersPager();
				} else {
					cyclePager();	
				}
				
				slideimage.each(function() { //this gives each of the images a src attribute once the window has loaded
					$(this).attr('src',$(this).attr('data-img'));
				});
	
				//pause the slideshow right away if autoplay is off
				if ( psAutoplay[num] === false ) {
					slideshow.cycle('pause');
				} else {
			
					if ( psAudio[num] === true ) {
						slideshow.delay(1000).queue(function() {
							slideshow.cycle('resume');	
							playbutton.fadeOut(100, function(){
							pausebutton.fadeIn(10);});	
							slideshow.parent().nextAll('.haiku-text-player:first').jPlayer('play');
						});
					} else {
						playbutton.fadeOut(100, function(){
						pausebutton.fadeIn(10);});	
					}	
				}
	
				
				//pause
				pausebutton.click(function() { 
					$(this).fadeOut(100, function(){
						playbutton.fadeIn(10);});
					if (psAudio[num] === true) {
						$(this).parent().parent().nextAll('.haiku-text-player:first').jPlayer('pause');
					}
					slideshow.cycle('pause');
				});
			
				//play
				playbutton.click(function() { 
					slideshow.cycle('resume');
					$(this).fadeOut(100, function(){
						pausebutton.fadeIn(10);});
					if ( psAudio[num] === true ) {
						$(this).parent().parent().nextAll('.haiku-text-player:first').jPlayer('play');
					}	
				});
	
				//restart
				restartbutton.click(function() { 
					$('#pager' + num + ' .numbers').empty();
					$(this).fadeOut(100, function(){
						pausebutton.fadeIn(10);});
					if ( psAudio[num] === true ) {
						$(this).parent().parent().nextAll('.haiku-text-player:first').jPlayer('play');	
					}
	
					if (psPagerStyle[num] === "numbers") {
						cycleNumbersPager();			
					} else {
						cyclePager();							
					}
				
				});	
	
				if ( psFluid === true ) {				
					
					$(window).resize(function() { //on window resize, force resize of the slideshows
						var $window;	
						$window = $(window).height();   // returns height of browser viewport
						$('img.psp-active').each(function() {
						 		$(this).css('max-height', ($window - 60));
						});
								
						slideshow.css('width','').css('height','');	
						var $h, $w;
						$h = slideshow.find('.slideshow-content').eq(currSlide[num]).outerHeight();
						$w = slideshow.find('.slideshow-content').eq(currSlide[num]).width();
						slideshow.css("height", $h).css("width", $w);
					});
			
				}	
	
				if ( psKeyboardNav === true ) {
					$(document).keydown(function(e){
						if(e.which == 37){
							$('#psprev' + num).click();
						} else if(e.which == 39) {
							$('#psnext' + num).click();
						}
					});
				}	
		
				if ( psTouchSwipe === true ) {
					slideimage.swipe({ 
						swipeLeft:function() { $('#psnext' + num).click()},
						swipeRight:function() { $('#psprev' + num).click()},
						allowPageScroll:"auto"
					});
				}	
				
				//build anchors
				function buildAnchors(idx, slide) { 
					if (psPagerStyle[num] === 'thumbs' || psPagerStyle[num] === 'carousel') {
						return '#pager' +num+ ' img:eq(' + (idx) + ')'; 
					}
					if (psPagerStyle[num] === "bullets") {
						 return '#pager' +num+ ' a:eq(' + (idx) + ')'; 
					}	 
				}
				
				function onBefore(curr,next,opts) {
					var slide = $(this);
					tabSlide[num] = opts.nextSlide + 1;
					//This drives the tabs
					var psTabs = Math.ceil( ( ( tabSlide[num] ) / psCarouselSize[num] ) -1 );
					if ( disableAutoScroll != true ) {
						$('#carouselnav' + num + ' a').eq(psTabs).click();
					} 
										
					//this adjusts the height & width of the slideshow
										
					if ( psFluid === true ) {				
						$window = $(window).height();   // returns height of browser viewport
							$('img.psp-active').each(function() {
					 		$(this).css('max-height', ($window - 60));
						});
					}
						
					slide.find('img').imagesLoaded(function () {
						var $h,$w;
						$h = slide.height(); //slideshow content height
						$w = slide.width(); //slideshow content width
						slideshow.height($h).width($w);
					});
	
				}
					
				function onAfter(curr,next,opts) {
					var slide = $(this);
					currSlide[num] = opts.currSlide;		
					var $h,$w;
					$h = slide.height(); //slideshow content height
					$w = slide.width(); //slideshow content height
														
					if ( ie === true ) {
						slideshow.height($h).width($w);
					}
	
									
					if ( psNoWrap[num] === true ) { //if wrapping is disabled, fade out the controls at the appropriate time
						if (opts.currSlide === 0 ) {
							slideshownav.find('.slideshow-prev, .sep').addClass('inactive');
						} else {
							slideshownav.find('.slideshow-prev, .sep').removeClass('inactive');
						}
							
						if (opts.currSlide === opts.slideCount-1) {
							slideshownav.find('.slideshow-next, .sep').addClass('inactive');
						} else {
							slideshownav.find('.slideshow-next').removeClass('inactive');
						}
					}
	
					if (psHash === true) { 
						window.location.hash = opts.currSlide + 1;
					}
	
					var caption = (opts.currSlide + 1) + ' ' + psInfoTxt + ' ' + opts.slideCount;
					$('.slideshow-info' + num).html(caption);
				} 
	
				function onEnd() {
					slideshownav.find('.slideshow-next, .sep').addClass('inactive');
					pausebutton.hide();
					playbutton.hide();
					restartbutton.show();	
				}
				
			});
		});
		
		
		    $(function(){
		        // Fancybox: YouTube iFrame
		        var fancybox_iframe = $('.video-youtube');
		        if (fancybox_iframe.length > 0){
		            fancybox_iframe.each(function(index){
		                // Inline frame width param
		                if( $(this).attr('href').match(/ww=[0-9]+/i) ){
		                    var fWidth = parseInt($(this).attr('href').match(/ww=[0-9]+/i)[0].replace('ww=',''));
		                } else {
		                    var fWidth = '680';
		                }
		                // Inline frame height param
		                if( $(this).attr('href').match(/wh=[0-9]+/i) ){
		                    var fHeight = parseInt($(this).attr('href').match(/wh=[0-9]+/i)[0].replace('wh=',''));
		                } else {
		                    var fHeight = '495';
		                }
		                if(window.console&&window.console.log) { 
		                    console.log('fWidth #'+index+': '+fWidth); 
		                    console.log('fHeight #'+index+': '+fHeight); 
		                }
		                $(this).fancybox({
		                    'padding'		: 0,
		                    'autoScale' : false,
		                    'title'			: this.title,
		                    'scrolling'	: "no",
		                    'href'			: this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
		                    'width'     : fWidth,
		                    'height'    : fHeight,
		                    'type'			: 'swf',
		                    'swf'				: {
		                    	'wmode'						: 'transparent',
		                    	'allowfullscreen'	: 'true'
		                    }
		                });
		            });
		        }
		    });
		
		    $(function(){
		        // Fancybox: Vimeo iFrame
		        var fancybox_iframe = $('.video-vimeo');
		        if (fancybox_iframe.length > 0){
		            fancybox_iframe.each(function(index){
		                // Inline frame width param
		                if( $(this).attr('href').match(/ww=[0-9]+/i) ){
		                    var fWidth = parseInt($(this).attr('href').match(/ww=[0-9]+/i)[0].replace('ww=',''));
		                } else {
		                    var fWidth = '680';
		                }
		                // Inline frame height param
		                if( $(this).attr('href').match(/wh=[0-9]+/i) ){
		                    var fHeight = parseInt($(this).attr('href').match(/wh=[0-9]+/i)[0].replace('wh=',''));
		                } else {
		                    var fHeight = '495';
		                }
		                if(window.console&&window.console.log) { 
		                    console.log('fWidth #'+index+': '+fWidth); 
		                    console.log('fHeight #'+index+': '+fHeight); 
		                }
		                $(this).fancybox({
		                	'padding'		: 0,
		                  'autoScale' : false,
		                  'title'			: this.title,
		                  'scrolling'	: "no",
		                  'href'			: this.href.replace(new RegExp("([0-9])","i"),'moogaloop.swf?autoplay=1&clip_id=$1'),				                    																	'width'     : fWidth,
		                  'height'    : fHeight,
		                  'type'			: 'swf'
			              });
		            });
		        }
		    });
			
	    $(function(){
	        // Fancybox: IFRAME
	        var fancybox_iframe = $('.slideshow-popup');
	        if (fancybox_iframe.length > 0){
	            fancybox_iframe.each(function(index){
	                // Inline frame width param
	                if( $(this).attr('href').match(/ww=[0-9]+/i) ){
	                    var fWidth = parseInt($(this).attr('href').match(/ww=[0-9]+/i)[0].replace('ww=',''));
	                } else {
	                    var fWidth = '625';
	                }
	                // Inline frame height param
	                if( $(this).attr('href').match(/wh=[0-9]+/i) ){
	                    var fHeight = parseInt($(this).attr('href').match(/wh=[0-9]+/i)[0].replace('wh=',''));
	                } else {
	                    var fHeight = '625';
	                }
	                if(window.console&&window.console.log) { 
	                    console.log('fWidth #'+index+': '+fWidth); 
	                    console.log('fHeight #'+index+': '+fHeight); 
	                }
	                $(this).fancybox({
	                    'type'              : 'iframe',
	                    'autoScale'         : false,
	                    'scrolling'					: "no",
	                    'width'             : fWidth,
	                    'height'            : fHeight

	                });
	            });
	        }
	    });
		
		
	}); 	
})(jQuery);
