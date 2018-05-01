<?php
$installer = $this;

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$setup->addAttribute('catalog_product', Conlabz_Rpsc_Helper_Data::ALLOWED_COUNTRIES_ATTR_CODE, array(
    'input'         => 'multiselect',
	'required'		=> false,
    'type'          => 'text',
    'label'         => 'Countries',
    'source'        => 'rpsc/system_config_source_countries',
    'backend'       => 'rpsc/system_config_backend_countries',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'       => 1,
));

$installer->startSetup();
$installer->endSetup();
