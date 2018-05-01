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
 * @package    AW_Catalogpermissions
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


$setup = $this;
$setup->startSetup();


$setup->addAttribute('catalog_product', AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRODUCT, array(
    'backend' => 'catalogpermissions/entity_attribute_backend_groups',
    'frontend' => '',
    'source' => 'catalogpermissions/entity_attribute_source_groups',
    'group' => 'Permissions',
    'label' => 'Disable product for',
    'input' => 'multiselect',
    'class' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'type' => 'text',
    'visible' => 1,
    'user_defined' => false,
    'default' => '',
    'apply_to' => '',
    'visible_on_front' => false,
    'required' => false
));

$setup->addAttribute('catalog_product', AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRICE, array(
    'backend' => 'catalogpermissions/entity_attribute_backend_groups',
    'frontend' => '',
    'source' => 'catalogpermissions/entity_attribute_source_groups',
    'group' => 'Permissions',
    'label' => 'Hide product price for',
    'input' => 'multiselect',
    'class' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'type' => 'text',
    'visible' => 1,
    'user_defined' => false,
    'default' => '',
    'apply_to' => '',
    'visible_on_front' => false,
    'required' => false
));

$setup->addAttribute('catalog_category', AW_Catalogpermissions_Helper_Data::CP_DISABLE_CATEGORY, array(
    'group' => 'Permissions',
    'label' => 'Hide category for a specific customer group',
    'type' => 'text',
    'input' => 'multiselect',
    'default' => '',
    'class' => '',
    'backend' => 'catalogpermissions/entity_attribute_backend_category',
    'frontend' => '',
    'source' => 'catalogpermissions/entity_attribute_source_groups',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
));




$setup->endSetup();
