//**********************************************************************
//
function unselectOtherDefaultSales(obj)
{
	//uncheck all other radio button for default sale column
	var inputs = document.getElementsByTagName("input");
	var i;
	for(i=0;i<inputs.length;i++)
	{
		if ((inputs[i].id != obj.id) && (inputs[i].id.indexOf('pp_is_default_sales') > 0))
			inputs[i].checked = false;
	}
}

//**********************************************************************
//
function unselectOtherDefaultPurchase(obj)
{
	//uncheck all other radio button for default sale column
	var inputs = document.getElementsByTagName("input");
	var i;
	for(i=0;i<inputs.length;i++)
	{
		if ((inputs[i].id != obj.id) && (inputs[i].id.indexOf('pp_is_default')) && (inputs[i].id.indexOf('pp_is_default_sales') == -1))
			inputs[i].checked = false;
	}
}