<?php

$installer = $this;

$installer->startSetup();

// get values. Get the first one and set all the same, its unlikely they would have different configs.
$shipFreightClassPresent = $installer->getConnection()->fetchRow("select value from {$this->getTable('core_config_data')}
        where path=('shipping/wsafreightcommon/ship_freight_class_present') limit 1");
$weightRestrict = $installer->getConnection()->fetchRow("select value from {$this->getTable('core_config_data')}
        where path=('shipping/wsafreightcommon/restrict_rates') limit 1");
$dimRestrict = $installer->getConnection()->fetchRow("select value from {$this->getTable('core_config_data')}
        where path=('shipping/wsafreightcommon/dimensional_restrict_rates') limit 1");

$forceFreight = $installer->getConnection()->fetchRow("select value from {$this->getTable('core_config_data')}
        where path=('shipping/wsafreightcommon/force_freight') limit 1");


$displayFreightRules='';

if (is_array($forceFreight) && $forceFreight['value']==1) {
    $displayFreightRules = 'product_ships_freight,';
}
if (is_array($weightRestrict) && $weightRestrict['value']==1) {
    $displayFreightRules.='weight,';
}

if (is_array($dimRestrict) && $dimRestrict['value']==1) {
    $displayFreightRules.='dimensions';
}

$shipFreightRules='weight,dimensions,productmust,';

if (is_array($shipFreightClassPresent) && $shipFreightClassPresent['value']==1) {
    $shipFreightRules.='product_freight';
}


$installer->run("

   INSERT IGNORE INTO {$this->getTable('core_config_data')} (scope, scope_id, path, `value`)
    SELECT scope, scope_id, 'shipping/wsafreightcommon/display_freight_rules', '$displayFreightRules'
    FROM {$this->getTable('core_config_data')}
    WHERE path='shipping/wsafreightcommon/restrict_rates';

    INSERT IGNORE INTO {$this->getTable('core_config_data')} (scope, scope_id, path, `value`)
    SELECT scope, scope_id, 'shipping/wsafreightcommon/ship_freight_rules', '$shipFreightRules'
    FROM {$this->getTable('core_config_data')}
    WHERE path='shipping/wsafreightcommon/ship_freight_class_present';


");

$installer->endSetup();