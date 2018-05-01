<?php
class Mage_Checkout_Block_Cart_Header extends Mage_Checkout_Block_Cart_Sidebar
{
	/**
	 * Class constructor
	 * Matthew
	 */
	public function __construct()
	{
		parent::__construct();
		$this->addItemRender('default', 'checkout/cart_item_renderer', 'checkout/cart/sidebar/default_header.phtml');
	}
}
