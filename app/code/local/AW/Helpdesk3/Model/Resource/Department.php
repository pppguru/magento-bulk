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
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdesk3_Model_Resource_Department extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('aw_hdu3/department', 'id');
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     *
     * @return $this|Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $_result = parent::_afterSave($object);
        if (strlen($object->getStoreIds()) > 0) {
            $object->setStoreIds(array_map('intval', explode(',', $object->getStoreIds())));
        } else {
            $object->setStoreIds(array());
        }
        return $_result;
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     *
     * @return $this|Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (is_array($object->getStoreIds())) {
            if (in_array(0, $object->getStoreIds())) {
                $object->setStoreIds(array(0));
            }
            $object->setStoreIds(implode(',', $object->getStoreIds()));
        }
        return parent::_beforeSave($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     *
     * @return $this|Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if (strlen($object->getStoreIds()) > 0) {
            $object->setStoreIds(array_map('intval', explode(',', $object->getStoreIds())));
        } else {
            $object->setStoreIds(array());
        }
        return parent::_afterLoad($object);
    }

    /**
     * @param AW_Helpdesk3_Model_Department $department
     * @param array $agentIds
     *
     * @return $this
     */
    public function addDepartmentAgents($department, $agentIds)
    {
        $write = $this->_getWriteAdapter();
        $where = $write->quoteInto('department_id =?', $department->getId());
        $write->delete($this->getTable('aw_hdu3/department_agent_link'), $where);
        foreach ($agentIds as $agentId) {
            $write->insert($this->getTable('aw_hdu3/department_agent_link'),
                array(
                    'department_id' => $department->getId(),
                    'agent_id'      => $agentId
                )
            );
        }
        return $this;
    }
}