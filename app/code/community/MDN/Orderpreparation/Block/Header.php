<?php

/**
 * Block pour l'index de la page de pr�paration de commandes
 *
 */
class MDN_OrderPreparation_Block_Header extends Mage_Core_Block_Template {

    protected function _construct() {
        parent::_construct();

        $this->setTemplate('Orderpreparation/Header.phtml');
    }

    /**
     * return button list
     *
     */
    public function getButtons() {

        $retour = array();

        $noOrderSelectedJsPopup = '';
        if(!$this->isThereAnySelectedOrder()){
            $noOrderSelectedJsPopup = "alert('".$this->__('Please select at least one order to prepare')."'); return false;";
        }
        
        //select orders
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/steps/select_orders'))
        {
            $item = array();
            $item['position'] = count($retour) + 1;
            $item['onclick'] = "document.location.href='" . Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_OrderPreparation') . "'";
            $item['caption'] = $this->__('Select orders');
            $item['base_url'] = 'OrderPreparation/OrderPreparation/index';
            $retour['select_orders'] = $item;
        }        
        
        //print (or download) picking list
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/steps/picking_list'))
        {
            if (Mage::getStoreConfig('orderpreparation/order_preparation_step/display_picking_list_button')) {
                if (mage::getStoreConfig('orderpreparation/order_preparation_step/print_method') == 'download') {
                    $item = array();
                    $item['position'] = count($retour) + 1;
                    $item['onclick'] = $noOrderSelectedJsPopup."document.location.href='" . Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_OnePagePreparation/DownloadPickingList') . "'";
                    $item['caption'] = $this->__('Picking list');
                    $retour['download_picking_list'] = $item;
                } else {
                    $item = array();
                    $item['position'] = count($retour) + 1;
                    $confirmMsg = $this->__('Picking list sent to printer');
                    $item['onclick'] = $noOrderSelectedJsPopup."ajaxCall('" . Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_OnePagePreparation/PrintPickingList') . "', '" . $confirmMsg . "')";
                    $item['caption'] = $this->__('Picking list');
                    $retour['print_picking_list'] = $item;
                }
            }
        }

        //Create shipments & invoices
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/steps/create_shipment_invoice'))
        {
            if (Mage::getStoreConfig('orderpreparation/order_preparation_step/display_create_shipments_button') == 1) {
                $item = array();
                $item['position'] = count($retour) + 1;
                $item['onclick'] = $noOrderSelectedJsPopup."document.location.href='" . Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_OrderPreparation/Commit') . "'";
                $item['caption'] = $this->__('Create shipments/invoices');
                $item['base_url'] = 'OrderPreparation/OrderPreparation/ShipmentAndInvoicesCreated';
                $retour['create_objects'] = $item;
            }
        }

        //Packing
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/steps/packing'))
        {
            if (mage::getStoreConfig('orderpreparation/order_preparation_step/display_packing_button')) {
                $item = array();
                $item['position'] = count($retour) + 1;
                $item['onclick'] = $noOrderSelectedJsPopup."document.location.href='" . Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_Packing') . "'";
                $item['caption'] = $this->__('Packing');
                $item['base_url'] = 'OrderPreparation/Packing/index';
                $retour['process_orders'] = $item;
            }
        }

        //Download documents
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/steps/print_download_documents'))
        {
            if (Mage::getStoreConfig('orderpreparation/order_preparation_step/display_download_document_button') == 1) {
                if (mage::getStoreConfig('orderpreparation/order_preparation_step/print_method') == 'download') {
                    $item = array();
                    $item['position'] = count($retour) + 1;
                    $item['onclick'] = $noOrderSelectedJsPopup."document.location.href='" . Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_OrderPreparation/DownloadDocuments') . "'";
                    $item['caption'] = $this->__('Download documents');
                    $retour['download_documents'] = $item;
                } else {
                    $item = array();
                    $item['position'] = count($retour) + 1;
                    $confirmMsg = $this->__('Documents sent to printer');
                    $item['onclick'] = $noOrderSelectedJsPopup."ajaxCall('" . Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_OrderPreparation/PrintDocuments') . "', '" . $confirmMsg . "')";
                    $item['caption'] = $this->__('Print documents');
                    $retour['print_documents'] = $item;
                }
            }
        }

        //Import trackings
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/steps/shipping_label'))
        {
            if (Mage::getStoreConfig('orderpreparation/order_preparation_step/display_shipping_label_button') == 1) {
                $item = array();
                $item['position'] = count($retour) + 1;
                $item['onclick'] = $noOrderSelectedJsPopup."document.location.href='" . Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_CarrierTemplate/ImportTracking') . "'";
                $item['caption'] = $this->__('Shipping label');
                $item['base_url'] = 'OrderPreparation/CarrierTemplate/ImportTracking';
                $retour['shipping_label_trackings'] = $item;
            }
        }

        //Notify customers
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/steps/notify'))
        {
            if (Mage::getStoreConfig('orderpreparation/order_preparation_step/display_notify_button')) {
                $item = array();
                $item['position'] = count($retour) + 1;
                $confirmMsg = $this->__('Customers notified');
                $item['onclick'] = $noOrderSelectedJsPopup."ajaxCall('" . Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_OrderPreparation/NotifyCustomers') . "', '" . $confirmMsg . "')";
                $item['caption'] = $this->__('Notify');
                $retour['notify_customers'] = $item;
            }
        }
        
        //Finish
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/steps/finish'))
        {
            if (Mage::getStoreConfig('orderpreparation/order_preparation_step/display_finish_button')) {
                $item = array();
                $item['position'] = count($retour) + 1;
                $item['onclick'] = $noOrderSelectedJsPopup."document.location.href='" . Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_OrderPreparation/Finish') . "'";
                $item['caption'] = $this->__('Finish');
                $retour['finish'] = $item;
            }
        }        
        return $retour;
    }

    /**
    * Return true if there is at least one select order on orderprepration
    */
    public function isThereAnySelectedOrder(){
        $count = mage::getModel('Orderpreparation/ordertoprepare')->countOrders(MDN_Orderpreparation_Model_OrderToPrepare::filterSelected);
        return ($count>0);
    }
   /**
     *
     * @param <type> $item
     * @return <type>
     */
    public function isCurrentItem($item)
    {
        $request = Mage::app()->getRequest();
        $module = strtolower($request->getModuleName());
        $controller = strtolower($request->getControllerName());
        $action = strtolower($request->getActionName());
        $url = $module.'/'.$controller.'/'.$action;

        if (isset($item['base_url']))
        {

            if (strtolower($item['base_url']) == $url)
                return true;
        }

        return false;
        
    }
    
    
}