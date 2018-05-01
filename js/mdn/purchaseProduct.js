//*********************************************************************************************************
//Link supplier to product
function linkSupplier()
{
    //recupere les infos
    var supplier_id = '';
    var product_id = '';
    supplier_id = document.getElementById('supplier').value;
    product_id = document.getElementById('product_id').value;

    //d�finit l'url
    var url = '';
    url = linkSupplierUrl;
    url = url.replace('XXX', product_id);
    url = url.replace('YYY', supplier_id);

    //Appel l'url en ajax
    var request = new Ajax.Request(url,
    {
        method:'get',
        onSuccess: function onSuccess(transport)
        {
            RefreshAssociatedSuppliers();
        },
        onFailure: function onAddressFailure() {
            alert('error');
        }
    }
    );
}

//************************************************************************************************************
//Refresh suppliers list
function RefreshAssociatedSuppliers()
{
    var url = refreshSupplierUrl;

    var divId = 'div_associated_suppliers' ;

    new Ajax.Updater(divId, url, {
        method: 'get',
        evalScripts:true
    });

}

//************************************************************************************************************
//Remove supplier (unlink)
function removeSupplier(id)
{
    if (confirm('Are you sure ?'))
    {
        //d�finit l'url
        var url = '';
        url = removeSupplierUrl;
        url = url.replace('XXX', id);

        //Appel en ajax
        var request = new Ajax.Request(url,
        {
            method:'get',
            onSuccess: function onSuccess(transport)
            {
                //Rafraichit la page
                RefreshAssociatedSuppliers();
            },
            onFailure: function onFailure()
            {
                alert('error');
            }
        }
        );
    }
}

//************************************************************************************************************
//Load supplier information
function loadSupplier(Id)
{
    var url = '';
    url = loadSupplierUrl;
    url = url.replace('XXX', Id);

    //Ajax call
    var request = new Ajax.Request(url,
    {
        method:'get',
        onSuccess: function onSuccess(transport)
        {
            //recupere les donn�es
            elementValues = eval('(' + transport.responseText + ')');

            //Affiche les donn�es dans les champs
            document.getElementById('sup_name').innerHTML = elementValues['sup_name'];
            document.getElementById('pps_product_name').value = elementValues['pps_product_name'];
            document.getElementById('sup_currency').innerHTML = elementValues['sup_currency'];
            document.getElementById('pps_num').value = elementValues['pps_num'];
            document.getElementById('pps_comments').value = elementValues['pps_comments'];
            document.getElementById('pps_reference').value = elementValues['pps_reference'];
            //document.getElementById('pps_price_position').value = elementValues['pps_price_position'];
            document.getElementById('pps_last_price').value = elementValues['pps_last_price'];
            document.getElementById('pps_last_unit_price').value = elementValues['pps_last_unit_price'];
            document.getElementById('pps_last_unit_price_supplier_currency').value = elementValues['pps_last_unit_price_supplier_currency'];
            document.getElementById('pps_quantity_product').value = elementValues['pps_quantity_product'];
            document.getElementById('pps_discount_level').value = elementValues['pps_discount_level'];
            document.getElementById('pps_can_dropship').value = elementValues['pps_can_dropship'];
            document.getElementById('pps_is_default_supplier').value = elementValues['pps_is_default_supplier'];
            document.getElementById('pps_supply_delay').value = elementValues['pps_supply_delay'];
            
            //Affiche le calque d'edition
            document.getElementById('div_supplier_edit').style.display = 'block';
        },
        onFailure: function onFailure()
        {
            alert('error');
            document.getElementById('div_supplier_edit').style.display = 'none';
        }
    }
    );

}

//************************************************************************************************************
//Save supplier information
function SaveAssociatedSupplier()
{
    //Save en ajax
    var url = '';
    url = saveSupplierUrl;

    var request = new Ajax.Request(
        url,
        {
            method: 'post',
            onSuccess: function onSuccess(transport)
            {
                //Rafraichit la page
                RefreshAssociatedSuppliers();
            },
            onFailure: function onFailure(transport)
            {
                //Rafraichit la page
                alert('error');
            },
            parameters: Form.serialize(document.getElementById('form_associated_suppliers'))
        }
        );
}