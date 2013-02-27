/**
 * CubePM Main JS
 */

jQuery(document).ready(function() {
	jQuery("#cpm_recipient").autocomplete(cubepm.ajax_url, { 
		extraParams: { action: 'cpm_recipient' },
		multiple: true,
		matchSubset: 0,
		highlight: 0,
		max: 5,
		formatItem: function(data) {
			return '<div class="cpm_recipient_suggest_result" style="background-image:url(https://secure.gravatar.com/avatar/'+data[1]+'?s=25);">'+data[0]+'</div>';
		}
	});
	jQuery("#cpm_recipient").blur(function(){
		cpm_recipients = new Array();
		jQuery(this).val().split(',').each(function(value, index){
			if(jQuery.trim(value)!='' && jQuery.inArray(jQuery.trim(value), cpm_recipients)==-1){
				cpm_recipients.push(jQuery.trim(value));
			}
		});
		this.value = cpm_recipients.join(', ');
	});
});