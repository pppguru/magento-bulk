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
class MDN_BackgroundTask_Model_Observer 
{
	/**
	 * Called by cron to execute tasks
	 *
	 */
	public function ExecuteTasks()
	{
		if (   (Mage::getStoreConfig('healthyerp/erp/enabled') == 1)
				&& (Mage::getStoreConfig('healthyerp/erp/disable_cron') == 0)) {

			mage::helper('BackgroundTask')->ExecuteTasks();
		}
	}
}
	