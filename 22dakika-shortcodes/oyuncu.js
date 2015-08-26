jQuery(document).ready(function($) {
    tinymce.create('tinymce.plugins.oyuncu_shortcode_plugin', {
        init : function(ed, url) {
                ed.addCommand('oyuncu_insert_shortcode', function() {
                    selected = tinyMCE.activeEditor.selection.getContent();

                    if (selected) {
                        content = '[oyuncu]' + selected + '[/oyuncu]';
                    } else {
                        content = '[oyuncu][/oyuncu]';
                    }
                    tinymce.execCommand('mceInsertContent', false, content);
                });

            ed.addButton('oyuncu_button', {title : 'Oyuncu ekle', cmd : 'oyuncu_insert_shortcode', image: url + '/oyuncu.png'});
        },   
    });

    tinymce.PluginManager.add('oyuncu_button', tinymce.plugins.oyuncu_shortcode_plugin);
});
