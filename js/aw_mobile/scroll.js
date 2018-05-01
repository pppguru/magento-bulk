/*remove scroll*/
Event.observe(window, 'load', function() {
    setTimeout(function(){
        window.scrollTo(0,0);
    },100);

});
Event.observe(window, 'popstate', function() {
    window.scrollTo(0,0);
});
