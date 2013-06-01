var jmil = jQuery.noConflict();
jmil.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jmil.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};

if((jmil.cookie('milat') == 0 )|| (jmil.cookie('milat') == null) )
	{
		jmil(document).ready(function() {    

				var id = '#pencere';
			
				//Get the screen height and width
				var karartmaHeight = jmil(document).height();
				var karartmaWidth = jmil(window).width();
			
				//Set heigth and width to karartma to fill up the whole screen
				jmil('#karartma').css({'width':karartmaWidth,'height':karartmaHeight});
				
				//transition effect        
				jmil('#karartma').fadeIn(1000);    
				jmil('#karartma').fadeTo("slow",0.8);    
			
				//Get the window height and width
				var winH = jmil(window).height();
				var winW = jmil(window).width();
					  
				//Set the popup window to center
				jmil(id).css('top',  winH/2-jmil(id).height()/2);
				jmil(id).css('left', winW/2-jmil(id).width()/2);
			
				//transition effect
				jmil(id).show(1000);     
			
			//if close button is clicked
			jmil('.window .close').click(function (e) {
				//Cancel the link behavior
				e.preventDefault();				
				jmil('#karartma').fadeOut();
				jmil('.window').slideUp();
				setTimeout( function() {
					jmil('#kutu').remove();
				}, 1000 );
			});        
			
			//if karartma is clicked
			jmil('#karartma').click(function (e) {
				jmil('#karartma').fadeOut();
				jmil('.window').slideUp();
				setTimeout( function() {
					jmil('#kutu').remove();
				}, 1000 );
			});
			/* If you want to delete the line 87 with the closing session if the esc
			jmil(document).bind('keydown', function(e) { 
				if (e.which == 27) {
					jmil('#karartma').fadeOut();
					jmil('.window').slideUp();
					setTimeout( function() {
						jmil('#kutu').remove();
					}, 1000 );
				}
			}); 
			 If you want to delete the line 95 with the closing session if the esc */
		});
	}   