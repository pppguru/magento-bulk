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


class AW_Advancedreports_Model_Mysql4_Option extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('advancedreports/option', 'option_id');
    }

    /**
     * Clear all custom options for the report for the administrator
     *
     * @param string  $reportId
     * @param integer $adminId
     *
     * @return AW_Advancedreports_Model_Mysql4_Option
     */
    public function clearReportOptions($reportId, $adminId)
    {
        $condition = array(
            $this->_getWriteAdapter()->quoteInto('report_id = ?', $reportId),
            $this->_getWriteAdapter()->quoteInto('admin_id = ?', $adminId),
        );

        $table = Mage::getSingleton('core/resource')->getTableName('advancedreports/option');
        $this->_getWriteAdapter()->delete($table, join(' AND ', $condition));
        return $this;
    }

    /**
     * Load an object
     *
     * @param Mage_Core_Model_Abstract $object
     * @param mixed                    $reportId
     * @param string                   $adminId
     * @param string                   $path
     *
     * @return $this
     */
    public function load3params(Mage_Core_Model_Abstract $object, $reportId, $adminId, $path)
    {
        $read = $this->_getReadAdapter();
        if ($read && $reportId && $adminId) {

            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where($this->getMainTable() . '.report_id=?', $reportId)
                ->where($this->getMainTable() . '.admin_id=?', $adminId)
                ->where($this->getMainTable() . '.path=?', $path)
            ;
            $data = $read->fetchRow($select);
            if ($data) {
                $object->setData($data);
            }
        }
        $this->_afterLoad($object);
        return $this;
    }
}
