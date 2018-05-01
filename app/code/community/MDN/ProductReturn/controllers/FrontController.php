<?php
class MDN_ProductReturn_FrontController extends Mage_Core_Controller_Front_Action
{
    public function ListAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    public function NewRequestSelectOrderAction()
    {
        if (!Mage::getStoreConfig('productreturn/general/allow_customer_request'))
        {
            Mage::getSingleton('customer/session')->addSuccess($this->__('You can not request for a new product return'));
            $this->_redirect('ProductReturn/Front/List');
        }
        else
        {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    /**
     * Redirect customer to authentication page if not logged in and action = CreateRequest
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $action = $this->getRequest()->getActionName();
        if ($action == 'NewRequest' || $action == 'NewRequestSelectOrder' || $action == 'List') {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                Mage::getSingleton('customer/session')->addError($this->__('You must be logged in to request for a return.'));
                Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('ProductReturn/Front/List', array('_current' => true)));
                $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
            }
        }

        return $this;
    }

    /**
     * New product return request
     *
     */
    public function NewRequestAction()
    {
        $OrderId = $this->getRequest()->getParam('order_id');

        //process verifications
        $Order      = mage::getModel('sales/order')->load($OrderId);
        $CustomerId = mage::Helper('customer')->getCustomer()->getId();
        if ($Order->getcustomer_id() != $CustomerId) {
            $this->_redirect('ProductReturn/Front/List/');
        } else {
            if (!mage::helper('ProductReturn')->IsOrderAvailable($Order)) {
                $this->_redirect('ProductReturn/Front/List/');
            } else {
                $this->loadLayout();
                $this->_initLayoutMessages('customer/session');
                $this->renderLayout();
            }

        }

    }

    /**
     * Submit new request
     *
     */
    public function SubmitRequestAction()
    {
        //create request
        $data = $this->getRequest()->getPost('data');

        //set main information
        $rma = mage::getModel('ProductReturn/Rma')->load($data['rma_id']);


        //if creation, set fields default values
        if ($data['rma_id'] == '') {
            $data['rma_created_at'] = date('Y-m-d H:n');
            $data['rma_updated_at'] = date('Y-m-d H:n');
            $customer               = mage::getModel('customer/customer')->load($data['rma_customer_id']);
            if ($customer)
                $data['rma_customer_name'] = $customer->getName();
            $data['rma_id']          = null;
            $data['rma_expire_date'] = date('Y-m-d', time() + 3600 * 24 * mage::getStoreConfig('productreturn/general/default_validity_duration'));
            $data['rma_status']      = mage::getStoreConfig('productreturn/product_return/new_status_for_request');
        }

        $rma->setData($data);
        $rmaProducts = $rma->getProducts();

        //on va verifier si il ya des quantit�s pour au moin 1 produit.
        //Sinon erreur pas d'insertion de rma
        $p_qty = false;

        foreach ($rmaProducts as $rmaProduct) {
            $id = $rmaProduct->getitem_id();

            if (isset($data['rp_qty_' . $id]) && $data['rp_qty_' . $id] > 0) {
                $p_qty = true;
            }
        }

        if ($p_qty == true) {

            $rma->save();

            //set sub products information
            foreach ($rmaProducts as $rmaProduct) {
                $id = $rmaProduct->getitem_id();

                //check
                if (isset($data['rp_qty_' . $id])) {
                    $qty          = $data['rp_qty_' . $id];
                    $description  = $data['rp_description_' . $id];
                    $reason       = $data['rp_reason_' . $id];
                    $request_type = $data['rp_request_type_' . $id];
                    $rma->updateSubProductInformation($rmaProduct, $qty, $description, $reason, null, $request_type);
                }

            }

            //notify admin
            $rma->NotifyCreationToAdmin();

            //confirm & redirect
            Mage::getSingleton('customer/session')->addSuccess($this->__('Product Return successfully sent.'));
            $this->_redirect('ProductReturn/Front/View', array('rma_id' => $rma->getId()));
        } else {

            Mage::getSingleton('customer/session')->addError($this->__('Please select quantities to return.'));
            $this->_redirect('ProductReturn/Front/NewRequest', array('order_id' => $data['rma_order_id']));
        }


    }

    /**
     * Check if a rma belong to current customer
     *
     * @param unknown_type $rmacustomer_Id
     *
     * @return unknown
     */
    public function RmaBelongToCustomer($rmacustomer_Id)
    {
        $CustomerId = mage::Helper('customer')->getCustomer()->getId();
        if ($rmacustomer_Id != $CustomerId)
            return false;
        else
            return true;

    }

    /**
     * View rma
     *
     */
    public function ViewAction()
    {

        //on verifie si le rma correspond a customer connect�
        $RmaId = $this->getRequest()->getParam('rma_id');
        $rma   = mage::getModel('ProductReturn/Rma')->load($RmaId);
        if ($this->RmaBelongToCustomer($rma->getrma_customer_id()) === false) {
            $this->_redirect('');
        } else {
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->renderLayout();
        }
    }

    /**
     * View after sale terms and commitments
     *
     */
    public function ViewCGVAction()
    {
        //on verifie si le rma correspond a customer connect�
        $RmaId = $this->getRequest()->getParam('rma_id');
        $rma   = mage::getModel('ProductReturn/Rma')->load($RmaId);
        if ($this->RmaBelongToCustomer($rma->getrma_customer_id()) === false) {
            $this->_redirect('ProductReturn/Front/List/');
        } else {
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->renderLayout();
        }
    }

    /**
     * Print pdf
     *
     */
    public function PrintPdfAction()
    {

        //on verifie si le rma correspond au customer connect�
        $RmaId = $this->getRequest()->getParam('rma_id');
        $rma   = mage::getModel('ProductReturn/Rma')->load($RmaId);
        if ($this->RmaBelongToCustomer($rma->getrma_customer_id()) === true) {
            try {
                $this->loadLayout();

                $pdf  = Mage::getModel('ProductReturn/Pdf_Rma')->getPdf(array($rma));
                $name = mage::helper('ProductReturn')->__('rma_') . $rma->getrma_ref() . '.pdf';
                $this->customPrepareDownloadResponse($name, $pdf->render(), 'application/pdf');
            } catch (Exception $ex) {
                die("Erreur lors de la g�n�ration du PDF: " . $ex->getMessage() . "<br>" . $ex->getTraceAsString());
            }
        } else {
            $this->_redirect('');
        }
    }

    /**
     * Allow to download pdf
     *
     * @param unknown_type         $fileName
     * @param unknown_type         $content
     * @param string|\unknown_type $contentType
     */
    protected function customPrepareDownloadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', strlen($content))
            ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName)
            ->setBody($content);
    }

    /**
     * Display form to create account
     */
    public function AccountFormAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $session = Mage::getSingleton('customer/session');
        $session->setBeforeAuthUrl(Mage::getUrl('ProductReturn/Front/NewRequestSelectOrder'));

        $this->renderLayout();
    }

    /**
     * Submit account form
     */
    public function SubmitAction()
    {

        //set data
        $data               = $this->getRequest()->getPost();
        $data['website_id'] = Mage::app()->getStore()->getWebsiteId();
        $data['store_id']   = Mage::app()->getStore()->getId();

        $session = Mage::getSingleton('customer/session');

        try {

            $dataprocess = Mage::getModel('ProductReturn/Processor')->process($data);
            $customer = $dataprocess['customer'];
            $order = $dataprocess['order'];
            $session->setCustomerAsLoggedIn($customer);

            //redirect customer
            $session->addSuccess($this->__('Thanks for registering !'));
            $this->_redirect('ProductReturn/Front/NewRequest', array('_secure' => true,'order_id' => $order->getId()));
        } catch (Exception $ex) {
            $session->setCustomerFormData($data);
            $session->addError($this->__($ex->getMessage()));
            $this->_redirect('*/*/AccountForm');
        }
    }

    public function SubmitMessageAction(){

        try{

            $clean = filter_var_array(
                $this->getRequest()->getPost(),
                array(
                    'rma_id' => FILTER_VALIDATE_INT,
                    'rma_message' => FILTER_SANITIZE_STRING
                )
            );

            if(empty($clean['rma_id']) || empty($clean['rma_message']))
                throw new Exception($this->__('Missing data'));

            $rma = Mage::getModel('ProductReturn/Rma')->load($clean['rma_id']);

            if(!$rma->getId()){
                throw new Exception($this->__('Rma no more available'));
            }

            $rma->sendMessage($clean['rma_message'], MDN_ProductReturn_Model_RmaMessage::AUTHOR_CUSTOMER);

            $this->_redirectReferer();

        }catch(Exception $e){

            Mage::getSingleton('customer/session')->addError($this->__($e->getMessage()));
            $this->_redirect('ProductReturn/Front/List');

        }

    }

}
