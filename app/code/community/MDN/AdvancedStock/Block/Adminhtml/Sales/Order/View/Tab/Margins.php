<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order information tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_AdvancedStock_Block_Adminhtml_Sales_Order_View_Tab_Margins
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{


    const DECIMAL_DEPTH = 2;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sales/order/view/tab/Margins.phtml');
    }
	
    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Retrieve source model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getOrder();
    }

    public function getItems()
    {
    	return $this->getOrder()->getAllItems();
    }

    public function getOrderMargin()
    {
        return Mage::getSingleton('AdvancedStock/Sales_Order_Margin')->getMargin($this->getOrder());
    }

    public function getOrderMarginPercent()
    {
        return Mage::getSingleton('AdvancedStock/Sales_Order_Margin')->getMarginPercent($this->getOrder());
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('AdvancedStock')->__('Margins');
    }

    public function getTabTitle()
    {
        return Mage::helper('AdvancedStock')->__('Margins');
    }

    public function canShowTab()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/erp_tabs/margins');
    }

    public function isHidden()
    {
        return false;
    }

    public function getOrderCurrencyCode(){
        return  Mage::app()->getLocale()->currency($this->getOrder()->getOrderCurrencyCode())->getSymbol();
    }

    private function formatNumberWithSymbol($number,$symbol){
        return number_format($number, self::DECIMAL_DEPTH).' '.$symbol;
    }


    public function getProductDisplayName($item){
        $displayName  = $item->getName();
        $displayName .= $item->getOrderItemOptions('<br>');
        $displayName .= ' ('.$item->getproduct_type().')';
        return $displayName;
    }

    public function formatMargin($marginPercentValue){
        return $this->formatNumberWithSymbol($marginPercentValue,'%');
    }

    public function formatPrice($price){
        return $this->formatNumberWithSymbol($price,$this->getOrderCurrencyCode());
    }
}