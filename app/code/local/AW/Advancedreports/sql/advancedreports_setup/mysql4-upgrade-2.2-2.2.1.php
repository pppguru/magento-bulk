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
 * @package    AW_Advancedreports
 * @version    2.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
/* @var bool $dropKeyRequire Is Require to drop old foreign key */
$dropKeyRequire = false;
try {
    $tableName = $this->getTable('aw_arep_sku_relevance');
    $sql = new Zend_Db_Expr("SHOW CREATE TABLE `{$tableName}`");
    foreach ($this->getConnection()->fetchPairs($sql) as $result) {
        if (strpos($result, "FK_AREP_VARCHAR_PRODUCT_SKU") !== false) {
            $dropKeyRequire = true;
        }
    }
} catch (Exception $e) {
}
if ($dropKeyRequire) {
    $installer->run(
        "ALTER TABLE {$this->getTable('aw_arep_sku_relevance')} DROP FOREIGN KEY `FK_AREP_VARCHAR_PRODUCT_SKU`;"
    );
}
$installer->endSetup();
