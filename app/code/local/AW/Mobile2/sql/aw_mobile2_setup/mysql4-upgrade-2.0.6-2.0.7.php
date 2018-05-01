<?php
/*This script removes entries that were created by aw_mobile*/
$installer = $this;
$installer->startSetup();

//Remove awmobile_setup entry from core_resource
$installer->run("DELETE FROM core_resource WHERE code = 'awmobile_setup';");

//Remove mobile_description product attribute
$installer->run("DELETE FROM eav_attribute WHERE attribute_code='mobile_description';");

//Remove mobile_footer_links from static blocks
$installer->run("DELETE FROM cms_block WHERE identifier='mobile_footer_links';");

$installer->endSetup();

