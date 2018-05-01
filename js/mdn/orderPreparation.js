//************************************************************************************************
//Save shipping informaiton in import tracking page
function submitShippingInformation()
{
	var url = urlSaveShippingInformation;
	var request = new Ajax.Request(
		url,
	    {
	        method:'POST',
	        onSuccess: function onSuccess(transport)
	        			{
	        				elementValues = eval('(' + transport.responseText + ')');
	        				alert(elementValues['message']);
	        			},
	        onFailure: function onFailure() 
	        			{
	        				alert('error');
	        			},
            parameters: Form.serialize(document.getElementById('form_data'))
	    }
    );
}

function changeItemQty(url, dropDown)
{
	url += 'new_qty/' + dropDown.value;

	var request = new Ajax.Request(
		url,
		{
			method:'GET',
			onSuccess: function onSuccess(transport)
			{
				elementValues = eval('(' + transport.responseText + ')');
				alert(elementValues['message']);
			},
			onFailure: function onFailure()
			{
				alert('error');
			}
		}
	);
}

//************************************************************************************************
//Set preparation warehouse
function changePreparationWarehouse()
{
	var url = urlChangePreparationWarehouse;
	url += 'warehouse_id/' + document.getElementById('preparation_warehouse').value;
	document.location.href = url;
}

//************************************************************************************************
//Set operator
function changeOperator()
{
	var url = urlChangeOperator;
	url += 'user_id/' + document.getElementById('operator').value;
	document.location.href = url;
}

//************************************************************************************************
//set current button
function selectThisButton(button)
{
    //remove class on all buttons in the page
    var buttons = document.getElementsByTagName('button');
    for (i=0; i < buttons.length; i++) 
    {
        buttons[i].className = 'scalable';
    }
    
    button.className = 'scalable current-button';
}