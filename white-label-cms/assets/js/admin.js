function wlcms_iframe_height(el){
    el.height =  el.contentWindow.document.body.scrollHeight;
    
    var iframeDocument = el.contentDocument || el.contentWindow.document;

    var anchorLinks = iframeDocument.querySelectorAll('a');

    anchorLinks.forEach(function(link) {
        if (!link.hasAttribute('target')) {
        link.setAttribute('target', '_top');
        }
    });
}