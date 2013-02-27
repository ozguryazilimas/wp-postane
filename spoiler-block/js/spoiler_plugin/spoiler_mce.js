(function() {
    tinymce.create('tinymce.plugins.spoiler', {
 
        init : function(ed, url){
            ed.addButton('spoiler', {
            title : 'Insert Spoiler',
			image: url + "/images/spoiler.png",
                onclick : function() {
                    ed.focus();
					var s = ed.selection.getContent();
					var rx = /(<span class=\"spoiler\">)(.*)(<\/span>)/;
					if( s.match(rx) != null ){
						var ns = s.replace(s.match(rx)[1],"").replace(s.match(rx)[3],"");
						ed.selection.setContent(ns);
					}else{
						ed.selection.setContent('<span class="spoiler"><span class="hidden-content">' + ed.selection.getContent() + '</span></span>');
					}
                },
            });
        }
    });
 
    tinymce.PluginManager.add('spoiler', tinymce.plugins.spoiler);
 
})();