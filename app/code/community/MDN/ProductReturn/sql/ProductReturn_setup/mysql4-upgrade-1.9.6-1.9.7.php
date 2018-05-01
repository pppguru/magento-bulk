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
 * @copyright  Copyright (c) 2013 BoostMyShop (http://www.boostmyshop.com/)
 * @author     : Florent Plantinet
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();


$installer->run("

ALTER TABLE  {$this->getTable('rma')}
ADD  `rma_manager_id` INT( 11 ) NOT NULL default '0';

");

$installer->endSetup();
