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
 * @package    AW_Helpdeskultimate
 * @version    2.10.7
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$this->getTable('helpdeskultimate/department')}` ADD `visible_on` VARCHAR(255) NOT NULL DEFAULT '';
");

$allStoreViews = 0;
$collection = Mage::getModel('helpdeskultimate/department')->getCollection();
foreach ($collection as $dep) {
    if ($dep->getData('visibility') == AW_Helpdeskultimate_Model_Source_Visibility::VISIBLE_FOR_CUSTOMER) {
        $dep->setData('visible_on', $allStoreViews);
    }
    $dep->save();
}
$installer->run("
    ALTER TABLE `{$this->getTable('helpdeskultimate/department')}` DROP `visibility`;
");
$installer->run("
    ALTER TABLE `{$this->getTable('helpdeskultimate/popmessage')}` CHANGE `uid` `uid` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL
");
$installer->endSetup();
