<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Purchase_Model_Constant extends Mage_Core_Model_Abstract
{
	private $_ProductManufacturerAttributeId = null;
    private $_ProductManufacturerAttributeCode = null;
	private $_ProductNameAttributeId = null;
	private $_OrderStatusAttributeId = null;
	private $_ProductStatusAttributeId = null;
	private $_ProductSmallImageAttributeId = null;
	private $_ProductPriceAttributeId = null;
	private $_ProductSpecialPriceAttributeId = null;
	private $_ProductSpecialPriceBeginDateAttributeId = null;
	private $_ProductEntityId = null;
	private $_ProductOrderedQtyAttributeId = null;
	private $_ProductReservedQtyAttributeId = null;
	private $_OrderPaymentValidatedAttributeId = null;
	private $_TablePrefix = null;
	
	public function getTablePrefix()
	{
		if ($this->_TablePrefix == null)
		{
			$this->_TablePrefix = (string)Mage::getConfig()->getTablePrefix();
		}
		return $this->_TablePrefix;
	}
	
	public function getProductEntityId()
	{
		if ($this->_ProductEntityId == null)
		{
			$this->_ProductEntityId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getId();
		}
		return $this->_ProductEntityId;
	}
	
	public function GetProductManufacturerAttributeId()
	{
		if ($this->_ProductManufacturerAttributeId == null)
		{
            $manufacturerAttributeId = Mage::getStoreConfig('purchase/supplyneeds/manufacturer_attribute');

            if($manufacturerAttributeId>0){
              $this->_ProductManufacturerAttributeId = $manufacturerAttributeId;
            }else{
              //default value is manufacturer attribute is not overridden by the configuration option
              $this->_ProductManufacturerAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'manufacturer')->getId();
            }
		}
		return $this->_ProductManufacturerAttributeId;
	}

    public function GetProductManufacturerAttributeCode()
	{
		if ($this->_ProductManufacturerAttributeCode == null)
		{
            $manufacturerAttributeId = $this->GetProductManufacturerAttributeId();

            if($manufacturerAttributeId>0){
              $entity = Mage::getModel('eav/entity_attribute')->load($manufacturerAttributeId);
              $this->_ProductManufacturerAttributeCode = $entity->getattribute_code();
            }
		}
		return $this->_ProductManufacturerAttributeCode;
	}
	
	public function GetProductNameAttributeId()
	{
		if ($this->_ProductNameAttributeId == null)
		{
			$this->_ProductNameAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name')->getId();
		}
		return $this->_ProductNameAttributeId;
	}
	
	public function GetProductManualSupplyNeedQtyAttributeId()
	{
		if ($this->_ProductNameAttributeId == null)
		{
			$this->_ProductNameAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'manual_supply_need_qty')->getId();
		}
		return $this->_ProductNameAttributeId;
	}

	public function GetOrderStatusAttributeId()
	{
		if ($this->_OrderStatusAttributeId == null)
		{
			$this->_OrderStatusAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('order', 'status')->getId();
		}
		return $this->_OrderStatusAttributeId;
	}
	
	public function GetProductStatusAttributeId()
	{
		if ($this->_ProductStatusAttributeId == null)
		{
			$this->_ProductStatusAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'status')->getId();
		}
		return $this->_ProductStatusAttributeId;
	}
	
	public function GetProductSmallImageAttributeId()
	{
		if ($this->_ProductSmallImageAttributeId == null)
		{
			$this->_ProductSmallImageAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'small_image')->getId();
		}
		return $this->_ProductSmallImageAttributeId;
	}
	
	public function GetProductPriceAttributeId()
	{
		if ($this->_ProductPriceAttributeId == null)
		{
			$this->_ProductPriceAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'price')->getId();
		}
		return $this->_ProductPriceAttributeId;
	}
    
    public function GetProductSpecialPriceAttributeId()
	{
		if ($this->_ProductSpecialPriceAttributeId == null)
		{
			$this->_ProductSpecialPriceAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'special_price')->getId();
		}
		return $this->_ProductSpecialPriceAttributeId;
	}
    
    public function GetProductSpecialPriceBeginDateAttributeId()
	{
		if ($this->_ProductSpecialPriceBeginDateAttributeId == null)
		{
			$this->_ProductSpecialPriceBeginDateAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'special_from_date')->getId();
		}
		return $this->_ProductSpecialPriceBeginDateAttributeId;
	}    
    
	
	public function GetProductOrderedQtyAttributeId()
	{
		if ($this->_ProductOrderedQtyAttributeId == null)
		{
			$this->_ProductOrderedQtyAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'ordered_qty')->getId();
		}
		return $this->_ProductOrderedQtyAttributeId;
	}

	public function GetProductReservedQtyAttributeId()
	{
		if ($this->_ProductReservedQtyAttributeId == null)
		{
			$this->_ProductReservedQtyAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'reserved_qty')->getId();
		}
		return $this->_ProductReservedQtyAttributeId;
	}
	
	public function GetOrderPaymentValidatedAttributeId()
	{
		if ($this->_OrderPaymentValidatedAttributeId == null)
		{
			$this->_OrderPaymentValidatedAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('order', 'payment_validated')->getId();
		}
		return $this->_OrderPaymentValidatedAttributeId;
	}
	
}
