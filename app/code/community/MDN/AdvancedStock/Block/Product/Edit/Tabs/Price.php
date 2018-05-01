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
class MDN_AdvancedStock_Block_Product_Edit_Tabs_Price extends Mage_Adminhtml_Block_Widget_Form
{

    const decimalCountAfterComaForPriceDisplay = 2;

	/**
	 * Product get/set
	 *
	 * @var unknown_type
	 */
	private $_product = null;
	public function setProduct($Product)
	{
		$this->_product = $Product;
		return $this;
	}
	public function getProduct()
	{
		return $this->_product;
	}
	
	/**
	 * Constructeur
	 *
	 */
	public function __construct()
	{
		$this->_blockGroup = 'AdvancedStock';
        $this->_objectId = 'id';
        $this->_controller = 'product';       
        
		parent::__construct();

	    $this->setTemplate('AdvancedStock/Product/Edit/Tab/Price.phtml');
		
	}	

	/**
	 * Return tax rate to use in pricer
	 */
	public function getPricerTaxRate()
	{
		//todo: d�porter la config dans advanced stock
		return Mage::getStoreConfig('purchase/purchase_product/pricer_default_tax_rate');
	}
    
    /**
    * Return current product cost
    */
    public function getProductCost() 
    {
		return $this->getProduct()->getcost();
	}
    
    /**
    * Return current product price, overrided by current special price if active
    */
    public function getProductCurrentPrice() 
    {
        $price = 0;
        $product = $this->getProduct();
        if($product!=null && $product->getId()>0){
            $price = $product->getprice();
            //Manage special price case
            $specialPrice = $this->getProductCurrentSpecialPrice();
            if($specialPrice>0){
                $price = $specialPrice;
            }
        }
		return $price;
	}
    
    /**
    * Return product price (without checking special price)
    */
    public function getProductPrice() 
    {
        $price = 0;
        $product = $this->getProduct();
        if($product!=null && $product->getId()>0){
            $price = $product->getprice();            
        }
		return $price;
	}
    
    /**
    * Return current product price, overrided by current special price if active
    */
    public function getProductCurrentSpecialPrice() 
    {  
        $specialPriceActive = 0;
        $product = $this->getProduct();
        if($product!=null && $product->getId()>0){
            $specialPrice = $product->getspecial_price();
            //A special price could be active if at least : if the special price is defined AND the specialPrice is < of the real price
            if(!empty($specialPrice) && ($specialPrice < $product->getprice())) {
            
                //Then : the special price is valid if specialPriceFromDate <= $today and the end date is not <=today                
                $today = date('Y-m-d 00:00:00');
                
                $specialPriceFromDate = $product->getspecial_from_date();
                $beginDateIsValid = false;               
                if(!empty($specialPriceFromDate)){
                    if(strtotime($specialPriceFromDate)<=strtotime($today)){
                        $beginDateIsValid = true;
                    }
                }                
                
                $specialPriceToDate = $product->getspecial_to_date();
                $endDateIsValid = false;
                if(!empty($specialPriceToDate)){
                    if(strtotime($specialPriceToDate)>=strtotime($today)){
                        $endDateIsValid = true;
                    }
                    //If Both date are date defined and valid
                    if($beginDateIsValid && $endDateIsValid){
                        $specialPriceActive = $specialPrice;
                    }
                }else{
                    //Case If there is no end date defined
                    if($beginDateIsValid){
                        $specialPriceActive = $specialPrice;
                    }
                }               
            }           
        }
		return $specialPriceActive;
    }
    
    public function debugSpecialPrice(){
        $specialPriceFromDate = $this->getProduct()->getspecial_from_date();
        $specialPriceToDate = $this->getProduct()->getspecial_to_date();
        $today = date('Y-m-d 00:00:00');    
        return "specialPriceFromDate=".$specialPriceFromDate." today=".$today." strtotime(specialPriceFromDate)=".strtotime($specialPriceFromDate)." strtotime(today)=".strtotime($today).' result='.(strtotime($specialPriceFromDate)<=strtotime($today));
    }
    
    /**
     * Rouding constant for pricing display in price tab
     */
    public function getPriceRouding(){
        return self::decimalCountAfterComaForPriceDisplay;
    }
    
}
