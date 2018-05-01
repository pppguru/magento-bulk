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
 * @author     : Sylvain SALERNO
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Helper_SupplierReturn extends Mage_Core_Helper_Abstract
{

    protected $_newRsr = null;
    protected $_inquiryRsr = null;
    protected $_sentRsr = null;
    protected $_completeRsr = null;

    /*
     * Return an array with an entry for each supplier who have products in rsrp table
     */
    public function getPendingProductsPerSupplier()
    {
        //init
        $pendingProductsFinal                    = null;
        $pendingProducts                         = array();
        $pendingProducts                         = $this->getProductsPending();
        $pendingProductsFinal['total']['amount'] = 0;
        $pendingProductsFinal['total']['qty']    = 0;
        $pendingProductsFinal['invalid']         = array();
        foreach ($pendingProducts as $product) {
            if ($product->getrsrp_do_not_value_it() == 0) {
                $price = $product->getrsrp_purchase_price();
                if ($price == null && ($product->getrsrp_sup_id() == null or $product->getrsrp_sup_id() == 0)) {
                    $pendingProductsFinal['invalid'][] = $product->getrsrp_product_id();
                    $pendingProductsFinal['total']['qty']++;
                } else {
                    if ($product->getrsrp_sup_id() != null) {
                        if (!isset($pendingProductsFinal[$product->getrsrp_sup_id()]['supplier_name'])) {
                            $pendingProductsFinal[$product->getrsrp_sup_id()]['supplier_name'] = mage::getModel('Purchase/Supplier')->load($product->getrsrp_sup_id())->getsup_name();
                            $pendingProductsFinal[$product->getrsrp_sup_id()]['qty']           = 0;
                            $pendingProductsFinal[$product->getrsrp_sup_id()]['amount']        = 0;
                        }
                        $pendingProductsFinal[$product->getrsrp_sup_id()]['qty']++;
                        $pendingProductsFinal[$product->getrsrp_sup_id()]['amount'] += $price;
                        $pendingProductsFinal['total']['qty']++;
                        $pendingProductsFinal['total']['amount'] += $price;
                    }
                }
            }
        }
        //sort by amount
        foreach ($pendingProductsFinal as $k => $v) {
            if ($k != 'total' && $k != 'invalid')
                $temp_array[$k] = $v['amount'];
        }
        if (isset($temp_array))
            arsort($temp_array);
        //create final table
        $final_array = array();

        if (isset($temp_array)) {
            foreach ($temp_array as $k => $v) {
                $final_array[$k] = $pendingProductsFinal[$k];
            }
        }
        $final_array['invalid'] = $pendingProductsFinal['invalid'];
        $final_array['total']   = $pendingProductsFinal['total'];

        return $final_array;
    }

    /*
     * Return informations about products without sup_id in rsrp
     */
    public function getPendingProductsWithoutSupplier()
    {
        //init
        $pendingProducts        = array();
        $pendingProducts['qty'] = 0;

        $pendingProducts_temp = mage::getModel('ProductReturn/SupplierReturn_Product')
            ->getCollection()
            ->addFieldToFilter('rsrp_rsr_id', array("null" => ''))
            ->addFieldToFilter('rsrp_sup_id', array("null" => ''));

        foreach ($pendingProducts_temp as $product) {
            $pendingProducts['qty']++;
        }


        $pendingProducts_temp = mage::getModel('ProductReturn/SupplierReturn_Product')
            ->getCollection()
            ->addFieldToFilter('rsrp_rsr_id', array("null" => ''))
            ->addFieldToFilter('rsrp_sup_id', '0');

        foreach ($pendingProducts_temp as $product) {
            $pendingProducts['qty']++;
        }

        return $pendingProducts;
    }


    /*
     * return products pending list
     */
    public function getProductsPending()
    {
        $collection = null;
        $collection = Mage::getModel('ProductReturn/SupplierReturn_Product')
            ->getCollection()
            ->addFieldToFilter('rsrp_rsr_id', array("null" => ''))
            ->addFieldToFilter('rsrp_status', MDN_ProductReturn_Model_SupplierReturn_Product::kProductStatusPending);

        return $collection;
    }

    /*
     * return list of supplier return with status new
     */
    public function getNewSupplierReturns()
    {
        if ($this->_newRsr == null)
            $this->_newRsr = Mage::getModel('ProductReturn/SupplierReturn')->getCollection()->addFieldToFilter('rsr_status', 'new');

        return $this->_newRsr;
    }

    /*
     * return list of supplier return with status inquiry
     */
    public function getInquirySupplierReturns()
    {
        if ($this->_inquiryRsr == null)
            $this->_inquiryRsr = Mage::getModel('ProductReturn/SupplierReturn')->getCollection()->addFieldToFilter('rsr_status', 'inquiry');

        return $this->_inquiryRsr;
    }

    /*
     * return list of supplier returns with status sent_to_supplier
     */
    public function getSentSupplierReturns()
    {
        if ($this->_sentRsr == null)
            $this->_sentRsr = Mage::getModel('ProductReturn/SupplierReturn')->getCollection()->addFieldToFilter('rsr_status', 'sent_to_supplier');

        return $this->_sentRsr;
    }

    /*
     * return list of supplier returns with status complete
     */
    public function getCompleteSupplierReturns()
    {
        if ($this->_completeRsr == null)
            $this->_completeRsr = Mage::getModel('ProductReturn/SupplierReturn')->getCollection()->addFieldToFilter('rsr_status', 'complete');

        return $this->_completeRsr;
    }

    /*
     * return an array with the supplier list
     */
    public function getSupplierList()
    {
        $col    = Mage::getModel('Purchase/Supplier')->getCollection()->setOrder('sup_name', 'ASC');
        $retour = array();
        foreach ($col as $sup) {
            $retour[$sup->getsup_id()] = $sup->getsup_name();
        }

        return $retour;
    }

    /*
     * build the select for the po id selection
     */
    public function loadSelect($productId, $supplierId, $defaultPopId)
    {
        if ($supplierId) {
            $col = Mage::getModel('Purchase/OrderProduct')->getCollection()->addFieldToFilter('pop_product_id', $productId)->join('Purchase/Order', 'pop_order_num = po_num')->addFieldToFilter('po_sup_num', $supplierId)->setOrder('po_order_id', 'DESC');
            //construct html_response
            if ($col->count() == 0) {
                $html = '<span style="color: red">' . $this->__('There is no purchase order for this product and this supplier.') . '</span>';
            } else {
                $html = "";
                $html .= '<select name="rsrp_pop_id" id="select_popid">';
                $html .= '<option value="empty">Select a Purchase Order</option>';
                foreach ($col as $popop) {
                    $html .= '<option value="' . $popop->getpop_num() . '"';
                    if ($popop->getpop_num() == $defaultPopId) {
                        $html .= ' selected';
                    }
                    $html .= '>' . $popop->getpo_order_id() . '</option>';
                }
                $html .= '</select>';
            }
        } else {
            $html = '<span style="color: red">' . $this->__('You must select a supplier before to select a purchase order') . '</span>';
        }

        return $html;
    }


    public function loadCheckSerial($rsrpId, $productId, $serial)
    {
        $data = Mage::getModel('ProductReturn/SupplierReturn_Product')->load($rsrpId)->setrsrp_serial($serial)->getSupplierIdAndPopIdAndPurchasePrice();
        $html = '';
        $html .= '<span style="font-weight: bold">' . $this->__('Check Result: ') . '</span><br />';
        $html .= $this->__('Supplier: ');
        if ($data['sup_id'] == null)
            $html .= '<span style="color :red">' . $this->__('Unable to found Supplier') . '</span><br />';
        else {
            $supplier = mage::getModel('Purchase/Supplier')->load($data['sup_id'])->getsup_name();
            $html .= $supplier . '<br />';
        }
        $html .= $this->__('Purchase Order: ');
        if ($data['pop_id'] == null)
            $html .= '<span style="color: red">' . $this->__('Unable to found Purchase Order') . '</span><br />';
        else {
            $purchaseOrder = Mage::getModel('Purchase/OrderProduct')->load($data['pop_id'])->getPurchaseOrder()->getpo_order_id();
            $html .= $purchaseOrder . '<br />';
        }
        $html .= $this->__('Purchase Price: ');
        if ($data['purchase_price'] == null)
            $html .= '<span style="color:red">' . $this->__('Unable to found price') . '</span>';
        else
            $html .= round($data['purchase_price'], 2) . ' ' . mage::getStoreConfig('currency/options/base');

        return $html;
    }

    public function getFormatedPendingProductsCsv()
    {
        $return = "\"" . Mage::Helper('ProductReturn')->__('ID') . "\",\"" . Mage::Helper('ProductReturn')->__('SKU') . "\",\"" . Mage::Helper('ProductReturn')->__('Name') . "\",\"" . Mage::Helper('ProductReturn')->__('Serial') . "\",\"" . Mage::Helper('ProductReturn')->__('Supplier Name') . "\",\"" . Mage::Helper('ProductReturn')->__('Supplier SKU') . "\",\"" . Mage::Helper('ProductReturn')->__('Supplier Reference') . "\",\"" . Mage::Helper('ProductReturn')->__('Purchase Price') . "\",\"" . Mage::Helper('ProductReturn')->__('Comments') . "\"\n";
        $col    = $this->getProductsPending();
        foreach ($col as $product) {
            $supname  = '';
            $supsku   = '';
            $supref   = '';
            $supplier = $product->getSupplier();
            if ($supplier) {
                $supname = $supplier->getsup_name();
            }
            $pps = mage::getModel('Purchase/ProductSupplier')->getCollection()->addFieldToFilter('pps_product_id', $product->getrsrp_product_id())->addFieldToFilter('pps_supplier_num', $product->getrsrp_sup_id())->getFirstItem();
            if ($pps) {
                $supref = $pps->getpps_reference();
            }
            if ($product->getPurchaseOrderItem()) {
                $supsku = $product->getPurchaseOrderItem()->getpop_supplier_ref();
            }

            $return .= "\"" . $product->getrsrp_id() . "\",\"" . $product->getrsrp_product_sku() . "\",\"" . $product->getrsrp_product_name() . "\",\"" . $product->getrsrp_serial() . "\",\"" . $supname . "\",\"" . $supsku . "\",\"" . $supref . "\",\"" . $product->getrsrp_purchase_price() . "\",\"" . $product->getrsrp_comments() . "\"\n";
        }

        return $return;
    }
}
