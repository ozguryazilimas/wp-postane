		<script type='text/javascript'>
		var $ = jQuery; //if you use google CDN version of jQuery comment this line.
			$(document).ready(function(){		
				
				$('.fep_cf_del').live('click',function(){
					$(this).parent().parent().remove();
				});
				
				$('.fep_cf_add').live('click',function(){
					var appendTxt = "<tr><td><input type='text' pattern='.{3,}' required name='dp_name[]' /></td> <td><input type='text' pattern='.{3,}' required name='dp_username[]' /></td> <td><input type='button' class='fep_cf_del' value='Delete' /></td></tr>";
					$('#options-table tr:last').after(appendTxt);			
				});        
			});
		</script>