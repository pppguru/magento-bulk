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
class MDN_ProductReturn_Block_Admin_GridOrder extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductReturnGridOrder');
        $this->_parentTemplate = $this->getTemplate();
        $this->setDefaultSort('status');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText(Mage::helper('ProductReturn')->__('No Items Found'));
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {
        if (mage::helper('ProductReturn/FlatOrder')->isFlatOrder()) {
            $collection = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->join('sales/order_address', '`sales/order_address`.entity_id=shipping_address_id', array('shipping_name' => "concat(firstname, ' ', lastname)"));
        } else {
            $collection = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
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
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'width'  => '80px',
            'type'   => 'text',
            'index'  => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'          => Mage::helper('sales')->__('Purchased from (store)'),
                'index'           => 'store_id',
                'type'            => 'store',
                'store_view'      => true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index'  => 'created_at',
            'type'   => 'datetime',
            'width'  => '100px',
        ));

        if (mage::helper('ProductReturn/FlatOrder')->isFlatOrder()) {
            $this->addColumn('shipping_name', array(
                'header'       => Mage::helper('sales')->__('Ship to Name'),
                'index'        => 'shipping_name',
                'filter_index' => "concat(firstname, ' ', lastname)"
            ));
        } else {
            $this->addColumn('shipping_name', array(
                'header' => Mage::helper('sales')->__('Ship to Name'),
                'index'  => 'shipping_name',
            ));
        }

        $this->addColumn('products', array(
            'header'   => Mage::helper('sales')->__('Products'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Grid_Column_Renderer_OrderProducts',
            'filter' => false
        ));
        
        $this->addColumn('base_grand_total', array(
            'header'   => Mage::helper('sales')->__('G.T. (Base)'),
            'index'    => 'base_grand_total',
            'type'     => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header'   => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index'    => 'grand_total',
            'type'     => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header'  => Mage::helper('sales')->__('Status'),
            'index'   => 'status',
            'type'    => 'options',
            'width'   => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'    => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base' => 'adminhtml/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
                ));
        }

        return parent::_prepareColumns();

    }

    public function getGridUrl()
    {
        return ''; //$this->getUrl('*/*/wishlist', array('_current'=>true));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));

        return $this->fetchView($templateName);
    }


    /**
     * D�finir l'url pour chaque ligne
     * permet d'acc�der � l'�cran "d'�dition" d'une commande
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/ProductReturn_Admin/Edit', array('order_id' => $row->getId()));
    }


}
