function BSProductWeightQty(){
	var self = this;
	self.products = ['10kg', '20kg', '25kg', '50lbs'];
	self.productHtml = {};	
	self.unitCode = '';
	self.weight = 0;	
	self.initializeAction = function(){
		//handle top right mini popup cart first		
		jQuery('.block-cart-header ol.mini-products-list li').each(function(index){		
			var el = jQuery(this);
			var optionText = self.getOptionText(el, '.product-name a');
			if(optionText==''){
				return;
			}						
			self.setAttributes(self.getWeightAttribute(optionText));
			if(self.isActionRequired()){
				self.redesignMiniCartRow(el);					
			}
			else{
				self.resetWeightAttrs();
			}
			
		});
		//handle side bar mini cart
		jQuery('#cart-sidebar li').each(function(index){			
			var el = jQuery(this);
			var optionText = self.getOptionText(el, '.product-name a');
			if(optionText==''){
				return;
			}			
			self.setAttributes(self.getWeightAttribute(optionText));
			if(self.isActionRequired()){
				var qty = el.find('p.qty').html();
				el.find('p.qty').html((qty*self.weight)+self.unitCode);
			}
			else{
				self.resetWeightAttrs();
			}			
		});
		//Handle shopping cart page
		if(window.location.href.indexOf('/checkout/cart') >= 0){
			var count = 0;
			jQuery('#shopping-cart-table tr').each(function(index){			
				var el = jQuery(this);
				var optionText = el.find('.item-options dd').html();
				if(self.isNullOrEmpty(optionText)){
					return;
				}				
				
				count++;
				var qtyBoxId = 'qty'+count;
				el.find('.input-text.qty').attr('id', qtyBoxId);					
				self.setAttributes(self.getWeightAttribute(optionText));					
				if(self.isActionRequired()){
					self.redesignCartRow(el, count, qtyBoxId);					
				}
				
			});
		}		
		else{
			//This is product details page
			jQuery('.select-buttons li').each(function(index){
				var el = jQuery(this);
				var child = el.children();				
				self.setAttributes(self.getWeightAttribute(child.text()));
				if(self.isActionRequired()){
					self.productHtml[self.getWeightAttribute(child.text())] = {'spin':self.getWeightQtySpinnerHtml()};
					child.click(function(){						
						self.setWeightQtyFeature(self.getWeightAttribute(child.text()));
					});
				}
				else{
					child.click(function(){						
						self.toggleChanges();
					});					
				}
			});
			jQuery('.add-to-cart #qty').on('input propertychange paste', function() {	
				self.updatePrice();
			});
		}
	};
	self.redesignOneStepCheckoutItemRows = function(){
		jQuery('table.onestepcheckout-summary tr').each(function(index){
			//alert(index);
			var el = jQuery(this);
			//alert(el.find('td.name').html());
			var optionText = self.getOptionText(el, 'td.name');
			if(optionText==''){
				return;
			}										
			self.setAttributes(self.getWeightAttribute(optionText));
			if(self.isActionRequired()){
				var qty = el.find('span.item-qty').html();
				el.find('span.item-qty').html((qty*self.weight)+self.unitCode);
				el.find('span.item-qty').css('padding', '0px 5px');
			}
			else{
				self.resetWeightAttrs();
			}
		});
	}
	self.redesignCartRow = function(el, counter, qtyBoxId){
		//Hide original QTY text box
		jQuery('#'+qtyBoxId).css('display', 'none');
		
		//Display Note
		var noteHtml = '<dl style="margin-left:100px; color:red;">Note: This Product is displayed in total '+self.unitCode.toUpperCase()+', not units.</dl>';
		jQuery(noteHtml).insertAfter(el.find('.product-name'));
		
		//Display spinner
		jQuery(self.getCartWeightQtySpinnerHtml(counter)).insertAfter(jQuery('#'+qtyBoxId));		
		
		//Calculate price per kg or lbs and display in the card in price per unit column
		var price = el.find('.cart-price span').first().html();
		var pricePerKg = self.roundDecimal(price.substring(1)/(self.weight));
		el.find('.cart-price span').first().html('$'+pricePerKg+' / '+self.unitCode);
	};
	self.redesignMiniCartRow = function(el){
		var qty = el.find('span.qty').html();
		var price = el.find('span.price').html();
		var pricePerUnitCode = self.roundDecimal(price.substring(1)/(self.weight));
		el.find('span.qty').html(qty*self.weight);
		el.find('span.price').html('$'+pricePerUnitCode+' / '+self.unitCode);
	};
	self.setWeightQtyFeature = function(weightAttr){
		jQuery('.add-to-cart label').css('display', 'none');
		jQuery('.add-to-cart #qty').css('display', 'none');
		self.clearOld();		
		jQuery('.add-to-cart').append(self.productHtml[weightAttr]['spin']);		
	};
	self.toggleChanges = function(){
		self.clearOld();
		jQuery('.add-to-cart label').css('display', 'block');
		jQuery('.add-to-cart #qty').css('display', 'block');
		jQuery('.add-to-cart #qty').val(1);
	};
	self.clearOld = function() {
		jQuery('.qty-spin-caution').remove();
		jQuery('.qty-spiner').remove();
	};
	self.resetWeightAttrs = function(){
		self.weight = 0;
		self.unitCode = '';
	};
	self.isNullOrEmpty = function(string){
		var found = false;
		if(string == null || string ==''){
			self.resetWeightAttrs();
			found =	true;
		}
		return found;
	};
	self.getOptionText = function(el, selector){
		var productName = el.find(selector).html();
		if(self.isNullOrEmpty(productName)){
			return '';
		}
		
		var optionText = productName.split(' - ')[1];
		if(self.isNullOrEmpty(optionText)){
			return '';
		}
		return optionText;				
	};	
	self.getWeightQtySpinnerHtml = function(){
		var spinnerId = 'txtQtyWeight';
		var qtyBoxId = '.add-to-cart #qty';
		var html = "<div class='qty-spiner'>"+
					"<span class='weight-text'>Qty:</span>"+	
					"<span><input type='text' value='"+self.weight+"' class='qty-size' id='"+spinnerId+"' onblur='customProductWeightQty.updateQtyAndPrice(3,"+self.weight+",\""+spinnerId+"\",\""+qtyBoxId+"\")'/></span>"+
					"<span class='spin'>"+							
						"<div class='button up size' onclick='customProductWeightQty.updateQtyAndPrice(1,"+self.weight+",\""+spinnerId+"\",\""+qtyBoxId+"\")'></div>"+
						"<div class='button down size' onclick='customProductWeightQty.updateQtyAndPrice(2,"+self.weight+",\""+spinnerId+"\",\""+qtyBoxId+"\")'></div>"+
					"</span>"+
					"<span class='kg-text'>"+self.unitCode+"</span>"+
				"</div>";
		return html;
	};
	self.getCartWeightQtySpinnerHtml = function(counter){
		var value = self.weight*jQuery('#qty'+counter).val();
		var spinnerId = 'txtQtyWeight'+counter;
		var qtyBoxId = '#qty'+counter;
		var html = "<div class='qty-spiner' style='width:57px;'>"+					
					"<span><input type='text' value='"+value+"' class='qty-size' id='"+spinnerId+"' onblur='onblur='customProductWeightQty.calculateQty(3,"+self.weight+",\""+spinnerId+"\",\""+qtyBoxId+"\")'/></span>"+
					"<span class='spin'>"+							
						"<div class='button up size' onclick='customProductWeightQty.calculateQty(1,"+self.weight+",\""+spinnerId+"\",\""+qtyBoxId+"\")'></div>"+
						"<div class='button down size' onclick='customProductWeightQty.calculateQty(2,"+self.weight+",\""+spinnerId+"\",\""+qtyBoxId+"\")'></div>"+
					"</span>"+
				"</div>";
		return html;
	};
	self.isActionRequired = function(){
		var isFound = false;		
		var weightAttr = self.weight + self.unitCode;		
		for(var i=0; i<self.products.length; i++){
			if(self.products[i]==weightAttr){
				//console.debug(weightAttr);
				isFound = true;				
				break;
			}
		}
		return isFound;
	};
	self.setAttributes = function(weightAttr){		
		self.weight = weightAttr.substring(0,2);
		var code = weightAttr.substring(2,4);
		//console.debug(weightAttr);
		if( code == 'kg' || code == 'lbs'){			
			self.unitCode = code;
		}
		else{
			self.unitCode = '';
		}
	};
	self.getWeightAttribute = function(optionText){
		return optionText.substring(0,4).toLowerCase();		
	};	
	self.updateQtyAndPrice = function(actionType, productType, spinnerId, qtyBoxId){
		self.calculateQty(actionType, productType, spinnerId, qtyBoxId);
		self.updatePrice();
	};	
	self.calculateQty = function(actionType, productType, spinnerId, qtyBoxId){
		productType = parseInt(productType, 10);
		var weight = parseInt(jQuery('#'+spinnerId).val(),10);
		var qty = parseInt((weight/productType),10);
		qty = (isNaN(qty) || qty<1) ? 1 : qty; 
		switch(actionType){
			case 1:					
				qty += 1;					
				break;
			case 2:					
				if(qty>1){
					qty -= 1;
				}
				break;
			default:					
				break;
		}
		weight = qty*productType;
		jQuery('#'+spinnerId).val(weight);
		jQuery(''+qtyBoxId).attr('value', qty);
	};
	self.updatePrice = function(){
		var productId = jQuery('input:hidden[name=product]').val();
		var qty = jQuery('.add-to-cart #qty').val();
		var data = 'id='+productId+'&qty='+qty;
		var ajaxUrl = window.location.origin+'/catalog/product/getPriceByQty/';
		jQuery.ajax({
			url : ajaxUrl,
			dataType : 'json',
			type : 'post',
			data : data,
			success : function(data) {				
				jQuery('.price-box span.price').html(data);				
			}
		});
	};
	self.roundDecimal = function(value){
		return Math.round(value*100)/100;
	};
}
var customProductWeightQty = null;
jQuery( document ).ready(function() {
	customProductWeightQty = new BSProductWeightQty();
	customProductWeightQty.initializeAction();
});