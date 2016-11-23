jQuery(function($) {
    $('#insert-image').click(function(e) {
        e.preventDefault();
        parent.wp.media.editor.open( window.wpActiveEditor );

        var state = parent.wp.media.frame._state;
        if ( state == "iframe:grabAndSave" ) {
            parent.wp.media.frame.setState("insert");
            parent.wp.media.frame.setState("iframe:grabAndSave");
        }
    });
});
