<?php

/*
 * 
 */
class MDN_Orderpreparation_Block_Widget_Grid_Column_Renderer_Actions extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $mode = $this->getColumn()->getmode();
        $retour = '';

        switch ($mode) {
            case 'selected':
                if (Mage::getSingleton('admin/session')->isAllowed('admin/sales/order/actions/view')) {
                    $retour = '<button onclick="window.open(\''.$this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getorder_id())).'\');" class="scalable" type="button"><span>' . $this->__('View order') . '</span></button>';
                }
                if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/mass_actions/add_to_selection'))
                    $retour .= '<br><button style="margin-top: 10px;" onclick="setLocation(\''.$this->getUrl('adminhtml/OrderPreparation_OrderPreparation/RemoveFromSelection', array('order_id' => $row->getorder_id())).'\');" class="scalable delete" type="button"><span>' . $this->__('Remove') . '</span></button>';
                break;
            case 'fullstock':
                if (Mage::getSingleton('admin/session')->isAllowed('admin/sales/order/actions/view'))
                    $retour = '<button onclick="window.open(\''.$this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getopp_order_id())).'\');" class="scalable" type="button"><span>' . $this->__('View order') . '</span></button>';
                if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/mass_actions/add_to_selection'))
                    $retour .= '<br><button style="margin-top: 10px;" onclick="setLocation(\''.$this->getUrl('adminhtml/OrderPreparation_OrderPreparation/AddToSelection', array('order_id' => $row->getopp_order_id())).'\');" class="scalable save" type="button"><span>' . $this->__('Select') . '</span></button>';
                break;
            case 'stockless':
                if (Mage::getSingleton('admin/session')->isAllowed('admin/sales/order/actions/view'))
                    $retour = '<button onclick="window.open(\''.$this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getopp_order_id())).'\');" class="scalable" type="button"><span>' . $this->__('View order') . '</span></button>';
                if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/mass_actions/add_to_selection'))
                    $retour .= '<br><button style="margin-top: 10px;" onclick="setLocation(\''.$this->getUrl('adminhtml/OrderPreparation_OrderPreparation/AddToSelection', array('order_id' => $row->getopp_order_id())).'\');" class="scalable save" type="button"><span>' . $this->__('Select') . '</span></button>';
                break;
            case 'ignored':
                if (Mage::getSingleton('admin/session')->isAllowed('admin/sales/order/actions/view'))
                    $retour = '<button onclick="window.open(\''.$this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getopp_order_id())).'\');" class="scalable" type="button"><span>' . $this->__('View order') . '</span></button>';
                if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/mass_actions/add_to_selection')) {
                    if( Mage::getStoreConfig('orderpreparation/misc/enable_ignored_orders_selection')) {
                        $retour .= '<br><button style="margin-top: 10px;" onclick="setLocation(\'' . $this->getUrl('adminhtml/OrderPreparation_OrderPreparation/AddToSelection', array('order_id' => $row->getopp_order_id())) . '\');" class="scalable save" type="button"><span>' . $this->__('Select') . '</span></button>';
                    }
                }
                break;
        }

        return $retour;
    }

}