<?php
/*This script removes entries that were created by aw_mobile2*/
// We have added this code again for updating mobile theme 2.0.6 to 3.0.3. Erik, 11 Nov 2016, mantis-400
$installer = $this;
$installer->startSetup();

//Remove aw_mobile2_setup entry from core_resource
$installer->run("DELETE FROM core_resource WHERE code = 'aw_mobile2_setup';");

//Remove mob2_description, mob2_cat_iphone_cms_block product attribute
$installer->run("DELETE FROM eav_attribute WHERE attribute_code='mob2_description';");
$installer->run("DELETE FROM eav_attribute WHERE attribute_code='mob2_cat_iphone_cms_block';");

$installer->endSetup();
