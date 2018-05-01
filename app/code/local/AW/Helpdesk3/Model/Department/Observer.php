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


class AW_Helpdesk3_Model_Department_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function adminUserSaveAfter(Varien_Event_Observer $observer)
    {
        $user = $observer->getEvent()->getObject();
        if ($user->getIsActive() !== null) {
            $agent = Mage::getModel('aw_hdu3/department_agent')->loadAgentByUserId($user->getId());
            $agent
                ->setUserId($user->getId())
                ->setName($user->getFirstname() . ' ' . $user->getLastname())
                ->setEmail($user->getEmail())
                ->setStatus(
                    $user->getIsActive() ?
                    AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE :
                    AW_Helpdesk3_Model_Source_Status::DISABLED_VALUE
                )
                ->save()
            ;
        }
        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function adminUserDeleteAfter(Varien_Event_Observer $observer)
    {
        $user = $observer->getEvent()->getObject();
        /**
         * @var $agent AW_Helpdesk3_Model_Department_Agent
         */
        $agent = Mage::getModel('aw_hdu3/department_agent')->loadAgentByUserId($user->getUserId());
        $agent
            ->setStatus(AW_Helpdesk3_Model_Source_Status::DELETED_VALUE)
            ->save()
        ;
        return $this;
    }
}
