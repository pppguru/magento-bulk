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
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$installer->endSetup();
try {
    $filterCollection = Mage::getResourceModel('marketsuite/filter_collection');
    foreach ($filterCollection as $filterModel) {
        $conditions = unserialize($filterModel->getData('conditions_serialized'));
        if (array_key_exists('conditions', $conditions) && is_array($conditions['conditions'])) {
            foreach ($conditions['conditions'] as $conditionId => $condition) {
                if (
                    $condition['type'] == 'marketsuite/rule_condition_product_productlist'
                    || $condition['type'] == 'marketsuite/rule_condition_product_producthistory'
                ) {
                    if (array_key_exists('conditions', $condition) && is_array($condition['conditions'])) {
                        foreach ($condition['conditions'] as $attributeId => $attribute) {
                            if ($attribute['attribute'] == 'category') {
                                $value = implode(',', $attribute['value']);
                                $conditions['conditions'][$conditionId]['conditions'][$attributeId]['value'] = $value;
                            }
                        }
                    }
                }
            }
        }
        $filterModel->getConditions()->setConditions(array());
        $filterModel->getConditions()->loadArray($conditions);
        $filterModel->save();
    }
} catch (Exception $e) {
    Mage::logException($e);
}