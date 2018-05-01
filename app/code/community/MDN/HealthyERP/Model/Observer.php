<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_HealthyERP_Model_Observer
{
    /**
     * Called by cron to HealthyERP probes
     *
     */
    public function CheckProbes()
    {
        try{
            mage::helper('HealthyERP/Probe')->checkAndNotify();
        }catch(Exception $ex){
            mage::log($ex->getMessage(), null, 'erp_healthy_cron_exception.log');
        }
    }
}