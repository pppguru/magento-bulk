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
 * @package    AW_Mobile3
 * @version    3.0.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


function updateValue(Mage_Eav_Model_Entity_Setup $setup, $entityTypeId, $code, $key, $value)
{
    $id = $setup->getAttribute($entityTypeId, $code, 'attribute_id');
    $setup->updateAttribute($entityTypeId, $id, $key, $value);
}
$installer = $this;
$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->removeAttribute('catalog_product', AW_Mobile3_Helper_Data::PRODUCT_DESCRIPTION_MOBILE_ATTRIBUTE);

$setup->addAttribute('catalog_product', AW_Mobile3_Helper_Data::PRODUCT_DESCRIPTION_MOBILE_ATTRIBUTE, array(
        'type'              => 'text',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Mobile Description',
        'input'             => 'textarea',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'group'             => 'Mobile Options',
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => true,
        'filterable'        => false,
        'comparable'        => false,
        'is_wysiwyg_enabled'   => true,
        'is_html_allowed_on_front' => true,
        'visible_on_front'  => false,
        'visible_in_advanced_search' => false,
        'unique'            => false,
    )
);

updateValue($setup, 'catalog_product', AW_Mobile3_Helper_Data::PRODUCT_DESCRIPTION_MOBILE_ATTRIBUTE, 'is_global', 0);
updateValue($setup, 'catalog_product', AW_Mobile3_Helper_Data::PRODUCT_DESCRIPTION_MOBILE_ATTRIBUTE, 'is_wysiwyg_enabled', true);
updateValue($setup, 'catalog_product', AW_Mobile3_Helper_Data::PRODUCT_DESCRIPTION_MOBILE_ATTRIBUTE, 'is_html_allowed_on_front', true);

$installer->removeAttribute('catalog_category', AW_Mobile3_Helper_Data::CATEGORY_IPHONE_CMS_BLOCK);

$installer->addAttribute('catalog_category', AW_Mobile3_Helper_Data::CATEGORY_IPHONE_CMS_BLOCK, array(
        'type'              => 'int',
        'group'             => 'Display Settings',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'CMS Block (Mobile Devices)',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'catalog/category_attribute_source_page',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
    )
);
$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    'Display Settings',
    AW_Mobile3_Helper_Data::CATEGORY_IPHONE_CMS_BLOCK,
    18
);
$installer->processMigration();

$installer->endSetup();

// sample home page setup
$sampleData = array(
    'title'         => "Sample Mobile Home Page",
    'root_template' => "one_column",
    'identifier'    => "aw_mobile3_sample_home_page",
    'content'       => "<div>\r\n<p><a href=\"#\" target=\"_self\"><img style=\"width: 100%; display: block; margin-left: auto; margin-right: auto;\" alt=\"\" src=\"{{media url=\"wysiwyg/iPhoneTheme3Large/sampleHomePage/banner.jpg\"}}\" /></a></p>\r\n<p><a href=\"#\" target=\"_self\"><img style=\"width: 100%; display: block; margin-left: auto; margin-right: auto;\" alt=\"\" src=\"{{media url=\"wysiwyg/iPhoneTheme3Large/sampleHomePage/Furniture.jpg\"}}\" /> </a></p>\r\n<p><a href=\"#\" target=\"_self\"><img style=\"width: 100%; display: block; margin-left: auto; margin-right: auto;\" alt=\"\" src=\"{{media url=\"wysiwyg/iPhoneTheme3Large/sampleHomePage/Electronics.jpg\"}}\" /></a></p>\r\n<p><a href=\"#\" target=\"_self\"><img style=\"width: 100%; display: block; margin-left: auto; margin-right: auto;\" alt=\"\" src=\"{{media url=\"wysiwyg/iPhoneTheme3Large/sampleHomePage/apparel.jpg\"}}\" /></a></p>\r\n</div>\r\n<div class=\"home-spot\">\r\n<div class=\"box best-selling\">{{widget type=\"catalog/product_widget_new\" products_count=\"5\" template=\"catalog/product/widget/new/content/new_grid.phtml\"}}</div>\r\n</div>",
    'is_active'     => "1",
    'sort_order'    => "0",
    'stores'        => array (0 => '0')
);
$samplePage = Mage::getModel('cms/page');
$samplePage->setData($sampleData);
$samplePage->save();

Mage::getModel('core/config')->saveConfig('aw_mobile3/general/iphone_home_page', "aw_mobile3_sample_home_page", 'default', 0);

