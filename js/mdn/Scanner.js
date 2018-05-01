

//**********************************************************************************************************
//**********************************************************************************************************
// INVENTORY - Product
//**********************************************************************************************************
//**********************************************************************************************************

var stockIdForProductLocation = '';

//***************************************************************************************************
//Link a new barcode to product
function linkBarcode(productId)
{
	
	var barcode = KC_value;
	if ((barcode != '') && (barcode != null))
	{
		//clean barcode
		barcode = cleanBarcode(barcode);
		
		//save barcode association
		var url = getNewBarcodeUrl;
		url += 'product_id/' + productId + '/barcode/' + barcode;
		
		//ajax request
		 var request = new Ajax.Request(
	        url,
	        {
	            method: 'GET',
	            onSuccess: function onSuccess(transport)
		        			{
		        				elementValues = eval('(' + transport.responseText + ')');
		        				
		        				if (elementValues['error'] == false)
		        					document.location.href = document.location.href;
		        				else
		        				{
		        					showSimpleMessage(elementValues['message']);
		        					resetField('query');
		        				}
		        			},
				onFailure: function onFailure(transport)
		        			{
								showSimpleMessage('An error occured');
		        			}
	        }
	    );	
	}
	
}

//***************************************************************************************************
//Set product location for specific stock
function setProductStockLocation(stockId)
{
	stockIdForProductLocation = stockId;
	enableCatchKeys('applyProductLocation(KC_value);');
	showInputBox('Please scan location barcode');
}

//***************************************************************************************************
//Apply barcode location
function applyProductLocation(location)
{
	var url = changeProductLocationUrl;
	url += 'stock_id/' + stockIdForProductLocation + '/location/' + location;
	document.location.href = url;
}

//**********************************************************************************************************
//**********************************************************************************************************
// INVENTORY - Free delivery
//**********************************************************************************************************
//**********************************************************************************************************

var freeDeliveryProductInfo = new Array();

//***************************************************************************************************
//Product scanned for free delivery
function freeDeliveryAddProduct(barcode)
{
	//Send ajax request to get product information
	var url = freeDeliveryAddProductUrl + 'barcode/' + barcode;
	alert('1');
	var request = new Ajax.Request(
		url,
	    {
	        method:'GET',
	        onSuccess: function onSuccess(transport)
	        			{
							alert('2');
	        				elementValues = eval('(' + transport.responseText + ')');
							if (elementValues['error'])
							{
								showSimpleMessage(elementValues['message']);
							}
							else
							{
								freeDeliveryProductInfo = elementValues;
								showInputBox('Please scan location barcode');
							}
	        			},
	        onFailure: function onFailure() 
	        			{
	        				alert('error');
	        			}
	    }
    );

}

//**********************************************************************************************************
//**********************************************************************************************************
// Purchase Order delivery
//**********************************************************************************************************
//**********************************************************************************************************

var productIdForAddBarcodeInPo = '';

//***************************************************************************************************
//
function linkBarcodeForPo(productId)
{
	productIdForAddBarcodeInPo = productId;
	enableCatchKeys('addProductBarcodeForPo(KC_value);');
	showInputBox('Please scan barcode for ' + getProductName(productId));
}

//***************************************************************************************************
//
function addLocationForPo(productId)
{
	productIdForAddBarcodeInPo = productId;
	enableCatchKeys('addProductLocationForPo(KC_value);');
	showInputBox('Please scan location for ' + getProductName(productId));
}

//***************************************************************************************************
//Manually add a location for a product in a PO
function addProductLocationForPo(barcode)
{
	var productId = productIdForAddBarcodeInPo;
	var i;
	for (i=0;i<products.length;i++)
	{
		if (products[i]['id'] == productId)
		{
			products[i]['location'] = barcode;
			document.getElementById('location_' + productId).value = barcode;			
		}
	}
	
	//reenable barcode scanning
	resetHandledKey();
	enableCatchKeys('newScanEntryForPurchaseOrder(KC_value);');
	closeSimpleMessage();
}

//***************************************************************************************************
//Manually add a barcode for a product in a PO
function addProductBarcodeForPo(barcode)
{
	var productId = productIdForAddBarcodeInPo;
	var i;
	for (i=0;i<products.length;i++)
	{
		if (products[i]['id'] == productId)
		{
			var nb = products[i]['barcodes'].length + 1;
			products[i]['barcodes'][nb] = barcode;
			document.getElementById('barcode_' + productId).value = barcode;			
		}
	}
	
	//reenable barcode scanning
	resetHandledKey();
	enableCatchKeys('newScanEntryForPurchaseOrder(KC_value);');
	closeSimpleMessage();
}

//***************************************************************************************************
//return product name from product id
function getProductName(productId)
{
	var i;
	for (i=0;i<products.length;i++)
	{
		if (products[i]['id'] == productId)
		{
			return products[i]['name'];
		}
	}
	
	return 'unknown';
}

//***************************************************************************************************
//Increment product qty
function incrementProductQty(productId)
{
	
	var qty = document.getElementById('product_' + productId).value;
	qty++;
	document.getElementById('product_' + productId).value = qty;
	document.getElementById('span_product_' + productId).innerHTML = qty;
		
	colorProductCell(productId);
}

//***************************************************************************************************
//decrement product qty
function decrementProductQty(productId)
{
	var qty = document.getElementById('product_' + productId).value;
	if (qty > 0)
	{
		qty--;
		document.getElementById('product_' + productId).value = qty;
		document.getElementById('span_product_' + productId).innerHTML = qty;
	}		
	
	colorProductCell(productId);
}

//***************************************************************************************************
//Color cell in green whe delivered qty = expected qty
function colorProductCell(productId)
{
	var qty = document.getElementById('product_' + productId).value;
	var color = '';


	var i;
	for (i=0;i<products.length;i++)
	{
		if (products[i]['id'] == productId)
		{
			color = '#ff0000';
			if (qty == products[i]['expected_qty'])
			{
				color = '#00FF00';		
				
				//delete row if configured
				if (deleteRowWhenQtyReached == 1)
				{
					var rowId = 'tr_product_' + productId;
					document.getElementById(rowId).style.display = 'none';
					
					var sortValue = products[i]['sort_value'];
					if (sortObjectProductsQtyReached(sortValue))
					{
						rowId = 'tr_sortvalue_' + sortValue;
						document.getElementById(rowId).style.display = 'none';
					}
				}
			}
			if (qty > products[i]['expected_qty'])
			{
				color = '#0000FF';			
				
				//display row if qty greater
				var rowId = 'tr_product_' + productId;
				document.getElementById(rowId).style.display = '';
			}
		}
	}

	document.getElementById('tr_product_' + productId).style.color = color;
	
}

//***************************************************************************************************
//Scan new entry for purchase order (add location scan after if enabled)

var scanLocationProductId = '';

function newScanEntryForPurchaseOrder(barcode)
{
	var productId = newScanEntry(barcode);
	
	//ask for location
	if ((scanLocationAfterProduct == 1) && (productId != ''))
	{
		//check if location has already been scaned
		var currentLocation = '';
		var i;
		for (i=0;i<products.length;i++)
		{
			if (products[i]['id'] == productId)
			{
				if (products[i]['location'] == '')
				{
					scanLocationProductId = productId;
					enableCatchKeys('scanProductLocation(KC_value);');
					showInputBox('Please scan product location for ' + products[i]['name']);				
					return true;
				}
			}
		}
	}
}

//***************************************************************************************************
//Scan product location
function scanProductLocation(barcode)
{

	//store location (if not null)
	if (barcode != '')
	{
		var i;
		for (i=0;i<products.length;i++)
		{
			if (products[i]['id'] == scanLocationProductId)
			{
				products[i]['location'] = barcode;
				document.getElementById('location_' + scanLocationProductId).value = barcode;
			}
		}
	}
	
	//reenable barcode scanning
	resetHandledKey();
	enableCatchKeys('newScanEntryForPurchaseOrder(KC_value);');
	closeSimpleMessage();
}

//***************************************************************************************************
//New scan entry
function newScanEntry(barcode)
{
	//search for product
	var productId = null;
	var i;
	
	for (i=0;i<products.length;i++)
	{
		//search for barcode
		var j;
		for(j=0;j<products[i]['barcodes'].length;j++)
		{
			if (barcode == products[i]['barcodes'][j])
			{
				//increment qty
				var productId = products[i]['id'];
				var qty = document.getElementById('product_' + productId).value;
				qty++;
				
				document.getElementById('product_' + productId).value = qty;
				document.getElementById('span_product_' + productId).innerHTML = qty;
				
				colorProductCell(productId);

				resetHandledKey();
				return productId;	
			}
				
		}
	}
	
	//if not found..
	showSimpleMessage('Unable to find product with barcode = ' + barcode);
	resetHandledKey();
	return '';
}

//**********************************************************************************************************
//**********************************************************************************************************
// PICKING
//**********************************************************************************************************
//**********************************************************************************************************

//**************************************************************************************************************
//Check if all products qty for 1 manufacturer are ok
function sortObjectProductsQtyReached(sortValue)
{
	var i;
	for (i=0;i<products.length;i++)
	{
		if (products[i]['sort_value'] == sortValue)
		{
			var productId = products[i]['id'];
			var expectedQty = products[i]['expected_qty'];
			var scannedQty = document.getElementById('product_' + productId).value;
			if (scannedQty < expectedQty)
				return false;
		}
	}
	
	return true;
}


//**********************************************************************************************************
//**********************************************************************************************************
// SCAN COMPARISON
//**********************************************************************************************************
//**********************************************************************************************************

function compareScan()
{
	var barcode = KC_value;
	
	//define mode
	var mode = '';
	if (document.getElementById('scan_1').innerHTML == '')
		mode = 'first_scan';
	else
	{
		if (document.getElementById('scan_2').innerHTML == '')
			mode = 'second_scan';
		else
			mode = 'first_scan';
	}
	
	
	//apply mode
	switch(mode)
	{
		case 'first_scan':
			document.getElementById('scan_1').innerHTML = barcode
			document.getElementById('scan_2').innerHTML = '';
			document.getElementById('result').innerHTML = '';
			break;
		case 'second_scan':
			document.getElementById('scan_2').innerHTML = barcode
			if (document.getElementById('scan_1').innerHTML == document.getElementById('scan_2').innerHTML)
				document.getElementById('result').innerHTML = '<font color="green">OK</front>';
			else
				document.getElementById('result').innerHTML = '<font color="red">ERROR</front>';
			break;
	}
	
	
	resetHandledKey()
}

//**********************************************************************************************************
//**********************************************************************************************************
// EDIT STOCK
//**********************************************************************************************************
//**********************************************************************************************************

//**************************************************************************************************************
//Increment qty
function incrementQty(inputId, divId)
{
	var value = document.getElementById(inputId).value;
	value++;
	document.getElementById(inputId).value = value;
	if (document.getElementById(divId))
		document.getElementById(divId).innerHTML = value;
}

//**************************************************************************************************************
//Decrement qty
function decrementQty(inputId, divId)
{
	var value = document.getElementById(inputId).value;
	if (value > 0)
		value--;
	document.getElementById(inputId).value = value;
	if (document.getElementById(divId))
		document.getElementById(divId).innerHTML = value;
}


//**********************************************************************************************************
//**********************************************************************************************************
// CATCH KEYS
//**********************************************************************************************************
//**********************************************************************************************************


var KC_catchKeys = false;
var KC_displayInput = null;
var KC_value = '';
var KC_onEnter = null;


//***************************************************************************************************
//enable catch keys
function enableCatchKeys(eOnEnter)
{
	KC_catchKeys = true;	
	KC_displayInput = document.getElementById('barcode');
	KC_onEnter = eOnEnter;	
}

//**********************************************************************************************************
//disable catch key
function disableCatchKeys()
{
	KC_catchKeys = false;	
}

//**********************************************************************************************************
//reset chars entered
function resetHandledKey()
{
	KC_value = '';
	if (KC_displayInput != null)
		KC_displayInput.innerHTML = '';
}

//**********************************************************************************************************
//handle key press
function handleKey(evt) {

	if (!KC_catchKeys)
		return true;

	var evt = (evt) ? evt : ((event) ? event : null); 
	var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
	var postambleKeyCode = postamble.charCodeAt(0);

    //if no postamble defined, set 13 for Enter key auto mapping
    if(!postambleKeyCode){
        postambleKeyCode = 13;
    }

    //try to get code from capabilities of the browser
    keyCode = evt.which;//FF : OK, Chrome OK, IE: NOK
    if(!keyCode) keyCode = evt.keyCode;//FF : NOK, Chrome OK, IE: OK
    if(!keyCode) keyCode = evt.charCode;//FF : OK, Chrome OK, IE: NOK


	if (evt.keyCode != postambleKeyCode)
	{
		KC_value += String.fromCharCode(evt.keyCode);

		if (KC_displayInput != null)
			KC_displayInput.innerHTML = KC_value;
	}
	else
	{		
		eval(KC_onEnter);
		KC_value = '';
	}
		
	return false;
} 

//**********************************************************************************************************
//execute function associated to enter
function commitBarcode()
{
	eval(KC_onEnter);
	resetHandledKey();
}


//**********************************************************************************************************
//**********************************************************************************************************
// TOOLS
//**********************************************************************************************************
//**********************************************************************************************************

//**********************************************************************************************************
//
function cleanBarcode(barcode)
{
	barcode = barcode.replace("\r", '');
	barcode = barcode.replace("\n", '');
	return barcode;
}

//**********************************************************************************************************
//
function getTopScrollPosition()
{
	return f_filterResults (
		window.pageYOffset ? window.pageYOffset : 0,
		document.documentElement ? document.documentElement.scrollTop : 0,
		document.body ? document.body.scrollTop : 0
	);
}

function f_filterResults(n_win, n_docel, n_body) {
	var n_result = n_win ? n_win : 0;
	if (n_docel && (!n_result || (n_result > n_docel)))
		n_result = n_docel;
	return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
}

//**********************************************************************************************************
//keep menu at the top of the screen
function handleScroll()
{
	var value = getTopScrollPosition();
	document.getElementById('div_menu').style.top = value + 'px';
}

//**********************************************************************************************************
//Show message
function showSimpleMessage(message)
{
	document.getElementById('div_content').style.display = 'none';
	document.getElementById('simple_message_btn_ok').style.display = '';
	document.getElementById('div_message_txt').innerHTML = message;
	document.getElementById('div_message').style.display = '';
}

//**********************************************************************************************************
//Show message
function showInputBox(message)
{
	document.getElementById('div_content').style.display = 'none';
	document.getElementById('simple_message_btn_ok').style.display = 'none';
	document.getElementById('div_message_txt').innerHTML = message;
	document.getElementById('div_message').style.display = '';
}

//**********************************************************************************************************
//close message
function closeSimpleMessage()
{
	document.getElementById('div_message').style.display = 'none';	
	document.getElementById('div_content').style.display = '';
}

//handler key press
document.onkeypress = handleKey; 
window.onscroll = handleScroll;
