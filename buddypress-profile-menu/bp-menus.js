jQuery(document).ready(function(){
    jQuery("li a").on('click', function(){
    	var linkrel = jQuery(this).attr("rel");
    	
    	jQuery.cookie('bp-activity-oldestpage', '1', { path: '/' } );
    	
    	if (linkrel == 'bp-directory-all-activity'){
        	jQuery.cookie('bp-activity-scope', 'all');
        }
        
        if (linkrel == 'bp-user-directory-my-friends-activity'){
        	jQuery.cookie('bp-activity-scope', 'friends');
        }
        
        if (linkrel == 'bp-user-directory-my-groups-activity'){
        	jQuery.cookie('bp-activity-scope', 'groups');
        }

        if (linkrel == 'bp-user-directory-my-favorites-activity'){
        	jQuery.cookie('bp-activity-scope', 'favorites');
        }
        
        if (linkrel == 'bp-user-directory-my-mentions-activity'){
        	jQuery.cookie('bp-activity-scope', 'mentions');
        }

        if (linkrel == 'bp-directory-all-members'){
        	jQuery.cookie('bp-members-scope', 'all');
        }
        
        if (linkrel == 'bp-user-directory-my-friends-members'){
        	jQuery.cookie('bp-members-scope', 'personal');
        }
        
        if (linkrel == 'bp-user-directory-my-topics-forums'){
        	jQuery.cookie('bp-forums-scope', 'personal');
        }
        
        if (linkrel == 'bp-directory-all-topics-forums'){
        	jQuery.cookie('bp-forums-scope', 'all');
        }

        if (linkrel == 'bp-user-directory-my-groups-group'){
        	jQuery.cookie('bp-groups-scope', 'personal');
        }
        
        if (linkrel == 'bp-directory-all-groups-group'){
        	jQuery.cookie('bp-groups-scope', 'all');
        }
        
        
    });

});