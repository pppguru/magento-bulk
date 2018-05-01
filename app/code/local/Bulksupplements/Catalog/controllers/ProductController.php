<?php
require_once 'Mage/Catalog/controllers/ProductController.php';
class Bulksupplements_Catalog_ProductController extends Mage_Catalog_ProductController
{
	public function viewAction()
	{
		$this->autoLogin(); // Matthew

		// Get initial data from request
		$categoryId = (int) $this->getRequest()->getParam('category', false);
		$productId  = (int) $this->getRequest()->getParam('id');
		$specifyOptions = $this->getRequest()->getParam('options');

		// Prepare helper and params
		$viewHelper = Mage::helper('catalog/product_view');

		$params = new Varien_Object();
		$params->setCategoryId($categoryId);
		$params->setSpecifyOptions($specifyOptions);

		// Render page
		try {
			$viewHelper->prepareAndRender($productId, $this, $params);
		} catch (Exception $e) {
			if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
				if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
					$this->_redirect('');
				} elseif (!$this->getResponse()->isRedirect()) {
					$this->_forward('noRoute');
				}
			} else {
				Mage::logException($e);
				$this->_forward('noRoute');
			}
		}
	}

	/* Customer auto login : Matthew */
	function autoLogin() { // Matthew
		$session = Mage::getSingleton('customer/session');
		$id = (int) trim($this->getRequest()->getParam('key'));
		$email = trim($this->getRequest()->getParam('email'));
		try{
			if(!empty($id) && !empty($email)){
				$customer = Mage::getModel('customer/customer')->load($id);
				if ($email == $customer->getEmail())
					$session->setCustomerAsLoggedIn($customer);
			}else{
//				throw new Exception ($this->__('The login attempt was unsuccessful. Some parameter is missing'));
			}
		}catch (Exception $e){
			$session->addError($e->getMessage());
		}
//        $this->_redirect('customer/account');
	}
	
	public function getPriceByQtyAction(){
		$productId  = (int) $this->getRequest()->getParam('id');
		$qty = (int) $this->getRequest()->getParam('qty');
		$product = Mage::getModel('catalog/product')->load($productId);
		//$tierPrice = $product->getTierPrice();
		$tierPrice = Mage_Catalog_Block_Product_Abstract::getTierPrices($product);
		//$groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		//$gprice = $product->getData('group_price');
		$productPrice = 0;		
		if(count($tierPrice>0)){		
			$tiers = array();
			foreach($tierPrice as $tp){
				$tiers[] = (int)$tp['price_qty'];
			}
			
			$myTierValue = max(array_intersect($tiers, range(0, $qty)));
			
			foreach($tierPrice as $tp){
				if($myTierValue == $tp['price_qty']){
					$productPrice = $tp['price'];
					break;
				}
			}
		}		
		if($productPrice==0){
			$productPrice = $product->getPrice();
			$groupPrice = $product->getGroupPrice();
			if($productPrice > $groupPrice){
				$productPrice = $groupPrice;
			}			
		}
		$totalPrice = $productPrice*$qty;
		$formattedPrice = Mage::helper('core')->currency($totalPrice, true, false);
		echo Mage::helper('core')->jsonEncode($formattedPrice);
	}
	
}
