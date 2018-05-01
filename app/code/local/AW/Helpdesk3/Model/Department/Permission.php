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
 * Class AW_Helpdesk3_Model_Department_Permission
 * @method string getId()
 * @method string getDepartmentId()
 * @method string getDepartmentIds()
 * @method string getAdminRoleIds()
 */
class AW_Helpdesk3_Model_Department_Permission extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/department_permission');
    }

    /**
     * @param $departmentId
     *
     * @return $this
     */
    public function loadByDepartmentId($departmentId)
    {
        return $this->load($departmentId, 'department_id');
    }

    /**
     * @param Mage_Admin_Model_User $user
     *
     * @return bool
     */
    public function isCanViewTicket($user)
    {
        $agent = Mage::getModel('aw_hdu3/department_agent')->loadAgentByUserId($user->getId());
        $agentDepartmentCollection = $agent->getDepartmentCollection();
        $departmentIds = $agentDepartmentCollection->getAllIds();

        //check department
        foreach ($departmentIds as $depId) {
            if (in_array($depId, $this->getDepartmentIds())) {
                return true;
            }
        }

        //check admin role
        if (in_array($user->getRole()->getId(), $this->getAdminRoleIds())) {
            return true;
        }
        return false;
    }

    public function save()
    {
        $this->isDeleted(!($this->getDepartmentIds() || $this->getAdminRoleIds()));
        return parent::save();
    }

}
