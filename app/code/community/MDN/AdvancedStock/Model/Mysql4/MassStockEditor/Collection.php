<?php

class MDN_AdvancedStock_Model_Mysql4_MassStockEditor_Collection  extends Mage_Catalog_Model_Resource_Product_Collection
{

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinFields();
        return $this;
    }


    protected function _joinFields()
    {

        $this->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('status');

        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if ($manufacturerCode)
            $this->addAttributeToSelect($manufacturerCode);

        $this->getSelect()
            ->join(array('si' => Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')),
                'si.product_id = e.entity_id',
                array('*'));

        //display only stock managed products whatever the type
        $this->getSelect()->where('(use_config_manage_stock = 1 OR manage_stock = 1)');

        return $this;
    }

    protected function _construct()
    {
        $this->_init('catalog/product');
        $this->setRowIdFieldName('item_id');
        $this->_initTables();
    }

    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->columns('item_id');
        return $this->getConnection()->fetchCol($idsSelect);
    }

    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns('COUNT(item_id)')
            ->reset(Zend_Db_Select::HAVING);

        return $select;
    }


    public function setOrder($attribute, $dir = 'DESC')
    {
        switch($attribute) {
            case 'shelf_location':
            case 'stock_id':
            case 'product_id':
            case 'qty':
                $this->getSelect()->order($attribute . ' ' . $dir);
                break;
            default:
                parent::setOrder($attribute, $dir);
        }
        return $this;
    }

    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        switch($attribute) {
            case 'shelf_location':
            case 'stock_id':
            case 'product_id':
            case 'qty':
                $conditionSql = $this->_getConditionSql($attribute, $condition);
                $this->getSelect()->where($conditionSql);
                break;
            default:
                parent::addAttributeToFilter($attribute, $condition, $joinType);
                break;
        }
        return $this;
    }

}