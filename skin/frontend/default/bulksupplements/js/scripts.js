jQuery(document).ready(function(){
	//=========footer links=======
	jQuery('.footer-block .col-1 ul:first li:first').addClass('first');
	jQuery('.footer-block .col-1 ul:first li').each(function(index){
		if(jQuery(this).html().indexOf('Advanced')!=-1){			
			jQuery(this).remove();
			return false;
		}
	});
  
	if(jQuery('.quick-access .links li:last').html().indexOf('out') != -1 ){
		jQuery('.footer-block .col-2 ul li').each(function(index){
			if(jQuery(this).html().indexOf('Your Account')!=-1){			
				jQuery(this).remove();
				return false;
			}
		});    
	}	
	//========//footer links======
    
	var fl=false, 
	fl2=false;
	jQuery('.block-cart-header .cart-content').hide();
	jQuery('.block-cart-header, .block-cart-header .cart-content').hover(function(){
		jQuery('.block-cart-header .cart-content').stop(true, true).slideDown(400);
	},function(){
		jQuery('.block-cart-header .cart-content').stop(true, true).delay(400).slideUp(300);
	});
	  
	jQuery('#custommenu a, #nav a, .breadcrumbs a').each(function() {
		aValue = jQuery(this).html();
		if (aValue.indexOf('Products by Category') != -1 || aValue.indexOf('Products by Goal') != -1) {
			jQuery(this).css('cursor', 'default');
			jQuery(this).attr('href', 'javascript:void(0)');
		}
	})
    
});
