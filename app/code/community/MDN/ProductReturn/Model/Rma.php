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
class MDN_ProductReturn_Model_Rma extends Mage_Core_Model_Abstract
{

    private $_customer = null;
    private $_salesOrder = null;
    private $_reservations = null;

    const kStatusRequested                  = 'requested';
    const kStatusNew                        = 'new';
    const kStatusProductReturnAccepted      = 'product_return_accepted';
    const kStatusProductReturnRefused       = 'product_return_refused';
    const kStatusProductsreceived           = 'product_received';
    const kStatusProductsreceivedbutrefused = 'product_received_but_refused';
    const kStatusExpertise                  = 'expertise_product';
    const kStatusRmaComplete                = 'complete';
    const kStatusRmaExpired                 = 'expired';
    const kStatusRmaWaitingForSupplier      = 'waiting_for_supplier';
    const kStatusRmaNpai                    = "bad address";

    public function _construct()
    {
        parent::_construct();
        $this->_init('ProductReturn/Rma');
    }

    //****************************************************************************************************************************************
    //****************************************************************************************************************************************
    // MAIN METHODS
    //****************************************************************************************************************************************
    //****************************************************************************************************************************************

    /**
     * Return customer
     *
     * @return unknown
     */
    public function getCustomer()
    {
        if ($this->_customer == null) {
            $this->_customer = mage::getModel('customer/customer')->load($this->getrma_customer_id());

            if ($this->_customer->getId() == "") {
                $this->_customer = mage::getModel('customer/customer');
                if ($this->getSalesOrder()->getBillingAddress()) {
                    $this->_customer->setFirstname($this->getSalesOrder()->getBillingAddress()->getfirstname());
                    $this->_customer->setLastname($this->getSalesOrder()->getBillingAddress()->getlastname());
                }
                $this->_customer->setEmail($this->getSalesOrder()->getCustomerEmail());
                $this->_customer->setStoreId($this->getSalesOrder()->getstore_id());
            }
        }

        return $this->_customer;
    }

    /**
     * Return billing address
     */
    public function getBillingAddress()
    {
        //set source address
        $fromAddress = null;
        if ($this->getrma_address_id() == 0)
            $fromAddress = $this->getSalesOrder()->getBillingAddress();
        else
            $fromAddress = mage::getModel('customer/address')->load($this->getrma_address_id());

        //copy object & return
        $billingAddress = mage::getModel('customer/address');
        $billingAddress->setId(0);
        if ($fromAddress) {
            foreach ($fromAddress->getData() as $key => $value)
                $billingAddress->setData($key, $value);
        }

        return $billingAddress;
    }

    /**
     * Return shipping address
     */
    public function getShippingAddress()
    {
        //set source address
        $fromAddress = null;
        if ($this->getrma_address_id() == 0)
            $fromAddress = $this->getSalesOrder()->getShippingAddress();
        else
            $fromAddress = mage::getModel('customer/address')->load($this->getrma_address_id());

        //copy object & return
        $shippingAddress = mage::getModel('customer/address');
        $shippingAddress->setId(0);
        if ($fromAddress) {
            foreach ($fromAddress->getData() as $key => $value)
                $shippingAddress->setData($key, $value);
        }

        return $shippingAddress;
    }

    /**
     * Return sales order
     *
     * @return unknown
     */
    public function getSalesOrder()
    {
        if ($this->_salesOrder == null) {
            $this->_salesOrder = mage::getModel('sales/order')->load($this->getrma_order_id());
        }

        return $this->_salesOrder;
    }

    /**
     * Return statuses
     *
     * @return unknown
     */
    public function getStatuses()
    {
        $retour                                           = array();
        $retour [self::kStatusRequested]                  = mage::helper('ProductReturn')->__(self::kStatusRequested);
        $retour [self::kStatusNew]                        = mage::helper('ProductReturn')->__(self::kStatusNew);
        $retour [self::kStatusProductReturnAccepted]      = mage::helper('ProductReturn')->__(self::kStatusProductReturnAccepted);
        $retour [self::kStatusProductReturnRefused]       = mage::helper('ProductReturn')->__(self::kStatusProductReturnRefused);
        $retour [self::kStatusProductsreceived]           = mage::helper('ProductReturn')->__(self::kStatusProductsreceived);
        $retour [self::kStatusProductsreceivedbutrefused] = mage::helper('ProductReturn')->__(self::kStatusProductsreceivedbutrefused);
        $retour [self::kStatusExpertise]                  = mage::helper('ProductReturn')->__(self::kStatusExpertise);
        $retour [self::kStatusRmaWaitingForSupplier]      = mage::helper('ProductReturn')->__(self::kStatusRmaWaitingForSupplier);
        $retour [self::kStatusRmaComplete]                = mage::helper('ProductReturn')->__(self::kStatusRmaComplete);
        $retour [self::kStatusRmaExpired]                 = mage::helper('ProductReturn')->__(self::kStatusRmaExpired);
        //$retour[self::kStatusRmaNpai]                     = Mage::helper('ProductReturn')->__(self::kStatusRmaNpai);

        return $retour;
    }

    /**
     * Return action array
     *
     * @return unknown
     */
    //todo: deprecated
    public function getAction()
    {
        die('getAction deprecated');
        $retour = array();
        //$retour [self::kActionRefund] = self::kActionRefund;
        $retour [self::kActionProductReturn] = mage::helper('ProductReturn')->__(self::kActionProductReturn);
        $retour [self::kActionExchange]      = mage::helper('ProductReturn')->__(self::kActionExchange);
        $retour [self::kActionRefund]        = mage::helper('ProductReturn')->__(self::kActionRefund);

        return $retour;
    }


    /**
     * Return products
     *
     * @return unknown
     */
    public function getProducts()
    {
        $collection = null;
        if ($this->getId() == null) {
            $collection = mage::getModel('sales/order_item')
                ->getCollection()
                ->addFieldToFilter('order_id', $this->getSalesOrder()->getId());
            //->addFieldToFilter('product_type', array('simple','bundle','configurable','grouped','virtual','downloadable'));
        } else {
            $collection = mage::getModel('sales/order_item')
                ->getCollection()
                ->join('ProductReturn/RmaProducts', 'item_id=rp_orderitem_id')
                ->addFieldToFilter('rp_rma_id', $this->getId())
                ->addFieldToFilter('order_id', $this->getSalesOrder()->getId());
            //->addFieldToFilter('product_type', array('simple','bundle','configurable','grouped','virtual','downloadable'));
        }

        return $collection;
    }

    /**
     * Update products
     *
     * @param unknown_type $rmaProduct
     * @param unknown_type $qty
     * @param unknown_type $description
     * @param unknown_type $reason
     * @param null         $serials
     * @param string       $request_type
     *
     * @internal param \unknown_type $action
     * @internal param \unknown_type $destination
     * @return \Mage_Core_Model_Abstract
     */
    public function updateSubProductInformation($rmaProduct, $qty, $description, $reason, $serials = null, $request_type = '')
    {
        $record = null;

        //load or init record
        $record = mage::getModel('ProductReturn/RmaProducts')->load($rmaProduct->getrp_id());

        $record->setrp_rma_id($this->getId());
        $record->setrp_product_id($rmaProduct->getproduct_id());
        $record->setrp_orderitem_id($rmaProduct->getitem_id());
        $record->setrp_qty($qty);
        $record->setrp_product_name($rmaProduct->getName());
        $record->setrp_description($description);
        $record->setrp_reason($reason);
        $record->setrp_request_type($request_type);
        if ($serials != null || $serials == '') {
            $record->setrp_serials($serials);
        }
        $record->save();

        return $record;
    }

    //****************************************************************************************************************************************
    //****************************************************************************************************************************************
    // RESERVATION
    //****************************************************************************************************************************************
    //****************************************************************************************************************************************

    /**
     * Return reservations associated to this rma
     *
     */
    public function getReservations()
    {
        if ($this->_reservations == null) {
            $this->_reservations = mage::getModel('ProductReturn/RmaReservation')
                ->getCollection()
                ->addFieldToFilter('rr_rma_id', $this->getId());
        }

        return $this->_reservations;
    }

    /**
     * Reserve product
     *
     * @param unknown_type $productId
     * @param int          $qty
     *
     * @throws Exception
     */
    public function reserveProduct($productId, $qty = 1)
    {
        //check if product is not already reserved
        if ($this->productReservedForRma($productId))
            throw new Exception(mage::helper('ProductReturn')->__('Product already reserved for RMA'));

        //process reservation (may thrown an error)
        mage::helper('ProductReturn/Reservation')->reserveProduct($this, $productId, $qty);

        //save reservation (if no error thrown)
        $reservation         = mage::getModel('ProductReturn/RmaReservation')
            ->setrr_rma_id($this->getId())
            ->setrr_product_id($productId)
            ->setrr_qty($qty)
            ->save();
        $this->_reservations = null;

        //add history
        $product = mage::getModel('catalog/product')->load($productId);
        $this->addHistoryRma(mage::helper('ProductReturn')->__('Product %s reserved', $product->getname()));
    }

    /**
     * Reserve product
     *
     * @param unknown_type $productId
     * @param null         $qty
     *
     * @throws Exception
     */
    public function releaseProduct($productId, $qty = null)
    {
        if (!$this->productReservedForRma($productId))
            throw new Exception(mage::helper('ProductReturn')->__('Product is not reserved for RMA'));

        //define qty if not set
        if ($qty == null) {
            $rmaReservation = $this->getRmaReservationForProduct($productId);
            $qty            = $rmaReservation->getrr_qty();
        }

        mage::helper('ProductReturn/Reservation')->releaseProduct($this, $productId, $qty);

        //delete reservation (if no error thrown)
        foreach ($this->getReservations() as $reservation) {
            if ($productId == $reservation->getrr_product_id()) {
                $reservation->delete();
                $this->_reservations = null;
            }
        }

        //add history
        $product = mage::getModel('catalog/product')->load($productId);
        $this->addHistoryRma(mage::helper('ProductReturn')->__('Product %s released', $product->getname()));
    }

    /**
     * Check if a product is reserved for RMA
     *
     * @param unknown_type $productId
     *
     * @return unknown
     */
    public function productReservedForRma($productId)
    {
        foreach ($this->getReservations() as $reservation) {
            if ($productId == $reservation->getrr_product_id())
                return true;
        }

        return false;
    }

    protected function getRmaReservationForProduct($productId)
    {
        foreach ($this->getReservations() as $reservation) {
            if ($productId == $reservation->getrr_product_id())
                return $reservation;
        }
    }

    //****************************************************************************************************************************************
    //****************************************************************************************************************************************
    // MODEL EVENTS
    //****************************************************************************************************************************************
    //****************************************************************************************************************************************

    /**
     * Store update time
     *
     * @return unknown
     */
    protected function _beforeSave()
    {
        //check authorisation only if we are in magento backend
        if (Mage::getSingleton('core/design_package') == 'adminhtml')
        {
            if (!$this->canEdit())
                throw new Exception($this->__('You are not allowed to save this rma'));
        }

        $this->setrma_updated_at(date('Y-m-d H:i:s'));

        return $this;
    }

    /**
     * delete sub products before deleting productreturn
     *
     * @return unknown
     */
    protected function _beforeDelete()
    {
        $collection = mage::getModel('ProductReturn/RmaProducts')
            ->getCollection()
            ->addFieldToFilter('rp_rma_id', $this->getId());
        foreach ($collection as $product) {
            $product->delete();
        }


        if (Mage::helper('ProductReturn/Returnlabel')->isExists($this)):
            $filelabel = Mage::helper('ProductReturn/Returnlabel')->getBaseReturnlabelUrl($this);
            unlink($filelabel);
        endif;

        return parent::_beforeDelete();
    }

    /**
     * Add history if status change
     *
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        //if rma status changes, update planning
        if ($this->getrma_status() != $this->getOrigData('rma_status')) {
            $this->addHistoryRma(mage::helper('ProductReturn')->__('Rma status change to ') . $this->getrma_status());

            //if status set to expired or complete, release products
            if (($this->getrma_status() == self::kStatusRmaExpired) || ($this->getrma_status() == self::kStatusRmaComplete)) {
                foreach ($this->getReservations() as $reservation) {
                    //process release already done in SaveAction() -> affectProductToCreateOrder() -> releaseProduct()
                    // $this->releaseProduct($reservation->getrr_product_id());
                    $reservation->delete();

                    // 13 / 09 /12 : fix update stock level after exchange with reserved product
                    if ($this->getrma_status() == self::kStatusRmaComplete) {

                        // get product reserved
                        $product = $reservation->getProduct();
                        if($product->getId()) {

                            $stock = $product->getStockItem();

                            // get qty to decrement
                            $rmaReservation = $this->getRmaReservationForProduct($product->getId());
                            $qty = $rmaReservation->getrr_qty();

                            if (!($stock->getqty() >= $qty)) throw new Exception($this->__('Stock level too low for reservation'));

                            //decrease product stock
                            $stock->setqty($stock->getqty() - $qty)->save();
                        }

                    }

                }
            }
            
            //notify customer on status change
            if (mage::getStoreConfig('productreturn/emails/auto_notify_customer'))
            {
                $this->NotifyCustomer();
            }
            
        }
    }

    //****************************************************************************************************************************************
    //****************************************************************************************************************************************
    // Emails
    //****************************************************************************************************************************************
    //****************************************************************************************************************************************

    /**
     * Notify customer depending of rma status
     *
     * @return unknown
     */
    public function NotifyCustomer()
    {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $templateId = Mage::getStoreConfig('productreturn/emails/template_' . $this->getrma_status(), $this->getCustomer()->getStoreId());
        if (!$templateId)
            return $this;
        $identityId = Mage::getStoreConfig('productreturn/emails/email_identity', $this->getCustomer()->getStoreId());

        $customerEmail = $this->getCustomer()->getemail();
        if ($this->getrma_customer_email() != '')
            $customerEmail = $this->getrma_customer_email();

        //set data to use in array
        $data = array
        (
            'caption'                => $this->getcaption(),
            'customer_name'          => $this->getCustomer()->getName(),
            'rma_id'                 => $this->getrma_ref(),
            'order_id'               => $this->getSalesOrder()->getincrement_id(),
            'rma_expire_date'        => mage::helper('core')->formatDate($this->getrma_expire_date(), 'short'),
            'store_name'             => $this->getSalesOrder()->getStoreGroupName(),
            'rma_reason'             => mage::helper('ProductReturn')->__($this->getrma_reason()),
            'rma_status'             => mage::helper('ProductReturn')->__($this->getrma_status()),
            'rma_description'        => $this->getrma_public_description(),
            'rma_public_description' => $this->getrma_public_description(),
            'rma_action'             => mage::helper('ProductReturn')->__($this->getrma_action()),
            'product_html'           => $this->getProductsAsHtml()
        );

        //send email
        Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'adminhtml', 'store' => $this->getCustomer()->getStoreId()))
            ->sendTransactional(
                $templateId,
                $identityId,
                $customerEmail,
                $this->getCustomer()->getname(),
                $data,
                $this->getSalesOrder()->getstore_id());

        //send email to cc_to
        $cc = mage::getStoreConfig('productreturn/emails/cc_to');
        if ($cc != '') {
            Mage::getModel('core/email_template')
                ->setDesignConfig(array('area' => 'adminhtml', 'store' => $this->getCustomer()->getStoreId()))
                ->sendTransactional(
                    $templateId,
                    $identityId,
                    $cc,
                    $this->getCustomer()->getname(),
                    $data,
                    $this->getSalesOrder()->getstore_id());
        }

        $this->addHistoryRma(mage::helper('ProductReturn')->__('Customer notified for status %s', mage::helper('ProductReturn')->__($this->getrma_status())));

        $translate->setTranslateInline(true);

        return $this;
    }

    protected function getProductsAsHtml()
    {
        $helper = Mage::helper('ProductReturn');
        $html   = '<table border="1" cellspacing="0" width="400">';
        $html .= '<tr><th>' . $helper->__('Product') . '</th><th>' . $helper->__('Quantity') . '</th><th>' . $helper->__('Reason') . '</th><th>' . $helper->__('Action') . '</th><th>' . $helper->__('Comments') . '</th></tr>';

        foreach ($this->getProducts() as $product) {
            if ($product->getrp_qty() == 0)
                continue;

            $html .= '<tr>';
            $html .= '<td>' . $product->getrp_product_name() . '</td>';
            $html .= '<td align="center">' . $product->getrp_qty() . '</td>';
            $html .= '<td align="center">' . $product->getrp_reason() . '</td>';
            $html .= '<td align="center">' . $product->getrp_action() . '</td>';
            $html .= '<td>' . $product->getrp_description() . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    /**
     * Send an email to admin to notify about new product return request
     *
     * @return unknown
     */
    public function NotifyCreationToAdmin()
    {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        //template information from syst > config > product return
        $templateId = Mage::getStoreConfig('productreturn/emails/template_rma_request', $this->getCustomer()->getStoreId());
        $identityId = Mage::getStoreConfig('productreturn/emails/email_identity', $this->getCustomer()->getStoreId());
        $sendTo     = Mage::getStoreConfig('productreturn/emails/email_new_request', $this->getCustomer()->getStoreId());

        //d�fini le tableau des donn�es qui sont utilis�e dans le mail
        $url  = Mage::helper('adminhtml')->getUrl('adminhtml/ProductReturn_Admin/Edit', array('rma_id' => $this->getId()));
        $data = array
        (
            'customer' => $this->getCustomer()->getName(),
            'url'      => $url
        );

        //envoi le mail
        Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'adminhtml', 'store' => $this->getCustomer()->getStoreId()))
            ->sendTransactional(
                $templateId,
                $identityId,
                $sendTo,
                'Magento',
                $data);

        $translate->setTranslateInline(true);

        return $this;
    }

    //****************************************************************************************************************************************
    //****************************************************************************************************************************************
    // MISC
    //****************************************************************************************************************************************
    //****************************************************************************************************************************************

    /**
     * Customer can edit rma ?
     *
     * @return bool
     */
    public function CustomerCanEdit()
    {
        return false;
    }

    /**
     * Load rma collection from customer
     *
     * @param int $customerId
     *
     * @return unknown
     */
    public function loadByCustomer($customerId)
    {

        $collection = $this->getCollection()->addFilter('rma_customer_id', $customerId)->join('sales/order', 'rma_order_id=entity_id');

        return $collection;
    }

    /**
     * Load rma collection from order id
     *
     * @param int $order_id
     *
     * @return unknown
     */
    public function loadByOrder($order_id)
    {

        $collection = $this->getCollection()->addFilter('rma_order_id', $order_id)->join('sales/order', 'rma_order_id=entity_id');

        return $collection;
    }

    /**
     * Is it possible to perform an action ?
     *
     * @return bool
     */
    public function CanAction()
    {

        if ($this->getrma_action_order_id() || $this->getrma_action_credit_memo_id())
            return false;
        else
            return true;
    }

    /**
     * manager can edit rma ?
     *
     * @return bool
     */
    public function canEdit()
    {
        if (!(($this->getRmaIsLocked() && !Mage::getSingleton('admin/session')->isAllowed('admin/sales/productreturn/customerreturn/action/editlockedrma')) || !Mage::getSingleton('admin/session')->isAllowed('admin/sales/productreturn/customerreturn/action/editrma'))){

            if (Mage::getStoreConfig('productreturn/product_return/prevent_edit_rma_manager') && !Mage::getSingleton('admin/session')->isAllowed('admin/sales/productreturn/customerreturn/action/editanyrma')) {


                if ($this->getrma_manager_id() != 0 && $this->getrma_manager_id() == Mage::getSingleton('admin/session')->getUser()->getId()) {
                    return true;
                }

                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * Create order
     *
     * @param unknown_type $OrderCreationData
     *
     * @return unknown
     */
    public function CreateOrder($OrderCreationData)
    {

        return mage::helper('ProductReturn/CreateOrder')->createOrder($OrderCreationData);
    }

    /**
     * Create credit memo
     *
     * @param unknown_type $CreditMemoCreationData
     *
     * @return unknown
     */
    public function CreateCreditMemo($CreditMemoCreationData)
    {

        return mage::helper('ProductReturn/CreateCreditmemo')->CreateCreditmemo($CreditMemoCreationData);
    }

    /**
     * Check if sales oder customer is guest
     *
     * @return unknown
     */
    public function IamGuest()
    {

        if ($this->getCustomer()->getId() == "")
            return true;
        else
            return false;
    }

    /**
     * Add a comment
     *
     * @param unknown_type $comments
     */
    public function addHistoryRma($comments)
    {
        $model = mage::getModel('ProductReturn/RmaHistory');
        $model->setrh_rma_id($this->getrma_id());
        $model->setrh_comments($comments);
        $model->setrh_date(date("Y-m-d H.i.s"));
        $model->save();
    }

    /**
     * return all comments merged
     *
     * @param bool|\unknown_type $html
     *
     * @return unknown
     */
    public function getAllCommentsMerged($html = true)
    {
        //init vars
        $value     = '';
        $breakline = '<br>';
        if (!$html)
            $breakline = "\r\n";

        //add general comments
        if ($this->getrma_public_description() != '')
            $value .= $this->getrma_public_description() . $breakline;
        if ($this->getrma_private_description() != '')
            $value .= $this->getrma_private_description() . $breakline;
        if ($this->getrma_aftersale_description() != '')
            $value .= $this->getrma_aftersale_description() . $breakline;

        //add products comments
        foreach ($this->getProducts() as $product) {
            if ($product->getrp_description() != '')
                $value .= $product->getrp_description() . $breakline;
        }

        return $value;
    }

    /**
     * Enter description here...
     *
     */
    public function productsReceived()
    {

        //store information
        $newStatus = mage::getStoreConfig('productreturn/product_return/new_status_for_product_received');
        $this->setrma_status($newStatus);
        $this->setrma_reception_date(date('Y-m-d'));
        $this->save();

        //add in history
        $this->addHistoryRma(mage::helper('ProductReturn')->__('Products received'));

        //notify customer
        if (mage::getStoreConfig('productreturn/emails/auto_notify_customer'))
            $this->NotifyCustomer();
    }

    /**
     * Toggle rma status to "complete" if all products have been processed
     *
     */
    public function toggleStatusIfAllProductsProcessed()
    {
        //skip if already complete
        if ($this->getrma_status() == self::kStatusRmaComplete)
            return;

        $hasUnprocessedProduct            = false;
        $hasProductWithQtyGreaterThanZero = false;
        foreach ($this->getProducts() as $product) {
            if (($product->getrp_action_processed() == 0) && ($product->getrp_qty() > 0)) {
                $hasUnprocessedProduct = true;

                return;
            }

            if ($product->getrp_qty() > 0)
                $hasProductWithQtyGreaterThanZero = true;
        }

        //if not has unprocessed product, toggle status and save
        if ((!$hasUnprocessedProduct) && ($hasProductWithQtyGreaterThanZero)) {
            $this->setrma_status(self::kStatusRmaComplete)->save();
        }
    }

    /**
     * Return reason for product Id
     *
     * @param unknown_type $productId
     *
     * @return string
     */
    public function getReasonForProductId($productId)
    {
        $value = '';

        foreach ($this->getProducts() as $rmaProduct) {
            if ($rmaProduct->getrp_product_id() == $productId)
                $value = $rmaProduct->getrp_reason();
        }

        return $value;
    }

    /**
     * Return action for product Id
     *
     * @param unknown_type $productId
     *
     * @return string
     */
    public function getActionForProductId($productId)
    {
        $value = '';

        foreach ($this->getProducts() as $rmaProduct) {
            if ($rmaProduct->getrp_product_id() == $productId)
                $value = $rmaProduct->getrp_action();
        }

        return $value;
    }

    /**
     * @param string $message
     * @param string $from admin|customer
     */
    public function sendMessage($message, $from){

        Mage::getModel('ProductReturn/RmaMessage')
            ->sendMessage($this, $message, $from);

    }

    /**
     * @return MDN_ProductReturn_Model_Mysql4_RmaMessage_Collection
     */
    public function getMessages(){

        return Mage::getModel('ProductReturn/RmaMessage')
            ->getCollection()
            ->addFieldToFilter('rmam_rma_id', $this->getId())
            ->setOrder('rmam_date', 'DESC');

    }

}
