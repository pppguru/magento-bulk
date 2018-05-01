var inventory = Class.create();
inventory.prototype = {

    //**********************************************************************************************************************************
    //initialize object
    initialize: function(eProductsUrl, eInventoryId, eResetLocationUrl, eUnknowBarcodeUrl, eDisplayQuantityButton){
        this.productsUrl = eProductsUrl;
        this.inventoryId = eInventoryId;
        this.location = null;
        this.products = null;
        this.resetLocationUrl = eResetLocationUrl;
        this.unknownBarcodeUrl = eUnknowBarcodeUrl;
        this.displayQuantityButton = eDisplayQuantityButton;
        this.manuallyCountQuantity = 0;
    },
    
    //**********************************************************************************************************************************
    //
    waitForScan: function()
    {
        document.getElementById('div_products').style.display = 'none';
        document.getElementById('div_summary').style.display = 'none';
        
        document.onkeypress = handleKey;

        mode = 'by_location';
        objmode = document.getElementById('ei_stock_take_mode');
        if(objmode){
            mode = objmode.value;
        }
       
        if(mode == 'by_location'){
            inventoryObj.showInstruction('Please scan location', false);
            enableCatchKeys(null, 'inventoryObj.scanLocation();', 'inventoryObj.barcodeDigitScanned();');
        }else{
            inventoryObj.initProductsAndDisableLocation();            
        }

    },

    //**********************************************************************************************************************************
    //
    barcodeDigitScanned:function()
    {
        inventoryObj.showMessage(KC_value);
    },
    
    //**********************************************************************************************************************************
    //Request location information to server //ERP-318
    initProductsAndDisableLocation: function()
    {
        //init vars
        var location = '';
        objDisabled = document.getElementById('ei_stock_take_disabled_tag');
        if(objDisabled){
            location = objDisabled.value;
        }
        KC_value = '';
        var url = this.productsUrl;
        url += 'location/' + location;
        url += '/ei_id/' + inventoryObj.inventoryId;
        
        //ajax request
        var request = new Ajax.Request(
        url,
        {
            method: 'GET',
            evalScripts: true,
            onSuccess: function onSuccess(transport)
            {
                elementValues = eval('(' + transport.responseText + ')');
                
                if (elementValues['error'] == true)
                {                    
                    inventoryObj.showMessage(errorMessage, true);
                }
                else
                {
                    //display products information
                    inventoryObj.hideMessage();
                    inventoryObj.showInstruction('Please scan a product barcode', false);
                    document.getElementById('div_products').innerHTML = elementValues['products_html'];
                    document.getElementById('div_summary').innerHTML = elementValues['location_html'];

                    inventoryObj.products = elementValues['products'];
                    inventoryObj.location = location;

                    document.onkeypress = handleKey;
                    enableCatchKeys(null, 'inventoryObj.scanProductBarcode();', 'inventoryObj.barcodeDigitScanned();');

                    document.getElementById('div_products').style.display = '';
                    document.getElementById('div_summary').style.display = '';

                    inventoryObj.showMessage('');
                        
                    inventoryObj.updateScannedQuantities();

                }

            },
            onFailure: function onFailure(transport)
            {
                inventoryObj.showMessage('An error occured', true);
            }
        }
    );
        
    },

    //**********************************************************************************************************************************
    //Request location information to server
    scanLocation: function()
    {
        if (!KC_value)
        {
            inventoryObj.showMessage('Please scan a valid location', true);
            return false;
        }

        var audio = document.getElementById("audio_ok");
        audio.play();

        //init vars
        var location = KC_value;
        KC_value = '';
        var url = this.productsUrl;
        url += 'location/' + location;
        url += '/ei_id/' + inventoryObj.inventoryId;

        //ajax request
        var request = new Ajax.Request(
        url,
        {
            method: 'GET',
            evalScripts: true,
            onSuccess: function onSuccess(transport)
            {
                elementValues = eval('(' + transport.responseText + ')');
                if (elementValues['error'] == true)
                {
                    var errorMessage = elementValues['message'];
                    if (errorMessage == 'error_location_scanned')
                    {
                        if (confirm('This location has already been scanned, do you want to reset scanned products and start again ?'))
                        {
                            //reset scanned products for this location
                            url = inventoryObj.resetLocationUrl;
                            url += 'location/' + location;
                            url += '/ei_id/' + inventoryObj.inventoryId;
                            var requestReset = new Ajax.Request(
                            url,
                            {
                                method: 'GET',
                                evalScripts: true,
                                onSuccess: function onSuccess(transport)
                                {
                                    KC_value = location;
                                    inventoryObj.scanLocation();
                                },
                                onFailure: function onFailure(transport)
                                {
                                    inventoryObj.showMessage('An error occured', true);
                                }
                            });

                        }
                        else
                        {
                            inventoryObj.showMessage('Location already scanned', true);
                        }
                    }
                    else
                    {
                        //display message
                        inventoryObj.showMessage(errorMessage, true);
                    }
                }
                else
                {
                    //display products information
                    inventoryObj.hideMessage();
                    inventoryObj.showInstruction('Please scan products', false);
                    document.getElementById('div_products').innerHTML = elementValues['products_html'];
                    document.getElementById('div_summary').innerHTML = elementValues['location_html'];

                    inventoryObj.products = elementValues['products'];
                    inventoryObj.location = location;

                    document.onkeypress = handleKey;
                    enableCatchKeys(null, 'inventoryObj.scanProductBarcode();', 'inventoryObj.barcodeDigitScanned();');

                    document.getElementById('div_products').style.display = '';
                    document.getElementById('div_summary').style.display = '';

                    inventoryObj.showMessage('');

                    inventoryObj.updateScannedQuantities();

                }

            },
            onFailure: function onFailure(transport)
            {
                inventoryObj.showMessage('An error occured', true);
            }
        }
    );

    },
    
    //**********************************************************************************************************************************
    // 
    addProductToTable: function (productInformation)
    {
        
        var table = document.getElementById('table_products');
        var rowCount = table.rows.length;
        var row = table.insertRow(rowCount);        

        //Sku
        var cellSku = row.insertCell(0);
        cellSku.innerHTML = productInformation.sku;

        //Name
        var cellName = row.insertCell(1);
        cellName.innerHTML = productInformation.name;

        //Scanned Qty
        var cellQty = row.insertCell(2);
        var html = '';

        if (inventoryObj.displayQuantityButton == 1)
            html += '<input type="button" value=" - " onclick="inventoryObj.decreaseQty(' + productInformation.product_id + ');">';
        
        html += '&nbsp;'
        html += '<input type="text" size="3" onchange="inventoryObj.textQuantityChanged(' + productInformation.product_id + ');" id="scanned_qty_' + productInformation.product_id + '" name="scanned_qty_' + productInformation.product_id + '" value="1">';
        html += '&nbsp;'

        if (inventoryObj.displayQuantityButton == 1)
            html += '<input type="button" value=" + " onclick="inventoryObj.increaseQty(' + productInformation.product_id + ');">';

        cellQty.innerHTML = html;

        //Expected Qty
        var cellExpected = row.insertCell(3);
        cellExpected.innerHTML = productInformation.expected_qty;

        //Difference
        var cellDiff = row.insertCell(4);
        cellDiff.id = 'diff_' + productInformation.product_id;
        
    },
    
    //**********************************************************************************************************************************
    // 
    updateScannedQuantities: function()
    {
        var productInformation = null;
        for(i=0;i<inventoryObj.products.length;i++)
        {
            productInformation = inventoryObj.products[i];
            
            document.getElementById('scanned_qty_' + productInformation.product_id).value = productInformation.scanned_qty;
            
            var diff = parseInt(productInformation.expected_qty - productInformation.scanned_qty);
            var color = 'green';
            if (diff != 0)
                color = 'red';
                
            document.getElementById('diff_' + productInformation.product_id).innerHTML = '<font color="' + color + '">' + diff + '</font>';
        }
        
        inventoryObj.updateTotalScannedQty();
        
    },

    //**********************************************************************************************************************************
    //
    textQuantityChanged: function(id)
    {
      var productInformation = null;
      productInformation = inventoryObj.findProductById(id);
      if(productInformation){
        var newValue = document.getElementById('scanned_qty_' + productInformation.product_id).value;;
        if(newValue>=0){
            productInformation.scanned_qty = newValue;
            productInformation.was_scanned = 1;
        }
      }
      inventoryObj.updateTotalScannedQty();
    },
    
    //*******************************************************************************************
    //
    scanProductBarcode: function()
    {
        var barcode = KC_value;
        KC_value = '';
		
		if (barcode == '')
		{
			inventoryObj.showMessage('', true);
			return;		
		}

        //find product
        var productInformation = null;
        for(var i=0;i<this.products.length;i++)
        {
            var barcodeBuffer = this.products[i].barcode;
            var barcodes = barcodeBuffer.split('|');
            for	(var j = 0; j < barcodes.length; j++) {
                if (barcodes[j] == barcode) {
                    productInformation = this.products[i];
                }
            }
        }

        //Manage error
        if (productInformation == null)
        {
            //if product is unknown, send an ajax request to try to find it
            var url = inventoryObj.unknownBarcodeUrl;
            url += 'barcode/' + barcode;
            url += '/ei_id/' + inventoryObj.inventoryId;
            var request = new Ajax.Request(
            url,
            {
                method: 'GET',
                evalScripts: true,
                onSuccess: function onSuccess(transport)
                {
                    elementValues = eval('(' + transport.responseText + ')');
                    var mode = elementValues['mode'];
                    switch(mode)
                    {
                        case 'error':
                            inventoryObj.showMessage(elementValues['message'], true);
                            break;
                        case 'add':
                            //add product to collection
                            var productInformation = elementValues['product'];
                            inventoryObj.products[inventoryObj.products.length] = productInformation;
                            inventoryObj.showMessage(productInformation.name + ' added');
                            
                            productInformation.scanned_qty++;
                            inventoryObj.addProductToTable(productInformation);
                            inventoryObj.updateScannedQuantities();

                            var audio = document.getElementById("audio_ok");
                            audio.play();

                            break;
                    }
                },
                onFailure: function onFailure(transport)
                {
                    inventoryObj.showMessage('An error occured', true);
                }                                        
            });
            
            
        }
        else
        {
            //increase qty
            inventoryObj.showMessage(productInformation.name + ' added');
            productInformation.scanned_qty++;
            productInformation.was_scanned = 1;

            //update display
            inventoryObj.updateScannedQuantities();

            var audio = document.getElementById("audio_ok");
            audio.play();

        }
    },
    
    //*******************************************************************************************
    //Cancel current location
    cancel: function()
    {
        if (!confirm('Are you sure to cancel the current location scan ? All scanned products for this location will be missed'))
            return false;
        
        inventoryObj.products = null;
        inventoryObj.waitForScan();
        
    },
    
    //*******************************************************************************************
    //
    commit: function()
    {
        //ask for confirmation
        if (!confirm('Do you confirm to commit the scanned products ?'))
            return false;
        
        //store location
        document.getElementById('eip_location').value = inventoryObj.location;
        
        //build string with products
        var string = '';
        for(i=0;i<inventoryObj.products.length;i++)
        {
            var productInformation = inventoryObj.products[i];
            if(productInformation.was_scanned == 1) {
                string += productInformation.product_id + '=' + productInformation.scanned_qty + ';';
            }
        }
        
        //submit form
        document.getElementById('product_datas').value = string;
        document.getElementById('frm_inventory').submit();
        
    },
    
    //*******************************************************************************************
    //
    increaseQty: function(id)
    {
        var productInformation = null;
        productInformation = inventoryObj.findProductById(id);
        if (productInformation)
        {
            productInformation.scanned_qty++;
            productInformation.was_scanned = 1;
            inventoryObj.updateScannedQuantities();
        }
    },
    
    //*******************************************************************************************
    //
    decreaseQty: function(id)
    {
        var productInformation = null;
        productInformation = inventoryObj.findProductById(id);
        if (productInformation)
        {
            if (productInformation.scanned_qty > 0)
                productInformation.scanned_qty--;
            productInformation.was_scanned = 1;
            inventoryObj.updateScannedQuantities();
        }        
    },
    
    //*******************************************************************************************
    //
    findProductById: function(id)
    {
        //find product
        for(i=0;i<this.products.length;i++)
        {
            if (this.products[i].product_id == id)
            {
                return this.products[i];
            }
        }
        return null;
    },
        
    //*******************************************************************************************
    //
    updateTotalScannedQty: function()  
    {
        var total = 0;
        for(i=0;i<inventoryObj.products.length;i++)
        {
            var value = inventoryObj.products[i].scanned_qty;
            if(value){
                total += parseInt(value);
            }
        }
        document.getElementById('total_scanned_qty').innerHTML = total;
        return total;
    },
    
    //*******************************************************************************************
    //
    isInteger: function (value){ 
        if((parseFloat(value) == parseInt(value)) && !isNaN(value)){
            return true;
        } else { 
            return false;
        } 
    },
    
    //******************************************************************************
    //
    showMessage: function(text, error)
    {
        if (text == '')
            text = '&nbsp;';

        if (error)
            text = '<font color="red">' + text + '</font>';
        else
            text = '<font color="green">' + text + '</font>';

        document.getElementById('div_message').innerHTML = text;
        document.getElementById('div_message').style.display = '';

        if (error)
        {
            var audio = document.getElementById("audio_nok");
            audio.play();
        }
    },

    //******************************************************************************
    //
    hideMessage: function()
    {
        document.getElementById('div_message').style.display = 'none';
    },


    //******************************************************************************
    //display instruction for current
    showInstruction: function(text)
    {
        document.getElementById('div_instruction').innerHTML = text;
        document.getElementById('div_instruction').style.display = '';
    },

    //******************************************************************************
    //
    hideInstruction: function()
    {
        document.getElementById('div_instruction').style.display = 'none';
    }
    
}

//******************************************************************************
//
function applyInventory()
{
    //check fields
    if (document.getElementById('apply_stock_movement_label').value == '')
    {
        alert('Please fill the stock movement label field');
        return false;
    }
    
    //submit
    document.getElementById('apply_inventory').value = 1;
    editForm.submit();
    
}

////ERP-318
function showMethodData()
{

    var options = ["supplier", "brand", "random"];
    options.forEach(function(value) {
        elementName = value+'_ei_stock_take_method_value';
        e = document.getElementById(elementName);
        if(e){
            e.style.display = 'none';
            e.disabled = true;
        }
    });

    option = document.getElementById('ei_stock_take_method_code');
    if(option){
        elementName = option.value+'_ei_stock_take_method_value';       
        e = document.getElementById(elementName);
        if(e){
            e.style.display = 'block';
            e.disabled = false;
        }
    }

}