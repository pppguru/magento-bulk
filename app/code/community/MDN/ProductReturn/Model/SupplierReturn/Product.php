<?php

class MDN_ProductReturn_Model_SupplierReturn_Product extends Mage_Core_Model_Abstract
{

    const kProductStatusPending          = 'Pending';
    const kProductStatusAssociated       = 'Associated_to_supplier_return';
    const kProductStatusRefunded         = 'Refunded';
    const kProductStatusStandardExchange = 'Standard_Exchange';
    const kProductStatusCreditMemo       = 'Credit_memo';
    const kProductStatusDestroy          = 'Destroy';


    protected $_supplierReturn = null;


    public function _construct()
    {
        parent::_construct();
        $this->_init('ProductReturn/SupplierReturn_Product');
    }


    public function reset()
    {
        $this->setData('rsrp_rsr_id', new Zend_Db_Expr('null'));
        $this->setrsrp_status(self::kProductStatusPending);
        $this->save();
    }


    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->setrsrp_creation_date(date('Y-m-d H:i:s', mage::getModel('core/date')->timestamp()));
            $this->setrsrp_status(self::kProductStatusPending);
        } else
            $this->setrsr_updated_at(date('Y-m-d H:i:s', mage::getModel('core/date')->timestamp()));

        //si le serial a changé , on va chercher les infos sur le fournisseur et le pop_id
        if ($this->getOrigData('rsrp_serial') != $this->getrsrp_serial()) {
            $data = $this->getSupplierIdAndPopIdAndPurchasePrice();
            if ($data['sup_id'] != null)
                $this->setrsrp_sup_id($data['sup_id']);

            if ($data['pop_id'] == null)
                $data['pop_id'] = 0;

            $this->setrsrp_pop_id($data['pop_id']);

            if ($data['purchase_price'] != null)
                $this->setrsrp_purchase_price($data['purchase_price']);
        } //si le serial n'a pas changé mais que le numéro de commande a changé on va voir si on peut récupérer un prix
        else if ($this->getOrigData('rsrp_pop_id') != $this->getrsrp_pop_id()) {
            $purchase_price = $this->getPurchasePriceByPopId();
            if ($purchase_price > 0) {
                $this->setrsrp_purchase_price($purchase_price);
            }
        }
    }


    /*
     * return statuses array
     */
    public function getStatuses()
    {
        return array(
            self::kProductStatusPending          => Mage::helper('ProductReturn')->__(self::kProductStatusPending),
            self::kProductStatusRefunded         => Mage::helper('ProductReturn')->__(self::kProductStatusRefunded),
            self::kProductStatusAssociated       => Mage::helper('ProductReturn')->__(self::kProductStatusAssociated),
            self::kProductStatusStandardExchange => Mage::helper('ProductReturn')->__(self::kProductStatusStandardExchange),
            self::kProductStatusCreditMemo       => Mage::helper('ProductReturn')->__(self::kProductStatusCreditMemo),
            self::kProductStatusDestroy          => Mage::helper('ProductReturn')->__(self::kProductStatusDestroy)
        );
    }


    /*
     * return supplier object
     */
    public function getSupplier()
    {
        $supId = $this->getrsrp_sup_id();
        if ($supId)
            return mage::getModel('Purchase/Supplier')->load($supId);
        else
            return null;
    }


    public function getPurchaseOrder()
    {
        $productId = $this->getrsrp_product_id();
        $pop       = mage::getModel('Purchase/OrderProduct')->load($productId);
        $po        = mage::getModel('Purchase/Order')->load($pop->getpop_order_num());

        return $po;
    }


    /*
     * this function try to find the supplier
     * it try to get him with the serial informations
     * it take informations in table purchase_product_supplier.
     * if there is only one supplier for the product, this is him !
     * else null ^^
     */
    public function getSupplierIdAndPopIdAndPurchasePrice()
    {
        //init
        $pop_id         = null;
        $sup_id         = null;
        $purchase_price = null;

        $rsrpId = $this->getrsrp_id();
        //try to get them using the serial
        if ($this->getrsrp_serial() != null) {
            $productSerialCollection = mage::getModel('AdvancedStock/ProductSerial')
                ->getCollection()
                ->addFieldToFilter('pps_product_id', $this->getrsrp_product_id())
                ->addFieldToFilter('pps_serial', $this->getrsrp_serial());
            if ($productSerialCollection->count() > 0) {
                $productSerial  = $productSerialCollection->getFirstItem();
                $orderProduct   = mage::getModel('Purchase/OrderProduct')->getCollection()->addFieldToFilter('pop_order_num', $productSerial->getpps_purchaseorder_id())->addFieldToFilter('pop_product_id', $this->getrsrp_product_id())->getFirstItem();
                $pop_id         = $orderProduct->getpop_num();
                $purchase_price = $orderProduct->getpop_price_ht_base();
                $sup_id         = $orderProduct->getPurchaseOrder()->getSupplier()->getsup_id();
            }
        }

        //try to get the sup_id only in the table purchase_product_supplier. If there is only one entry for this product, there is only one possible supplier
        if ($sup_id == null) {
            $productSupplier = Mage::getModel('Purchase/ProductSupplier')->getCollection()->addFieldToFilter('pps_product_id', $this->getrsrp_product_id());
            if ($productSupplier->count() == 1) {
                $sup_id = $productSupplier->getFirstItem()->getpps_supplier_num();
            }
        }

        return array('sup_id' => $sup_id, 'pop_id' => $pop_id, 'purchase_price' => $purchase_price);
    }

    public function getPurchasePriceByPopId()
    {
        if ($this->getrsrp_pop_id() != null) {
            $orderProduct = mage::getModel('Purchase/OrderProduct')->getCollection()->addFieldToFilter('pop_num', $this->getrsrp_pop_id())->addFieldToFilter('pop_product_id', $this->getrsrp_product_id());
            if ($orderProduct->count() == 1) {
                return $orderProduct->getFirstItem()->getpop_price_ht_base();
            }
        }

        return 0;
    }

    public function getPurchaseOrderItem()
    {
        $popId = $this->getrsrp_pop_id();
        if ($popId)
            return mage::getModel('Purchase/OrderProduct')->load($popId);

        return null;
    }


    public function getSupplierReturn()
    {
        if ($this->_supplierReturn == null) {
            $rsrId                 = $this->getrsr_id();
            $this->_supplierReturn = mage::getModel('ProductReturn/SupplierReturn')->load($rsrId);
        }

        return $this->_supplierReturn;
    }


    /* 
     *  set a status to a rsrp
     *  firt argument is the status
     */
    public function process($action, $param = null)
    {
        //the object has been loaded by the controller.
        $this->setrsrp_status($action);
        switch ($action) {
            case self::kProductStatusCreditMemo:
                //nothing for the moment
                //maybe we must create a CreditMemo, but it's for us et not for a customer so ?
                break;
            case self::kProductStatusDestroy:
                //Add one product to the destroy warehouse
                if(Mage::getModel('catalog/product')->load($this->getrsrp_product_id())->getId()) {
                    $rmaRef = Mage::getModel('ProductReturn/RmaProducts')->load($this->getrsrp_rp_id())->getRma()->getrma_ref();
                    mage::getModel('AdvancedStock/StockMovement')->createStockMovement(
                        $this->getrsrp_product_id(),
                        null,
                        mage::getStoreConfig('productreturn/supplier_return/destroy_warehouse'),
                        1,
                        mage::helper('ProductReturn')->__('Product destroyed after a supplier return') . ' RMA #' . $rmaRef
                    );
                }
                break;
            case self::kProductStatusRefunded:
                //no stock movements needed
                break;
            case self::kProductStatusStandardExchange:
                //add one product to the normal warehouse
                //getting rma_ref
                if(Mage::getModel('catalog/product')->load($this->getrsrp_product_id())->getId()) {
                    $rmaRef = Mage::getModel('ProductReturn/RmaProducts')->load($this->getrsrp_rp_id())->getRma()->getrma_ref();
                    mage::getModel('AdvancedStock/StockMovement')->createStockMovement(
                        $this->getrsrp_product_id(),
                        null,
                        mage::getStoreConfig('productreturn/supplier_return/normal_warehouse'),
                        1,
                        mage::helper('ProductReturn')->__('Product back from a supplier return') . ' RMA #' . $rmaRef
                    );
                }
                break;
        }
        $this->save();
    }


    /*
     * param: array with rma products
     * create rsrps from rma products
     */
    public function createFromRmaProducts($rmaProduct)
    {
        $this->setrsrp_product_id($rmaProduct->getrp_product_id());
        $this->setrsrp_product_name($rmaProduct->getrp_product_name());
        $this->setrsrp_rp_id($rmaProduct->getrp_id());
        if ($rmaProduct->getrp_description() != null && $rmaProduct->getrp_description() != '')
            $this->setrsrp_comments($rmaProduct->getrp_description());
        if ($rmaProduct->getrp_serials() != null && $rmaProduct->getrp_serials() != '')
            $this->setrsrp_serial($rmaProduct->getrp_serials());
        $this->setrsrp_product_sku(mage::getModel('catalog/product')->load($rmaProduct->getrp_product_id())->getsku());
        $this->save();
    }

}