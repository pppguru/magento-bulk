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
 * Bestsellers Report Grid
 */
class AW_Advancedreports_Block_Advanced_Bestsellers_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    const OPTION_BESTSELLER_GROUPED_SKU = 'advancedreports_bestsellers_options_skutype';

    protected $_routeOption = AW_Advancedreports_Helper_Data::ROUTE_ADVANCED_BESTSELLERS;
    protected $_customData = array();
    protected $_bestsellerVarData;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate($this->_helper()->getGridTemplate());
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridBestsellers');
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        /** @var AW_Advancedreports_Model_Mysql4_Collection_Bestsellers $collection */
        $collection = Mage::getResourceModel('advancedreports/collection_bestsellers');

        $this->setCollection($collection);

        $dateFrom = $this->_getMysqlFromFormat($this->getFilter('report_from'));
        $dateTo = $this->_getMysqlToFormat($this->getFilter('report_to'));

        $this->getCollection()->setDateFilter($dateFrom, $dateTo)->setState();

        $storeIds = $this->getStoreIds();
        if (count($storeIds)) {
            $this->setStoreFilter($storeIds);
        }

        $this->addOrderItems($this->getCustomOption('advancedreports_bestsellers_options_bestsellers_count'));

        $key = $this->getFilter('reload_key');
        if ($key === 'qty') {
            $this->getCollection()->orderByQty();
        } elseif ($key === 'total') {
            $this->getCollection()->orderByTotal();
        }
        //echo $this->getCollection()->getSelect();
        $this->_prepareData();
    }

    public function getRoute()
    {
        return $this->_routeOption;
    }

    /**
     * Collection
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Bestsellers
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Retrieves initialization array for custom report option
     *
     * @return array
     */
    public function  getCustomOptionsRequired()
    {
        $array = parent::getCustomOptionsRequired();
        $skutypes = Mage::getSingleton('advancedreports/system_config_source_skutype')->toOptionArray();
        $addArray = array(
            array(
                'id'      => 'advancedreports_bestsellers_options_bestsellers_count',
                'type'    => 'text',
                'args'    => array(
                    'label'    => $this->_helper()->__('Products to show'),
                    'title'    => $this->_helper()->__('Products to show'),
                    'name'     => 'advancedreports_bestsellers_options_bestsellers_count',
                    'class'    => '',
                    'required' => true,
                ),
                'default' => '10',
            ),
            array(
                'id'      => self::OPTION_BESTSELLER_GROUPED_SKU,
                'type'    => 'select',
                'args'    => array(
                    'label'    => $this->_helper()->__('SKU usage'),
                    'title'    => $this->_helper()->__('SKU usage'),
                    'name'     => self::OPTION_BESTSELLER_GROUPED_SKU,
                    'class'    => '',
                    'required' => true,
                    'values'   => $skutypes,
                ),
                'default' => AW_Advancedreports_Model_System_Config_Source_Skutype::SKUTYPE_SIMPLE,
            ),
        );
        return array_merge($array, $addArray);
    }

    /**
     * Filter collection by Store Ids
     *
     * @param array $storeIds
     *
     * @return AW_Advancedreports_Block_Advanced_Bestsellers_Grid
     */
    public function setStoreFilter($storeIds)
    {
        $this->getCollection()->setStoreFilter($storeIds);
        return $this;
    }

    public function addOrderItems($limit = 10)
    {
        $skuType = $this->getCustomOption(self::OPTION_BESTSELLER_GROUPED_SKU);

        $this->getCollection()->addOrderItems($limit, $skuType);
        return $this;
    }

    public function getChartParams()
    {
        return $this->_helper()->getChartParams($this->_routeOption);
    }

    public function getNeedReload()
    {
        return $this->_helper()->getNeedReload($this->_routeOption);
    }

    public function hasRecords()
    {
        return (count($this->_customData))
        && $this->_helper()->getChartParams($this->_routeOption)
        && count($this->_helper()->getChartParams($this->_routeOption));
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }

    public function getShowCustomGrid()
    {
        return true;
    }

    public function getHideNativeGrid()
    {
        return true;
    }

    public function getHideShowBy()
    {
        return true;
    }

    protected function _addBestsellerData($row)
    {
        if (count($this->_customData)) {
            foreach ($this->_customData as &$d) {
                if ($d['id'] === $row['id']) {
                    $qty = $d['ordered_qty'];
                    $total = $d['total'];
                    unset($d['total']);
                    unset($d['ordered_qty']);
                    $d['total'] = $row['total'] + $total;
                    $d['ordered_qty'] = $row['ordered_qty'] + $qty;
                    return $this;
                }
            }
        }
        $this->_customData[] = $row;
        return $this;
    }

    /*
     * Need to sort bestsellers array
     */
    protected function _compareTotalElements($a, $b)
    {
        if ($a['total'] == $b['total']) {
            return 0;
        }
        return ($a['total'] > $b['total']) ? -1 : 1;
    }

    /*
    * Need to sort bestsellers array
    */
    protected function _compareQtyElements($a, $b)
    {
        if ($a['ordered_qty'] == $b['ordered_qty']) {
            return 0;
        }
        return ($a['ordered_qty'] > $b['ordered_qty']) ? -1 : 1;
    }

    /*
     * Prepare data array for Pie and Grid
     */
    protected function _prepareData()
    {
        # Extract data from collection
        $col = $this->getCollection();
        if ($col && count($col)) {
            foreach ($col as $_subItem) {
                $row = array();
                # Get all colummns values
                foreach ($this->_columns as $column) {
                    if (!$column->getIsSystem()) {
                        $row[$column->getIndex()] = $column->getRowField($_subItem);
                    }
                }
                # Add quantity
                $row['ordered_qty'] = $_subItem->getSumQty();
                # Add total
                $row['total'] = $_subItem->getSumTotal();
                # Add product id
                $row['id'] = $_subItem->getProductId();
                if (isset($row['id']) && isset($row['name'])) {
                    $_product = Mage::getModel('catalog/product')->load($row['id']);
                    if ($_product->getData()) {
                        $row['name'] = $_product->getName();
                    }
                    unset($_product);
                }
                $this->_addBestsellerData($row);
            }
        }

        if (!count($this->_customData)) {
            return $this;
        }

        $key = $this->getFilter('reload_key');
        if ($key === 'qty') {
            # Sort data
            usort($this->_customData, array(&$this, "_compareQtyElements"));
            # Splice array
            array_splice(
                $this->_customData, $this->getCustomOption('advancedreports_bestsellers_options_bestsellers_count')
            );

            # All qty
            $qty = 0;
            foreach ($this->_customData as $d) {
                $qty += $d['ordered_qty'];
            }
            foreach ($this->_customData as $i => &$d) {
                $d['order'] = $i + 1;
                $d['percent'] = round($d['ordered_qty'] * 100 / $qty, 2) . ' %';
                $d['percent_data'] = round($d['ordered_qty'] * 100 / $qty, 2);
                //Add title
                $d['title'] = $d['name'] . ' (' . $d['percent'] . ')';
            }
        } elseif ($key === 'total') {
            //Sort data
            usort($this->_customData, array(&$this, "_compareTotalElements"));
            //Splice array
            array_splice(
                $this->_customData, $this->getCustomOption('advancedreports_bestsellers_options_bestsellers_count')
            );

            //All qty
            $total = 0;
            foreach ($this->_customData as $d) {
                $total += $d['total'];
            }
            foreach ($this->_customData as $i => &$d) {
                $d['order'] = $i + 1;
                $d['percent'] = round($d['total'] * 100 / $total, 2) . ' %';
                $d['percent_data'] = round($d['total'] * 100 / $total, 2);
                //Add title
                $d['title'] = $d['name'] . ' (' . $d['percent'] . ')';
            }
        } else {
            return $this;
        }

        $this->_helper()->setChartData($this->_customData, $this->_helper()->getDataKey($this->_routeOption));
        parent::_prepareData();
        return $this;
    }

    public function getBestsellerData()
    {
        return $this->_customData;
    }

    public function getCustomVarData()
    {
        if ($this->_customVarData) {
            return $this->_customVarData;
        }
        foreach ($this->_customData as $d) {
            $obj = new Varien_Object();
            $obj->setData($d);
            $this->_customVarData[] = $obj;
        }
        return $this->_customVarData;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'order',
            array(
                 'header'   => Mage::helper('reports')->__('N'),
                 'width'    => '60px',
                 'align'    => 'right',
                 'index'    => 'order',
                 'type'     => 'number',
                 'sortable' => false,
            )
        );

        $this->addColumn(
            'sku',
            array(
                 'header'   => Mage::helper('reports')->__('SKU'),
                 'width'    => '120px',
                 'index'    => 'sku',
                 'type'     => 'text',
                 'sortable' => false,
            )
        );

        $this->addColumn(
            'name',
            array(
                'header'   => Mage::helper('reports')->__('Product Name'),
                'index'    => 'name',
                'type'     => 'text',
                'sortable' => false,
            )
        );

        $this->addColumn(
            'percent',
            array(
                'header'                 => $this->_helper()->__('Percent'),
                'width'                  => '60px',
                'align'                  => 'right',
                'index'                  => 'percent',
                'type'                   => 'text',
                'sortable'               => false,
                'custom_sorting_percent' => 1,
            )
        );

        $this->addColumn(
            'ordered_qty',
            array(
                'header'   => $this->_helper()->__('Quantity'),
                'width'    => '120px',
                'align'    => 'right',
                'index'    => 'ordered_qty',
                'total'    => 'sum',
                'type'     => 'number',
                'sortable' => false,
            )
        );

        $this->addColumn(
            'total',
            array(
                'header'        => Mage::helper('reports')->__('Total'),
                'width'         => '120px',
                'type'          => 'currency',
                'currency_code' => $this->getCurrentCurrencyCode(),
                'total'         => 'sum',
                'index'         => 'total',
                'sortable'      => false,
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'   => Mage::helper('catalog')->__('Action'),
                'width'    => '50px',
                'type'     => 'action',
                'align'    => 'right',
                'getter'   => 'getId',
                'actions'  => array(
                    array(
                        'caption' => $this->_helper()->__('View'),
                        'url'     => array(
                            'base'   => 'adminhtml/catalog_product/edit',
                            'params' => array(),
                        ),
                        'field'   => 'id',
                    )
                ),
                'filter'   => false,
                'sortable' => false,
                'index'    => 'stores',
            )
        );

        $this->addExportType('*/*/exportOrderedCsv', $this->_helper()->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel', $this->_helper()->__('Excel'));

        return $this;
    }

    public function getChartType()
    {
        return AW_Advancedreports_Block_Chart::CHART_TYPE_PIE3D;
    }

    public function getRowUrl($row)
    {
        //return $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getProductId() ));
    }

    public function getExcel($filename = '')
    {
        $this->_prepareGrid();

        $data = array();
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem() && $column->getIndex() != 'stores') {
                $row[] = $column->getHeader();
            }
        }
        $data[] = $row;

        if (count($this->getCustomVarData())) {
            foreach ($this->getCustomVarData() as $obj) {
                $row = array();
                foreach ($this->getColumns() as $column) {
                    if (!$column->getIsSystem() && $column->getIndex() != 'stores') {
                        $row[] = $column->getRowField($obj);
                    }
                }
                $data[] = $row;
            }
        }

        $xmlObj = new Varien_Convert_Parser_Xml_Excel();
        $xmlObj->setVar('single_sheet', $filename);
        $xmlObj->setData($data);
        $xmlObj->unparse();

        return $xmlObj->getData();
    }

    public function getCsv($filename = '')
    {
        $csv = '';
        $this->_prepareGrid();
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem() && $column->getIndex() != 'stores') {
                $data[] = '"' . $column->getHeader() . '"';
            }
        }
        $csv .= implode(',', $data) . "\n";

        if (!count($this->getCustomVarData())) {
            return $csv;
        }

        foreach ($this->getCustomVarData() as $obj) {
            $data = array();
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem() && $column->getIndex() != 'stores') {
                    $data[]
                        = '"' . str_replace(array('"', '\\'), array('""', '\\\\'), $column->getRowField($obj)) . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }
        return $csv;
    }
}
