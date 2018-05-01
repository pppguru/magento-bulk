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


class AW_Advancedreports_Helper_Tools_Aggregator extends AW_Advancedreports_Helper_Data
{
    /**
     * Type Periods
     */
    const TYPE_PERIODS = 'periods';

    /**
     * Type List
     */
    const TYPE_LIST = 'list';

    /**
     * Table Prefix
     */
    const TABLE_PREFIX = 'aw_arep_aggregated';

    /**
     * Date INdex Field
     */
    const DATE_KEY_FIELD = 'period_key';


    /**
     * Aggregator period
     *
     * @var string
     */
    protected $_type = self::TYPE_PERIODS;

    /**
     * Report id
     *
     * @var string
     */
    protected $_reportId = 'default';
    protected $_init = false;
    protected $_timeType = 'created_at';
    protected $_storeAppendix = 'main';
    protected $_md5 = null;

    /**
     * Grid
     *
     * @var AW_Advancedreports_Block_Advanced_Grid
     */
    protected $_grid = null;

    protected $_collection = array();

    /**
     * Initialize aggregator instance
     * This step is required
     *
     * @param AW_Advancedreports_Block_Advanced_Grid $grid     Active grid
     * @param string                                 $type     'list' or 'periods'
     * @param string                                 $aggId    Id of report
     * @param string                                 $timeType 'created at' or 'updated_at'
     *
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    public function initAggregator($grid = null, $type = null, $aggId = null, $timeType = null)
    {
        if ($aggId) {
            $this->_reportId = $aggId;
        }
        if ($type) {
            $this->_type = $type;
        }
        if ($timeType) {
            $this->_timeType = $timeType;
        }
        if ($grid) {
            $this->_grid = $grid;
            $this->_values = $grid->getOptionsValues();
        }
        $this->_init = true;
        return $this;
    }

    /**
     * Retrieve connection for read data
     *
     * @return  Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getReadAdapter()
    {
        return $this->_helper()->getReadAdapter();
    }

    /**
     * Retrieve connection for write data
     *
     * @return  Varien_Db_Adapter_Pdo_Mysql
     */
    public function _getWriteAdapter()
    {
        return $this->_helper()->getWriteAdapter();
    }

    /**
     * Set tore filter to aggregator
     *
     * @param string|integer|array $store
     *
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    public function setStoreFilter($store)
    {
        $this->_storeAppendix = is_array($store) ? implode("_", $store) : $store;
        return $this;
    }

    protected function _validateInstance()
    {
        if (!$this->_init) {
            Mage::throwException('Aggregator initialization required');
        }
    }

    protected function _getDBTable($tableName)
    {
        return $this->_getResource()->getTableName($tableName);
    }

    /**
     * Retrieves table name
     *
     * @return string
     */
    public function getTableName()
    {
        $this->_validateInstance();

        $arr = array(
            self::TABLE_PREFIX,
            md5(
                implode(
                    "_",
                    array(
                         $this->_reportId,
                         $this->_type,
                         $this->_timeType,
                         $this->_storeAppendix,
                         $this->_values,
                    )
                )
            )
        );

        return substr($this->_getDBTable(implode("_", $arr)), 0, 63);
    }

    /**
     * Retrieves active grid
     *
     * @return AW_Advancedreports_Block_Advanced_Grid
     */
    public function getGrid()
    {
        $this->_validateInstance();
        return $this->_grid;
    }

    /**
     * Check table existanse in database
     *
     * @param string $tableName
     *
     * @return boolean
     */
    private function _tableExists($tableName)
    {
        if ($this->_getReadAdapter()->showTableStatus($tableName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create database for grid columns
     *
     * @param string                                 $name
     * @param AW_Advancedreports_Block_Advanced_Grid $grid
     *
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    private function _createFlatTable($name, $grid)
    {
        if ($grid) {
            $table = new Varien_Db_Ddl_Table();
            $table->setName($name);
            $periodKey = self::DATE_KEY_FIELD;

            $table->addColumn(
                'entity_id',
                Varien_Db_Ddl_Table::TYPE_BIGINT,
                null,
                array(
                    'unsigned' => true,
                    'primary'  => true,
                    'nullable' => false,
                    'identity' => true,
                )
            );

            $table->addColumn(
                $periodKey,
                Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                null,
                array(
                     'nullable' => false,
                )
            );

            foreach ($grid->getSetupColumns() as $column) {
                $index = $column->getIndex();
                $type = $column->getDdlType();
                $size = $column->getDdlSize();
                $options = $column->getDdlOptions();

                if ($index && $type) {
                    $table->addColumn($index, $type, $size, $options);
                }
            }

            $write = $this->_getWriteAdapter()->createTable($table);
        }
        return $this;
    }

    /**
     * Truncate table with <code>$name</code>
     *
     * @param string                      $name
     * @param Varien_Db_Adapter_Pdo_Mysql $connection
     *
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    private function _dropFlatTable($name, $connection)
    {
        if ($this->_tableExists($name)) {
            try {
                $connection->exec(new Zend_Db_Expr("DROP TABLE IF EXISTS `{$name}`"));
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    /**
     * Check aggregator table existanse and create it
     *
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    private function checkATable()
    {
        $table = $this->getTableName();
        if (!$this->_tableExists($table)) {
            $this->_createFlatTable($table, $this->getGrid());
        }
        return $this;
    }

    /**
     * Collection
     *
     * @return AW_Advancedreports_Model_Mysql4_Aggregation_Collection
     */
    protected function _getAggrCollection()
    {
        return Mage::getModel('advancedreports/aggregation')->getCollection();
    }

    protected function _cleanExpiredInPeriod($from, $to)
    {
        $collection = $this->_getAggrCollection();
        $collection->setExpiredFilter();
        $writeAdapter = $this->_getWriteAdapter();
        /** @var $expiredItem AW_Advancedreports_Model_Aggregation */
        foreach ($collection as $expiredItem) {
            $writeAdapter->delete(
                $expiredItem->getData('table'),
                array(
                     'period_key >= ?' => $expiredItem->getData('from'),
                     'period_key <= ?' => $expiredItem->getData('to'),
                )
            );
            $expiredItem->delete();
        }
    }

    protected function _reaggregateRequired($from, $to)
    {
        $periods = array();

        # 1.  Cleaning expired data
        $this->_cleanExpiredInPeriod($from, $to);

        # 2. Searching of unindexed data
        $collection = $this->_getAggrCollection();
        $collection
            ->setPeriodFilter($from, $to)
            ->setTableFilter($this->getTableName())
            ->setExpiredFilter(AW_Advancedreports_Model_Aggregation::EXPIRED_FALSE)
            ->setToOrdering()
        ;

        if ($collection->getSize()) {
            return $collection->reagregateRequired();
        } else {
            return array(
                array('from' => $from, 'to' => $to),
            );
        }
    }

    protected function _incSec($datetime)
    {
        $date = new Zend_Date(
            $datetime,
            null,
            $this->_helper()->getLocale()->getLocaleCode()
        );
        $date->addSecond(1);
        return $date->toString(self::MYSQL_ZEND_DATE_FORMAT);
    }

    protected function _decSec($datetime)
    {
        $date = new Zend_Date(
            $datetime,
            null,
            $this->_helper()->getLocale()->getLocaleCode()
        );
        $date->subSecond(1);
        return $date->toString(self::MYSQL_ZEND_DATE_FORMAT);
    }

    protected function _getPriodsToAggregate($from, $to)
    {
        # Aggreagate all
        return $this->_reaggregateRequired($from, $to);
    }

    /**
     * Setup aggregated collection
     * <ol>
     * <li>Create table if not exists</li>
     * <li>Check periods to aggregate</li>
     * <li>Aggregate them</li>
     * <li>Set up collection</li>
     * </ol>
     *
     * @param datetime $from
     * @param datetime $to
     *
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    public function prepareAggregatedCollection($from, $to)
    {
        $this->_validateInstance();

        # 1. Creating table if not exists
        $this->checkATable();

        # 2. Getting period to aggregate
        $periods = $this->_getPriodsToAggregate($from, $to);

        # 3. Aggreagating them
        $_expiresAt = new Zend_Date(time());
        $_expiresAt->addDay(AW_Advancedreports_Model_Aggregation::EXPIRES_AFTER);
        foreach ($periods as $period) {
            try {
                if (!isset($period['today']) || !$period['today']) {
                    $aggregating = Mage::getModel('advancedreports/aggregation')
                        ->setFrom($period['from'])
                        ->setTo($period['to'])
                        ->setTable($this->getTableName())
                        ->setData('timetype', $this->_timeType)
                        ->setData('expired', $_expiresAt->toString('Y-MM-dd'));
                }

                $this->_aggregateData($period['from'], $period['to']);

                if (!isset($period['today']) || !$period['today']) {
                    $aggregating->save();
                }

            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        # 4. Set up aggreagted collection
        $collection = Mage::getModel('advancedreports/cache')->getCollection();
        $collection->setPeriodFilter($from, $to);
        $collection->setMainTable($this->getTableName());

        $this->_collection = $collection;
        return $this;
    }

    protected function _getDataKeyColumn()
    {
        foreach ($this->getGrid()->getSetupColumns() as $column) {
            if ($column->getIsPeriodKey()) {
                return $column->getIndex();
            }
        }
        return null;
    }

    /**
     * Retrieves max primary key
     *
     * @param string $table
     *
     * @return int
     */
    protected function _getMaxPrimaryKey($table)
    {
        $select = $this->_getReadAdapter()->select();
        $select->from($table, array('MAX(entity_id)'))->order('entity_id DESC')->limit(1);

        if ($id = $this->_getReadAdapter()->fetchOne($select)) {
            return (int)$id;
        } else {
            return 0;
        }
    }

    /**
     * Get data from  report and write them to
     *
     * @param datetime $from
     * @param datetime $to
     *
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    protected function _aggregateData($from, $to)
    {
        $data = $this->getGrid()->getPreparedData($from, $to);

        $table = $this->getTableName();
        $dateKey = $this->_getDataKeyColumn();
        $columns = $this->getGrid()->getSetupColumns();
        $periodKeyField = self::DATE_KEY_FIELD;
        $keys = array('entity_id', self::DATE_KEY_FIELD);
        foreach ($columns as $column) {
            $keys[] = $column->getIndex();
        }
        $this->_getWriteAdapter()->beginTransaction();
        $this->_getWriteAdapter()->delete(
            $table, "(`{$periodKeyField}` >= '{$from}') AND (`{$periodKeyField}` <= '{$to}')"
        );
        $max = $this->_getMaxPrimaryKey($table);
        if ($data) {
            foreach ($data as $row) {
                $values = array(++$max, $row[$dateKey]);
                foreach ($columns as $column) {
                    $values[] = ($column->getType() == 'text') ? str_replace("'", "''", $row[$column->getIndex()])
                        : $row[$column->getIndex()];
                }
                $bind = array();
                for ($i = 0; $i < count($keys); $i++) {
                    $bind[$keys[$i]] = $values[$i];
                }
                $this->_getWriteAdapter()->insert($table, $bind);
            }
        }
        $this->_getWriteAdapter()->commit();
        return $this;
    }

    /**
     * Retrieves collection with cached data
     *
     * @return AW_Advancedreports_Model_Mysql4_Cache_Collection
     */
    public function getAggregatetCollection()
    {
        return $this->_collection;
    }

    /**
     * Completelly remove cache
     *
     * @return AW_Advancedreports_Helper_Tools_Aggregator
     */
    public function cleanCache()
    {
        $write = $this->_getWriteAdapter();
        /** @var  AW_Advancedreports_Model_Mysql4_Aggregation_Collection $aggrs */
        $aggrs = Mage::getModel('advancedreports/aggregation')->getCollection();
        foreach ($aggrs->getAllTables() as $table) {
            $this->_dropFlatTable($table['table'], $write);
        }
        $aggrs->clearTable();
        return $this;
    }
}
