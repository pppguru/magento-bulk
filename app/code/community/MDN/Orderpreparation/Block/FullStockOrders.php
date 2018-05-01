<?php

/**
 * Tableau contenant la liste des commandes pr�parables
 *
 */
class MDN_Orderpreparation_Block_FullStockOrders extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_parentTemplate = '';

    public function __construct() {
        parent::__construct();
        $this->setId('FullStockOrdersGrid');
        $this->_parentTemplate = $this->getTemplate();
        //$this->setTemplate('Orderpreparation/FullStockOrders.phtml');
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
        $this->setDefaultLimit(mage::getStoreConfig('orderpreparation/misc/default_page_size'));
        $this->setUseAjax(true);
        $this->setVarNameFilter('orderpreparation_fullstock');

        $this->setDefaultSort('opp_sort_value');
        $this->setDefaultDir('ASC');
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $collection = mage::getModel('Orderpreparation/ordertoprepare')->getFullStockOrdersFromCache();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns() {
        $this->addColumn('opp_sort_value', array(
            'header' => '',
            'width' => '0px',
            'index' => 'opp_sort_value',
            'filter' => false,
            'sort' => false,
            'renderer' => 'MDN_Orderpreparation_Block_Widget_Grid_Column_Renderer_Empty',
        ));

        $this->addColumn('real_order_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'index' => 'opp_order_increment_id',
        ));

        //Organizer
        $this->addColumn('organizer', array(
            'header' => Mage::helper('Organizer')->__('Organizer'),
            'renderer' => 'MDN_Organizer_Block_Widget_Column_Renderer_Comments',
            'align' => 'center',
            'entity' => 'order',
            'filter' => false,
            'sort' => false,
            'entity_id_field' => 'opp_order_id'
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'opp_shipto_name',
        ));

        $this->addColumn('content', array(
            'header' => Mage::helper('sales')->__('Remain to Ship'),
            'index' => 'opp_remain_to_ship',
            'renderer' => 'MDN_Orderpreparation_Block_Adminhtml_Widget_Grid_Column_Renderer_Content',
            'sortable' => false,
        ));

        $this->addColumn('summary', array(
            'header' => Mage::helper('Orderpreparation')->__('Summary'),
            'align' => 'left',
            'index' => 'opp_details',
            'renderer' => 'MDN_Orderpreparation_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderPrepationGeneratedItems',
            'sortable' => false
        ));

        $this->addColumn('shipping_method', array(
            'header' => Mage::helper('sales')->__('Shipping Method'),
            'index' => 'opp_shipping_method'
        ));

        $this->addColumn('payment_validated', array(
            'header' => Mage::helper('sales')->__('Payment<br>validated'),
            'width' => '42px',
            'index' => 'opp_payment_validated',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));

        Mage::dispatchEvent('orderpreparartion_fullstock_createcolums', array('grid' => $this));

        $this->addColumn('actions', array(
            'header' => Mage::helper('purchase')->__('Actions'),
            'mode' => 'fullstock',
            'renderer' => 'MDN_Orderpreparation_Block_Widget_Grid_Column_Renderer_Actions',
            'align' => 'center',
            'filter' => false,
            'sortable' => false
        ));


        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/FullStockOrderGrid', array('_current' => true));
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

 
    protected function _prepareMassaction() {

        //check permissions
        if (!Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/mass_actions'))
            return $this;

        $this->setMassactionIdField('opp_order_id');
        $this->getMassactionBlock()->setFormFieldName('full_stock_orders_order_ids');

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/mass_actions/add_to_selection')) {
            $this->getMassactionBlock()->addItem('add_selection', array(
                'label' => Mage::helper('sales')->__('Add to Selection'),
                'url' => $this->getUrl('*/*/massAddToSelection'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/mass_actions/download_picking_list')) {
            $this->getMassactionBlock()->addItem('download_picking_list', array(
                'label' => Mage::helper('sales')->__('Download Picking list'),
                'url' => $this->getUrl('*/*/massDownloadPickingList'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/mass_actions/download_preparation_pdf')) {
            $this->getMassactionBlock()->addItem('download_preparation_pdf', array(
                'label' => Mage::helper('sales')->__('Download preparation pdf'),
                'url' => $this->getUrl('*/*/massDownloadPreparationPdf'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation/prepare_order/mass_actions/change_shipping_method')) {
            $this->getMassactionBlock()->addItem('change_shipping_method', array(
                'label' => Mage::helper('sales')->__('Change shipping method'),
                'url' => $this->getUrl('*/*/massChangeShippingMethod'),
                'additional' => array(
                    'methods' => array(
                        'name' => 'method',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => Mage::helper('catalog')->__('Methods'),
                        'values' => mage::helper('Orderpreparation/ShippingMethods')->getArray())
                )
            ));
        }

        return $this;
    }

    /**
     * Retourne les commentaires pour toues les commandes s�lectionn�es
     *
     */
    public function getAllComments() {
        $retour = '';
        $collection = Mage::getSingleton('Orderpreparation/ordertoprepare')->getFullStockOrdersFromCache();
        foreach ($collection as $item) {
            $comments = mage::helper('Organizer')->getEntityCommentsSummary('order', $item->getopp_order_id(), true);
            if ($comments != '')
                $retour .= '<a href="' . $this->getUrl('adminhtml/sales_order/view', array('order_id' => $item->getopp_order_id())) . '"><b>Order #' . $item->getopp_order_increment_id() . '</b></a> : ' . $comments;
        }
        return $retour;
    }
    
    public function getRowUrl($row) {
            return "#" . $row->getId();
    }

}
