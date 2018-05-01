var packing = Class.create();
packing.prototype = {

    //**********************************************************************************************************************************
    //initialize object
    initialize: function(orderInformationUrl, changeShippingMethodUrl, askForSerial, displayOnlyCurrentGroup){
        this.orderInformationUrl = orderInformationUrl;
        this.changeShippingMethodUrl = changeShippingMethodUrl;
        this.orderId = null;
        this.products = null;
        this.groupIds = null;
        this.currentGroupId = null;
        this.askForSerial = askForSerial;
        this.displayOnlyCurrentGroup = displayOnlyCurrentGroup;
        this.lastProductScanned = null;
    },

    //**********************************************************************************************************************************
    //
    waitForScan: function()
    {
        objPacking.showInstruction(objPacking.translate('Scan order to Pack'), false);

        document.onkeypress = handleKey;
        enableCatchKeys(null, 'objPacking.scanOrderBarcode();', 'objPacking.barcodeDigitScanned();');

    },

    //**********************************************************************************************************************************
    changeShippingMethod: function()
    {
        if (document.getElementById('change_shipping_method'))
        {
            var newMethod = document.getElementById('change_shipping_method').value;
            if (!newMethod)
                return false;
            var url = objPacking.changeShippingMethodUrl + 'new_method/' + newMethod + '/order_id/' + this.orderId;
            document.location.href = url;
        }
    },

    //**********************************************************************************************************************************
    //
    barcodeDigitScanned:function()
    {
        objPacking.showMessage(KC_value);
    },


    //******************************************************************************
    //
    showMessage: function(text, error)
    {
        if (error)
        {
            var audio = document.getElementById("audio");
            audio.play();
        }
        
        if (text == '')
            text = '&nbsp;';

        if (error)
            text = '<font color="red">' + text + '</font>';
        else
            text = '<font color="green">' + text + '</font>';

        document.getElementById('div_message').innerHTML = text;
        document.getElementById('div_message').style.display = '';
        objPacking.addToPackingLogs(text);
    },

    //******************************************************************************
    //
    hideMessage: function()
    {
        document.getElementById('div_message').style.display = 'none';
    },

    //Simulate an order or a barcode selection
    pushOrderOrBarcodeAsKeyEvent: function(orderId)
    {
        KC_value = orderId;
        objPacking.scanOrderBarcode();
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
    scanOrderBarcode: function()
    {
        //init vars
        var barcode = KC_value;
        KC_value = '';
        var url = objPacking.orderInformationUrl;
        url += 'barcode/' + barcode;

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
                        if (elementValues['redirectUrl']) {
                            window.location = elementValues['redirectUrl'];
                        }else{
                            objPacking.showMessage(elementValues['message'], true);
                        }
                    }
                    else
                    {
                        //display order information
                        objPacking.hideMessage();
                        objPacking.showInstruction(objPacking.translate('Please scan products'), false);
                        document.getElementById('div_main').innerHTML = elementValues['order_html'];

                        //init datas
                        objPacking.orderId = elementValues['order_id'];
                        objPacking.products = elementValues['products_json'];
                        objPacking.lastProductScanned = null;

                        //group
                        objPacking.groupIds = elementValues['group_ids'];
                        
                        if (objPacking.displayOnlyCurrentGroup)
                            objPacking.currentGroupId = objPacking.groupIds[0];
                        
                        document.onkeypress = handleKey;
                        enableCatchKeys(null, 'objPacking.scanProductBarcode();', 'objPacking.barcodeDigitScanned();');

                        document.getElementById('div_main').style.display = '';

                        //sync order list
                        document.getElementById('order_list').value = barcode;

                        objPacking.showMessage('');
                        
                        objPacking.updateQuantities();
                        objPacking.hideNonActiveGroups();

                    }

                },
                onFailure: function onFailure(transport)
                {
                    objPacking.showMessage(objPacking.translate('An error occured'), true);
                }
            }
            );
    },


    //******************************************************************************
    //
    scanProductBarcode: function()
    {
        //init vars
        var barcode = KC_value;
        KC_value = '';

        //get product id
        var productInformation = objPacking.getProduct(barcode, true);
        if (productInformation == null || barcode.length == 0)
            objPacking.showMessage(objPacking.translate('Unknown barcode ') + barcode, true);
        else
        {
            if (objPacking.displayOnlyCurrentGroup)
            {
                if (productInformation.group_id != objPacking.currentGroupId)
                {
                    objPacking.showMessage(objPacking.translate('This product does not belong to the current group !'), true);
                    return false;
                }
            }
            
            //check qty
            if (productInformation.qty_scanned == productInformation.qty)
            {
                objPacking.showMessage(objPacking.translate('Product quantity already scanned !'), true);
                return false;
            }

            //increment qty
            objPacking.lastProductScanned = productInformation;
            objPacking.showMessage(productInformation.name + objPacking.translate(' scanned'));
            productInformation.qty_scanned += 1;
            objPacking.updateQuantities();
            
            //ask for serial
            if (objPacking.askForSerial == 1)
            {
                objPacking.showMessage(objPacking.translate('Please scan serial number for ') + productInformation.name + objPacking.translate(' (press enter to skip)'));
                enableCatchKeys(null, 'objPacking.scanProductSerial();', 'objPacking.barcodeDigitScanned();');
            }
            
        }
    },
    
    /**
     *
     */
    scanProductSerial: function()
    {
        //init vars
        var serialNumber = KC_value;
        KC_value = '';
        
        if (serialNumber)
        {
            objPacking.showMessage(objPacking.translate('Serial number added : ') + serialNumber);
            var productId = objPacking.lastProductScanned.id;
            if (document.getElementById('serials_' + productId))
                document.getElementById('serials_' + productId).value += serialNumber + "\n";
        }
        else
        {
            objPacking.showMessage(objPacking.translate('No serial number saved'));
        }
        
        //wait for next product
        enableCatchKeys(null, 'objPacking.scanProductBarcode();', 'objPacking.barcodeDigitScanned();');
    },
    
    /**
     * 
     */
    decreaseProduct: function (productId)
    {
        //get product
        productInformation = objPacking.getProductFromId(productId);
        
        //update qty
        if (productInformation.qty_scanned > 0)
        {
            objPacking.showMessage(productInformation.name + objPacking.translate(' scanned - 1'));
            productInformation.qty_scanned -= 1;
            objPacking.updateQuantities();
        }
    },



    increaseProduct: function (productId)
    {
        //get product
        productInformation = objPacking.getProductFromId(productId);
        
        //update qty
        if (productInformation.qty_scanned < productInformation.qty)
        {
            objPacking.showMessage(productInformation.name + objPacking.translate(' scanned + 1'));
            productInformation.qty_scanned += 1;
            objPacking.updateQuantities();
        }
    },

    increaseProductToMax: function (productId)
    {
        //get product
        productInformation = objPacking.getProductFromId(productId);

        //update qty
        if (productInformation.qty_scanned < productInformation.qty)
        {
            objPacking.showMessage(productInformation.name + objPacking.translate(' scanned to max'));
            productInformation.qty_scanned = productInformation.qty;
            objPacking.updateQuantities();
        }
    },

    /**
     *
     */
    decreaseProductToMin: function (productId)
    {
        //get product
        productInformation = objPacking.getProductFromId(productId);

        //update qty
        if (productInformation.qty_scanned > 0)
        {
            objPacking.showMessage(productInformation.name + objPacking.translate(' scanned to min'));
            productInformation.qty_scanned = 0;
            objPacking.updateQuantities();
        }
    },


    //******************************************************************************
    //
    getProduct: function (barcode, skipProductFullStock)
    {
        var i;
        var productInformation = null;
        for(i=0;i<this.products.length;i++)
        {
            //skip product if doesnt belong to current group
            if (this.currentGroupId)
            {
                if (this.currentGroupId != this.products[i].group_id)
                    continue;
            }
            
            if ((this.products[i].barcode == barcode) || (this.products[i].additional_barcodes.indexOf(barcode) >= 0))
            {
                
                if ((this.products[i].qty_scanned == this.products[i].qty) && (skipProductFullStock))
                    continue;
                
                productInformation = this.products[i];
            }
        }
        return productInformation;
    },
    
    //******************************************************************************
    //
    getProductFromId: function (productId)
    {
        var i;
        var productInformation = null;
        for(i=0;i<this.products.length;i++)
        {
            if (this.products[i].id == productId)
            {
                productInformation = this.products[i];
            }
        }
        return productInformation;
    },
    
    //******************************************************************************
    //
    updateQuantities: function()
    {
        var i;
        var productInformation = null;
        for(i=0;i<this.products.length;i++)
        {
            productInformation = this.products[i];
            document.getElementById('qty_scanned_' + productInformation.id).innerHTML = productInformation.qty_scanned;

            var remainingQty = (productInformation.qty - productInformation.qty_scanned);
            var color = 'red';
            if (remainingQty == 0)
            {
                color = 'green';                
                document.getElementById('preparation_line_' + productInformation.id).style.backgroundColor = '#00CC33';
            }
            else
            {              
              document.getElementById('preparation_line_' + productInformation.id).style.backgroundColor = '#FFFFFF';
            }
            document.getElementById('qty_to_scan_' + productInformation.id).innerHTML =  '<font color="' + color + '">' + remainingQty + '</font>';
        }
        
        objPacking.displayGroupProgress();
    },
    
    //******************************************************************************
    //
    displayGroupProgress: function()
    {
        var i;
        for(i=0;i<objPacking.groupIds.length;i++)
        {
            var currentGroupId = objPacking.groupIds[i];
            var groupColor = '#00CC33';
            if (!objPacking.groupIsComplete(currentGroupId))
                groupColor = 'orange';
            if (document.getElementById('tr_header_group_' + currentGroupId))
                document.getElementById('tr_header_group_' + currentGroupId).style.backgroundColor = groupColor;
        }
    },
    
    //******************************************************************************
    //
    groupIsComplete: function(groupId)
    {
        var i;
        var productInformation = null;
        for(i=0;i<objPacking.products.length;i++)
        {
            productInformation = objPacking.products[i];
            if (productInformation.group_id == groupId)
            {
                var remainingQty = (productInformation.qty - productInformation.qty_scanned);

                if (remainingQty > 0)
                    return false;
            }
        }
        
        return true;
    },
    
    //******************************************************************************
    //
    hideNonActiveGroups: function()
    {
        //dont process if option is not enabled
        if (!objPacking.displayOnlyCurrentGroup)
            return false;
        
        //get all tr related to groups items
        var requiredClassName = 'group_' + objPacking.currentGroupId;
        var trs = $$(".preparation_group");
        var i;
        for(i=0;i<trs.length;i++)
        {
            if (trs[i].hasClassName(requiredClassName))
                trs[i].show();
            else
                trs[i].hide();
        }
        
    },

    //************************************************************************************
    //
    nextGroup: function()
    {
        //check if group is complete
        if (!objPacking.groupIsComplete(objPacking.currentGroupId))
        {
            alert('The current group is not complete !');
            return false;
        }
        
        //select next group
        var i;
        var currentGroupIndex = -1;
        for(i=0;i < objPacking.groupIds.length;i++)
        {
            if (objPacking.groupIds[i] == objPacking.currentGroupId)
                currentGroupIndex = i;
        }
        
        //if it is the last group
        if (currentGroupIndex == (objPacking.groupIds.length - 1))
        {
            alert('All groups are complete, you can commit the packing');
            return false;
        }
        else
        {
            objPacking.currentGroupId = objPacking.groupIds[currentGroupIndex + 1];
            objPacking.hideNonActiveGroups();
        }
        
    },

    //******************************************************************************
    //
    commit: function(askForWeight, weight, askForParcelCount)
    {
        //check that all products have been scanned in the requested qty
        var productInformation = null;
        for(i=0;i<this.products.length;i++)
        {
            productInformation = this.products[i];
            var remainingQty = (productInformation.qty - productInformation.qty_scanned);
            if (remainingQty > 0)
            {
                objPacking.showMessage(remainingQty + ' ' + productInformation.name + objPacking.translate(' are missing !'), true);
                return false
            }

        }
        
        //ask the weight
        if (askForWeight)
        {
            weight = prompt('Please confirm the weight', weight);
            if (!weight)
                return false;
        }
        
        //ask for parcel count
        var parcelCount = 1;
        if (askForParcelCount)
        {
            parcelCount = prompt('Please confirm the parcel count', parcelCount);
            if (!parcelCount)
                return false;
        }
        
        //update form items
        document.getElementById('order_id').value = objPacking.orderId;
        document.getElementById('weight').value = weight;
        document.getElementById('parcel_count').value = parcelCount;

        //merge all carriertemplatedata fields in a single one
        var carriertemplatedata = '';
        var all = document.getElementsByTagName("*");
        for (var i=0, max=all.length; i < max; i++) {
            if (all[i].id.indexOf('templatedata') > 0)
            {
                if (carriertemplatedata != '')
                    carriertemplatedata += ';';
                carriertemplatedata += all[i].id + '=' + all[i].value;
            }
        }
        document.getElementById('carriertemplatedata').value = carriertemplatedata;

        //append serials
        var serialsSerialized = '';
        for(i=0;i<this.products.length;i++)
        {
            productInformation = this.products[i];
            var serialTextareaId = 'serials_' + productInformation.id;
            if (document.getElementById(serialTextareaId))
            {
                serialsSerialized += productInformation.id + '=' + document.getElementById(serialTextareaId).value + ';';
            }
        }
        
        //submit form
        document.getElementById('serials').value = serialsSerialized;
        document.getElementById('frm_packing').submit();

        //lock button to avoid double click adn double shipment
        document.getElementById('packing_btn').disabled=true;

        //clean logs
        objPacking.resetPackingLogs();


    },

    //******************************************************************************
    //
    cancel: function()
    {
        document.location.href = document.location.href;
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
    },

    displayPackingLogs : function() {
      //document.getElementById('packing_log').style.display = 'block';
      var content = document.getElementById('packing_log').innerHTML;
      //display windows
      win = new Window({
          className: "alphacube",
          title: "History",
          width:800,
          height:600,
          destroyOnClose:true,
          closable:true,
          draggable:true,
          recenterAuto:true,
          okLabel: "OK"
      });
      win.setHTMLContent(content);
      win.showCenter();
    },
    
    addToPackingLogs : function(log) {      
      previousLog = document.getElementById('packing_log').innerHTML;
      document.getElementById('packing_log').innerHTML = previousLog + '<br>' + log;
    },

    resetPackingLogs : function() {
      document.getElementById('packing_log').innerHTML = '';
    }
}

function updateHorsGabarit(){

    var parcelType = document.getElementById("carriertemplatedata[parceltype]").value;
    var height = document.getElementById("carriertemplatedata[height]").value;
    var length = document.getElementById("carriertemplatedata[length]").value;
    var width = document.getElementById("carriertemplatedata[width]").value;
    var diam = document.getElementById("carriertemplatedata[diam]").value;
    var result = 0;

    if(parcelType == 1){
        if(length >=0 && width >=0 && height >=0){
            var totalDim = length + width + height;
            if(length <= 100 && width >= 11 && height >= 1 && length >= 16 && totalDim  <= 150){
                if(length > 100 && width > 100 && height > 100){
                    result = 1;
                }
            }else{
                if(totalDim > 200 || width < 11 || height < 1 || length < 16){
                    alert("Package does not meet Colissimo packages size requirements.");
                }
                result = 1;
            }
        }
    }

    if(parcelType == 2){
        if(length>0 && diam>0){
            var totalDim = length + diam * 2;
            if(length >= 16 && diam >= 5 && totalDim <= 150 && totalDim >= 26){
                return 1;
            }else{
                alert("Package does not meet Colissimo packages size requirements.");
            }
        }else{
            result = 1;
        }
    }
    document.getElementById("carriertemplatedata[gabarit]").selectedIndex = result;
}