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
 * @package    AW_Advancedreports
 * @version    2.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

/**
 * Sales Report Grid
 */
class AW_Advancedreports_Block_Advanced_Sales_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    const OPTION_SALES_GROUPED_SKU = 'advancedreports_sales_options_skutype';

    protected $_routeOption = AW_Advancedreports_Helper_Data::ROUTE_ADVANCED_SALES;
    protected $_optCollection;
    protected $_optCache = array();

    /**
     * Cache with addresses for orders
     *
     * @var array
     */
    protected $_addresses = array();

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate($this->_helper()->getGridTemplate());
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setId('gridAdvancedSales');

        # Init aggregator
        $this->getAggregator()->initAggregator(
            $this, AW_Advancedreports_Helper_Tools_Aggregator::TYPE_LIST, $this->getRoute(),
            $this->_helper()->confOrderDateFilter()
        );
        $storeIds = $this->getStoreIds();
        if (count($storeIds)) {
            $this->getAggregator()->setStoreFilter($storeIds);
        }
    }

    public function getHideShowBy()
    {
        return true;
    }

    /**
     * Retrieves initialization array for custom report option
     *
     * @return array
     */
    public function  getCustomOptionsRequired()
    {
        $array = parent::getCustomOptionsRequired();

        $include = Mage::getModel('advancedreports/system_config_source_include');
        $skutypes = Mage::getSingleton('advancedreports/system_config_source_skutype')->toOptionArray();
        $addArray = array(
            array(
                'id'      => 'include_refunded',
                'type'    => 'select',
                'args'    => array(
                    'label'  => $this->_helper()->__('Include refunded items'),
                    'title'  => $this->_helper()->__('Include refunded items'),
                    'name'   => 'include_refunded',
                    'values' => $include->toOptionArray(),
                ),
                'default' => '1',
            ),
            array(
                'id'      => self::OPTION_SALES_GROUPED_SKU,
                'type'    => 'select',
                'args'    => array(
                    'label'    => $this->_helper()->__('SKU usage'),
                    'title'    => $this->_helper()->__('SKU usage'),
                    'name'     => self::OPTION_SALES_GROUPED_SKU,
                    'class'    => '',
                    'required' => true,
                    'values'   => $skutypes,
                ),
                'default' => AW_Advancedreports_Model_System_Config_Source_Skutype::SKUTYPE_SIMPLE,
            ),

        );
        return array_merge($array, $addArray);
    }

    protected function _addCustomData($row)
    {
        $this->_customData[] = $row;
        return $this;
    }

    /**
     * Prepare array with collected data
     *
     * @param datetime $from
     * @param datetime $to
     *
     * @return array
     */
    public function getPreparedData($from, $to)
    {
        /** @var AW_Advancedreports_Model_Mysql4_Collection_Sales $collection */
        $collection = Mage::getResourceModel('advancedreports/collection_sales');
        $collection->reInitSelect();

        $collection->setDateFilter($from, $to)->setState();
        $storeIds = $this->getStoreIds();
        if (count($storeIds)) {
            $collection->setStoreFilter($storeIds);
        }

        $collection->addOrderItems($this->getCustomOption(self::OPTION_SALES_GROUPED_SKU))
            ->addCustomerInfo()
            ->addManufacturer()
            ->addAddress();

        if (!$this->getCustomOption('include_refunded')) {
            $collection->excludeRefunded();
        }

        $this->setCollection($collection);
        $this->_prepareData();
        return $this->getCustomVarData();
    }

    public function _prepareCollection()
    {
        $this
            ->_setUpReportKey()
            ->_setUpFilters()
        ;

        # Start aggregator
        $dateFrom = $this->_getMysqlFromFormat($this->getFilter('report_from'));
        $dateTo = $this->_getMysqlToFormat($this->getFilter('report_to'));
        $this->getAggregator()->prepareAggregatedCollection($dateFrom, $dateTo);

        /** @var AW_Advancedreports_Model_Mysql4_Cache_Collection $collection */
        $collection = $this->getAggregator()->getAggregatetCollection();
        $this->setCollection($collection);

        if ($sort = $this->_getSort()) {
            $collection->addOrder($sort, $this->_getDir());
            $this->getColumn($sort)->setDir($this->_getDir());
        } else {
            $collection->addOrder('order_created_at', 'DESC');
        }
        $this->_saveFilters();
        $this->_setColumnFilters();
    }

    protected function _addOptionToCache($id, $value)
    {
        $this->_optCache[$id] = $value;
    }

    protected function _optionInCache($id)
    {
        if (count($this->_optCache)) {
            foreach ($this->_optCache as $key => $value) {
                if ($key == $id) {
                    return $value;
                }
            }
        }
        return null;
    }

    protected function _getManufacturer($optionId)
    {
        if (!$this->_optCollection) {
            $this->_optCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setStoreFilter(0, false)
                ->load()
            ;
        }
        # seach in quick cache
        if ($val = $this->_optionInCache($optionId)) {
            return $val;
        }
        # search in chached collection
        foreach ($this->_optCollection as $item) {
            if ($optionId == $item->getOptionId()) {
                $this->_addOptionToCache($optionId, $item->getValue());
                return $item->getValue();
            }
        }
        return null;
    }

    protected function _prepareData()
    {
        foreach ($this->getCollection() as $item) {
            $row = $item->getData();

            if (isset($row['order_ship_country_id'])) {
                $row['order_ship_country'] = $row['order_ship_country_id'];
            }
            if (isset($row['order_bil_country_id'])) {
                $row['order_bil_country'] = $row['order_bil_country_id'];
            }

            # Billing/Shipping logic
            if (isset($row['order_ship_country'])) {
                $row['order_country'] = $row['order_ship_country'];
            } elseif (isset($row['order_bil_country'])) {
                $row['order_country'] = $row['order_bil_country'];
            }
            if (isset($row['order_ship_region'])) {
                $row['order_region'] = $row['order_ship_region'];
            } elseif (isset($row['order_bil_region'])) {
                $row['order_region'] = $row['order_bil_region'];
            }
            if (isset($row['order_ship_city'])) {
                $row['order_city'] = $row['order_ship_city'];
            } elseif (isset($row['order_bil_city'])) {
                $row['order_city'] = $row['order_bil_city'];
            }
            if (isset($row['order_ship_postcode'])) {
                $row['order_postcode'] = $row['order_ship_postcode'];
            } elseif (isset($row['order_bil_postcode'])) {
                $row['order_postcode'] = $row['order_bil_postcode'];
            }
            if (isset($row['order_ship_email'])) {
                $row['customer_email'] = $row['order_ship_email'];
            } elseif (isset($row['order_bil_email'])) {
                $row['customer_email'] = $row['order_bil_email'];
            }
            if ((!isset($row['customer_email']) || !($row['customer_email']))
                && $this->_helper()->checkExtensionVersion('Mage_Sales', '1.4.0.15', '>=')
            ) {
                /** @var $order Mage_Sales_Model_Order */
                $order = Mage::getModel('sales/order')->loadByIncrementId($row['order_increment_id']);
                if ($order->getCustomerEmail()) {
                    $row['customer_email'] = $order->getCustomerEmail();
                } elseif ($order->getShippingAddress() && $order->getShippingAddress()->getEmail()) {
                    $row['customer_email'] = $order->getShippingAddress()->getEmail();
                } elseif ($order->getBillingAddress() && $order->getBillingAddress()->getEmail()) {
                    $row['customer_email'] = $order->getBillingAddress()->getEmail();
                }
            }

            if (isset($row['simple_sku'])) {
                $row['sku'] = $row['simple_sku'];
            }

            if (isset($row['sku'])) {
                $this->_addCustomData($row);
            }
        }
        return $this;
    }

    protected function _prepareColumns()
    {
        $defValue = sprintf("%f", 0);
        $defValue = Mage::app()->getLocale()->currency($this->getCurrentCurrencyCode())->toCurrency($defValue);

        $this->addColumn(
            'order_increment_id',
            array(
                'header'      => $this->_helper()->__('Order #'),
                'index'       => 'order_increment_id',
                'type'        => 'text',
                'width'       => '80px',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'ddl_size'    => 255,
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'order_created_at',
            array(
                'header'        => $this->_helper()->__('Order Date'),
                'index'         => 'order_created_at',
                'type'          => 'datetime',
                'width'         => '140px',
                'is_period_key' => true,
                'ddl_type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'ddl_size'      => null,
                'ddl_options'   => array('nullable' => false),
            )
        );

        $this->addColumn(
            'xsku',
            array(
                'header'      => $this->_helper()->__('SKU'),
                'width'       => '120px',
                'index'       => 'xsku',
                'type'        => 'text',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'ddl_size'    => 255,
                'ddl_options' => array('nullable' => false),
            )
        );

        $this->addColumn(
            'customer_email',
            array(
                'header'      => $this->_helper()->__('Customer Email'),
                'index'       => 'customer_email',
                'type'        => 'text',
                'width'       => '100px',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'ddl_size'    => 255,
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'customer_group',
            array(
                'header'      => $this->_helper()->__('Customer Group'),
                'index'       => 'customer_group',
                'type'        => 'text',
                'width'       => '100px',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'ddl_size'    => 255,
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'order_country',
            array(
                'header'      => $this->_helper()->__('Country'),
                'index'       => 'order_country',
                'type'        => 'country',
                'width'       => '100px',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'ddl_size'    => 10,
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'order_region',
            array(
                'header'      => $this->_helper()->__('Region'),
                'index'       => 'order_region',
                'type'        => 'text',
                'width'       => '100px',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'ddl_size'    => 255,
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'order_city',
            array(
                'header'      => $this->_helper()->__('City'),
                'index'       => 'order_city',
                'type'        => 'text',
                'width'       => '100px',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'ddl_size'    => 255,
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'order_postcode',
            array(
                'header'      => $this->_helper()->__('Zip Code'),
                'index'       => 'order_postcode',
                'type'        => 'text',
                'width'       => '60px',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'ddl_size'    => 255,
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'name',
            array(
                'header'      => $this->_helper()->__('Product Name'),
                'index'       => 'name',
                'type'        => 'text',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'ddl_size'    => 255,
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'product_manufacturer',
            array(
                'header'      => $this->_helper()->__('Manufacturer'),
                'index'       => 'product_manufacturer',
                'type'        => 'text',
                'width'       => '100px',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'ddl_size'    => 255,
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'xqty_ordered',
            array(
                'header'      => $this->_helper()->__('Qty. Ordered'),
                'width'       => '60px',
                'index'       => 'xqty_ordered',
                'total'       => 'sum',
                'type'        => 'number',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'    => '12,4',
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'xqty_invoiced',
            array(
                'header'      => $this->_helper()->__('Qty. Invoiced'),
                'width'       => '60px',
                'index'       => 'xqty_invoiced',
                'total'       => 'sum',
                'type'        => 'number',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'    => '12,4',
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'xqty_shipped',
            array(
                'header'      => $this->_helper()->__('Qty. Shipped'),
                'width'       => '60px',
                'index'       => 'xqty_shipped',
                'total'       => 'sum',
                'type'        => 'number',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'    => '12,4',
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'xqty_refunded',
            array(
                'header'      => $this->_helper()->__('Qty. Refunded'),
                'width'       => '60px',
                'index'       => 'xqty_refunded',
                'total'       => 'sum',
                'type'        => 'number',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'    => '12,4',
                'ddl_options' => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_xprice',
            array(
                'header'           => $this->_helper()->__('Price'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_xprice',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'disable_total'    => 1,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_original_xprice',
            array(
                'header'           => $this->_helper()->__('Original Price'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_original_xprice',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'disable_total'    => 1,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_row_subtotal',
            array(
                'header'           => $this->_helper()->__('Subtotal'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_row_subtotal',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_tax_amount',
            array(
                'header'           => $this->_helper()->__('Tax'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_tax_amount',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_discount_amount',
            array(
                'header'           => $this->_helper()->__('Discounts'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_discount_amount',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_tax_amount',
            array(
                'header'           => $this->_helper()->__('Tax'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_tax_amount',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_row_xtotal',
            array(
                'header'           => $this->_helper()->__('Total'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_row_xtotal',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_row_xtotal_incl_tax',
            array(
                'header'           => $this->_helper()->__('Total Incl. Tax'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_row_xtotal_incl_tax',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_row_xinvoiced',
            array(
                'header'           => $this->_helper()->__('Invoiced'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_row_xinvoiced',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_tax_invoiced',
            array(
                'header'           => $this->_helper()->__('Tax Invoiced'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_tax_invoiced',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_row_xinvoiced_incl_tax',
            array(
                'header'           => $this->_helper()->__('Invoiced Incl. Tax'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_row_xinvoiced_incl_tax',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_row_xrefunded',
            array(
                'header'           => $this->_helper()->__('Refunded'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_row_xrefunded',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_tax_xrefunded',
            array(
                'header'           => $this->_helper()->__('Tax Refunded'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_tax_xrefunded',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'base_row_xrefunded_incl_tax',
            array(
                'header'           => $this->_helper()->__('Refunded Incl. Tax'),
                'width'            => '80px',
                'type'             => 'currency',
                'currency_code'    => $this->getCurrentCurrencyCode(),
                'total'            => 'sum',
                'index'            => 'base_row_xrefunded_incl_tax',
                'column_css_class' => 'nowrap',
                'default'          => $defValue,
                'ddl_type'         => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'ddl_size'         => '12,4',
                'ddl_options'      => array('nullable' => true),
            )
        );

        $this->addColumn(
            'view_order',
            array(
                'header'      => $this->_helper()->__('View Order'),
                'width'       => '70px',
                'type'        => 'action',
                'align'       => 'left',
                'getter'      => 'getOrderId',
                'actions'     => array(
                    array(
                        'caption' => $this->_helper()->__('View'),
                        'url'     => array(
                            'base'   => 'adminhtml/sales_order/view',
                            'params' => array(),
                        ),
                        'field'   => 'order_id',
                    )
                ),
                'filter'      => false,
                'sortable'    => false,
                'index'       => 'order_id',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'ddl_size'    => null,
                'ddl_options' => array('nullable' => true, 'unsigned' => true),
            )
        );

        $this->addColumn(
            'view_product',
            array(
                'header'      => $this->_helper()->__('View Product'),
                'width'       => '70px',
                'type'        => 'action',
                'align'       => 'left',
                'getter'      => 'getProductId',
                'actions'     => array(
                    array(
                        'caption' => $this->_helper()->__('View'),
                        'url'     => array(
                            'base'   => 'adminhtml/catalog_product/edit',
                            'params' => array(),
                        ),
                        'field'   => 'id',
                    )
                ),
                'filter'      => false,
                'sortable'    => false,
                'index'       => 'product_id',
                'ddl_type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'ddl_size'    => null,
                'ddl_options' => array('nullable' => true, 'unsigned' => true),
            )
        );

        $this->addExportType('*/*/exportOrderedCsv', $this->_helper()->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel', $this->_helper()->__('Excel'));
        return $this;
    }

    public function getChartType()
    {
        return 'none';
    }

    public function hasRecords()
    {
        return false;
    }

    public function getPeriods()
    {
        return array();
    }

    public function getGridUrl()
    {
        $params = Mage::app()->getRequest()->getParams();
        $params['_secure'] = Mage::app()->getStore(true)->isCurrentlySecure();
        return $this->getUrl('*/*/grid', $params);
    }

    public function  hasAggregation()
    {
        return true;
    }
}
