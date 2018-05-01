function openMyPopup(url, title) {


    if ($('browser_window') && typeof(Windows) != 'undefined') {
        Windows.focus('browser_window');
        return;
    }

    var dialogWindow = Dialog.info(null, {
        closable:true,
        resizable:true,
        draggable:true,
        className:'magento',
        windowClassName:'popup-window',
        title: title,
        top:50,
        width:1000,
        height:600,
        zIndex:1000,
        recenterAuto:false,
        hideEffect:Element.hide,
        showEffect:Element.show,
        id:'browser_window',
        url:url
    });
}

function closeMyPopup() {
    Windows.close('browser_window');
}


function fillDatesFromPeriod(dropdown)
{
    var value = dropdown.value;

    if (value != 'custom') {
        document.getElementById('date_selector').style.display = 'none';
        var t = value.split('|');
        document.getElementById('date_from').value = t[0];
        document.getElementById('date_to').value = t[1];
    }
    else
    {
        document.getElementById('date_selector').style.display = 'inline';
    }
}

function submitReportFilters(isFormLess, ajaxUrl, container)
{
    if (!isFormLess)
    {
        document.getElementById('form_smartreport').submit();
    }
    else
    {

        ajaxUrl = ajaxUrl.replace('{period}', document.getElementById('smart_report_period').value);
        ajaxUrl = ajaxUrl.replace('{date_from}', document.getElementById('date_from').value);
        ajaxUrl = ajaxUrl.replace('{date_to}', document.getElementById('date_to').value);
        ajaxUrl = ajaxUrl.replace('{group_by_date}', encodeURIComponent(document.getElementById('smart_report_group_by_date').value));
        ajaxUrl = ajaxUrl.replace('{sm_store}', encodeURIComponent(document.getElementById('smart_report_store').value));

        var usedContainer = 'page:main-container';
        var containers = container.split('|');
        var i;
        for (i=0;i<containers.length;i++)
        {
            if (document.getElementById(containers[i]))
                usedContainer = containers[i];
        }

        new Ajax.Updater(usedContainer, ajaxUrl, {evalScripts:true});
    }
}