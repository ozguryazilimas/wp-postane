var ExactMetrics_Popular_Posts = {

	init: function () {
		this.grab_widgets_with_ajax();
	},

	grab_widgets_with_ajax: function () {
		var xhr = new XMLHttpRequest();
		var url = exactmetrics_pp.ajaxurl;
		var widgets_jsons = document.querySelectorAll( '.exactmetrics-popular-posts-widget-json' ),
			i,
			widgets_length = widgets_jsons.length;

		var params = 'action=exactmetrics_popular_posts_get_widget_output&post_id=' + exactmetrics_pp.post_id;

		for ( i = 0; i < widgets_length; ++ i ) {
			params += '&data[]=' + widgets_jsons[i].innerHTML
		}
		xhr.open( 'POST', url );
		xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
		xhr.onload = function () {
			if ( xhr.status === 200 ) {
				let rendered_widgets = JSON.parse( xhr.responseText );
				for ( i = 0; i < widgets_length; ++ i ) {
					widgets_jsons[i].parentElement.innerHTML = rendered_widgets[i];
				}
			}
		};
		xhr.send( params );
	},
};

ExactMetrics_Popular_Posts.init();
