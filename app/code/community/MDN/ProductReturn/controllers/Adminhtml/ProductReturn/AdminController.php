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
//Controlleur pour la gestion des contacts
class MDN_ProductReturn_Adminhtml_ProductReturn_AdminController extends Mage_Adminhtml_Controller_Action
{

    private $_OrderCreationData = null;
    private $_CreditMemoCreationData = null;

    /**
     * Display product return grid
     *
     */
    public function GridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function GridOrderAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Edit product return
     *
     */
    public function EditAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Delete product return
     *
     */
    public function DeleteAction()
    {
        $productReturnId = $this->getRequest()->getParam('rma_id');
        $rma             = mage::getModel('ProductReturn/Rma')->load($productReturnId);
        $rma->delete();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product Return Deleted'));
        $this->_redirect('adminhtml/ProductReturn_Admin/Grid');
    }

    /**
     * Save product return information
     *
     */
    public function SaveAction()
    {

        $data = $this->getRequest()->getPost('data');
        $rma  = mage::getModel('ProductReturn/Rma')->load($data['rma_id']);

        try {
            //if creation, set fields default values
            $isCreation = false;
            if ($data['rma_id'] == '') {
                $isCreation             = true;
                $data['rma_created_at'] = date('Y-m-d H:n');
                $data['rma_updated_at'] = date('Y-m-d H:n');
                $customer               = mage::getModel('customer/customer')->load($data['rma_customer_id']);
                if ($customer)
                    $data['rma_customer_name'] = $customer->getName();
                $data['rma_id'] = null;

                //set default expiration date
                if ($data['rma_expire_date'] == '')
                    $data['rma_expire_date'] = date('Y-m-d', time() + 3600 * 24 * mage::getStoreConfig('productreturn/general/default_validity_duration'));
            }

            //check date
            if ($data['rma_expire_date'] == '')
                $data['rma_expire_date'] = null;
            if ($data['rma_reception_date'] == '')
                $data['rma_reception_date'] = null;
            if ($data['rma_return_date'] == '')
                $data['rma_return_date'] = null;

            //ipload shipping label
            if (isset($_FILES['returnlabel']) && $_FILES['returnlabel']['name'] != '') {

                $uploader = new Varien_File_Uploader('returnlabel');
                $uploader->setAllowedExtensions(array('pdf'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $path = Mage::getBaseDir('media') . DS . 'rma_return_labels';

                if (!is_dir($path)) {
                    mkdir($path);
                }

                $filename = md5($data['rma_id'] . '-' . $data['rma_ref']) . '.pdf';

                $uploader->save($path, $filename);


            }

            //save
            $rma->setData($data);
            $rmaProducts = $rma->getProducts();
            $rma->save();

            //set sub products information
            foreach ($rmaProducts as $rmaProduct) {
                if (mage::getModel('ProductReturn/RmaProducts')->productIsDisplayed($rmaProduct)) {
                    $id  = $rmaProduct->getitem_id();
                    if (isset($data['rp_qty_' . $id]))
                        $qty = $data['rp_qty_' . $id];
                    else
                        $qty = $rmaProduct->getrp_qty();
                    if (isset($data['rp_description_' . $id])) {
                        $description  = $data['rp_description_' . $id];
                        $serials      = $data['rp_serials_' . $id];
                        $reason       = $data['rp_reason_' . $id];
                        $request_type = $data['rp_request_type_' . $id];
                    } else {
                        $description  = '';
                        $serials      = '';
                        $reason       = '';
                        $request_type = '';
                    }
                    $rmaProductItem = $rma->updateSubProductInformation($rmaProduct, $qty, $description, $reason, $serials, $request_type);
                }
            }
            
            if (!$isCreation) {

                //retrieve datas
                $this->initDataOrder($rma);
                $this->initDataCreditMemo($rma);

                //create objects (if contain products)
                if (count($this->_CreditMemoCreationData['products']) > 0)
                    $creditMemo = mage::helper('ProductReturn/CreateCreditmemo')->CreateCreditmemo($this->_CreditMemoCreationData);
                if (count($this->_OrderCreationData['products']) > 0) {
                    $order = mage::helper('ProductReturn/CreateOrder')->CreateOrder($this->_OrderCreationData);
                    mage::helper('ProductReturn/Reservation')->affectProductsToCreatedOrder($rma, $order);

                    $invoice = mage::helper('ProductReturn/CreateInvoice')->CreateInvoice($order);
                }

                //process products destination
                $rmaProducts = $rma->getProducts();
                foreach($rmaProducts as $product)
                {
                    if ($product->getrp_destination_processed())
                        continue;
                    if (!$product->getrp_destination())
                        continue;
                    $productData = array('rp_id' => $product->getrp_id(), 'sku' => $product->getsku(), 'qty' => $product->getrp_qty(), 'destination' => $product->getrp_destination());
                    $description = mage::helper('ProductReturn')->__('Product return #%s', $rma->getrma_ref());
                    // product back to stock only if shipped !
                    if($productData['qty'] <= $product->getqty_shipped()) {
                        Mage::helper('ProductReturn/Stock')->manageProductDestination($productData, $rma->getSalesOrder()->getStore()->getwebsite_id(), $description, $rma);
                    }
                }

                //misc
                $rma->toggleStatusIfAllProductsProcessed();
            }
            //confirm			
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));

            if ($data['rma_is_locked'] == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('RMA Locked'));
            }

            if(isset($data['message']) && !empty($data['message']))
                $rma->sendMessage($data['message'], MDN_ProductReturn_Model_RmaMessage::AUTHOR_ADMIN);

        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : ') . $ex->getMessage());
        }
        //redirect
        $this->_redirect('adminhtml/ProductReturn_Admin/Edit', array('rma_id' => $rma->getId()));
    }

    /**
     * Print PDF
     *
     */
    public function PrintAction()
    {
        try {
            //recupere la commande
            $rmaId = $this->getRequest()->getParam('rma_id');
            $rma   = Mage::getModel('ProductReturn/Rma')->load($rmaId);

            $obj = mage::getModel('ProductReturn/Pdf_Rma');
            $pdf = $obj->getPdf(array($rma));
            $this->_prepareDownloadResponse(mage::helper('ProductReturn')->__('Product Return #') . $rma->getId() . '.pdf', $pdf->render(), 'application/pdf');
        } catch (Exception $ex) {
            die("An error occured : " . $ex->getMessage());
        }
    }

    /**
     * Send email to customer
     *
     */
    public function NotifyAction()
    {

        try {
            //j envoi un mail au client
            $RmaId = $this->getRequest()->getParam('rma_id');
            $rma   = mage::getModel('ProductReturn/Rma')->load($RmaId);
            $rma->NotifyCustomer();

            //confirme
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('customer successfully notified.'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to notify customer') . ' : ' . $ex->getMessage());
        }

        //redirige sur la page RMA
        $this->_redirect('adminhtml/ProductReturn_Admin/Edit', array('rma_id' => $rma->getId()));
    }



    /**
     * Enter description here...
     *
     */
    public function SelectedProductReturnGridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ProductReturn/Admin_Customer_Edit_Tab_ProductReturn')->toHtml()
        );

    }

    /**
     * Display popup to select product to exchange
     *
     */
    public function ProductExchangeSelectionPopupAction()
    {

        $RmaId = $this->getRequest()->getParam('rma_id');
        $rma   = mage::getModel('ProductReturn/Rma')->load($RmaId);
        Mage::register('current_rma', $rma);

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     *
     *
     */
    public function ProductsReceivedAction()
    {
        try {
            $RmaId = $this->getRequest()->getParam('rma_id');
            $rma   = mage::getModel('ProductReturn/Rma')->load($RmaId);
            $rma->productsReceived();

            //confirme
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product reception processed'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured') . ' : ' . $ex->getMessage());
        }

        //redirige sur la page RMA
        $this->_redirect('adminhtml/ProductReturn_Admin/Edit', array('rma_id' => $rma->getId()));
    }

    /**
     * Return ajax reservation product grid
     *
     */
    public function ReservationGridAction()
    {
        $this->loadLayout();
        $rmaId = $this->getRequest()->getParam('rma_id');
        $rma   = mage::getModel('ProductReturn/Rma')->load($rmaId);
        mage::register('current_rma', $rma);
        $Block = $this->getLayout()->createBlock('ProductReturn/Productreturn_Edit_Tab_ReservationGrid');
        $this->getResponse()->setBody($Block->toHtml());
    }

    /**
     * Reserve product for RMA
     *
     */
    public function ReserveProductAction()
    {
        $rmaId     = $this->getRequest()->getParam('rma_id');
        $productId = $this->getRequest()->getParam('product_id');
        $qty       = $this->getRequest()->getParam('qty');

        try {
            $rma = mage::getModel('ProductReturn/Rma')->load($rmaId);
            $rma->reserveProduct($productId, $qty);

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product reserved'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));
        }

        //redirect
        $this->_redirect('adminhtml/ProductReturn_Admin/Edit', array('rma_id' => $rmaId, 'tab' => 'reservation'));
    }

    public function ReleaseProductAction()
    {
        $rmaId     = $this->getRequest()->getParam('rma_id');
        $productId = $this->getRequest()->getParam('product_id');

        try {
            $rma = mage::getModel('ProductReturn/Rma')->load($rmaId);
            $rma->releaseProduct($productId);

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product released'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));
        }

        //redirect
        $this->_redirect('adminhtml/ProductReturn_Admin/Edit', array('rma_id' => $rmaId, 'tab' => 'reservation'));
    }

    //***********************************************************************************************************************************************
    //***********************************************************************************************************************************************
    //***** Methods to collect datas for creditmemo and order creation
    //***********************************************************************************************************************************************
    //***********************************************************************************************************************************************

    /**
     * Init Data for Order
     *
     */
    private function initDataOrder($rma)
    {

        $data = $this->getRequest()->getPost('data');

        //add products
        $this->_OrderCreationData['products'] = array();
        $this->_OrderCreationData['rma_ref']  = $rma->getrma_ref();
        $this->_OrderCreationData['rma_id']   = $rma->getId();

        //helper for calculate tax
        $helper          = mage::helper('tax');
        $ShippingAddress = $rma->getShippingAddress();
        $BillingAddress  = $rma->getBillingAddress();

        $CustomerTaxClass = $rma->getCustomer()->getTaxClassId();
        $storeId          = $rma->getCustomer()->getStoreId();
        $weight           = 0;

        //add products
        foreach ($rma->getProducts() as $item) {
            $rpId = $item->getrp_id();
            switch ($this->getActionForProduct($rpId)) {

                case 'return':
                    $newProduct = array('product_id'     => Mage::getStoreConfig('productreturn/product_return/fake_product_id'),
                                        'src_product_id' => Mage::getStoreConfig('productreturn/product_return/fake_product_id'),
                                        'qty'            => $item->getrp_qty(),
                                        'price'          => 0,
                                        'price_ht'       => 0,
                                        'product_name'   => mage::helper('ProductReturn')->__('Return product : ') . $item->getname(),
                                        'rp_id'          => $rpId,
                                        'action'         => 'return',
                                        'destination'    => $this->getDestinationForProduct($rpId));
                    $this->_OrderCreationData['products'][] = $newProduct;
                    $weight += $item->getweight();
                    break;

                case 'exchange':
                    $substitutionProductId = $this->getRequest()->getPost('hidden_exchange_' . $rpId);
                    $substitutionProduct   = mage::getModel('catalog/product')->load($substitutionProductId);
                    $srcProductId          = $item->getrp_product_id();

                    //if product type is configurable, set sub product as source product
                    if ($item->getproduct_type() == 'configurable') {
                        foreach ($rma->getSalesOrder()->getAllItems() as $subProduct) {
                            if ($subProduct->getparent_item_id() == $item->getitem_id())
                                $srcProductId = $subProduct->getproduct_id();
                        }
                    }

                    $price = $this->getRequest()->getPost('exhange_price_adjustment_' . $rpId);
                    if ($price <= 0)
                        $price = 0;
                    else
                        $price = str_replace('+', '', $price);

                    $coef = $helper->getPrice($substitutionProduct, 1, true, $ShippingAddress, $BillingAddress, $CustomerTaxClass, $storeId);
                    $priceHt = number_format($price / $coef, 4, '.', '');

                    $newProduct  = array('product_id'       => $substitutionProduct->getId(),
                                        'src_product_id'   => $srcProductId,
                                        'qty'              => $item->getrp_qty(),
                                        'price'            => $price,
                                        'price_ht'         => $priceHt,
                                        'rp_id'            => $rpId,
                                        'action'           => 'exchange',
                                        'price_adjustment' => $price,
                                        'product_name'     => $substitutionProduct->getname(),
                                        'destination'      => $this->getDestinationForProduct($rpId));
                    $this->_OrderCreationData['products'][] = $newProduct;
                    $weight += $substitutionProduct->getweight();
                    break;
            }
        }

        //add technical costs
        if ($data['rma_technical_cost'] > 0) {
            $fakeProductId = Mage::getStoreConfig('productreturn/product_return/fake_product_id');
            $fakeProduct   = Mage::getModel('catalog/product')->load($fakeProductId);
            $qty           = 1;

            $price   = $data['rma_technical_cost'];
            $coef = $helper->getPrice($fakeProduct, 1, true, $ShippingAddress, $BillingAddress, $CustomerTaxClass, $storeId);
            $priceHt = number_format($price / $coef, 4, '.', '');

            $product_name = $data['rma_libelle_action'];
            if (empty($product_name))
                $product_name = Mage::getStoreConfig('productreturn/product_return/fake_product_label');

            $newProduct                             = array('product_id'     => $fakeProductId,
                                                            'src_product_id' => $fakeProductId,
                                                            'qty'            => $qty,
                                                            'price'          => $price,
                                                            'price_ht'       => $priceHt,
                                                            'rp_id'          => null,
                                                            'product_name'   => $product_name);
            $this->_OrderCreationData['products'][] = $newProduct;
        }

        $this->_OrderCreationData['weight'] = $weight;

        $this->_OrderCreationData['payment_method']  = $data['payment_method'];
        $this->_OrderCreationData['shipping_method'] = $data['rma_carrier'];

        //info customer (from customer address)
        $this->_OrderCreationData['customer_firstname']  = $rma->getCustomer()->getfirstname();
        $this->_OrderCreationData['customer_lastname']   = $rma->getCustomer()->getlastname();
        $this->_OrderCreationData['customer_prefix']     = $rma->getCustomer()->getprefix();
        $this->_OrderCreationData['customer_middlename'] = $rma->getCustomer()->getmiddlename();
        $this->_OrderCreationData['customer_suffix']     = $rma->getCustomer()->getsuffix();

        //other information
        $this->_OrderCreationData['customer_email']    = $rma->getCustomer()->getemail();
        $this->_OrderCreationData['customer_id']       = $rma->getCustomer()->getid();
        $this->_OrderCreationData['customer_taxvat']   = $rma->getCustomer()->gettaxvat();
        $this->_OrderCreationData['customer_group_id'] = $rma->getCustomer()->getgroup_id();
        $this->_OrderCreationData['store_id']          = $rma->getSalesOrder()->getstore_id();
        $this->_OrderCreationData['website_id']        = $rma->getSalesOrder()->getwebsite_id();

        //billing addresses
        $this->_OrderCreationData['billing_address'] = array();
        $billingAddress                              = $rma->getBillingAddress();
        foreach ($billingAddress->getData() as $key => $value) {
            $this->_OrderCreationData['billing_address'][$key] = $value;
        }

        //billing addresses
        $this->_OrderCreationData['shipping_address'] = array();
        $shippingAddress                              = $rma->getShippingAddress();
        foreach ($shippingAddress->getData() as $key => $value) {
            $this->_OrderCreationData['shipping_address'][$key] = $value;
        }

        //for guest
        if ($rma->IamGuest())
            $this->_OrderCreationData['customer_guest'] = 1;
        else
            $this->_OrderCreationData['customer_guest'] = 0;

        //tax for shipping
        $helper = mage::helper('tax');
        $this->_OrderCreationData['shipping_cost'] = $data['rma_shipping_cost'];
        $this->_OrderCreationData['shipping_taxamount'] = $data['rma_shipping_cost'] - Mage::helper('ProductReturn/Tax')->shippingInclToExcl($data['rma_shipping_cost'], $rma->getSalesOrder());

        //info order
        $this->_OrderCreationData['order_currency_code']     = $rma->getSalesOrder()->getorder_currency_code();
        $this->_OrderCreationData['base_currency_code']      = $rma->getSalesOrder()->getbase_currency_code();
        $this->_OrderCreationData['store_currency_code']     = $rma->getSalesOrder()->getstore_currency_code();
        $this->_OrderCreationData['global_to_currency_rate'] = $rma->getSalesOrder()->getGlobalCurrencyCode();
        $this->_OrderCreationData['base_to_global_rate']     = $rma->getSalesOrder()->getBaseToGlobalRate();
        $this->_OrderCreationData['base_to_order_rate']      = $rma->getSalesOrder()->getBaseToOrderRate();
        $this->_OrderCreationData['store_to_base_rate']      = $rma->getSalesOrder()->getstore_to_base_rate();
        $this->_OrderCreationData['store_to_order_rate']     = $rma->getSalesOrder()->getstore_to_order_rate();
        $this->_OrderCreationData['state']                   = $rma->getSalesOrder()->getstate();
        $this->_OrderCreationData['customer_note_notify']    = $rma->getSalesOrder()->getcustomer_note_notify();

        $this->_OrderCreationData['order_id'] = $rma->getrma_order_id();
    }

    /*
     * Init data for credit memo
     *
     */
    private function initDataCreditMemo($rma)
    {
        $data                                      = $this->getRequest()->getPost('data');
        $this->_CreditMemoCreationData['products'] = array();

        foreach ($rma->getProducts() as $item) {
            $rpId = $item->getrp_id();

            //check if product action is refund or exchange
            if (($this->getActionForProduct($rpId) != 'refund') && ($this->getActionForProduct($rpId) != 'exchange'))
                continue;

            //if exchange & price adjustment is negative
            $priceAdjustment = $this->getRequest()->getPost('exhange_price_adjustment_' . $rpId);
            if (($this->getActionForProduct($rpId) == 'exchange') && ($priceAdjustment >= 0))
                continue;
            if ($priceAdjustment < 0)
                $priceAdjustment = -$priceAdjustment;

            //add product
            $newProduct = array('product_id'     => $item->getrp_product_id(),
                                'order_item_id' => $item->getrp_orderitem_id(),
                                'src_product_id' => $item->getrp_product_id(),
                                'qty'            => $item->getrp_qty(),
                                'rp_id'          => $rpId,
                                'price'          => $priceAdjustment,
                                'price_ht'       => $priceAdjustment,
                                'product_name'   => $item->getname(),
                                'destination'    => $this->getDestinationForProduct($rpId));
            $this->_CreditMemoCreationData['products'][] = $newProduct;

            //if product type is configurable, add sub products
            if ($item->getproduct_type() == 'configurable') {
                foreach ($rma->getSalesOrder()->getAllItems() as $subProduct) {
                    if ($subProduct->getparent_item_id() == $item->getitem_id()) {
                        $newProduct = array('product_id'     => $subProduct->getproduct_id(),
                                            'order_item_id' => $subProduct->getId(),
                                            'src_product_id' => $item->getproduct_id(),
                                            'qty'            => $item->getrp_qty(),
                                            'rp_id'          => null,
                                            'price'          => $priceAdjustment,
                                            'price_ht'       => $priceAdjustment,
                                            'product_name'   => $subProduct->getname(),
                                            'destination'    => $this->getDestinationForProduct($rpId));
                        $this->_CreditMemoCreationData['products'][] = $newProduct;
                    }
                }
            }

            //if product is child of bundle product, add bundle product
            if ($bundleItemArray = $this->productIsChildOfBundleProduct($item, $rma->getProducts())) {
                $this->_CreditMemoCreationData['products'][] = $bundleItemArray;
            }
        }

        $this->_CreditMemoCreationData['refund_shipping_fees']   = (isset($data['refund_shipping_fees']));
        $this->_CreditMemoCreationData['refund_shipping_amount'] = Mage::helper('ProductReturn/Tax')->shippingInclToExcl($data['refund_shipping_amount'], $rma->getSalesOrder());

        $this->_CreditMemoCreationData['order_id']               = $rma->getrma_order_id();
        $this->_CreditMemoCreationData['rma_id']                 = $rma->getrma_id();

        $this->_CreditMemoCreationData['refund'] = $data['credit_memo_fee'];
        $this->_CreditMemoCreationData['fee']    = $data['credit_memo_refund'];

        if (isset($data['rma_refund_online'])) {
            $this->_CreditMemoCreationData['refund_online'] = $data['rma_refund_online'];
        } else {
            $this->_CreditMemoCreationData['refund_online'] = 0;
        }

    }

    //******************************************************************************************************************************************************
    //******************************************************************************************************************************************************
    //TOOLS
    //******************************************************************************************************************************************************
    //******************************************************************************************************************************************************

    /**
     * Return action for product
     *
     * @param unknown_type $rpId
     *
     * @return unknown
     */
    protected function getActionForProduct($rpId)
    {
        return $this->getRequest()->getPost('rad_action_' . $rpId);
    }

    /**
     * Check if product is sub product of a bundle
     *
     * @param unknown_type $item
     * @param              $allItems
     *
     * @return array|null
     */
    protected function productIsChildOfBundleProduct($item, $allItems)
    {
        //if has parent
        if ($item->getparent_item_id()) {
            //load parent
            $parentItem = mage::getModel('sales/order_item')->load($item->getparent_item_id());
            if ($parentItem->getproduct_type() == 'bundle') {
                $value = array('product_id'     => $parentItem->getproduct_id(),
                               'order_item_id' => $parentItem->getId(),
                               'src_product_id' => $parentItem->getproduct_id(),
                               'qty'            => 1,
                               'rp_id'          => null,
                               'price'          => 0,
                               'price_ht'       => 0,
                               'product_name'   => $parentItem->getname(),
                               'destination'    => MDN_ProductReturn_Model_RmaProducts::kDestinationStock);

                return $value;
            }
        }

        return null;
    }

    /**
     *
     *
     * @param unknown_type $rpId
     *
     * @return mixed
     */
    protected function getDestinationForProduct($rpId)
    {
        return $this->getRequest()->getPost('dest_' . $rpId);
    }

    /**
     * Check if a data has changed
     *
     * @param unknown_type $dataName
     * @param unknown_type $object
     *
     * @return bool
     */
    protected function dataHasChanged($dataName, $object)
    {
        return ($object->getData($dataName) == $object->getOrigData($dataName));
    }

    public function ConfigurationAction()
    {
        $this->_redirect('adminhtml/system_config/edit', array('section' => 'productreturn'));
    }

    protected function _isAllowed()
    {
        return true;
    }

}