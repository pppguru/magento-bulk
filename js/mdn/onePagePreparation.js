
//**********************************************************************************************************************
//
function printDocuments(url)
{
    var request = new Ajax.Request(
        url,
        {
            method: 'GET'
        }
        );
}

//**********************************************************************************************************************
//
function displaySerialCount(id)
{
    var serialText = document.getElementById('serials_' + id).value;
    var spanCount = document.getElementById('serial_nb_' + id);
	
    var t_serials = serialText.split("\n");
    var nb = 0;
    var i;
    for(i=0;i<t_serials.length;i++)
    {
        if (t_serials[i] != '')
            nb++;
    }
	
    spanCount.innerHTML = nb + ' serials';
}

//**********************************************************************************************************************
//
function performAction(action)
{
    var t = action.split(";");
    var type = t[0];
    var url = t[1];
	
    switch(type)
    {
        case 'download':
            document.location.href = url;
            break;
        case 'ajax':
            var request = new Ajax.Request(
                url,
                {
                    method: 'GET'
                }
                );
            break;
        case 'redirect':
            document.location.href = url;
            break;
    }
	
    document.getElementById('actions_list').selectedIndex = 0;
}

//*****************************************************************************************************************************************
//call an url using ajax
function ajaxCall(url, confirmMsg)
{
    var request = new Ajax.Request(
        url,
        {
            method: 'GET',
            onSuccess: function onSuccess(transport)
            {
                elementValues = eval('(' + transport.responseText + ')');
                if (elementValues['error'] == true)
                {
                    alert(elementValues['message']);
                }
                else
                {
                    if (confirmMsg != '')
                        alert(confirmMsg);
                }
								
            },
            onFailure: function onFailure(transport)
            {
                alert('error');
            }
        }
        );
}

//*****************************************************************************************************************************************
//commit button
function commit(saveData, createShipmentInvoices, printDocuments, downloadDocuments, printShippingLabel, selectNexOrder)
{
    //define setttings
    if (createShipmentInvoices)
        document.getElementById('create').value = 1;
	
    if (printDocuments)
        document.getElementById('print_documents').value = 1;
	
    if (printShippingLabel)
        document.getElementById('print_shipping_label').value = 1;

    //call main method using ajax
    var request = new Ajax.Request(
        saveDataUrl,
        {
            method: 'post',
            onSuccess: function onSuccess(transport)
            {
                elementValues = eval('(' + transport.responseText + ')');
                if (elementValues['error'] == true)
                {
                    alert(elementValues['message']);
                }
                else
                {
                    //download documents (invoice & shipment)
                    if (downloadDocuments)
                        document.location.href = downloadDocumentUrl;
									
                    //select next order
                    if (selectNexOrder)
                        document.location.href = nextOrderUrl;
                    else
                        document.location.href = refreshUrl;
										
                }
            },
            onFailure: function onFailure(transport)
            {
                alert('error');
            },
            parameters: Form.serialize(document.getElementById('form_onepage_preparation'))
        }
        );

}

//*******************************************************************************************************************
//*******************************************************************************************************************
// BARCODE FEATURE
//*******************************************************************************************************************
//*******************************************************************************************************************

//*******************************************************************************************************************
//Barcode typed
function readBarcode()
{
    var barcode = KC_value;

	//if next order
	if (barcode == 'nextorder')
	{
		if (document.getElementById('next_order_button'))
		{
			document.getElementById('next_order_button').click();
			return true;
		}
	}
	
    //find product with this barcode
    var productId = findProductWithBarcode(barcode);
    if (!productId)
    {
        alert('Cant find product with barcode="' + barcode + '"');
        return false;
    }

    //update scanned qty
    var scannedQtyId = 'scanned_qty_' + productId;
    var scannedQty = document.getElementById(scannedQtyId).value;
    scannedQty++;
    document.getElementById(scannedQtyId).value = scannedQty;

    //update qty to scan
    var neededQty = document.getElementById('ordered_qty_' + productId).value;
    var qtyToScan = neededQty - scannedQty;
    document.getElementById('remaining_to_scan_' + productId).innerHTML = qtyToScan;

    //update image
    if (neededQty == scannedQty)
    {
        document.getElementById('scanned_ok_' + productId).src = scannedOkImageUrl;
        document.getElementById('scanned_ok_' + productId).style.display = '';
        document.getElementById('remaining_to_scan_' + productId).style.display = 'none';

        //if all products are scanned, commit
        if (allProductsScanned())
        {
            document.getElementById('commit_button').click();
        }
    }
    else if (neededQty < scannedQty)
    {
        alert('You dont need this product again !');
        return false;
    }

    return true;
}

//*******************************************************************************************************************
//find product for barcode
function findProductWithBarcode(barcode)
{
    var ids = new Array();
    var prefix = 'barcode_';
    var inputs = document.getElementsByTagName('input');
    for (i=0; i < inputs.length; i++)
    {
        if (inputs[i] && inputs[i].id != null)
        {
            if (inputs[i].id.indexOf(prefix) != -1)
            {
                if (inputs[i].value == barcode)
                {
                    return inputs[i].id.replace(prefix, '');
                }
            }
        }
    }

    return null;
    
}

//*******************************************************************************************************************
//check if all product are scanned
function allProductsScanned()
{
    var prefix = 'barcode_';
    var inputs = document.getElementsByTagName('input');
    for (i=0; i < inputs.length; i++)
    {
        if (inputs[i] && inputs[i].id != null)
        {
            if (inputs[i].id.indexOf(prefix) != -1)
            {
                var productId = inputs[i].id.replace(prefix, '');
                var orderedQty = document.getElementById('ordered_qty_' + productId).value;
                var scannedQty = document.getElementById('scanned_qty_' + productId).value;
                var remainingToScan = orderedQty - scannedQty;
                if (remainingToScan > 0)
                    return false;
            }
        }
    }
    return true;
}