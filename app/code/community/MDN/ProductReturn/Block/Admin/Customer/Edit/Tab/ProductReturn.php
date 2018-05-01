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
class MDN_ProductReturn_Block_Admin_Customer_Edit_Tab_ProductReturn extends Mage_Adminhtml_Block_Widget_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductReturnGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('ProductReturn')->__('No Items Found'));

        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {

        $CustomerId = $this->getRequest()->getParam('id');
        $collection = Mage::getModel('ProductReturn/Rma')->loadByCustomer($CustomerId);

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

        $this->addColumn('rma_ref', array(
            'header' => Mage::helper('ProductReturn')->__('Ref'),
            'index'  => 'rma_ref',
            'width'  => '100px'
        ));

        $this->addColumn('rma_created_at', array(
            'header' => Mage::helper('ProductReturn')->__('Date'),
            'index'  => 'rma_created_at',
            'type'   => 'date'
        ));

        $this->addColumn('rma_customer_name', array(
            'header' => Mage::helper('ProductReturn')->__('Customer'),
            'index'  => 'rma_customer_name'
        ));

        $this->addColumn('rma_status', array(
            'header'  => Mage::helper('ProductReturn')->__('Status'),
            'index'   => 'rma_status',
            'type'    => 'options',
            'options' => mage::getModel('ProductReturn/Rma')->getStatuses(),
        ));

        $this->addColumn('rma_products', array(
            'header'   => Mage::helper('ProductReturn')->__('Products'),
            'index'    => 'rma_products',
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnProducts',
            'filter'   => false,
            'sortable' => false
        ));

        $this->addColumn('comments', array(
            'header'   => Mage::helper('ProductReturn')->__('Comments'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_Comments'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/ProductReturn_Admin/SelectedProductReturnGrid', array('_current' => true));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));

        return $this->fetchView($templateName);
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/ProductReturn_Admin/Edit', array('rma_id' => $row->getId()));
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('ProductReturn')->__('Product Returns');
    }

    public function getTabTitle()
    {
        return Mage::helper('ProductReturn')->__('Product Returns');
    }

    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');

        return (bool)$customer->getId();
    }

    public function isHidden()
    {
        return false;
    }


    /**
     * AJAX TAB's
     * If you want to use an AJAX tab, uncomment the following functions
     * Please note that you will need to setup a controller to recieve
     * the tab content request
     *
     */
    /**
     * Retrieve the class name of the tab
     * Return 'ajax' here if you want the tab to be loaded via Ajax
     *
     * return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Determine whether to generate content on load or via AJAX
     * If true, the tab's content won't be loaded until the tab is clicked
     * You will need to setup a controller to handle the tab request
     *
     * @return bool
     */
    public function getSkipGenerateContent()
    {
        return true;
    }

    /**
     * Retrieve the URL used to load the tab content
     * Return the URL here used to load the content by Ajax
     * see self::getSkipGenerateContent & self::getTabClass
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('adminhtml/ProductReturn_Admin/SelectedProductReturnGrid', array('_current' => true));
    }

    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'orders';
    }
}
