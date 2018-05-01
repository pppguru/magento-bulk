
//*********************************************************************************************************
//Lie un fabricant au produit
function linkManufacturer()
{

	//recupere les infos
	var manufacturer_id = '';
	var product_id = '';
	manufacturer_id = document.getElementById('manufacturer').value;
	product_id = <?php echo $this->getProductId(); ?>;
	
	//d�finit l'url
	var url = '';
	url = '<?php echo $this->getUrl('Purchase/Products/LinkManufacturer', array('product_id' => 'XXX', 'manufacturer_id' => 'YYY')); ?>';
	url = url.replace('XXX', product_id);
	url = url.replace('YYY', manufacturer_id);
	
	//Appel l'url en ajax
	var request = new Ajax.Request(url,
	    {
	        method:'get',
	        onSuccess: function onSuccess(transport)
	        			{
	        				RefreshAssociatedManufacturers()
	        			},
	        onFailure: function onAddressFailure() {alert('error');}
	    }
    );
}

//************************************************************************************************************
//charge une association avec un manufacturer pour l'�diter
function loadManufacturer(Id)
{
	//d�finit l'url
	var url = '';
	url = '<?php echo $this->getUrl('Purchase/Products/GetManufacturerInformation', array('ppm_id' => 'XXX')) ?>';
	url = url.replace('XXX', Id);

	//Appel en ajax
	var request = new Ajax.Request(url,
	    {
	        method:'get',
	        onSuccess: function onSuccess(transport)
	        			{
	        				//recupere les donn�es
	        				elementValues = eval('(' + transport.responseText + ')');
	        				
	        				//Affiche les donn�es dans les champs
	        				document.getElementById('ppm_reference').value = elementValues['ppm_reference'];
	        				document.getElementById('ppm_comments').value = elementValues['ppm_comments'];
	        				document.getElementById('ppm_id').value = elementValues['ppm_id'];
	        				
	        				//Affiche le calque d'edition
	        				document.getElementById('div_manufacturer_edit').style.display = 'block';
	        			},
	        onFailure: function onAddressFailure() 
	        			{
							document.getElementById('div_manufacturer_edit').style.display = 'none';
	        				alert('error');
	        			}
	    }
    );
	
}


//************************************************************************************************************
//Rafraichit la liste des manifacturers associ�s
function RefreshAssociatedManufacturers()
{
	//definit l'url
	var url = '';
	url = '<?php echo $this->getUrl('Purchase/Products/getAssociatedManufacturers', array('product_id' => $this->getProductId())); ?>';
	
	//affiche l'url dans le cadre
	var updater = new Ajax.Updater('purchase_product_tabs_tab_manufacturers_content', url, {method: 'get'});
}

//************************************************************************************************************
//Supprime l'association avec un manufacturer
function removeManufacturer(id)
{
	if (confirm('<?php echo $this->__('Are you sure ?'); ?>'))
	{
		//d�finit l'url
		var url = '';
		url = '<?php echo $this->getUrl('Purchase/Products/DeleteAssociatedManufacturer', array('ppm_id' => 'XXX')); ?>';
		url = url.replace('XXX', id);
		
		//Appel en ajax
		var request = new Ajax.Request(url,
		    {
		        method:'get',
		        onSuccess: function onSuccess(transport)
		        			{
		        				//Rafraichit la page
								RefreshAssociatedManufacturers();
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
//sauvegarde les modifs sur le manufacturer
function SaveAssociatedManufacturer()
{
	//Save en ajax
	var url = '';
	url = '<?php echo $this->getUrl('Purchase/Products/SaveAssociatedManufacturer'); ?>';
	
    var request = new Ajax.Request(
        url,
        {
            method: 'post',
            onSuccess: function onSuccess(transport)
		        			{
		        				//Rafraichit la page
								RefreshAssociatedManufacturers();
		        			},
			onFailure: function onFailure(transport)
		        			{
		        				//Rafraichit la page
								alert('error');
		        			},
            parameters: Form.serialize(document.getElementById('form_associated_manufacturers'))
        }
    );
}



//*********************************************************************************************************
//Lie un fournisseur au produit
function linkSupplier()
{
	//recupere les infos
	var supplier_id = '';
	var product_id = '';
	supplier_id = document.getElementById('supplier').value;
	product_id = document.getElementById('product_id').value;
	
	//d�finit l'url
	var url = '';
	url = '<?php echo $this->getUrl('Purchase/Products/LinkSupplier', array('product_id' => 'XXX', 'supplier_id' => 'YYY')); ?>';
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
	        onFailure: function onAddressFailure() {alert('error');}
	    }
    );
}

//************************************************************************************************************
//Rafraichit la liste des suppliers associ�s
function RefreshAssociatedSuppliers()
{
	//definit l'url
	var url = '';
	url = '<?php echo $this->getUrl('Purchase/Products/getAssociatedSuppliers', array('product_id' => $this->getProductId())); ?>';
	
	//affiche l'url dans le cadre
	var updater = new Ajax.Updater('purchase_product_tabs_tab_suppliers_content', url, {method: 'get'});
}

//************************************************************************************************************
//Supprime l'association avec un supplier
function removeSupplier(id)
{
	if (confirm('<?php echo $this->__('Are you sure ?'); ?>'))
	{
		//d�finit l'url
		var url = '';
		url = '<?php echo $this->getUrl('Purchase/Products/DeleteAssociatedSupplier', array('pps_id' => 'XXX')); ?>';
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
//charge une association avec un supplier pour l'�diter
function loadSupplier(Id)
{
	//d�finit l'url
	var url = '';
	url = '<?php echo $this->getUrl('Purchase/Products/GetSupplierInformation', array('pps_id' => 'XXX')); ?>';
	url = url.replace('XXX', Id);

	//Appel en ajax
	var request = new Ajax.Request(url,
	    {
	        method:'get',
	        onSuccess: function onSuccess(transport)
	        			{
	        				//recupere les donn�es
	        				elementValues = eval('(' + transport.responseText + ')');
	        				
	        				//Affiche les donn�es dans les champs
	        				document.getElementById('pps_num').value = elementValues['pps_num'];
	        				document.getElementById('pps_comments').value = elementValues['pps_comments'];
	        				document.getElementById('pps_reference').value = elementValues['pps_reference'];
	        				document.getElementById('pps_price_position').value = elementValues['pps_price_position'];
	        				document.getElementById('pps_last_price').value = elementValues['pps_last_price'];
	        				
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
//sauvegarde les modifs sur le manufacturer
function SaveAssociatedSupplier()
{
	//Save en ajax
	var url = '';
	url = '<?php echo $this->getUrl('Purchase/Products/SaveAssociatedSupplier'); ?>';
	
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

function printLabels()
{
    var url = printLabelUrl;
    var qty = document.getElementById('label_count').value;
    url += 'qty/' + qty;
    alert(url);

}