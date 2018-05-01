<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    protected $_gridAttributes = array();

    protected function _preparePage()
    {
        $this->getCollection()->setPageSize((int) $this->getParam($this->getVarNameLimit(), Mage::getStoreConfig('ampgrid/general/number_of_records')));        
        $this->getCollection()->setCurPage((int) $this->getParam($this->getVarNamePage(), $this->_defaultPage));
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setExportVisibility('true');
        $this->setChild('attributes_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('ampgrid')->__('Grid Attribute Columns'),
                    'onclick'   => 'pAttribute.showConfig();',
                    'class'     => 'task'
                ))
        );

        if (Mage::getStoreConfig('ampgrid/general/sorting'))
        {
            $this->setChild('sortcolumns_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                    'label'     => Mage::helper('ampgrid')->__('Sort Columns'),
                    'onclick'   => 'pgridSortable.init();',
                    'class'     => 'task',
                    'id'        => 'pgridSortable_button',
                ))
            );
        }
        
        if (Mage::helper('ampgrid/mode')->isMulti())
        {
            $this->setChild('saveall_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('ampgrid')->__('Save'),
                    'onclick'   => 'peditGrid.saveAll();',
                    'class'     => 'save disabled',
                    'id'        => 'ampgrid_saveall_button'
                ))
        );
        }
        
        $this->_gridAttributes = Mage::helper('ampgrid')->prepareGridAttributesCollection();
        
        return $this;
    }
    
   protected function _addColumnFilterToCollection($column)
    {
       
        if ($this->getCollection()) {
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
       
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $cond = $column->getFilter()->getCondition();
                                
                if ($field && isset($cond)) {
            
                    if (strpos($field, 'am_attribute_') !== FALSE){
                        $attribute = str_replace('am_attribute_', '', $field);
                        
                        $this->getCollection()->addAttributeToFilter($attribute, $cond);
//                        print $this->getCollection()->getSelect();
                    } else {
//                        var_dump(1234);
//                        exit(1);
                        parent::_addColumnFilterToCollection($column);
//                        $this->getCollection()->addFieldToFilter($field , $cond);
                    }
                }
            }
        }
        return $this;
    }
    
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ?
                $column->getFilterIndex() : $column->getIndex();
        
            if (strpos($columnIndex, 'am_attribute_') !== FALSE){
                $attribute = str_replace('am_attribute_', '', $columnIndex);
                $collection->addAttributeToSort($attribute, $column->getDir());
            } else {
                parent::_setCollectionOrder($column);
                
//                var_dump($columnIndex);
//                exit(1);
//                $this->setOrder($collection, $columnIndex, strtoupper($column->getDir()));                
            }
        }
        return $this;
    }
    
    public function setOrder($collection, $attribute, $dir = 'desc')
    {
        if ($attribute == 'price') {
            $collection->addAttributeToSort($attribute, $dir);
        } else {
            $collection->getSelect()->order($attribute . ' ' .strtoupper($dir));
        }
        return $collection;
    }
    
    protected function _prepareCustomSorting(){
        
        $sortCollection = $this->_getCollection();
        
        Mage::register('product_collection', $sortCollection);
        
        $sortCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        
        $sortCollection->getSelect()->columns('e.entity_id');
        
//        $sortCollection->getSelect()->joinLeft(
//            array('related' => $sortCollection->getTable('catalog/product_link')),
//            'related.product_id=e.entity_id and related.link_type_id = '.Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED,
//            array(
//                'related_count' => 'COUNT(related.link_id)'
//            ));
        
        $sortCollection->getSelect()->joinLeft(
            array('related' => $sortCollection->getTable('catalog/product_link')),
            'related.product_id=e.entity_id and related.link_type_id = '.Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED,
            array(
                'COUNT(related.link_id) as related_products'
            ));
        
        $sortCollection->getSelect()->joinLeft(
            array('upsell' => $sortCollection->getTable('catalog/product_link')),
            'upsell.product_id=e.entity_id and upsell.link_type_id = '.Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL,
            array(
                'COUNT(upsell.link_id) as up_sells'
            ));
        
        $sortCollection->getSelect()->joinLeft(
            array('crosssel' => $sortCollection->getTable('catalog/product_link')),
            'crosssel.product_id=e.entity_id and crosssel.link_type_id = '.Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL,
            array(
                'COUNT(crosssel.link_id) as cross_sells'
            ));
        
        $sortCollection->getSelect()->group('e.entity_id');
        
        $this->setCollection($sortCollection);
        
        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
                
        
        
        
        
        $tableName = uniqid('am_expg_tmp_');
        
        $drop = 'DROP TEMPORARY TABLE IF EXISTS `' . $tableName . '`';
        $create = '
            CREATE TEMPORARY TABLE `' . $tableName . '` (
            `order_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `product_id` INT(10) UNSIGNED NOT NULL DEFAULT "0",
            PRIMARY KEY (`order_id`),
            UNIQUE KEY `product_id` (`product_id`)
          ) ENGINE=MYISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
        ';
        
        $sortCollection->getSelect()->reset(Zend_Db_Select::LIMIT_COUNT);
        $sortCollection->getSelect()->reset(Zend_Db_Select::LIMIT_OFFSET);
        
        $insert = 'INSERT INTO `' . $tableName . '` (product_id) ' . 'select entity_id from (' . $sortCollection->getSelect() . ') as t';
        
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $write->query($drop);
        $write->query($create);
        $write->query($insert);
            
        return $tableName;
    }
    
    protected function _getCollection(){
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        if (Mage::getStoreConfig('ampgrid/additional/avail'))
        {
            $collection->joinField('is_in_stock',
                'cataloginventory/stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }

        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $adminStore);
            $collection->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        // ^^^^ ORIGINAL CODE END


        /**
        * Adding special price if set in configuration        
        */
        if (Mage::getStoreConfig('ampgrid/additional/special_price_dates'))
        {
            $collection->joinAttribute('special_from_date', 'catalog_product/special_from_date', 'entity_id', null, 'left', $store->getId());
            $collection->joinAttribute('special_to_date', 'catalog_product/special_to_date', 'entity_id', null, 'left', $store->getId());
        }
        
        /**
        * Adding code to the grid
        */
        
        if (Mage::getStoreConfig('ampgrid/additional/thumb'))
        {
            $collection->joinAttribute('thumbnail', 'catalog_product/thumbnail', 'entity_id', null, 'left', $store->getId());
        }
        
        /**
        * Adding attributes
        */
        if ($this->_gridAttributes->getSize() > 0)
        {
            foreach ($this->_gridAttributes as $attribute)
            {
                $collection->joinAttribute($attribute->getAttributeCode(), 'catalog_product/' . $attribute->getAttributeCode(), 'entity_id', null, 'left', $store->getId());
            }
        }
        
        return $collection;
    }
    
    protected function _prepareCollection()
    {
        
        
        $tableSortingName = $this->_prepareCustomSorting();
        
        $collection = $this->_getCollection();
        
        $this->setCollection($collection);
        
        $this->_preparePage();
        
        $filter   = $this->getParam($this->getVarNameFilter(), null);
        
//        $collection->getSize();
        
//        $collection->getSelect()->joinLeft(
//            array('sorting' => $tableSortingName),
//            'sorting.product_id=e.entity_id',
//            array());
//        
//
//        $t = $collection->getCurPage();
//        $collection->setCurPage(1);
//        
//        $collection->load();
//        $collection->setCurPage($t);
        
        
        if (is_null($filter)) {
            $collection->getSelect()->joinLeft(
                array('sorting' => $tableSortingName),
                'sorting.product_id=e.entity_id',
                array());
        } else {
            $collection->getSelect()->joinInner(
                array('sorting' => $tableSortingName),
                'sorting.product_id=e.entity_id',
                array());
        }
        
        
//        $collection->setOrder('IFNULL(sorting.order_id, 99999)');
        
        $collection->getSelect()->order('IFNULL(sorting.order_id, 99999)');
        
//        $this->getCollection()->getSelect()->limit(2);
        
//        print $this->getCollection()->getSelect().'<br/><br/>';
        
        
        
        
        
        $this->getCollection()->addWebsiteNamesToResult();
        
        
        
        return $this;
        // ^^^^ ORIGINAL CODE END
    }
    
    protected function _prepareColumns()
    {
        $this->addExportType('ampgrid/adminhtml_product/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('ampgrid/adminhtml_product/exportExcel', Mage::helper('customer')->__('Excel XML'));
        if (Mage::getStoreConfig('ampgrid/additional/thumb'))
        {
            // will add thumbnail column to be the first one
            $this->addColumn('thumb',
                array(
                    'header'    => Mage::helper('catalog')->__('Thumbnail'),
                    'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_thumb',
                    'index'		=> 'thumbnail',
                    'sortable'  => true,
                    'filter'    => false,
                    'width'     => 90,
            ));
        }

        
        if (Mage::helper('ampgrid')->isCategoryColumnEnabled())
        {
            $categoryFilter  = false;
            $categoryOptions = array();
            if (Mage::getStoreConfig('ampgrid/additional/category_filter'))
            {
                $categoryFilter = 'ampgrid/adminhtml_catalog_product_grid_filter_category';
                $categoryOptions = Mage::helper('ampgrid/category')->getOptionsForFilter();
            }
            
            // adding categories column
            $this->addColumn('categories',
                array(
                    'header'    => Mage::helper('catalog')->__('Categories'),
                    'index'     => 'category_id',
                    'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_category',
                    'sortable'  => false,
                    'filter'    => $categoryFilter,
                    'type'      => 'options',
                    'options'   => $categoryOptions,
            ));
        }

        if (Mage::getStoreConfig('ampgrid/additional/link')){
            $this->addColumn('Link', array(
                'header'        => $this->__('&gt;&gt;'),
                'index'         => 'name',
                'type'          => 'text',
                'sortable'  => false,
                'filter'    => false,
                'width' => "20px",
                'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_link',
            ));
        }
        parent::_prepareColumns();



        $actionsColumn = null;
        if (isset($this->_columns['action']))
        {
            $actionsColumn = $this->_columns['action'];
            unset($this->_columns['action']);
        }
        // from version 2.4.1
        $colsToRemove = Mage::getStoreConfig('ampgrid/additional/remove');
        if ($colsToRemove)
        {
            $colsToRemove = explode(',', $colsToRemove);
            foreach ($colsToRemove as $c)
            {
                $c = trim($c);
                if (isset($this->_columns[$c]))
                {
                    unset($this->_columns[$c]);
                }                
            }
        }
        
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory') && Mage::getStoreConfig('ampgrid/additional/avail')) 
        {
            $this->addColumn('is_in_stock',
                array(
                    'header'  => Mage::helper('catalog')->__('Availability'),
                    'type'    => 'options',
                    'options' => array(0 => $this->__('Out of stock'), 1 => $this->__('In stock')),
                    'index'   => 'is_in_stock',
            ));
        }

        if (Mage::getStoreConfig('ampgrid/additional/created_at'))
        {
            $this->addColumn('created_at', array(
                'header'        => $this->__('Creation Date'),
                'index'         => 'created_at',
                'type'          => 'date',
            ));
        }

        if (Mage::getStoreConfig('ampgrid/additional/modified_at'))
        {
            $this->addColumn('updated_at', array(
                'header'        => $this->__('Last Modified Date'),
                'index'         => 'updated_at',
                'type'          => 'date',
            ));
        }
        
        // adding special price columns
        if (Mage::getStoreConfig('ampgrid/additional/special_price_dates'))
        {
            $this->addColumn('special_from_date', array(
                'header'        => $this->__('Special Price From'),
                'index'         => 'special_from_date',
                'type'          => 'date',
            ));
            $this->addColumn('special_to_date', array(
                'header'        => $this->__('Special Price To'),
                'index'         => 'special_to_date',
                'type'          => 'date',
            ));
        }
        
        if (Mage::getStoreConfig('ampgrid/additional/related_products'))
        {
            $this->addColumn('related_products', array(
                'header' => $this->__('Related Products'),
                'index' => 'related_products',
//                'sortable' => false,
                'filter' => false,
                'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_related',
            ));
        }
        
        if (Mage::getStoreConfig('ampgrid/additional/up_sells'))
        {
            $this->addColumn('up_sells', array(
                'header' => $this->__('Up Sells'),
                'index' => 'up_sells',
//                'sortable' => false,
                'filter' => false,
                'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_related',
            ));
        }
        
        if (Mage::getStoreConfig('ampgrid/additional/cross_sells'))
        {
            $this->addColumn('cross_sells', array(
                'header' => $this->__('Cross Sells'),
                'index' => 'cross_sells',
//                'sortable' => false,
                'filter' => false,
                'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_related',
            ));
        }



        
        // adding cost column
        
        if ($this->_gridAttributes->getSize() > 0)
        {
            Mage::register('ampgrid_grid_attributes', $this->_gridAttributes);
                    
                    
            Mage::helper('ampgrid')->attachGridColumns($this, $this->_gridAttributes, $this->_getStore());
        }
        
        if ($actionsColumn)
        {
            $this->_columns['action'] = $actionsColumn;
        }


        $this->sortColumnsByDragPosition();
    }

    public function addColumn($columnId, $column){
        
        if (isset($column['sortable']) && !isset($column['renderer']) && $column['sortable'] === FALSE){
            
            
            if (isset($column['type']) && $column['type'] == 'action'){
                $column['renderer']  = 'ampgrid/adminhtml_catalog_product_grid_renderer_action';
            }
            else if (isset($column['options'])){
                $column['renderer']  = 'ampgrid/adminhtml_catalog_product_grid_renderer_options';
            } 
        }
        
        return parent::addColumn($columnId, $column);
    }

    public function sortColumnsByDragPosition()
    {
        if (!Mage::getStoreConfig('ampgrid/general/sorting'))
        {
            return $this;
        }
        $keys = array_keys($this->_columns);
        $values = array_values($this->_columns);

        $extraKey = '';
        if (Mage::getStoreConfig('ampgrid/attr/byadmin'))
        {
            $extraKey = Mage::getSingleton('admin/session')->getUser()->getId();
        }
        $orderedFields = (string) Mage::getStoreConfig('ampgrid/attributes/sorting' . $extraKey);
        if ($orderedFields)
        {
            $orderedFields = explode(',', $orderedFields);
        } else
        {
            return $this;
        }

        for ($i = 0; $i < count($orderedFields) - 1; $i++)
        {
            $columnsOrder[$orderedFields[$i + 1]] = $orderedFields[$i];
        }

        foreach ($columnsOrder as $columnId => $after) {
            if (array_search($after, $keys) !== false) {
                // Moving grid column
                $positionCurrent = array_search($columnId, $keys);

                $key = array_splice($keys, $positionCurrent, 1);
                $value = array_splice($values, $positionCurrent, 1);

                $positionTarget = array_search($after, $keys) + 1;

                array_splice($keys, $positionTarget, 0, $key);
                array_splice($values, $positionTarget, 0, $value);

                $this->_columns = array_combine($keys, $values);
            }
        }

        end($this->_columns);
        $this->_lastColumnId = key($this->_columns);
        return $this;
    }
    
    public function getAttributesButtonHtml()
    {
        return $this->getChildHtml('attributes_button');
    }

    public function getSortColumnsButtonHtml()
    {
        return $this->getChildHtml('sortcolumns_button');
    }
    
    public function getSaveAllButtonHtml()
    {
        return $this->getChildHtml('saveall_button');
    }
       
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html = $this->getSaveAllButtonHtml() . $this->getSortColumnsButtonHtml() . $this->getAttributesButtonHtml() . $html;
        return $html;
    }
    
   protected function _prepareMassaction()
   {
        parent::_prepareMassaction();
        Mage::dispatchEvent('am_product_grid_massaction', array('grid' => $this)); 
   }    
}
