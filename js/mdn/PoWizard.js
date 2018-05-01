function sendPurchaseOrder(purchaseOrderId)
{
	//check date
	var supplyDate = document.getElementById('po_supply_date_' + purchaseOrderId).value;
	if (supplyDate == '')
	{
		alert('Please, fill supply date');
		return false;
	}
	
	//submit
	var url = submitPurchaseOrderUrl;
	url += 'po_id/' + purchaseOrderId + '/supply_date/' + supplyDate;
	document.location.href = url;
	
}