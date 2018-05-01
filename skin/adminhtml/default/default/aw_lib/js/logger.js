Event.observe(window, 'load', function(){
    awLoggerResetAll();
});



function openGridRow(grid, event)
{
    var tr = Event.findElement(event, 'tr');
    var element = tr.select(".aw-lib-cell")[0];
    if (!element) {
        element = tr.select(".aw-lib-full-cell")[0];
    }
    var elementInfo = tr.select(".aw-lib-info-cell")[0];
    if (element.hasClassName('aw-lib-cell')) {
        awLoggerResetAll();
        element.removeClassName('aw-lib-cell');
        element.addClassName('aw-lib-full-cell');
        elementInfo.removeClassName('aw-lib-info-cell');
        elementInfo.addClassName('aw-lib-info-full-cell');
    }
    else {
        awLoggerResetAll();
    }
}

function awLoggerResetAll()
{
    $$('.aw-lib-full-cell').each(function(el){
        el.removeClassName('aw-lib-full-cell');
        el.addClassName('aw-lib-cell');
    });
    $$('.aw-lib-info-full-cell').each(function(el){
        el.removeClassName('aw-lib-info-full-cell');
        el.addClassName('aw-lib-info-cell');
    });
}