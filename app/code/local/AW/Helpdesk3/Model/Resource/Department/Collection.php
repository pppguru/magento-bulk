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


class AW_Helpdesk3_Model_Resource_Department_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/department');
    }

    protected function _toOptionHash($valueField = 'id', $labelField = 'title')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    /**
     * @param $agentId
     *
     * @return $this
     */
    public function addFilterByAgentId($agentId)
    {
        $this->getSelect()
            ->join(
                array(
                    'dep_a_link' => $this->getTable('aw_hdu3/department_agent_link')
                ),
                'main_table.id = dep_a_link.department_id AND dep_a_link.agent_id = ' . $agentId,
                array()
            )
        ;
        return $this;
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function addFilterByStoreId($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }

        if (!is_array($store)) {
            $store = array($store);
        }
        $this->addFieldToFilter('store_ids', array(array('finset' => $store),array('finset' => '0')));
        return $this;
    }

    public function sortByOrder()
    {
        return $this->setOrder('sort_order', Varien_Data_Collection::SORT_ORDER_ASC);
    }

    public function addActiveFilter()
    {
        return $this->addFieldToFilter('status', AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE);
    }

    /**
     * @return $this
     */
    public function addNotDeletedFilter()
    {
        return $this->addFieldToFilter('status',array('neq' => AW_Helpdesk3_Model_Source_Status::DELETED_VALUE));
    }


}