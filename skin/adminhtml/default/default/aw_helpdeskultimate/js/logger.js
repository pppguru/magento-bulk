Event.observe(window, 'load', function(){
    awHduLoggerResetAll();
    rewriteNativeGridObject();
});

function rewriteNativeGridObject()
{
     awcoreLogGridJsObject.rowClickCallback = awHduLoggerRowClick;
}

function awHduLoggerRowClick(grid, event)
{
    var tr = Event.findElement(event, 'tr');
    var element = tr.select(".aw-hdu-min-cell")[0];
    if (!element) {
        element = tr.select(".aw-hdu-optimal-cell")[0];
    }

    if (element.hasClassName('aw-hdu-min-cell')) {
        awHduLoggerResetAll();
        element.removeClassName('aw-hdu-min-cell');
        element.addClassName('aw-hdu-optimal-cell');
    }
    else {
        awHduLoggerResetAll();
    }
}

function awHduLoggerResetAll()
{
     $$('.aw-hdu-optimal-cell').each(function(el){
        el.removeClassName('aw-hdu-optimal-cell');
        el.addClassName('aw-hdu-min-cell');
    });
}
