//******************************************************************************
//Check stock movement creation
function createStockMovement()
{	
	//check if stock movement is possible
    var request = new Ajax.Request(
        smc_validation_url,
        {
            method: 'post',
            onSuccess: function onSuccess(transport)
		        			{
		        				elementValues = eval('(' + transport.responseText + ')');
		        				
		        				//if error, display message
		        				if (elementValues['error'] == 1)
		        				{
		        					alert(elementValues['message']);
		        				}
		        				else
		        				{
		        					//if OK, create stock movement
		        					commitStockMovement();
		        				}
		        			},
			onFailure: function onFailure(transport)
		        			{
								alert('An error occured !');
		        			},
            parameters: Form.serialize(document.getElementById('stock_movement_creation'))
        }
    );
	
}

//******************************************************************************
//commit stock movement
function commitStockMovement()
{
var request = new Ajax.Request(
        smc_commit_url,
        {
            method: 'post',
            onSuccess: function onSuccess(transport)
		        			{
		        				elementValues = eval('(' + transport.responseText + ')');
		        				
		        				//if error, display message
		        				if (elementValues['error'] == 1)
		        				{
		        					alert(elementValues['message']);
		        				}
		        				else
		        				{
		        					//if OK, refresh page
		        					document.location.href = document.location.href;
		        					
		        				}
		        			},
			onFailure: function onFailure(transport)
		        			{
								alert('An error occured !');
		        			},
            parameters: Form.serialize(document.getElementById('stock_movement_creation'))
        }
    );
}