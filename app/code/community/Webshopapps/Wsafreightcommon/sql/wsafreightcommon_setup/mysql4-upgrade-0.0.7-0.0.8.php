<?php

$installer = $this;

$installer->startSetup();

// get values. Get the first one and set all the same, its unlikely they would have different configs.
$hideAddressType = $installer->getConnection()->fetchRow("select value from {$this->getTable('core_config_data')} where path=('shipping/wsafreightcommon/hide_residential_bar') limit 1");
$hideLiftgate = $installer->getConnection()->fetchRow("select value from {$this->getTable('core_config_data')} where path=('shipping/wsafreightcommon/hide_liftgate') limit 1");

$accessorialValue='';

if (is_array($hideAddressType) && $hideAddressType['value']==1) {
  $accessorialValue = 'address_type,';
}
if (is_array($hideLiftgate) && $hideLiftgate['value']==1) {
    $accessorialValue.='liftgate';
}

$installer->run("

    INSERT IGNORE INTO {$this->getTable('core_config_data')} (scope, scope_id, path, `value`)
    SELECT scope, scope_id, 'shipping/wsafreightcommon/hide_accessorials', '$accessorialValue'
    FROM {$this->getTable('core_config_data')}
    WHERE path='shipping/wsafreightcommon/hide_residential_bar';


");

$installer->endSetup();