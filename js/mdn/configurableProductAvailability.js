
//Function to update availability for configurable product
function updateAvailability(spConfig)
{
	
	//retrieve product id
	var productId;
	var possibleProductIds = null;
	productId = null;
    for(var i=spConfig.settings.length-1;i>=0;i--){
        var selected = spConfig.settings[i].options[spConfig.settings[i].selectedIndex];
        if(selected.config){
        	//if possible product ids is null, init array
        	if (possibleProductIds == null)
        	{
        		possibleProductIds = selected.config.products.toString().split(',');
        	}
        	else
        	{
        		//else, remove product ids that are not in twice array
        		var newProductIdsArray = selected.config.products.toString().split(',');
				possibleProductIds = union(possibleProductIds, newProductIdsArray);
        	}
        }
    }
    
    //define product id
    if (possibleProductIds.length == 1)
    	productId = possibleProductIds[0];
    
    //get product information
    var information;
    information = null;
    for (var i=0;i<spConfig.config.subProductsAvailability.length;i++)
    {
    	if (spConfig.config.subProductsAvailability[i].id == productId)
    		information = spConfig.config.subProductsAvailability[i];
    }
    
    //display information
    var mainDiv;
    mainDiv = document.getElementById('div_availability');
	if (information == null)
	{
		//if no information found, hide availability block
		mainDiv.style.display = '';
		mainDiv.innerHTML = 'Please select options';
	}
	else
	{
		//show availability block
		mainDiv.style.display = '';		
		
		//display informations
		mainDiv.innerHTML = information.availability;
	}
}

function union(t1, t2)
{
	var retour = new Array();
	var i1;
	var i2;
	for(i1=0;i1<t1.length;i1++)
	{
		for(i2=0;i2<t2.length;i2++)
		{
			if (t1[i1] == t2[i2])
				retour[retour.length] = t1[i1];
		}
	}
	return retour;
}
