<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();


$setup->addAttribute('catalog_product', Conlabz_Rpsc_Helper_Data::ACCESS_CONTROL_ATTR_CODE, array(
    'input'         => 'select',
	'required'		=> false,
    'type'          => 'varchar',
    'label'         => 'Rpsc Access control',
    'source'        => 'rpsc/entity_source_accesscontrol',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'       => 0,
));


$setup->updateAttribute(
    'catalog_product',
    Conlabz_Rpsc_Helper_Data::ACCESS_CONTROL_ATTR_CODE,
    array(
        'used_in_product_listing' => true
    )
);

$setup->updateAttribute(
    'catalog_product',
    Conlabz_Rpsc_Helper_Data::ACCESS_CONTROL_ATTR_CODE,
    'note',
    'If selected, values will overide configuration in System > Configuration > Checkout > Rpsc'
);

$installer->startSetup();
$installer->endSetup();
