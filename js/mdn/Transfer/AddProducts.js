var transferAddProducts = Class.create();
transferAddProducts.prototype = {

    //**********************************************************************************************************************************
    //initialize object
    initialize: function(productInformationUrl){
        this.products = new Array();
        this.productInformationUrl = productInformationUrl;
    },

    //**********************************************************************************************************************************
    //
    waitForScan: function()
    {
        objScanner.showInstruction('Scan product barcode', false);

        document.onkeypress = handleKey;
        enableCatchKeys(null, 'objScanner.scanProductBarcode();', 'objScanner.barcodeDigitScanned();');

    },

    //**********************************************************************************************************************************
    //
    waitForLocationScan: function()
    {
        objScanner.showInstruction('Scan location', false);

        document.onkeypress = handleKey;
        enableCatchKeys(null, 'objScanner.scanLocationBarcode();', 'objScanner.barcodeDigitScanned();');

    },

    //**********************************************************************************************************************************
    //
    barcodeDigitScanned:function()
    {
        objScanner.showMessage(KC_value);
    },

    //**********************************************************************************************************************************
    //
    scanProductBarcode: function()
    {
        //init vars
        var barcode = KC_value;

        //check if product already downloaded
        var productRow = objScanner.findProduct(barcode);
        if (!productRow)
        {
            objScanner.getProductInformation(barcode);
        }
        else
        {
            //increase scanned qty
            objScanner.increaseQty(productRow.entity_id)
            objScanner.showMessage(productRow.name + ' added');
        }
        
    },

    //**********************************************************************************************************************************
    //
    commit: function()
    {
        //ask for confirmation
        if (!confirm('Do you confirm ?'))
            return false;

        //store serialized datas
        document.getElementById('data').value = objScanner.serializeData();

        //submit form
        document.getElementById('form').submit();

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
    },

    //******************************************************************************
    //
    findProduct: function(barcode)
    {
        for(i=0;i<objScanner.products.length;i++)
        {
            if (objScanner.products[i].barcode == barcode)
            {
                return objScanner.products[i];
            }
        }

        return null;
    },
    
    //******************************************************************************
    //Send ajax request to get product information
    getProductInformation: function(barcode)
    {
        var url = objScanner.productInformationUrl;
        url += 'barcode/' + barcode;
        var request = new Ajax.Request(
            url,
            {
                method:'GET',
                onSuccess: function onSuccess(transport)
                {
                    elementValues = eval('(' + transport.responseText + ')');
                    if (elementValues['error'])
                    {
                        objScanner.showMessage(elementValues['message'], true);
                        return false;
                    }
                    else
                    {
                        var product = elementValues['product'];
                        objScanner.products[objScanner.products.length] = product;
                        objScanner.increaseQty(product.entity_id);
                        objScanner.showMessage(elementValues['message']);
                        return true;
                    }
                },
                onFailure: function onFailure() 
                {
                        objScanner.showMessage('An error occured', true);
                        return false;
                }
            }
            );
        
    },
    
    //******************************************************************************
    //
    showScannedProducts: function()
    {
        var html = '<table border="1" width="100%" cellspacing="0">';
        html += '<tr>';
        html += '<td class="po_th">Image</th>';
        html += '<td class="po_th">Barcode</th>';
        html += '<td class="po_th">Sku</th>';
        html += '<td class="po_th">Name</th>';
        html += '<td class="po_th">Qty</th>';
        html += '</tr>';

        for(i=0;i<objScanner.products.length;i++)
        {
            var product = objScanner.products[i];
            html += '<tr>';
            html += '<td class="po_td"><img src="' + product.image_url + '" width="50" height="50"></td>';
            html += '<td class="po_td">' + product.barcode + '</td>';
            html += '<td class="po_td">' + product.sku + '</td>';
            html += '<td class="po_td">' + product.name + '</td>';
            html += '<td class="po_td"><input type="button" value=" - " onclick="objScanner.decreaseQty(' + product.entity_id + ')"> ' + product.qty + ' <input type="button" value=" + " onclick="objScanner.increaseQty(' + product.entity_id + ')"></td>';
            html += '</tr>';
        }

        html += '</table>';

        document.getElementById('div_summary').innerHTML = html;
    },

    //******************************************************************************
    //
    decreaseQty: function(productId)
    {
        for(i=0;i<objScanner.products.length;i++)
        {
            var product = objScanner.products[i];
            if (product.entity_id == productId)
            {
                if (product.qty > 0)
                {
                    product.qty -= 1;
                    objScanner.showScannedProducts();
                }
            }
        }
    },

    //******************************************************************************
    //
    increaseQty: function(productId)
    {
        for(i=0;i<objScanner.products.length;i++)
        {
            var product = objScanner.products[i];
            if (product.entity_id == productId)
            {
                product.qty += 1;
                objScanner.showScannedProducts();
            }
        }

    },

    //******************************************************************************
    //serialize products data
    serializeData: function()
    {
        var string = '';

        for(i=0;i<objScanner.products.length;i++)
        {
            var product = objScanner.products[i];
            if (product.qty > 0)
            {
                string += product.entity_id + '=' + product.qty + '#';
            }
        }

        return string;
    }

}