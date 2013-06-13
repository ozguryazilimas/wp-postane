jQuery.fn.extend({
  highlight: function(term, insensitive, span_class){
    var regex = new RegExp('(<[^>]*>)|('+ term.replace(/([-.*+?^${}()|[\]\/\\])/g,"\\$1") +')', insensitive ? 'ig' : 'g');
    return this.html(this.html().replace(regex, function(a, b, c){
      if (jQuery.support.opacity) {
        return (a.charAt(0) == '<') ? a : '<mark class="'+ span_class +'">' + c + '</mark>';
      } else {
        return (a.charAt(0) == '<') ? a : '<span class="'+ span_class +'">' + c + '</span>';
      }
    }));
  }
});
jQuery(document).ready((function($){

  function get_hlst_query() {
    var ref = document.referrer.split('?');
/*
console.log('referer query parameters: ' + ref[1]);
*/
    if (typeof(ref[1]) != 'undefined'){
      var term;
      if (document.referrer.indexOf(document.domain) < 9) {
        term = 's';
      } else if (document.referrer.indexOf('yahoo.com') > -1) {
        term = 'p';
      } else if (document.referrer.indexOf('goodsearch.com') > -1) {
        term = 'keywords';
      } else if (document.referrer.indexOf('mywebsearch.com') > -1) {
        term = 'searchfor';
      } else if (document.referrer.indexOf('baidu.') > -1) {
        term = 'wd';
      } else {
        term = 'q';
      }
/*
console.log('searchengine term: ' + term);
*/
      var parms = ref[1].split('&');
/*
console.log('parms split into ' + parms.length);
*/
      for (var i=0; i < parms.length; i++) {
/*
console.log('parameter ' + i + ': ' + parms[i]);
*/
        var pos = parms[i].indexOf('=');
        if (pos > 0) {
            if(term == parms[i].substring(0,pos)) {
              qstr = decodeURIComponent((parms[i].substring(pos+1)+'').replace(/\+/g, '%20'));
/*
console.log('search query found: ' + qstr);
*/
              qarr = qstr.match(/([^\s"]+)|"([^"]*)"/g);
              for (var j=0; j < qarr.length; j++){
/*
console.log('added ' + qarr[j] + ' to search array');
*/
                hlst_query[j] = qarr[j].replace(/"/g,'');
              }
              break;
            }
        }
      }
    }
  }

  if (typeof(hlst_query) != 'undefined') {
    if (hlst_query.length == 0) {
/*
console.log('going into get_hlst_query()');
*/
    	get_hlst_query();
    }
    var area; var i; var s;
    for (s in hlst_areas){
      area = $(hlst_areas[s]);
      if (area.length != 0){
        for (var l = 0; l < area.length; l++) {
		for (i in hlst_query){
/*
console.log('keyword: ' + hlst_query[i]);
*/
		  area.eq(l).highlight(hlst_query[i], 1, 'hilite term-' + i);
		}
	}
      	break;
      }
    }
  }
  
  if ('function'==typeof Cufon) Cufon.refresh();
}));
