jQuery(document).ready(function($){
	$('#addCommentImage').click(function(){
		var imageLoc = prompt('Enter the Image URL:');
		if ( imageLoc ) {
			$('#comment').val($('#comment').val() + '[img]' + imageLoc + '[/img]');
		}
		return false;
	});
});
