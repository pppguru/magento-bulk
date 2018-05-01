//******************************************************************************************************
//Display poroduct stock summary windows
function showProductStockSummary(url, productName)
{
	//retrieve content via ajax
	
	 var request = new Ajax.Request(
        url,
        {
            method: 'GET',
            onSuccess: function onSuccess(transport)
	        			{
	        				var content = transport.responseText;
	        				
							//display windows
							win = new Window({className: "alphacube", title: productName, width:800, height:400, destroyOnClose:true,closable:true,draggable:true, recenterAuto:true, okLabel: "OK"});
							win.setHTMLContent(content);
							win.showCenter();
	        			},
			onFailure: function onFailure(transport)
	        			{
							alert('An error occured : ' + url);
	        			}
        }
    );	
	
}

function showStockTab(tabContentId, tabId)
{
	document.getElementById('tab-content-stock').className = 'tab-content-invisible';
	document.getElementById('tab-content-salesorder').className = 'tab-content-invisible';
	document.getElementById('tab-content-purchaseorder').className = 'tab-content-invisible';

	document.getElementById('tab-stock').className = 'tab-unselected';
	document.getElementById('tab-salesorder').className = 'tab-unselected';
	document.getElementById('tab-purchaseorder').className = 'tab-unselected';

	
	document.getElementById(tabContentId).className = 'tab-content-visible';
	document.getElementById(tabId).className = 'tab-selected';
}
