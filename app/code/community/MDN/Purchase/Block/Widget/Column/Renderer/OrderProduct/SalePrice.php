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
class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_SalePrice extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract {

    public function render(Varien_Object $row) {
        $currencyRate = $this->getColumn()->getcurrency_change_rate();

        $specialPrice = $row->getspecial_price();
        $realPrice = $row->getsale_price();
        $displayPrice = $realPrice;
        $debug = "";
        if(!empty($specialPrice) && ($specialPrice < $realPrice)){
        
            $specialPriceFromDate = $row->getspecial_price_begin();
            $specialPriceToDate = Mage::getModel('Catalog/Product')->load($row->getpop_product_id())->getspecial_to_date();
            $today = date('Y-m-d 00:00:00');
            //$debug .= "<p>specialPriceFromDate=".$specialPriceFromDate."</p>  <p>specialPriceToDate=".$specialPriceToDate."</p>";
            //$debug .="<p>today=".$today."</p> strtotime(specialPriceFromDate)=".strtotime($specialPriceFromDate)." strtotime(today)=".strtotime($today).' result='.(strtotime($specialPriceFromDate)<=strtotime($today))."</p>";
            $beginDateIsValid = false;
            if(!empty($specialPriceFromDate)){
                if(strtotime($specialPriceFromDate)<=strtotime($today)){
                    $beginDateIsValid = true;
                }
            }
            
            $endDateIsValid = false;
            if(!empty($specialPriceToDate)){
                if(strtotime($specialPriceToDate)>=strtotime($today)){
                    $endDateIsValid = true;
                }
                //If Both date are date defined and valid
                if($beginDateIsValid && $endDateIsValid){
                    $displayPrice = $specialPrice;
                }
            }else{
                //Case If there is no end date defined
                if($beginDateIsValid){
                    $displayPrice = $specialPrice;
                }
            }
            
        } 
        
        $salePrice = number_format($this->convertSalePriceToExcludingTax($displayPrice), 2, '.', '');

        $html = '<input type="hidden" id="product_price_' . $row->getpop_num().'" value="'.$salePrice.'">'.$debug;               
        $html .= '<script>var product_price_' . $row->getpop_num() . ' = ' . $salePrice . ';</script>';
        $html .= '<div id="div_sellprice_' . $row->getpop_num() . '"></div>';

        return $html;
    }

    /**
     * Convert price
     */
    private function convertSalePriceToExcludingTax($price) {
        if (Mage::helper('tax')->priceIncludesTax()) {
            //if price includes tax, calculate excl tax price
            $taxRate = Mage::getStoreConfig('purchase/purchase_product/pricer_default_tax_rate');
            $price = $price / (1 + ($taxRate / 100));
            return $price;
        }
        else
            return $price;
    }

}