<?php
/*
Plugin Name: Comment Quicktags +
Plugin URI: http://dancameron.org/wordpress/wordpress-plugins/comment-quicktags-10/
Description: Inserts a quicktag toolbar on the blog comment form. js_quicktags is a slightly modified version of Alex King's newer <a href="http://www.alexking.org/blog/2005/07/01/javascript-quicktags-12/">Quicktag.js</a> plugin modified from original found <a href=" http://www.asymptomatic.net/wp-hacks">here</a>.
Version: 1.1
Author: Dan Cameron
Author URI: http://www.dancameron.org
*/ 
/*
Comment Quicktags - Inserts a quicktag toolbar on the blog comment form.

This code is licensed under the MIT License.
http://www.opensource.org/licenses/mit-license.php
Copyright (c) 2005 Owen Winkler

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the
Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software,
and to permit persons to whom the Software is furnished to
do so, subject to the following conditions:

The above copyright notice and this permission notice shall
be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

/*
Comment Quicktags - Inserts a quicktag toolbar on the blog comment form.

*** Directions For Use ***
Copy the CommentQT folder into your wp-content/plugins directory.
Activate this plugin in the WordPress Admin Panel.


*** Styling the Toolbar ***
The toolbar CSS id is "#ed_toolbar", so you could add this 
to your stylesheet, I use:

#ed_toolbar input
{
	background: #14181B;
	color: white;
	border:2px dashed #323136; 
	padding: 0px;
	width: 65px;
}
#ed_toolbar input:hover 
{
	background: #323136;
	color: white;
	border:2px dashed #14181B; 
	padding: 0px;
	width: 65px;
}
}


*/

if(defined('ABSPATH')) :

	function comment_quicktags($unused) {
		$scripturl = get_settings('siteurl') . '/wp-content/plugins/js_quicktags.js';
		$thisurl = get_settings('siteurl') . '/wp-content/plugins/' . basename(__FILE__);
		echo '<script src="' . $scripturl . '" type="text/javascript"></script>' . "\n";
		echo '<script src="' . $thisurl . '" type="text/javascript"></script>' . "\n";
		ob_start('comment_quicktags_ob');
	}
	
	function comment_quicktags_ob($content) {
		$toolbar = '<script type="text/javascript">edToolbar();</script>';
		$activate = '<script type="text/javascript">var edCanvas = document.getElementById(\'\\2\');</script>';
		$content = preg_replace('/<textarea(.*?)id="([^"]*)"(.*?)>(.*?)<\/textarea>/', $toolbar . '<textarea\\1id="\\2"\\3>\\4</textarea>'.$activate, $content);
		return $content;
	}
	
	add_action('wp_head', 'comment_quicktags');

else :

?>


var edButtons = new Array();

var extendedStart = edButtons.length;

// below here are the extended buttons
edButtons[edButtons.length] = 
new edButton('ed_block'
,'B-Quote'
,'<blockquote>'
,'</blockquote>'
,'q'
);

edButtons[edButtons.length] = 
new edButton('ed_link'
,'Link'
,''
,'</a>'
,'a'
); // special case



edButtons.push(
	new edButton(
		'ed_ext_link'
		,'Ext. Link'
		,''
		,'</a>'
		,'e'
	)
); // special case


edButtons[edButtons.length] = 
new edButton('ed_strong'
,'Strong'
,'<strong>'
,'</strong>'
,'b'
);

edButtons[edButtons.length] = 
new edButton('ed_em'
,'em'
,'<em>'
,'</em>'
,'i'
);

edButtons[edButtons.length] = 
new edButton('ed_pre'
,'Code'
,'<code>'
,'</code>'
,'c'
);

edButtons[edButtons.length] = 
new edButton('ed_strike'
,'Strike'
,'<strike>'
,'</strike>'
,'s'
);





edButtons.push(
	new edButton(
		'ed_ol'
		,'OL'
		,'<ol>\n'
		,'</ol>\n\n'
		,'o'
	)
);

edButtons.push(
	new edButton(
		'ed_li'
		,'LI'
		,'\t<li>'
		,'</li>\n'
		,'l'
	)
);

<?

endif;
?>