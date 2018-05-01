<?php

class AW_Advancedreports_Model_Mysql4_Collection_Additional_Newvsreturning
    extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Reinitialize collection
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Newvsreturning
     */
    public function reInitOrdersCollection()
    {
        /** @var string $sql Help SQL */
        $sql = null;

        $orderTable = $this->getMainTableName();
        $this->getSelect()->reset();

        $tableAlias = $this->_getSalesCollectionTableAlias();

        $arr = array(
            'date'           => "DATE_FORMAT(main_table.created_at, '%Y-%m-%d') AS date",
            'orders_count'   => "COUNT({$tableAlias}.entity_id)", # Just because it's unique
            'customer_id'    => "{$tableAlias}.customer_id",
            'customer_email' => "{$tableAlias}.customer_email",
        );

        if ($sql) {
            $arr['orders_count'] = new Zend_Db_Expr('(' . $sql . ')');
        }

        $this->getSelect()->from(array($tableAlias => $orderTable), $arr);
        $this->getSelect()->group(array("date", "{$tableAlias}.customer_email"));
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $this->getSelect()->where('main_table.status IN(?)', explode(',', $this->_helper()->confProcessOrders()));
        }
        return $this;
    }

    public function getMainTableName()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $orderTable = $this->_helper()->getSql()->getTable('sales_flat_order');
        } else {
            $orderTable = $this->_helper()->getSql()->getTable('sales_order');
        }
        return $orderTable;
    }

    public function getOldCustomers($from, array $customersEmails)
    {
        $_select = $this->getConnection()->select();
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $_select
            ->from(array('order_flat' => $this->getMainTableName()), 'order_flat.customer_email')
            ->where('order_flat.created_at < ?', $from)
            ->where('order_flat.customer_email IN(?)', $customersEmails)
            ->group('order_flat.customer_email')
        ;
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $_select->where('order_flat.status IN(?)', explode(',', $this->_helper()->confProcessOrders()));
        }
        $result = $readConnection->fetchAll($_select);
        return $result;
    }
}
