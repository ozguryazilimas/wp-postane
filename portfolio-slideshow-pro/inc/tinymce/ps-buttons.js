(function() {
	tinymce.create('tinymce.plugins.buttonPlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mcebutton', function() {
				ed.windowManager.open({
					file : url + '/button_popup.php', // file that contains HTML for our modal window
					width : 280 + parseInt(ed.getLang('button.delta_width', 0)), // size of our window
					height : 380 + parseInt(ed.getLang('button.delta_height', 0)), // size of our window
					inline : 1
				}, {
					plugin_url : url
				});
			});
			 
			// Register buttons
			ed.addButton('psbutton', {title : 'Insert Slideshow', cmd : 'mcebutton', image: url + '/includes/images/icon.gif' });
		},
		 
		getInfo : function() {
			return {
				longname : 'Insert Slideshow',
				author : 'Raygun',
				authorurl : 'http://madebyraygun.com',
				infourl : 'http://madebyraygun.com',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});
	 
	// Register plugin
	// first parameter is the button ID and must match ID elsewhere
	// second parameter must match the first parameter of the tinymce.create() function above
	tinymce.PluginManager.add('psbutton', tinymce.plugins.buttonPlugin);

})();