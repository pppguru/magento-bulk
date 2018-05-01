function openGridRow(grid, event) {
    var element = Event.findElement(event, 'tr');

    if (['a', 'input', 'select', 'option'].indexOf(Event.element(event).tagName.toLowerCase()) != -1)
        return;

    if (element.title) {
        var win = window.open(element.title, 'followuppreview', 'width=600,height=400,resizable=1,scrollbars=1');
        win.focus();
        Event.stop(event);
    }
}
