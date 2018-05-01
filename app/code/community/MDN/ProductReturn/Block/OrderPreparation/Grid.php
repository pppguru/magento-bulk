<?php
/**
 * Tableau contenant la liste des commandes pr�parables
 *
 */
class MDN_ProductReturn_Block_OrderPreparation_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_parentTemplate = '';

    public function __construct()
    {
        parent::__construct();
        $this->setId('OrderPreparationRmaGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->_parentTemplate = $this->getTemplate();
        //$this->setTemplate('Orderpreparation/SelectedOrders.phtml');
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
        $this->setDefaultLimit(mage::getStoreConfig('orderpreparation/misc/default_page_size'));
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {
        //get selectedOrdersIds
        $selectedOrdersIds = mage::getModel('Orderpreparation/ordertoprepare')->getSelectedOrdersIds();

        //load rma orders to process
        if (mage::helper('ProductReturn/FlatOrder')->isFlatOrder()) {
            //todo : check under 1.4.1.1
            $collection = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('nin' => $selectedOrdersIds))
                ->addAttributeToFilter('order_type', 'rma')
                ->addAttributeToFilter('state', array('nin' => array('complete', 'canceled', 'closed')))
                ->join('sales/order_address', '`sales/order_address`.entity_id=shipping_address_id', array('shipping_name' => "concat(firstname, ' ', lastname)"));
        } else {
            $collection = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('nin' => $selectedOrdersIds))
                ->addAttributeToFilter('order_type', 'rma')
                ->addAttributeToFilter('state', array('nin' => array('complete', 'canceled', 'closed')))
                ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
                ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
                ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                ->addExpressionAttributeToSelect('billing_name',
                    'CONCAT({{billing_firstname}}, " ", {{billing_lastname}})',
                    array('billing_firstname', 'billing_lastname'))
                ->addExpressionAttributeToSelect('shipping_name',
                    'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})',
                    array('shipping_firstname', 'shipping_lastname'));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Defini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {

        $this->addColumn('increment_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'type'   => 'number',
            'index'  => 'increment_id'
        ));

        $this->addColumn('state', array(
            'header' => Mage::helper('sales')->__('State'),
            'index'  => 'state'
        ));

        //Organizer
        $this->addColumn('organizer', array(
            'header'   => Mage::helper('Organizer')->__('Organizer'),
            'renderer' => 'MDN_Organizer_Block_Widget_Column_Renderer_Comments',
            'align'    => 'center',
            'entity'   => 'order',
            'filter'   => false,
            'sort'     => false
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index'  => 'shipping_name'
        ));

        $this->addColumn('content', array(
            'header'   => Mage::helper('ProductReturn')->__('Content'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_OrderPreparationContent',
            'index'    => 'content',
            'sortable' => false,
            'filter'   => false
        ));

        $this->addColumn('preparation_warehouse', array(
            'header'   => Mage::helper('AdvancedStock')->__('Preparation<br>Warehouse'),
            'width'    => '80',
            'index'    => 'preparation_warehouse',
            'align'    => 'center',
            'type'     => 'options',
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_PreparationWarehouse',
            'filter'   => false,
            'sort'     => false
        ));

        //Mage::dispatchEvent('orderpreparartion_selected_createcolums', array('grid'=>$this));

        $this->addColumn('actions', array(
            'header'   => Mage::helper('purchase')->__('Actions'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_OrderPreparationAction',
            'align'    => 'center',
            'filter'   => false,
            'sortable' => false
        ));


        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/ProductReturn_OrderPreparation/Grid', array('_current' => true));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));

        return $this->fetchView($templateName);
    }

    /**
     * Retourne l'id du client courant
     *
     */
    public function getCurrentCustomerId()
    {
        return Mage::registry('current_customer')->getId();
    }

    /**
     * Return comments for all orders
     *
     */
    public function getAllComments()
    {
        $retour     = '';
        $collection = Mage::getSingleton('Orderpreparation/ordertoprepare')
            ->getSelectedOrders();
        foreach ($collection as $item) {
            $comments = mage::helper('Organizer')->getEntityCommentsSummary('order', $item->getId(), true);
            if ($comments != '')
                $retour .= '<a href="' . $this->getUrl('adminhtml/sales_order/view', array('order_id' => $item->getId())) . '"><b>Order #' . $item->getincrement_id() . '</b></a> : ' . $comments;
        }

        return $retour;
    }

}