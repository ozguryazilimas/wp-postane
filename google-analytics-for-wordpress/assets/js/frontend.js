/** 
 * Developer's Notice:
 * 
 * Note: JS in this file (and this file itself) is not garunteed backwards compatibility. JS can be added, changed or removed at any time without notice.
 * For more information see the `Backwards Compatibility Guidelines for Developers` section of the README.md file.
 */
/**
 * Handles:
 * - JS Events handling
 *
 * @since 6.0.12
 */
var MonsterInsights = function(){
    // MonsterInsights JS  events tracking works on all major browsers, including IE starting at IE 7, via polyfills for any major JS fucntion used that
    // is not supported by at least  95% of the global and/or US browser marketshare. Currently, IE 7 & 8 which as of 2/14/17 have under 0.25% global marketshare, require
    // us to polyfill Array.prototype.lastIndexOf, and if they continue to drop, we might remove this polyfill at some point. In that case note that events tracking
    // for IE 7/8 will continue to work, with the exception of events tracking of downloads.
    var lastClicked = '';
    this.setLastClicked = function(a){ 
        lastClicked = a;
    }
    this.getLastClicked = function () {
        return lastClicked;
    }

    function __gaTrackerClickEventPHP() {
        var debug_mode = false;
        if ( monsterinsights_frontend.is_debug_mode === "true" ) {
            debug_mode = true;
        }

        var inbound_paths = [];
        var download_extensions = [];

        if ( typeof monsterinsights_frontend.download_extensions == 'string' ) {
            download_extensions = monsterinsights_frontend.download_extensions.split(",");
        }
        if ( typeof monsterinsights_frontend.inbound_paths == 'string' ) {
            inbound_paths = monsterinsights_frontend.inbound_paths.split(",");
        }
        
        var phpvalues = { 
            'is_debug_mode'       : debug_mode,
            'download_extensions' : download_extensions, /* Let's get the extensions to track */
            'inbound_paths'       : inbound_paths, /* Let's get the internal paths to track */
            'home_url'            : monsterinsights_frontend.home_url, /* Let's get the url to compare for external/internal use */
            'track_download_as'   : monsterinsights_frontend.track_download_as, /* should downloads be tracked as events or pageviews */
            'internal_label'      : "outbound-link-" + monsterinsights_frontend.internal_label, /* What is the prefix for internal-as-external links */
        };
        return phpvalues;
    }

    function __gaTrackerLog ( message ) {
        var phpvalues     = __gaTrackerClickEventPHP();
        var is_debug_mode =  phpvalues.is_debug_mode || window.monsterinsights_debug_mode; /* Console log instead of running? */
        if ( is_debug_mode ) {
            console.log( message );
        }
    }

    function __gaTrackerClickEvent( event ) {
        var phpvalues     = __gaTrackerClickEventPHP();
        var el            = event.srcElement || event.target;
        __gaTrackerLog( "__gaTracker.hasOwnProperty(loaded)" );
        __gaTrackerLog( __gaTracker.hasOwnProperty( "loaded" ) );
        __gaTrackerLog( "__gaTracker.loaded" );
        __gaTrackerLog( __gaTracker.loaded );
        __gaTrackerLog( "Event.which: " + event.which );
        __gaTrackerLog( "El: ");
        __gaTrackerLog( el );
        __gaTrackerLog( "GA Loaded and click: " + ! ( ! __gaTracker.hasOwnProperty( "loaded" ) || __gaTracker.loaded != true || ( event.which != 1 && event.which != 2 && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey  ) ) ) ;

        /* If GA is blocked or not loaded, or not main|middle|touch click then don't track */
        if ( ! __gaTracker.hasOwnProperty( "loaded" ) || __gaTracker.loaded != true || ( event.which != 1 && event.which != 2 && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey  ) ) {
             __gaTrackerLog( "Will track: false");
             lastClicked = 'ganlonte';
            return;
        }

        /* Loop up the DOM tree through parent elements if clicked element is not a link (eg: an image inside a link) */
        while ( el && (typeof el.tagName == 'undefined' || el.tagName.toLowerCase() != 'a' || !el.href ) ) {
            el = el.parentNode;
        }

        /* if a link with valid href has been clicked */
        if ( el && el.href ) {
            /* el is an a element so we can parse it */
            /* el.href;      => "http://example.com:3000/pathname/?search=test#hash" */
            /* el.protocol;  => "http:" */
            /* el.hostname;  => "example.com" */
            /* el.port;      => "3000" */
            /* el.pathname;  => "/pathname/" */
            /* el.search;    => "?search=test" */
            /* el.hash;      => "#hash"
            /* el.host;      => "example.com:3000 */

            var link                = el.href;
            var extension           = el.href;
            var type                = 'internal'; /* By default, we assume all links are internal ones, which we don't track by default */
            var download_extensions = phpvalues.download_extensions; /* Let's get the extensions to track */
            var inbound_paths       = phpvalues.inbound_paths; /* Let's get the internal paths to track */
            var home_url            = phpvalues.home_url; /* Let's get the url to compare for external/internal use */
            var track_download_as   = phpvalues.track_download_as; /* should downloads be tracked as events or pageviews */
            var internal_label      = phpvalues.internal_label; /* What is the prefix for internal-as-external links */

            __gaTrackerLog("Will track: true");

            /* Remove the anchor at the end, if there is one */
            extension = extension.substring( 0, (extension.indexOf( "#" ) == -1 ) ? extension.length : extension.indexOf( "#" ) );

            /* Remove the query after the file name, if there is one */
            extension = extension.substring( 0, (extension.indexOf( "?" ) == -1 ) ? extension.length : extension.indexOf( "?" ) );

            /* Remove everything before the last slash in the path */
            extension = extension.substring( extension.lastIndexOf( "/" ) + 1, extension.length );

            /* Remove everything but what's after the first period */
            extension = extension.substring( extension.indexOf( "." ) + 1 );

            var currentdomain = (function(){
               var i=0,currentdomain=document.domain,p=currentdomain.split('.'),s='_gd'+(new Date()).getTime();
               while(i<(p.length-1) && document.cookie.indexOf(s+'='+s)==-1){
                  currentdomain = p.slice(-1-(++i)).join('.');
                  document.cookie = s+"="+s+";domain="+currentdomain+";";
               }
               document.cookie = s+"=;expires=Thu, 01 Jan 1970 00:00:01 GMT;domain="+currentdomain+";";
               return currentdomain;
            })();

            if (typeof String.prototype.endsWith !== 'function') {
                String.prototype.endsWith = function(suffix) {
                    return this.indexOf(suffix, this.length - suffix.length) !== -1;
                };
            }
            if (typeof String.prototype.startsWith !== 'function') {
                String.prototype.startsWith = function(prefix) {
                    return this.indexOf(prefix) === 0;
                };
            }

            if ( typeof Array.prototype.includes !== 'function') {
              Object.defineProperty(Array.prototype, 'includes', {
                value: function(searchElement, fromIndex) {

                  // 1. Let O be ? ToObject(this value).
                  if (this == null) {
                    throw new TypeError('"this" is null or not defined');
                  }

                  var o = Object(this);

                  // 2. Let len be ? ToLength(? Get(O, "length")).
                  var len = o.length >>> 0;

                  // 3. If len is 0, return false.
                  if (len === 0) {
                    return false;
                  }

                  // 4. Let n be ? ToInteger(fromIndex).
                  //    (If fromIndex is undefined, this step produces the value 0.)
                  var n = fromIndex | 0;

                  // 5. If n ≥ 0, then
                  //  a. Let k be n.
                  // 6. Else n < 0,
                  //  a. Let k be len + n.
                  //  b. If k < 0, let k be 0.
                  var k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

                  // 7. Repeat, while k < len
                  while (k < len) {
                    // a. Let elementK be the result of ? Get(O, ! ToString(k)).
                    // b. If SameValueZero(searchElement, elementK) is true, return true.
                    // c. Increase k by 1.
                    // NOTE: === provides the correct "SameValueZero" comparison needed here.
                    if (o[k] === searchElement) {
                      return true;
                    }
                    k++;
                  }

                  // 8. Return false
                  return false;
                }
              });
            }

            if ( typeof Array.prototype.lastIndexOf !== 'function' ) {
              Array.prototype.lastIndexOf = function(searchElement /*, fromIndex*/) {
                'use strict';

                if (this === void 0 || this === null) {
                  throw new TypeError();
                }

                var n, k,
                  t = Object(this),
                  len = t.length >>> 0;
                if (len === 0) {
                  return -1;
                }

                n = len - 1;
                if (arguments.length > 1) {
                  n = Number(arguments[1]);
                  if (n != n) {
                    n = 0;
                  }
                  else if (n != 0 && n != (1 / 0) && n != -(1 / 0)) {
                    n = (n > 0 || -1) * Math.floor(Math.abs(n));
                  }
                }

                for (k = n >= 0 ? Math.min(n, len - 1) : len - Math.abs(n); k >= 0; k--) {
                  if (k in t && t[k] === searchElement) {
                    return k;
                  }
                }
                return -1;
              };
            }

            function monsterinsightsStringTrim(x) {
                return x.replace(/^\s+|\s+$/gm,'');
            }

            __gaTrackerLog( "Link: " + link);
            __gaTrackerLog( "Extension: " + extension );
            __gaTrackerLog( "Protocol: " + el.protocol );
            __gaTrackerLog( "External: " + (el.hostname.length > 0 && currentdomain.length > 0 && ! el.hostname.endsWith( currentdomain )) );
            __gaTrackerLog( "Current domain: " + currentdomain );
            __gaTrackerLog( "Link domain: " + el.hostname );
            __gaTrackerLog( "Outbound Paths: " + inbound_paths );
            

            /* Let's get the type of click event this is */
            if ( monsterinsightsStringTrim( el.protocol ) == 'mailto' ||  monsterinsightsStringTrim( el.protocol ) == 'mailto:' ) { /* If it's an email */
                type = "mailto"; 
            } else if ( download_extensions.length > 0 && extension.length > 0 && download_extensions.includes(extension) ) { /* If it's a download */
                type = "download"; 
            } else if ( el.hostname.length > 0 && currentdomain.length > 0 && ! el.hostname.endsWith( currentdomain ) ) { /* If it's a outbound */
                type = "external"; 
            } else {
                var index, len;
                var pathname = el.pathname;
                for ( index = 0, len = inbound_paths.length; index < len; ++index ) {
                    if ( inbound_paths[ index ].length > 0 && pathname.startsWith( inbound_paths[ index ] ) ) {
                        type = "internal-as-outbound";
                        break;
                    }
                }
            }

            lastClicked = type;

            __gaTrackerLog( "Type: " + type );

            /* Let's track everything but internals (that aren't internal-as-externals) */
            if ( type !== 'internal' && ! link.match( /^javascript\:/i ) ) {

                /* Is actual target set and not _(self|parent|top)? */
                var target = ( el.target && !el.target.match( /^_(self|parent|top)$/i ) ) ? el.target : false;

                /* Assume a target if Ctrl|shift|meta-click */
                if ( event.ctrlKey || event.shiftKey || event.metaKey || event.which == 2 ) {
                    target = "_blank";
                }

                __gaTrackerLog( "Control Key: " + event.ctrlKey );
                __gaTrackerLog( "Shift Key: " + event.shiftKey );
                __gaTrackerLog( "Meta Key: " + event.metaKey );
                __gaTrackerLog( "Which Key: " + event.which );
                __gaTrackerLog( "Target: " + target );
            

                var __gaTrackerHitBackRun = false; /* Tracker has not yet run */

                /* HitCallback to open link in same window after tracker */
                var __gaTrackerHitBack = function() {
                    /* Run the hitback only once */
                    if ( __gaTrackerHitBackRun ){
                        return;
                    }
                    __gaTrackerHitBackRun = true;
                    window.location.href = link;
                };
                
                if ( target ) { /* If target opens a new window then just track */
                    if ( type == 'download' ) {
                        if ( track_download_as == 'pageview' ) {
                            __gaTracker( 'send', 'pageview', link );
                            __gaTrackerLog( "Target | Download | Send | Pageview | " + link );
                        } else {
                            __gaTracker( 'send', 'event', 'download', link );
                            __gaTrackerLog( "Target | Download | Send | Event | " + link );
                        }
                    } else if ( type == 'mailto' ) {
                        __gaTracker( 'send', 'event', 'mailto', link );
                        __gaTrackerLog( "Target | Mailto | Send | Event | Mailto | " + link );
                    } else if ( type == 'internal-as-outbound' ) {
                        __gaTracker( 'send', 'event', internal_label, link, el.title );
                        __gaTrackerLog( "Target | Internal-As-Outbound | Send | event | " + internal_label + " | " + link + " | " + el.title );
                    } else if ( type == 'external' ) {
                        __gaTracker( 'send', 'event', 'outbound-link', link, el.title );
                        __gaTrackerLog( "Target | External | Send | 'outbound-link' | " + link + " | " + el.title );
                    } else {
                        __gaTrackerLog( "Target | " + type + " | " + link + " is not a tracked click." );
                    }
                } else { 
                    /* Prevent standard click, track then open */
                    if ( type != 'external' && type != 'internal-as-outbound' ) {
                        if (!event.defaultPrevented) {
                            event.preventDefault ? event.preventDefault() : event.returnValue = !1;
                        }
                    }
                    
                    if ( type == 'download' ) {
                        if ( track_download_as == 'pageview' ) {
                            __gaTracker( 'send', 'pageview', link, { "hitCallback": __gaTrackerHitBack } );
                            __gaTrackerLog( "Not Target | Download | Send | Pageview | " + link );
                        } else {
                            __gaTracker( 'send', 'event', 'download',{ "hitCallback": __gaTrackerHitBack } );
                            __gaTrackerLog( "Not Target | Download | Send | Event | " + link );
                        }
                    } else if ( type == 'mailto' ) {
                        __gaTracker( 'send', 'event', 'mailto', link, { "hitCallback": __gaTrackerHitBack } );
                        __gaTrackerLog( "Not Target | Mailto | Send | Event | Mailto | " + link );
                    } else if ( type == 'internal-as-outbound' ) {
                        window.onbeforeunload = function(e) {
                            if (!event.defaultPrevented) {
                                event.preventDefault ? event.preventDefault() : event.returnValue = !1;
                            }
                            if ( ! navigator.sendBeacon ) {
                                __gaTracker( 'send', 'event', internal_label, link, el.title, { "hitCallback": __gaTrackerHitBack } );
                            } else {
                                __gaTracker( 'send', 'event', internal_label, link, el.title, { transport: 'beacon',"hitCallback": __gaTrackerHitBack } );
                            }

                            setTimeout( __gaTrackerHitBack, 1000 );

                            __gaTrackerLog( "Not Target | Internal-As-Outbound | Send | event | " + internal_label + " | " + link + " | " + el.title );
                            __gaTrackerLog( "Internal as Outbound: Redirecting" );
                        };
                        lastClicked = type;
                        __gaTrackerLog( "Internal as Outbound: Not redirecting" );
                    } else if ( type == 'external' ) {
                        window.onbeforeunload = function(e) {
                            if ( ! navigator.sendBeacon ) {
                                __gaTracker( 'send', 'event', 'outbound-link', link, el.title, { "hitCallback": __gaTrackerHitBack } )
                            } else {
                                __gaTracker( 'send', 'event', 'outbound-link', link, el.title, { transport: 'beacon',"hitCallback": __gaTrackerHitBack } )
                            }

                            setTimeout( __gaTrackerHitBack, 1000 );

                            __gaTrackerLog( "Not Target | External | Send | 'outbound-link' | " + link + " | " + el.title );
                            __gaTrackerLog( "External: Redirecting" );
                        };
                        lastClicked = type;
                        __gaTrackerLog( "External: Not redirecting" );
                    } else {
                        __gaTrackerLog(  "Not Target | " + type + " | " + link + " is not a tracked click." );
                    }

                    if ( type != 'external' && type != 'internal-as-outbound' ) {
                        /* Run hitCallback again if GA takes longer than 1 second */
                        setTimeout( __gaTrackerHitBack, 1000 );
                    }
                }
            }
        } else {
            lastClicked = 'internal';
            __gaTrackerLog( "Will track: false");
        }
    }

    var __gaTrackerWindow    = window;
    var __gaTrackerEventType = "click";
    /* Attach the event to all clicks in the document after page has loaded */
    __gaTrackerWindow.addEventListener ? __gaTrackerWindow.addEventListener( "load", function() {document.body.addEventListener(__gaTrackerEventType, __gaTrackerClickEvent, false)}, false)
                                       : __gaTrackerWindow.attachEvent && __gaTrackerWindow.attachEvent("onload", function() {document.body.attachEvent( "on" + __gaTrackerEventType, __gaTrackerClickEvent)});
};
var MonsterInsightsObject = new MonsterInsights();