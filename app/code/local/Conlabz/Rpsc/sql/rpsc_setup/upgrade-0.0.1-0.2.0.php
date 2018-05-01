<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->updateAttribute(
    'catalog_product',
    Conlabz_Rpsc_Helper_Data::ALLOWED_COUNTRIES_ATTR_CODE,
    array(
        'used_in_product_listing' => true
    )
);

$setup->updateAttribute(
    'catalog_product',
    Conlabz_Rpsc_Helper_Data::ALLOWED_COUNTRIES_ATTR_CODE,
    'note',
    'Can be allowed or denied as specified in System > Configuration > Checkout > Rpsc'
);

$setup->updateAttribute(
    'catalog_product',
    Conlabz_Rpsc_Helper_Data::ALLOWED_COUNTRIES_ATTR_CODE,
    array(
        'label' => 'Countries'
    )
);

$installer->startSetup();
$installer->endSetup();
