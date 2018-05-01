var scannerDelivery = Class.create();
scannerDelivery.prototype = {

    //**********************************************************************************************************************************
    //initialize object
    initialize: function(jsonData, poId, scanLocation, scanSerial, displayPackaging, assignBarcode){
        this.products = jsonData;
        this.poId = poId;
        this.scanLocation = scanLocation;
        this.scanSerial = scanSerial;
        this.displayPackaging = displayPackaging;
        this.assignBarcode = assignBarcode;
        this.currentProduct = null;
        this.currentBarcode = null;
    },

    //**********************************************************************************************************************************
    //
    waitForScan: function()
    {
        objScannerDelivery.showInstruction(objScannerDelivery.translate('Scan product barcode'), false);

        document.onkeypress = handleKey;
        enableCatchKeys(null, 'objScannerDelivery.scanProductBarcode();', 'objScannerDelivery.barcodeDigitScanned();');
    },

    //**********************************************************************************************************************************
    //
    waitForLocationScan: function()
    {
        objScannerDelivery.showInstruction(objScannerDelivery.translate('Scan location'), false);

        document.onkeypress = handleKey;
        enableCatchKeys(null, 'objScannerDelivery.scanLocationBarcode();', 'objScannerDelivery.barcodeDigitScanned();');

        document.getElementById('btn_skip_location').style.display = '';
    },

    //**********************************************************************************************************************************
    //
    waitForSerialScan: function()
    {
        objScannerDelivery.showInstruction(objScannerDelivery.translate('Scan serial number (press enter to skip)'), false);

        document.onkeypress = handleKey;
        enableCatchKeys(null, 'objScannerDelivery.scanSerialBarcode();', 'objScannerDelivery.barcodeDigitScanned();');        
    },
    
    //**********************************************************************************************************************************
    //
    skipLocation: function()
    {
        document.getElementById('btn_skip_location').style.display = 'none';
        if (objScannerDelivery.scanSerial){
            objScannerDelivery.waitForSerialScan();
        }else{
            objScannerDelivery.waitForScan();
        }
    },

    //**********************************************************************************************************************************
    //
    barcodeDigitScanned:function()
    {
        objScannerDelivery.showMessage(KC_value);
    },

    //**********************************************************************************************************************************
    //
    scanProductBarcode: function()
    {
        //init vars
        var barcode = KC_value;

        if (!barcode)
        {
            objScannerDelivery.showMessage(objScannerDelivery.translate('Empty barcode'), true);
        }

        //find product with barcode
        var product = objScannerDelivery.findProduct(barcode);
        if (product == null)
        {
            objScannerDelivery.currentProduct = null;
            objScannerDelivery.currentBarcode = barcode;
            if (this.assignBarcode)
            {
                objScannerDelivery.showInstruction(objScannerDelivery.translate('Assign barcode to product'), false);
                objScannerDelivery.showMessage(objScannerDelivery.translate('Unknown barcode : ') + barcode, true);
                objScannerDelivery.showAffectBarcode();
            }
            else
            {
                objScannerDelivery.showMessage(objScannerDelivery.translate('Unknown barcode : ') + barcode, true);
            }
        }
        else
        {            
            objScannerDelivery.showMessage(product.name + objScannerDelivery.translate(' scanned'));
            objScannerDelivery.currentProduct = product;

            product.scanned_qty++;            

            var allowScanSerial = true;
            
            //Location scan if enabled
            if (objScannerDelivery.scanLocation)
            {
                objScannerDelivery.waitForLocationScan();
                //serial scan if enabled and if location has been scanned
                if(!objScannerDelivery.currentProduct.new_location){
                    allowScanSerial = false;
                }
            }            

            if(allowScanSerial){
                if (objScannerDelivery.scanSerial)
                {
                    objScannerDelivery.waitForSerialScan();
                }
            }
            
        }

        objScannerDelivery.showScannedProducts();
        objScannerDelivery.showProductInformation();
    },

    //**********************************************************************************************************************************
    //scan location for current product
    scanLocationBarcode: function()
    {
        //init vars
        var location = KC_value;
        var product = objScannerDelivery.currentProduct;
        product.new_location = location;
        objScannerDelivery.showMessage(objScannerDelivery.translate('Location assigned'));
        document.getElementById('btn_skip_location').style.display = 'none';
        objScannerDelivery.showScannedProducts();
        if (objScannerDelivery.scanSerial){
            objScannerDelivery.waitForSerialScan();
        }else{
            objScannerDelivery.waitForScan();
        }
    },
    
    //**********************************************************************************************************************************
    //Scan sreial numbers
    scanSerialBarcode: function()
    {
        //init vars
        var serial = KC_value;
        var product = objScannerDelivery.currentProduct;
        product.serials += serial + ' ';
        document.getElementById('btn_skip_location').style.display = 'none';
        objScannerDelivery.showMessage(objScannerDelivery.translate('Serial number assigned'));//todo don't display this if skipped'
        objScannerDelivery.showScannedProducts();
        objScannerDelivery.waitForScan();
    },

    //**********************************************************************************************************************************
    //
    commit: function()
    {
        //ask for confirmation
        if (!confirm(objScannerDelivery.translate('Do you confirm ?')))
            return false;

        //lock button to avoid double click adn double delivery
        document.getElementById('po_scanner_commit_button').disabled=true;

        //store serialized datas
        document.getElementById('data').value = objScannerDelivery.serializeData();

        //submit form
        document.getElementById('form_delivery').submit();

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

        if (error)
        {
            var audio = document.getElementById("audio");
            audio.play();
        }

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
        for(i=0;i<objScannerDelivery.products.length;i++)
        {
            if ((objScannerDelivery.products[i].barcode == barcode) 
                || (objScannerDelivery.products[i].new_barcode == barcode)
                || (objScannerDelivery.products[i].additional_barcodes.indexOf(barcode) >= 0))
            {
                return objScannerDelivery.products[i];
            }
        }

        return null;
    },
    
    //******************************************************************************
    //
    showScannedProducts: function()
    {
        var html = '<table border="1" width="100%" cellspacing="0">';
        html += '<tr>';
        html += '<td class="po_th">' + objScannerDelivery.translate('Barcode') + '</th>';
        html += '<td class="po_th">' + objScannerDelivery.translate('Sku') + '</th>';
        html += '<td class="po_th">' + objScannerDelivery.translate('Name') + '</th>';
        html += '<td class="po_th">' + objScannerDelivery.translate('Scanned Qty') + '</th>';
        html += '<td class="po_th">' + objScannerDelivery.translate('Missing Qty') + '</th>';
        if(this.displayPackaging)
            html += '<td class="po_th">' + objScannerDelivery.translate('Packaging') + '</th>';
        html += '<td class="po_th">' + objScannerDelivery.translate('Location') + '</th>';
        if (objScannerDelivery.scanSerial){
            html += '<td class="po_th">' + objScannerDelivery.translate('Serial numbers') + '</th>';
        }
        html += '</tr>';

        for(i=0;i<objScannerDelivery.products.length;i++)
        {
            var product = objScannerDelivery.products[i];
            if (product.scanned_qty > 0)
            {
                html += '<tr>';
                html += '<td class="po_td">' + (product.barcode != null ? product.barcode : '') + ' ' + product.new_barcode + '</td>';
                html += '<td class="po_td">' + product.sku + '</td>';
                html += '<td class="po_td">' + product.name + '</td>';
                html += '<td class="po_td">';
                if (allowQuantityButtons)
                    html += '<input type="button" value=" - " onclick="objScannerDelivery.decreaseQty(' + product.pop_id + ')">';
                html += product.scanned_qty;
                if (allowQuantityButtons)
                    html += ' <input type="button" value=" + " onclick="objScannerDelivery.increaseQty(' + product.pop_id + ')">';
                html += '</td>';
                html += '<td class="po_td">' + (product.expected_qty - product.scanned_qty) + '</td>';
                if(this.displayPackaging)
                    html += '<td class="po_td">' + (product.packaging) + '</td>';
                html += '<td class="po_td">' + (product.new_location ? product.new_location : (product.location == null ? '' : product.location)) + '</td>';
                if (objScannerDelivery.scanSerial){
                    html += '<td class="po_td">' + (product.serials) + '</td>';
                }
                html += '</tr>';
            }
        }

        html += '</table>';

        //update current product qty & location
        if (objScannerDelivery.currentProduct)
        {
            document.getElementById('div_main_qty').innerHTML = objScannerDelivery.currentProduct.scanned_qty + ' / ' + objScannerDelivery.currentProduct.expected_qty;
            document.getElementById('div_main_location').innerHTML = objScannerDelivery.translate('Location') + ' : ' + (objScannerDelivery.currentProduct.new_location ? objScannerDelivery.currentProduct.new_location : objScannerDelivery.currentProduct.location);
        }

        document.getElementById('div_summary').innerHTML = html;
    },

    //******************************************************************************
    //
    showProductInformation: function()
    {
        
        if (objScannerDelivery.currentProduct != null)
        {
            document.getElementById('div_affect_barcode').style.display = 'none';

            document.getElementById('div_main').style.display = '';
            document.getElementById('div_main_name').innerHTML = objScannerDelivery.currentProduct.name;
            document.getElementById('div_main_sku').innerHTML = objScannerDelivery.currentProduct.sku;
            document.getElementById('div_main_location').innerHTML = objScannerDelivery.translate('Location') + ' : ' + (objScannerDelivery.currentProduct.new_location ? objScannerDelivery.currentProduct.new_location : objScannerDelivery.currentProduct.location);
            document.getElementById('div_main_barcode').innerHTML = objScannerDelivery.currentProduct.barcode + ' ' + objScannerDelivery.currentProduct.new_barcode;

            document.getElementById('img_main_picture').style.display = '';
            if (objScannerDelivery.currentProduct.image_url)
                document.getElementById('img_main_picture').src = objScannerDelivery.currentProduct.image_url;
            else
                document.getElementById('img_main_picture').style.display = 'none';
            
            document.getElementById('div_main_qty').innerHTML = objScannerDelivery.currentProduct.scanned_qty + ' / ' + objScannerDelivery.currentProduct.expected_qty;

            if (allowQuantityButtons)
            {
                document.getElementById('btn_increase').style.display = '';
                document.getElementById('btn_decrease').style.display = '';
            }
            if(this.displayPackaging)
                document.getElementById('div_main_packaging').innerHTML = objScannerDelivery.currentProduct.packaging;
        }
        else
        {
            if (allowQuantityButtons)
            {
                document.getElementById('btn_increase').style.display = 'none';
                document.getElementById('btn_decrease').style.display = 'none';
            }
            document.getElementById('div_main').style.display = 'none';
        }
    },

    //******************************************************************************
    //
    showAffectBarcode: function()
    {
        document.getElementById('div_affect_barcode').style.display = '';

        var html = '<p class="po_scanner_h1">' + objScannerDelivery.translate('Barcode') + ' ' + objScannerDelivery.currentBarcode + objScannerDelivery.translate(' is unknown, do you want to assign it to a product :') + '</p>';

        html += '<table border="1" width="100%" cellspacing="0">';
        html += '<tr>';
        html += '<td class="po_th">' + objScannerDelivery.translate('Barcode') + '</th>';
        html += '<td class="po_th">' + objScannerDelivery.translate('Sku') + '</th>';
        html += '<td class="po_th">' + objScannerDelivery.translate('Name') + '</th>';
        html += '<td class="po_th">' + objScannerDelivery.translate('Expected Qty') + '</th>';
        if(this.displayPackaging)
            html += '<td class="po_th">' + objScannerDelivery.translate('Packaging') + '</th>';
        html += '<td class="po_th">' + objScannerDelivery.translate('Location') + '</th>';
        html += '<td class="po_th">' + objScannerDelivery.translate('Assign') + '</th>';
        html += '</tr>';

        for(i=0;i<objScannerDelivery.products.length;i++)
        {
            var product = objScannerDelivery.products[i];
            
            html += '<tr>';
            html += '<td class="po_td">' + (product.barcode != null ? product.barcode : '') + ' ' + product.new_barcode + '</td>';
            html += '<td class="po_td">' + product.sku + '</td>';
            html += '<td class="po_td">' + product.name + '</td>';
            html += '<td class="po_td">' + (product.expected_qty) + '</td>';
            if(this.displayPackaging)
                html += '<td class="po_td">' + product.packaging + '</td>';

            html += '<td class="po_td">' + product.location + '</td>';
            html += '<td class="po_td"><input type="button" value="' + objScannerDelivery.translate('Assign') + '" onclick="objScannerDelivery.affectCurrentBarcode(' + product.pop_id + ')"></td>';
            html += '</tr>';

        }

        html += '</table>';

        document.getElementById('div_affect_barcode').innerHTML = html;
        document.getElementById('div_affect_barcode').style.display = '';
    },

    //******************************************************************************
    //
    affectCurrentBarcode: function(popId)
    {
        for(i=0;i<objScannerDelivery.products.length;i++)
        {
            var product = objScannerDelivery.products[i];
            if (product.pop_id == popId)
            {
                product.new_barcode = objScannerDelivery.currentBarcode;
                KC_value = objScannerDelivery.currentBarcode;
                objScannerDelivery.scanProductBarcode();
            }
        }

        KC_value = '';

    },

    //******************************************************************************
    //
    decreaseQty: function(popId)
    {
        for(i=0;i<objScannerDelivery.products.length;i++)
        {
            var product = objScannerDelivery.products[i];
            if (product.pop_id == popId) {
                if (product.scanned_qty > 0) {
                    product.scanned_qty -= 1;
                    objScannerDelivery.showScannedProducts();
                }
            }
        }
    },

    //******************************************************************************
    //
    increaseQty: function(popId)
    {
        for(i=0;i<objScannerDelivery.products.length;i++)
        {
            var product = objScannerDelivery.products[i];
            if (product.pop_id == popId) {
                product.scanned_qty += 1;                
                objScannerDelivery.showScannedProducts();
            }
        }

    },

    //******************************************************************************
    //serialize products data
    serializeData: function()
    {
        var string = '';

        for(i=0;i<objScannerDelivery.products.length;i++)
        {
            var product = objScannerDelivery.products[i];
            if (product.scanned_qty > 0)
            {
                string += 'pop_id=' + product.pop_id + ';';
                string += 'scanned_qty=' + product.scanned_qty + ';';
                string += 'new_location=' + product.new_location + ';';
                string += 'new_barcode=' + product.new_barcode + ';';
                string += 'serials=' + product.serials + ';';
                string += '#';
            }
        }
       
        return string;
    },
    
    //******************************************************************************
    //
    translate: function(text) {
        try {
            if(Translator){
               return Translator.translate(text);
            }
        }
        catch(e){}
        return text;
    }

}