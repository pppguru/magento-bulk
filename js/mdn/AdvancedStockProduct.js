//*********************************************************************************************************
//method to store current tab in a hidden field
function beforeSaveProduct()
{
	//Stock current tab in hidden form item
	var currentTabId = advancedstock_product_tabsJsTabs.activeTab.name;
	document.getElementById('current_tab').value = currentTabId;

	//submit form
	editForm.submit();
}

//*********************************************************************************************************************************************
//function to enable or disable field depending of checkbox state
function toggleFieldFromCheckbox(checkboxId, fieldId)
{
	var checked = document.getElementById(checkboxId).checked;
	var field = document.getElementById(fieldId);	
	if (checked)
		field.disabled = true;
	else
		field.disabled = false;
}

//*********************************************************************************************************************************************
//function to enable or disable field depending of combo state
function toggleFieldFromCombo(comboId, fieldId)
{
	var checked = document.getElementById(comboId).value;
	var field = document.getElementById(fieldId);	
	if (checked == 1)
		field.disabled = true;
	else
		field.disabled = false;
	
}

//*********************************************************************************************************
//
function refreshPrices(from)
{
	
	var price = 0;
	var special_price = 0;
    var price_ttc = 0;
	var margin_percent = 0;
	var priceforcalculation = 0;
	var cost = 0;

    var taxCoef = 1 + (taxRate / 100);

	cost = document.getElementById('cost').innerHTML;
    price = document.getElementById('price').innerHTML;
    price_ttc = document.getElementById('price_ttc').innerHTML;
    special_price = document.getElementById('special_price').innerHTML;

	switch (from)
	{
		case 'margin':
			margin_percent = parseFloat(document.getElementById('margin_percent').value);
			if (!isNaN(cost) && cost > 0) {
                price = cost / (1 - margin_percent / 100);
                price_ttc = (price * taxCoef * 100) / 100;
            }
			break;

		case 'price':			
            if(!isNaN(special_price) && special_price > 0 && special_price != price){
                priceforcalculation = special_price;
            }else{
                priceforcalculation = price;
            }
			price_ttc = (priceforcalculation * taxCoef * 100) / 100;           
			if (!isNaN(cost) && cost > 0)
				margin_percent = (priceforcalculation - cost) / priceforcalculation * 100;
			break;

		case 'price_ttc':
            if(!isNaN(special_price) && special_price > 0 && special_price != price_ttc){
                priceforcalculation = special_price;
            }else{
                priceforcalculation = price_ttc;
            }
            if (taxCoef > 0){
                price = priceforcalculation / taxCoef;
            }
			if (!isNaN(cost) && cost > 0)
				margin_percent = (price - cost) / price * 100;
			break;
	}
	
	//Display and round
	document.getElementById('margin_percent').innerHTML = margin_percent.toFixed(decimalCount);
	document.getElementById('price').innerHTML = Math.round(price*Math.pow(10,decimalCount))/Math.pow(10,decimalCount);
	document.getElementById('price_ttc').innerHTML = Math.round(price_ttc*Math.pow(10,decimalCount))/Math.pow(10,decimalCount);
}

//****************************************************************************************************************
//print barcode labels
function printLabels()
{
    var url = printLabelUrl;
    var qty = document.getElementById('label_count').value;
    if(qty>0){
        url += 'qty/' + qty;
        document.location.href = url;
    }else{
        alert('Please set a quantity to print > 0');
    }

}

//****************************************************************************************************************
//auto calculate prefered stock level
function autoCalculatePreferedStockLevel(productId)
{
	alert('ok');
	//var url = autoCalculatePreferedStockLevelUrl + 'product_id/' + productId;
	//alert(url);
}