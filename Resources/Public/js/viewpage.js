(function( p, undefined ) {

    p.addUrlParameter = function(url, parameterName, parameterValue, atStart){
        replaceDuplicates = false;
        if(url.indexOf('#') > 0) {
            var cl = url.indexOf('#');
            urlhash = url.substring(url.indexOf('#'),url.length);
        } else {
            urlhash = '';
            cl = url.length;
        }
        sourceUrl = url.substring(0,cl);

        var urlParts = sourceUrl.split("?");
        var newQueryString = "";

        if (urlParts.length > 1) {
            var parameters = urlParts[1].split("&");
            for (var i=0; (i < parameters.length); i++) {
                var parameterParts = parameters[i].split("=");
                if (!(replaceDuplicates && parameterParts[0] == parameterName)) {
                    if (newQueryString == "")
                        newQueryString = "?";
                    else
                        newQueryString += "&";
                    newQueryString += parameterParts[0] + "=" + (parameterParts[1]?parameterParts[1]:'');
                }
            }
        }
        if (newQueryString == "")
            newQueryString = "?";

        if(atStart){
            newQueryString = '?'+ parameterName + "=" + parameterValue + (newQueryString.length>1?'&'+newQueryString.substring(1):'');
        } else {
            if (newQueryString !== "" && newQueryString != '?')
                newQueryString += "&";
            newQueryString += parameterName + "=" + (parameterValue?parameterValue:'');
        }
        return urlParts[0] + newQueryString + urlhash;
    };

    (function( v, undefined ) {

        v.viewFrameId = 'tx_viewpage_iframe';

        v.resizeViewFrame = function( type, elementId ) {
            elementId = elementId || v.viewFrameId;
            var style = '',
                iframe = window.document.getElementById(elementId);
            switch (type) {
                case 'mobile':
                    style += v.styleMobile || 'min-width:335px;max-width:335px;';
                    break;
                case 'tablet':
                    style += v.styleTablet || 'min-width:783px;max-width:899px;';
                    break;
                case 'laptop':
                    style += v.styleLaptop || 'min-width:995px;max-width:1199px;';
                    break;
                case 'desktop':
                    style += v.styleDesktop || 'min-width:1215px;';
                    break;
            }
            iframe.setAttribute('style', style);
        };

        v.onLoadViewFrame = function( e ) {
            var iframe = e.target || e.srcElement,
                linksLength = iframe.contentDocument.links.length,
                links = iframe.contentDocument.links;
            for (var i = 0; i < linksLength; i++) {
                if (links[i].href.slice(-1) == '/') {
                    links[i].href += '?aloha_topBar_disable=1';
                }
            };
        };

    }( p.viewpage = p.viewpage || {} ));

    (function( p, a, undefined ) {

        a.viewFrameId = 'tx_pxafoundation_viewpage_iframe';

        a.onLoadViewFrame = function( e ) {
            var iframe = e.target || e.srcElement,
                pageId = iframe.contentWindow.alohaUrl.replace(/.*id=(\d+).*/, "$1"),
                $ = window.Aloha.jQuery,
                pageButtons = $('.page-edit-buttons a');
            pageButtons.each(function( i, el){
                el.href = el.href.replace(/([?&])edit\[pages\]\[\d*/, "$1edit[pages][" + pageId).replace(/([?&])uid=\d*/, "$1uid=" + pageId).replace(/([?&])id=\d*/, "$1id=" + pageId);
                el.setAttribute('onclick', el.getAttribute('onclick').replace(/([?&])edit\[pages\]\[\d*/, "$1edit[pages][" + pageId).replace(/([?&])uid=\d*/, "$1uid=" + pageId).replace(/([?&])id=\d*/, "$1id=" + pageId));
            });
            p.viewpage.onLoadViewFrame(e);
        };

        a.resizeViewFrame = function( type ) {
            var iframe = window.document.getElementById(a.viewFrameId),
                $ = window.Aloha.jQuery;
            if (iframe) {
                p.viewpage.resizeViewFrame(type, a.viewFrameId);
            } else {
                $('body').children().not('#aloha-top-bar, #sb-container').remove();
                $('body').prepend('<div style="height:' + (window.innerHeight - (parseInt($('body').css('marginTop'))) || 0) + 'px;"><iframe height="100%" id="' + a.viewFrameId + '" src="' + p.addUrlParameter(window.location.href, 'aloha_topBar_disable', '1') + '" onload="pxa.aloha.onLoadViewFrame(event); return false;"></iframe></div>');
                p.viewpage.resizeViewFrame(type, a.viewFrameId);
            }
        };

    }( p, p.aloha = p.aloha || {} ));

}( window.pxa = window.pxa || {} ));