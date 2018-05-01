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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

//update data from old version
$installer->run(" 

update {$this->getTable('rma_products')}, {$this->getTable('rma')}
set rp_reason = rma_reason
where rma_id = rp_rma_id;

update {$this->getTable('rma_products')}
set rp_action_processed = 1, rp_destination_processed = 1
where rp_rma_id in (select rma_id from {$this->getTable('rma')} where rma_action is not null);

");

$installer->endSetup();
