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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Model_SupplierReturn extends Mage_Core_Model_Abstract
{
    const kReturnStatusNew            = 'new';
    const kReturnStatusInquiry        = 'inquiry';
    const kReturnStatusSentToSupplier = 'sent_to_supplier';
    const kReturnStatusComplete       = 'complete';

    protected $_supplier = null;
    protected $_product = null;
    protected $_history = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('ProductReturn/SupplierReturn');
    }

    public function getReturnStatuses()
    {
        return array(
            self::kReturnStatusNew            => Mage::helper('ProductReturn')->__(self::kReturnStatusNew),
            self::kReturnStatusInquiry        => Mage::helper('ProductReturn')->__(self::kReturnStatusInquiry),
            self::kReturnStatusSentToSupplier => Mage::helper('ProductReturn')->__(self::kReturnStatusSentToSupplier),
            self::kReturnStatusComplete       => Mage::helper('ProductReturn')->__(self::kReturnStatusComplete)
        );
    }

    protected function _beforeSave()
    {
        if (!$this->getId())
            $this->setrsr_created_at(date('Y-m-d H:i:s', mage::getModel('core/date')->timestamp()));
        else
            $this->setrsr_updated_at(date('Y-m-d H:i:s', mage::getModel('core/date')->timestamp()));

        if ($this->getOrigData('rsr_status') != $this->getrsr_status()) {

            switch ($this->getrsr_status()) {
                case self::kReturnStatusNew:
                    $this->setrsr_status_set_to_inquiry_at(null);
                    $this->setrsr_status_set_to_sent_at(null);
                    break;

                case self::kReturnStatusInquiry:
                    $this->setrsr_status_set_to_inquiry_at(date('Y-m-d H:i:s', mage::getModel('core/date')->timestamp()));
                    $this->setrsr_status_set_to_sent_at(null);
                    break;

                case self::kReturnStatusSentToSupplier:
                    $this->setrsr_status_set_to_sent_at(date('Y-m-d H:i:s', mage::getModel('core/date')->timestamp()));
                    break;
            }
        }

    }

    protected function _afterSave()
    {
        $history_message = '';
        if ($this->_hasChanged('rsr_status') && $this->getrsr_status() != self::kReturnStatusNew) {
            if ($history_message != '') $history_message .= "<br />";
            $history_message .= mage::helper('ProductReturn')->__('Changing status from ') . mage::helper('ProductReturn')->__($this->getOrigData('rsr_status')) . mage::helper('ProductReturn')->__(' to ') . mage::helper('ProductReturn')->__($this->getrsr_status());
        }

        if ($this->_hasChanged('rsr_reference')) {
            if ($history_message != '') $history_message .= "<br />";
            $history_message .= mage::helper('ProductReturn')->__('Updating reference');
        }

        if ($this->_hasChanged('rsr_supplier_reference')) {
            if ($history_message != '') $history_message .= "<br />";
            $history_message .= mage::helper('ProductReturn')->__('Updating supplier reference');
        }
        if ($this->_hasChanged('rsr_comments')) {
            if ($history_message != '') $history_message .= "<br />";
            $history_message .= mage::helper('ProductReturn')->__('Updating comments');
        }

        if ($history_message != '') {
            $this->addHistory($history_message);
        }

    }

    protected function _hasChanged($attribute)
    {
        if ($this->getData($attribute) != $this->getOrigData($attribute))
            return true;

        return false;
    }

    public function getSupplier()
    {
        if ($this->_supplier == null) {
            $supId           = $this->getrsr_supplier_id();
            $this->_supplier = mage::getModel('Purchase/Supplier')->load($supId);
        }

        return $this->_supplier;
    }

    public function getFormatedSupplierAddress()
    {
        $supId    = $this->getrsr_supplier_id();
        $supplier = $this->getSupplier();
        $address  = $supplier->getsup_name() .
            "\n\n" . $supplier->getsup_address1() .
            "\n" . $supplier->getsup_address2() .
            "\n\n" . $supplier->getsup_zipcode() . " " . $supplier->getsup_city() .
            "\n" . strtoupper($supplier->getsup_country());
        if ($supplier->getsup_tel() != null) {
            $address .= "\nT:" . $supplier->getsup_tel();
        }
        if ($supplier->getsup_fax() != null) {
            $address .= "\nF:" . $supplier->getsup_fax();
        }

        return $address;
    }

    public function sendToSupplier($comments)
    {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $templateId = Mage::getStoreConfig('productreturn/supplier_return/template_supplierreturn');
        $identityId = Mage::getStoreConfig('productreturn/emails/email_identity_supplierreturn');
        $data       = array(
            'subject'  => $this->__('Supplier Return Request'),
            'name'     => $this->getSupplier()->getsup_name(),
            'comments' => $comments
        );

        $log_msg = 'sending return request to supplier by email';

        //Définit si on doit joindre le pdf
        $Attachments           = array();
        $pdf                   = mage::getModel('ProductReturn/Pdf_SupplierReturn')->getPdf(array($this));
        $Attachment            = array();
        $Attachment['name']    = mage::helper('ProductReturn')->__('Supplier Return #') . $this->getrsr_id() . '.pdf';
        $Attachment['content'] = $pdf->render();
        $Attachments[]         = $Attachment;

        //envoi le mail
        if (!empty($templateId))
            Mage::getModel('core/email_template')
                ->setDesignConfig(array('area' => 'adminhtml' /*, 'store' => $this->getCustomer()->getStoreId()*/))
                ->sendTransactional(
                    $templateId,
                    $identityId,
                    $this->getSupplier()->getsup_rma_mail(),
                    $this->getSupplier()->getsup_name(),
                    $data,
                    null,
                    $Attachments);
        else
            throw new Exception('Template Transactionnel Empty');

        $this->addHistory($log_msg);

        return $this;

    }

    public function getProducts()
    {
        if ($this->_product == null) {
            $rsrId          = $this->getrsr_id();
            $this->_product = mage::getModel('ProductReturn/SupplierReturn_Product')->getCollection()->addFieldToFilter('rsrp_rsr_id', $rsrId);
        }

        return $this->_product;
    }

    public function getHistory()
    {
        if ($this->_history == null) {
            $rsrId = $this->getrsr_id();
            //todo : on ne trie pas dans les model
            $this->_history = mage::getModel('ProductReturn/SupplierReturn_History')->getCollection()->addFieldToFilter('rsrh_rsr_id', $rsrId);
        }

        return $this->_history;
    }

    public function addHistory($description)
    {
        $rsrId   = $this->getrsr_id();
        $history = Mage::getModel('ProductReturn/SupplierReturn_History');
        $history->setrsrh_rsr_id($rsrId)
            ->setrsrh_date(date('Y-m-d H:i:s', mage::getModel('core/date')->timestamp()))
            ->setrsrh_description($description);
        $history->save();

        return ($history);
    }

    //create a new supplier return, and return this.
    public function createSupplierReturn($supId)
    {
        $rsr = null;
        $rsr = mage::getModel('ProductReturn/SupplierReturn');
        $rsr->setrsr_supplier_id($supId)
            ->setrsr_status('new');
        $rsr->save();
        $rsr->setrsr_reference('SR_' . $supId . '_' . date('Ymd') . '_' . $rsr->getrsr_id());
        $rsr->save();

        return $rsr;
    }

    public function addProduct($rsrp_id)
    {
        $rsrp = null;
        $rsrp = mage::getModel('ProductReturn/SupplierReturn_Product')->load($rsrp_id);
        $rsrp->setrsrp_rsr_id($this->getrsr_id());
        $rsrp->setrsrp_status('Associated to supplier return');
        $rsrp->save();
    }

    public function removeProduct($rsrp_id)
    {
        $rsrp = null;
        $rsrp = mage::getModel('ProductReturn/SupplierReturn_Product')->load($rsrp_id);
        $rsrp->reset();
    }


    public function toggleStatusIfAllProductsProcessed()
    {
        //skip if already complete
        if ($this->getrsr_status() == self::kReturnStatusComplete)
            return;

        $hasUnprocessedProduct = false;

        foreach ($this->getProducts() as $product) {
            if ($product->getrsrp_status() == MDN_ProductReturn_Model_SupplierReturn_Product::kProductStatusPending OR $product->getrsrp_status() == MDN_ProductReturn_Model_SupplierReturn_Product::kProductStatusAssociated) {
                $hasUnprocessedProduct = true;
            }
        }
        if (!$hasUnprocessedProduct) {
            $this->setrsr_status(self::kReturnStatusComplete)->save();
        }
    }

    public function getTotalPurchasePrice()
    {
        $price = 0;
        $rsrId = $this->getrsr_id();
        $rsrps = $this->getProducts();
        foreach ($rsrps as $rsrp) {
            if ($rsrp->getPurchaseOrderItem()) {
                $price += $rsrp->getrsrp_purchase_price();
            }
        }

        return $price;
    }

}