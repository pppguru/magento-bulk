/*
* Display the Invoice Popup for creation or edition of a Supplier Invoice
 */
function showSupplierInvoiceWindows(url, title)
{
	 var request = new Ajax.Request(
        url,
        {
            method: 'GET',
            onSuccess: function onSuccess(transport)
	        			{
							win = new Window({
									className: "alphacube",
									title: title,
									width: 480,
									height: 400,
									destroyOnClose: true,
									closable: true,
									draggable: true,
									recenterAuto: true,
									okLabel: "OK"}
							);
							win.setHTMLContent(transport.responseText);
							win.showCenter();
	        			},
			onFailure: function onFailure(transport)
	        			{
							alert('An error occured : ' + url);
	        			}
        }
    );
}

/*
 * Display a confirmation Popup for removing a Supplier Invoice
 */
function confirmDelete(url, title){
	if (confirm(title) == true) {
		document.location.href = url;
	}
}

