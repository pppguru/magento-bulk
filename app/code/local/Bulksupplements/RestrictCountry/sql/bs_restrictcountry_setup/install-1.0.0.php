<?php
$installer = $this;
$installer->startSetup();

$setup = Mage::getModel('eav/entity_setup', 'core_setup');
$setup->removeAttribute('catalog_product', 'rpsc_countries');
$setup->removeAttribute('catalog_product', 'rpsc_access_control');

$installer->run("DELETE FROM core_resource WHERE code='rpsc_setup'");
$installer->run("DELETE FROM core_config_data WHERE path like 'checkout/rpsc%'");

$setup->addAttribute('catalog_product', 'bs_restricted_countries', array(
    'input'         => 'multiselect',
	'required'		=> false,
    'type'          => 'varchar',
    'label'         => 'Restricted Countries',
    'source'        => 'restrictcountry/system_config_source_countries',
    'backend'       => 'eav/entity_attribute_backend_array',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'       => true,
	'visible_on_front' =>false,
	'used_in_product_listing'=>true	
));
$installer->endSetup();