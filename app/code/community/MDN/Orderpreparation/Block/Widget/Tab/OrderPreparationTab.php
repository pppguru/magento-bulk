<?php

/**
 * tab pour la pr�paration des commandes
 *
 */
class MDN_Orderpreparation_Block_Widget_Tab_OrderPreparationTab extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('order_preparation_tabs');
        //$this->setDestElementId('main-container');
        $this->setDestElementId('order_preparation_tabs');
        $this->setTitle(Mage::helper('customer')->__('Order Preparation'));
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    protected function _beforeToHtml() {

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/fullstock_order')) {
            $this->addTab('fullstockorders', array(
                'label' => Mage::helper('customer')->__('Full stock Orders') . ' (' . mage::getModel('Orderpreparation/ordertoprepare')->countOrders(MDN_Orderpreparation_Model_OrderToPrepare::filterFullstock) . ')',
                'content' => $this->getLayout()->createBlock('Orderpreparation/FullStockOrders')->setTemplate('Orderpreparation/FullStockOrders.phtml')->toHtml()
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/stockless_order')) {
            $this->addTab('stocklessorders', array(
                'label' => Mage::helper('customer')->__('Stockless Orders') . ' (' . mage::getModel('Orderpreparation/ordertoprepare')->countOrders(MDN_Orderpreparation_Model_OrderToPrepare::filterStockless) . ')',
                'content' => $this->getLayout()->createBlock('Orderpreparation/StocklessOrders')->setTemplate('Orderpreparation/StocklessOrders.phtml')->toHtml()
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/ignored_order')) {
            $this->addTab('ignoredorders', array(
                'label' => Mage::helper('customer')->__('Ignored Orders') . ' (' . mage::getModel('Orderpreparation/ordertoprepare')->countOrders(MDN_Orderpreparation_Model_OrderToPrepare::filterIgnored) . ')',
                'content' => $this->getLayout()->createBlock('Orderpreparation/IgnoredOrders')->setTemplate('Orderpreparation/IgnoredOrders.phtml')->toHtml(),
                'active' => true
            ));
        }

        Mage::dispatchEvent('orderpreparartion_create_tabs', array('tab' => $this));

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/selected_orders')) {
            $this->addTab('selectedorders', array(
                'label' => Mage::helper('customer')->__('Selected Orders') . ' (' . mage::getModel('Orderpreparation/ordertoprepare')->countOrders(MDN_Orderpreparation_Model_OrderToPrepare::filterSelected) . ')',
                'content' => $this->getLayout()->createBlock('Orderpreparation/SelectedOrders')->setTemplate('Orderpreparation/SelectedOrders.phtml')->toHtml(),
                'active' => true
            ));
        }
        
        return parent::_beforeToHtml();
    }

    /**
     * Return tab + force refresh + warehouse selector
     */
    protected function _toHtml() {
        
        if (!Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/steps/select_orders'))
            return '';
        
        
        $retour = parent::_toHtml();
        $forceRefreshUrl = $this->getUrl('adminhtml/OrderPreparation_OrderPreparation/RefreshList');

        $button = '';
        $script = '';
        
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/allow_scope_change')) {
            $button = '<center><div class="entry-edit"><fieldset><div>';
            $button .= '<div align="left" style="width: 50%; float: left;">';
            $button .= '<b>' . $this->__('You are preparing orders from warehouse : ') . '</b>' . $this->getWarehouseCombo();
            if (!mage::getStoreConfig('orderpreparation/misc/single_user_mode')) {
              $button .= ' <b>' . $this->__('Operator : ') . '</b>';
            }
            $button .= $this->getOperatorCombo();
            $button .= '</div>';
            $button .= '<div align="right" style="width: 50%; float: left;">';
            $button .= '<button onclick="document.location.href=\'' . $forceRefreshUrl . '\'" class="scalable save" type="button"><span>' . $this->__('Distribute orders in tabs') . '</span></button>';
            $button .= '</div>';
            $button .= '</div></fieldset><div></center>';
            $button .= '<div class="clear"></div>';

            //declare url
            $script = '<script>';
            $script .= "urlChangePreparationWarehouse = '" . $this->getUrl('adminhtml/OrderPreparation_OrderPreparation/setPreparationWarehouse') . "';";
            $script .= "urlChangeOperator = '" . $this->getUrl('adminhtml/OrderPreparation_OrderPreparation/setOperator') . "';";
            $script .= '</script>';
        }

        return $script . $button . $retour;
    }

    /**
     * Return combobox to select preparation warehouse
     */
    protected function getWarehouseCombo() {
        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();

        $warehouses = mage::helper('AdvancedStock/Warehouse')->getWarehousesForPreparation();
        $html = '&nbsp; <select name="preparation_warehouse" id="preparation_warehouse" onchange="changePreparationWarehouse();">';
        foreach ($warehouses as $warehouse) {
            $selected = '';
            if ($preparationWarehouseId == $warehouse->getId())
                $selected = ' selected ';
            $html .= '<option value="' . $warehouse->getId() . '" ' . $selected . '>' . $warehouse->getstock_name() . '</option>';
        }
        $html .= '</select>&nbsp; ';
        return $html;
    }

    /**
     * Return combobox to select operator
     */
    protected function getOperatorCombo() {
        $operatorId = mage::helper('Orderpreparation')->getOperator();
        $html = '';
        
        if (!mage::getStoreConfig('orderpreparation/misc/single_user_mode')) {
           $users = mage::getModel('admin/user')
                        ->getCollection()
                        ->addFieldToFilter('is_active',1)
                        ->setOrder('username','ASC');

          $html = '&nbsp; <select name="operator" id="operator" onchange="changeOperator();">';
          foreach ($users as $user) {
              $selected = '';
              if ($operatorId == $user->getId())
                  $selected = ' selected ';
              $html .= '<option value="' . $user->getId() . '" ' . $selected . '>' . $user->getusername() . '</option>';
          }
          $html .= '</select> &nbsp; ';
        }else{
           $html .= '<hidden name="operator" id="operator" value=" ' . $operatorId . '">';
        }

        return $html;
    }

}
