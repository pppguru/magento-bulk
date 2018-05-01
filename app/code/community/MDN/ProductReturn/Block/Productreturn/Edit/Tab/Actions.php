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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order history tab
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_ProductReturn_Block_Productreturn_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
{

    private $_rma = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ProductReturn/Tab/Actions.phtml');
    }

    /**
     * return current rma
     *
     * @return unknown
     */
    public function getRma()
    {
        return mage::registry('current_rma');
    }

    //todo: deprecated
    public function getLinkActionAlreadyPerform()
    {
        die('getLinkActionAlreadyPerform deprecated');
        $actionName = '';
        $entityUrl  = '';
        $entityName = '';
        switch ($this->getRma()->getrma_action()) {
            case MDN_ProductReturn_Model_Rma::kActionExchange:
                $actionName = $this->__('Product exchange');
                $order      = mage::getModel('sales/order')->load($this->getRma()->getrma_action_order_id());
                $entityUrl  = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId()));
                $entityName = $this->__('Order #%s', $order->getIncrementId());
                break;
            case MDN_ProductReturn_Model_Rma::kActionProductReturn :
                $actionName = $this->__('Product returned');
                $order      = mage::getModel('sales/order')->load($this->getRma()->getrma_action_order_id());
                $entityUrl  = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId()));
                $entityName = $this->__('Order #%s', $order->getIncrementId());
                break;
            case MDN_ProductReturn_Model_Rma::kActionRefund :
                $actionName = $this->__('Order refunded');
                $creditmemo = mage::getModel('sales/order_creditmemo')->load($this->getRma()->getrma_action_order_id());
                $entityUrl  = $this->getUrl('adminhtml/sales_creditmemo/view', array('creditmemo_id' => $creditmemo->getId()));
                $entityName = $this->__('Credit Memo #%s', $creditmemo->getIncrementId());
                break;
        }

        return $actionName . ' : <a href="' . $entityUrl . '">' . $entityName . '</a>';
    }

    /**
     * Return payment methods
     *
     * @param unknown_type         $name
     * @param string|\unknown_type $value
     *
     * @return unknown
     */
    public function getPaymentMethodAsCombo($name, $value = '')
    {
        $displayDisabledPaymentMethods = mage::getStoreConfig('productreturn/product_return/display_disabled_payment_methods');

        if ($value == '')
            $value = mage::getStoreConfig('productreturn/product_return/default_payment_method');
        $retour = '<select name="' . $name . '" id="' . $name . '">';

        if ($displayDisabledPaymentMethods)
            $paymentmethods = Mage::getModel('Payment/config')->getAllMethods();
        else
            $paymentmethods = Mage::getModel('Payment/config')->getActiveMethods();

        $retour .= '<option value="" ></option>';
        foreach ($paymentmethods as $method) {
            $selected = '';
            if ($value == $method->getId())
                $selected = ' selected="selected" ';
            $retour .= '<option value="' . $method->getId() . '" ' . $selected . '>' . $method->getTitle() . '</option>';
        }
        $retour .= '</select>';

        return $retour;
    }

    /**
     * Return carriers list (shipping methods)
     *
     * @param unknown_type         $name
     * @param string|\unknown_type $value
     *
     * @return unknown
     */
    public function getCarriersAsCombo($name, $value = '')
    {
        $displayDisabledShippingMethods = mage::getStoreConfig('productreturn/product_return/display_disabled_payment_methods');

        if ($value == '')
            $value = mage::getStoreConfig('productreturn/product_return/default_shipment');

        $retour   = '<select name="' . $name . '" id="' . $name . '" style="width: 300px;">';
        $carriers = Mage::getStoreConfig('carriers', 0);

        foreach ($carriers as $key => $item) {

            if (!isset($item['model']))
                continue;

            if (Mage::getStoreConfigFlag('carriers/' . $key . '/active', 0) || $displayDisabledShippingMethods) {

                $instance = mage::getModel($item['model']);

                if ($item['model']) {
                    $model          = mage::getModel($item['model']);
                    try
                    {
                        $allowedMethods = $model->getAllowedMethods();
                        if ($allowedMethods) {
                            foreach ($allowedMethods as $methodKey => $method) {

                                $finalKey = $key . '_' . $methodKey;
                                $selected = ($value == $finalKey) ? ' selected="selected" ' : '';
                                $retour .= '<option value="' . $finalKey . '" ' . $selected . '>' . $instance->getConfigData('title') . ' - ' . $method . '</option>';

                            }

                        }

                    }
                    catch(Exception $ex)
                    {
                        Mage::logException($ex);
                    }
                }
            }
        }

        $retour .= '</select>';

        return $retour;
    }

    /**
     * Return html code to select refund action for product
     *
     */
    public function getRefundHtml($product)
    {

        //check if can refund
        if (($product->getqty_invoiced() - $product->getqty_refunded()) <= 0)
            return 'X';

        $name    = 'rad_action_' . $product->getrp_id();
        $onclick = "actionChanged(" . $product->getrp_id() . ", this)";
        $html    = '<input type="radio" id="' . $name . '" name="' . $name . '" value="refund" onclick="' . $onclick . '">';

        return $html;
    }

    /**
     *
     *
     */
    public function getExchangeHtml($product)
    {

        //check if can exchange
        if ($product->getqty_shipped() <= 0)
            return 'X';

        //radio button
        $name    = 'rad_action_' . $product->getrp_id();
        $onclick = "actionChanged(" . $product->getrp_id() . ", this)";
        $html    = '<input type="radio" id="' . $name . '" name="' . $name . '" value="exchange" onclick="' . $onclick . '"><br>';

        //add hidden field to store product id for exchange
        $name = 'hidden_exchange_' . $product->getrp_id();
        $html .= '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $product->getrp_product_id() . '">';

        //exchanged product
        $spanContent = '<span id="span_exchange_product_name_' . $product->getrp_id() . '">' . $product->getname() . '</span>';
        $spanContent .= ' <img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" onclick="selectExchangeProduct(' . $product->getrp_id() . ');">';
        $spanContent .= '<br>' . $this->__('Adjust price (incl tax)') . ' : <input type="textbox" size="5" id="exhange_price_adjustment_' . $product->getrp_id() . '" name="exhange_price_adjustment_' . $product->getrp_id() . '" value="+0" onkeyup="displayAjustPriceTextHelper(' . $product->getrp_id() . ');">';
        $spanContent .= '<br><span id="exhange_text_helper_adjustment_' . $product->getrp_id() . '" name="exhange_text_helper_adjustment_' . $product->getrp_id() . '"><span>';

        $html .= '<span id="span_exchange_' . $product->getrp_id() . '" style="display: none;">' . $spanContent . '</span>';

        return $html;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $product
     *
     * @return string
     */
    public function getNoActionHtml($product)
    {
        $name    = 'rad_action_' . $product->getrp_id();
        $onclick = "actionChanged(" . $product->getrp_id() . ", this)";
        $html    = '<input type="radio" id="' . $name . '" name="' . $name . '" value="noaction" checked onclick="' . $onclick . '">';

        return $html;
    }

    public function getReturnProductHtml($product)
    {

        //check if can exchange
        if ($product->getqty_shipped() <= 0)
            return 'X';

        $name    = 'rad_action_' . $product->getrp_id();
        $onclick = "actionChanged(" . $product->getrp_id() . ", this)";
        $html    = '<input type="radio" id="' . $name . '" name="' . $name . '" value="return" onclick="' . $onclick . '">';

        return $html;
    }

    /**
     * Enter description here...
     *
     */
    public function getProductDestinationHtml($product)
    {
        $name         = 'dest_' . $product->getrp_id();
        $retour       = '<select name="' . $name . '" id="' . $name . '" style="display: none">';
        $destinations = mage::getModel('ProductReturn/RmaProducts')->getDestinations();
        foreach ($destinations as $key => $label) {
            $retour .= '<option value="' . $key . '">' . $label . '</option>';
        }

        $retour .= '</select>';

        return $retour;
    }

    /**
     * Return url to display popup
     *
     */
    public function getProductExchangeSelectionPopup()
    {
        return $this->getUrl('adminhtml/ProductReturn_Admin/ProductExchangeSelectionPopup', array('rma_id' => $this->getRma()->getId(),  'rp_id' => 'XXX'));
    }

    /**
     * Enter description here...
     *
     * @param $product
     *
     * @return unknown
     */
    public function hasBeenProcessed($product)
    {
        return ($product->getrp_action_processed() == 1);
    }

    /**
     * @param MDN_AdvancedStock_Model_Sales_Order_Item $product
     * @return string $value
     */
    public function getProceedInformation($product)
    {
        $value = '';

        //add information about action
        switch ($product->getrp_action()) {
            case 'refund':
                $value = mage::helper('ProductReturn')->__('Refunded');
                break;
            case 'return':
                $value = mage::helper('ProductReturn')->__('Returned');
                break;
            case 'exchange':
                $value = mage::helper('ProductReturn')->__('Exchange');
                break;
        }

        $value .= ' (' . $product->getrp_associated_object() . ') ';

        //add information about destination
        $value .= ' - ' . mage::helper('ProductReturn')->__($this->getRpDestinationLabel($product));

        return $value;
    }

    /**
     * @param MDN_AdvancedStock_Model_Sales_Order_Item $product
     * @return string $label
     */
    public function getRpDestinationLabel($product){

        if(preg_match('#^warehouse_#', $product->getrp_destination())){

            $warehouseId = str_replace('warehouse_','',$product->getrp_destination());
            $label = $this->__('Back to warehouse '.Mage::getModel('AdvancedStock/Warehouse')->load($warehouseId)->getstock_name());

        }else{

            $label = $product->getrp_destination();

        }

        return $label;

    }

    /**
     * Enter description here...
     *
     */
    public function getProductName($product)
    {
        return mage::getModel('ProductReturn/RmaProducts')->getProductName($product);
    }

    /**
     * Return shipping amount
     *
     * @return <type>
     */
    public function getShippingAmountInclTax()
    {
        return $this->getRma()->getSalesOrder()->getshipping_incl_tax();
    }


    /**
     * Set if we can do online refund for order
     *
     * @return boolean
     */
    public function canRefundOnline()
    {
        $order   = $this->getRma()->getSalesOrder();
        $payment = $order->getPayment();

        if ($payment && $payment->canRefund()) {
            return true;
        }

        return false;
    }

    public function canShowTab()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/productreturn/customerreturn/view/processproducts');
    }

}
