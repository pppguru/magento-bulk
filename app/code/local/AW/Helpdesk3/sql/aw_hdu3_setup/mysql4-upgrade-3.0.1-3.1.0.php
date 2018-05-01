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

$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('aw_hdu3/department')} CHANGE is_active status tinyint(2) UNSIGNED NOT NULL;

    ALTER TABLE {$this->getTable('aw_hdu3/ticket_status')} CHANGE is_active status tinyint(2) UNSIGNED NOT NULL;

    ALTER TABLE {$this->getTable('aw_hdu3/ticket_priority')} CHANGE is_active status tinyint(2) UNSIGNED NOT NULL;

    ALTER TABLE {$this->getTable('aw_hdu3/department_agent')} ADD COLUMN status tinyint(2) UNSIGNED NOT NULL DEFAULT 1;
");
$installer->endSetup();