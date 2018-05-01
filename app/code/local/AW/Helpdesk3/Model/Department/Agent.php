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


/**
 * Class AW_Helpdesk3_Model_Department_Agent
 * @method string getId()
 * @method string getUserId()
 * @method string getName()
 * @method string getEmail()
 */
class AW_Helpdesk3_Model_Department_Agent extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/department_agent');
    }

    /**
     * @param int $userID
     *
     * @return $this
     */
    public function loadAgentByUserId($userID)
    {
        return $this->load($userID, 'user_id');
    }

    /**
     * @return AW_Helpdesk3_Model_Resource_Department_Collection
     */
    public function getDepartmentCollection()
    {
        $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection()->addActiveFilter()->addNotDeletedFilter();
        $departmentCollection->addFilterByAgentId($this->getId());
        return $departmentCollection;
    }

    /**
     * @return AW_Helpdesk3_Model_Resource_Department_Collection
     */
    public function getFullDepartmentCollection()
    {
        $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection()->addNotDeletedFilter();
        $departmentCollection->addFilterByAgentId($this->getId());
        return $departmentCollection;
    }

    /**
     * @return AW_Helpdesk3_Model_Resource_Department_Collection
     */
    public function getTicketCollection()
    {
        /** @var AW_Helpdesk3_Model_Resource_Ticket_Collection $ticketCollection */
        $ticketCollection = Mage::getModel('aw_hdu3/ticket')->getCollection();
        $ticketCollection->addFilterByAgentId($this->getId());
        return $ticketCollection;
    }
}
