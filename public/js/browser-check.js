jQuery(function($) {
    var userAgent = window.navigator.userAgent;
    var msie = userAgent.indexOf("MSIE ");
    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
        if(window.location.pathname !== "/ie-error"){
            //Redirect to IE-Error Page
            window.location.replace("/ie-error");
        }
    }
});
