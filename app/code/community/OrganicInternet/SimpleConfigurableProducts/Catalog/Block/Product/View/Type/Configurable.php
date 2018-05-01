<?php

/**
 * Fix extension conflict
 * 15 Dec 2016, Erik
 */
// class OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Type_Configurable
// 	extends Mage_Catalog_Block_Product_View_Type_Configurable
class OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Type_Configurable
	extends MDN_SalesOrderPlanning_Block_Front_Catalog_Product_View_Type_Configurable
{
	public function getAllowProducts() // Matthew for out of stock view.
	{
		if (!$this->hasAllowProducts()) {
			$products = array();
			$skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
			$allProducts = $this->getProduct()->getTypeInstance(true)
				->getUsedProducts(null, $this->getProduct());
			foreach ($allProducts as $product) {
//                if ($product->isSaleable() || $skipSaleableCheck) {
				if ($product->isInStock()) {    // Matthew: show all enabled associated products.
					$products[] = $product;
				}
			}

			$this->setAllowProducts($products);
		}
		return $this->getData('allow_products');
	}

	public function getJsonConfig()
	{
		$config = Zend_Json::decode(parent::getJsonConfig());    // from default
//        $config = Zend_Json::decode(CJM_CustomStockStatus_Block_Catalog_Product_View_Type_Configurable::getJsonConfig()); // by Matthew: Custom Stock extension conflict fix


		$childProducts = array();

		//Create the extra price and tier price data/html we need.
		foreach ($this->getAllowProducts() as $product) {
			$productId  = $product->getId();
			$childProducts[$productId] = array(
				"price" => $this->_registerJsPrice($this->_convertPrice($product->getPrice())),
				"finalPrice" => $this->_registerJsPrice($this->_convertPrice($product->getFinalPrice()))
			);

			if (Mage::getStoreConfig('SCP_options/product_page/change_name')) {
				$childProducts[$productId]["productName"] = $product->getName();
			}
			if (Mage::getStoreConfig('SCP_options/product_page/change_description')) {
				$childProducts[$productId]["description"] = $this->helper('catalog/output')->productAttribute($product, $product->getDescription(), 'description');
			}
			if (Mage::getStoreConfig('SCP_options/product_page/change_short_description')) {
				$childProducts[$productId]["shortDescription"] = $this->helper('catalog/output')->productAttribute($product, nl2br($product->getShortDescription()), 'short_description');
			}

			if (Mage::getStoreConfig('SCP_options/product_page/change_attributes')) {
				$childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
				$childProducts[$productId]["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
					->setProduct($product)
					->toHtml();
			}

			#if image changing is enabled..
			if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
				#but dont bother if fancy image changing is enabled
				if (!Mage::getStoreConfig('SCP_options/product_page/change_image_fancy')) {
					#If image is not placeholder...
					if($product->getImage()!=='no_selection') {
						$childProducts[$productId]["imageUrl"] = (string)Mage::helper('catalog/image')->init($product, 'image');
					}
				}
			}
		}

		//Remove any existing option prices.
		//Removing holes out of existing arrays is not nice,
		//but it keeps the extension's code separate so if Varien's getJsonConfig
		//is added to, things should still work.
		if (is_array($config['attributes'])) {
			foreach ($config['attributes'] as $attributeID => &$info) {
				if (is_array($info['options'])) {
					foreach ($info['options'] as &$option) {
						unset($option['price']);
					}
					unset($option); //clear foreach var ref
				}
			}
			unset($info); //clear foreach var ref
		}

		$p = $this->getProduct();
		$config['childProducts'] = $childProducts;
		if ($p->getMaxPossibleFinalPrice() != $p->getFinalPrice()) {
			$config['priceFromLabel'] = $this->__('Price From:');
		} else {
			$config['priceFromLabel'] = $this->__('');
		}
		//getUrl without extra parameter simply returns the non secure url and the ajax call fails when the client is in https.
		//So, we need to be abale to return secure url if the client is running on https. Mohin - May 28, 2016
		//$config['ajaxBaseUrl'] = Mage::getUrl('oi/ajax/');
		$config['ajaxBaseUrl'] = Mage::getUrl('oi/ajax/', array('_forced_secure' => Mage::app()->getStore()->isCurrentlySecure()));
		$config['productName'] = $p->getName();
		$config['description'] = $this->helper('catalog/output')->productAttribute($p, $p->getDescription(), 'description');
		$config['shortDescription'] = $this->helper('catalog/output')->productAttribute($p, nl2br($p->getShortDescription()), 'short_description');

		if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
			$config["imageUrl"] = (string)Mage::helper('catalog/image')->init($p, 'image');
		}

		$childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
		$config["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
			->setProduct($this->getProduct())
			->toHtml();

		if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
			if (Mage::getStoreConfig('SCP_options/product_page/change_image_fancy')) {
				$childBlock = $this->getLayout()->createBlock('catalog/product_view_media');
				$config["imageZoomer"] = $childBlock->setTemplate('catalog/product/view/media.phtml')
					->setProduct($this->getProduct())
					->toHtml();
			}
		}

		if (Mage::getStoreConfig('SCP_options/product_page/show_price_ranges_in_options')) {
			$config['showPriceRangesInOptions'] = true;
			$config['rangeToLabel'] = $this->__('to');
		}
		return Zend_Json::encode($config);
		//parent getJsonConfig uses the following instead, but it seems to just break inline translate of this json?
		//return Mage::helper('core')->jsonEncode($config);
	}
}
