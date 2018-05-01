<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Marketsuite_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('mssCustomerGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Get current selected rule id
     *
     * @return int|null
     */
    public function getCurrentRuleId()
    {
        $data = array();
        $filter = $this->getRequest()->getParam('filter');
        if (is_null($filter)) {
            $filter = $this->getParam('filter');
        }
        parse_str(urldecode(base64_decode($filter)), $data);

        if (!empty($data['marketsuite_rule_id'])) {
            return $data['marketsuite_rule_id'];
        }

        return null;
    }

    /**
     * Prepare collection for grid by MSS Rule Id
     * if rule is not specified get all collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        if ($this->getCurrentRuleId() !== null) {
            $collection = Mage::getModel('marketsuite/api')->exportCustomers($this->getCurrentRuleId());
        } else {
            $collection = Mage::getModel('customer/customer')->getCollection();
        }

        $collection
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('gender')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
        ;

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare massaction block
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('customer_listing_id');
        $this->getMassactionBlock()->setFormFieldName('customer_id');

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt' => 0))
            ->load()
            ->toOptionHash()
        ;

        $this->getMassactionBlock()->addItem('move', array(
            'label'=> Mage::helper('marketsuite')->__('Move selected customers to customer group'),
            'url'  => $this->getUrl('*/*/massMove'),
            'confirm' => Mage::helper('marketsuite')->__('Are you sure?'),
            'additional'=> array(
                'visibility'=> array(
                    'name'=> 'group',
                    'type'=> 'select',
                    'class'=> 'required-entry',
                    'label'=> Mage::helper('marketsuite')->__('Customer groups'),
                    'values'=> $groups
                )
            )
        ));

        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                 'header' => Mage::helper('customer')->__('ID'),
                 'width'  => '50px',
                 'index'  => 'entity_id',
                 'type'   => 'number',
            )
        );

        $this->addColumn(
            'name',
            array(
                 'header' => Mage::helper('customer')->__('Name'),
                 'index'  => 'name',
            )
        );

        $this->addColumn(
            'email',
            array(
                 'header' => Mage::helper('customer')->__('Email'),
                 'width'  => '150',
                 'index'  => 'email',
            )
        );

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt' => 0))
            ->load()
            ->toOptionHash()
        ;

        $this->addColumn(
            'group',
            array(
                 'header'  => Mage::helper('customer')->__('Group'),
                 'width'   => '100',
                 'index'   => 'group_id',
                 'type'    => 'options',
                 'options' => $groups,
            )
        );

        $this->addColumn(
            'Telephone',
            array(
                 'header' => Mage::helper('customer')->__('Telephone'),
                 'width'  => '100',
                 'index'  => 'billing_telephone',
            )
        );

        $this->addColumn(
            'billing_postcode',
            array(
                 'header' => Mage::helper('customer')->__('ZIP'),
                 'width'  => '90',
                 'index'  => 'billing_postcode',
            )
        );

        $this->addColumn(
            'billing_country_id',
            array(
                 'header' => Mage::helper('customer')->__('Country'),
                 'width'  => '100',
                 'type'   => 'country',
                 'index'  => 'billing_country_id',
            )
        );

        $this->addColumn(
            'billing_region',
            array(
                 'header' => Mage::helper('customer')->__('State/Province'),
                 'width'  => '100',
                 'index'  => 'billing_region',
            )
        );

        $this->addColumn(
            'customer_since',
            array(
                 'header'    => Mage::helper('customer')->__('Customer Since'),
                 'type'      => 'datetime',
                 'align'     => 'center',
                 'index'     => 'created_at',
                 'gmtoffset' => true,
            )
        );

        $this->addColumn(
            'gender',
            array(
                 'header'                    => Mage::helper('customer')->__('Gender'),
                 'type'                      => 'options',
                 'align'                     => 'center',
                 'options'                   => Mage::getModel('marketsuite/source_gender')->toOptionHash(),
                 'width'                     => 90,
                 'index'                     => 'gender',
                 'filter_condition_callback' => array($this, '_filterGenderCondition'),
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'website_id',
                array(
                     'header'  => Mage::helper('customer')->__('Website'),
                     'align'   => 'center',
                     'width'   => '80px',
                     'type'    => 'options',
                     'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                     'index'   => 'website_id',
                )
            );
        }

        $this->addColumn(
            'action',
            array(
                 'header'    => Mage::helper('customer')->__('Action'),
                 'align'     => 'center',
                 'width'     => '100',
                 'type'      => 'action',
                 'getter'    => 'getId',
                 'actions'   => array(
                     array(
                         'caption' => Mage::helper('customer')->__('View'),
                         'url'     => array('base' => '*/*/view'),
                         'field'   => 'id',
                     )
                 ),
                 'filter'    => false,
                 'sortable'  => false,
                 'index'     => 'stores',
                 'is_system' => true,
            )
        );

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * Add MSS rule selector before reset button
     *
     * @return string
     */
    public function getResetFilterButtonHtml()
    {
        $filterSelect = $this->getLayout()->createBlock('adminhtml/html_select')->setData(
            array(
                 'id'    => 'marketsuite_rule_id',
                 'title' => Mage::helper('marketsuite')->__('Apply MSS rule:'),
                 'name'  => 'marketsuite_rule_id',
                 'class' => 'select',
                 'style' => 'width:100px;',
                 'value' => $this->getCurrentRuleId(),
            )
        );

        $options = array(
            array(
                'value' => null,
                'label' => Mage::helper('marketsuite')->__('---Please Select---'),
            )
        );

        $options = array_merge(
            $options,
            Mage::getModel('marketsuite/filter')->getActiveRuleCollection()->addSortByName()->toOptionArray()
        );

        $filterSelect->setOptions($options);

        $html = '<span class="filter">' . Mage::helper('marketsuite')->__('Apply MSS rule:') . '&nbsp;'
            . $filterSelect->toHtml()
            . '</span>'
        ;

        return $html . $this->getChildHtml('reset_filter_button');
    }

    /**
     * Get row url for $row
     *
     * @param Varien_Object $row
     *
     * @return bool|string
     */
    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('customer/manage')) {
            return $this->getUrl('*/*/view', array('id' => $row->getId()));
        }
        return false;
    }

    /**
     * Callback function for filter customer collection by gender
     *
     * @param $collection
     * @param $column
     */
    protected function _filterGenderCondition($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == AW_Marketsuite_Model_Source_Gender::NOT_SPECIFIED) {
            $collection->addAttributeToFilter('gender', array('null' => true), 'left');
        } else {
            $collection->addAttributeToFilter('gender', array('eq' => $value));
        }
    }
}