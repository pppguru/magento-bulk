<?php

require_once ('../app/Mage.php');
session_start();
Mage::reset();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$modules = array('advancedstock', 'backgroundtask', 'healthyerp', 'orderpreparation', 'organizer', 'purchase', 'planning', 'scanner');

$tableName = Mage::getSingleton('core/resource')->getTableName('core/config_data');

//delete queries

echo "\n\n## ERP Configuration script generator\n";
echo "## Build : ".date('Y-m-d H:i:s')."\n";
echo "## Run this script on dev server, save as sql file and run it on your live server\n";

echo "\n\n## Delete existing settings\n";
foreach($modules as $module)
{
    $query = 'delete from '.$tableName.' where path like "'.$module.'%";';
    echo $query."\n";
}

//insert queries
foreach($modules as $module)
{
    echo "\n\n## Insert settings for ".$module."\n";

    $sql = 'select * from '.$tableName.' where path like "'.$module.'%"';
    $collection = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
    foreach($collection as $item)
    {
        $query = 'insert into '.$tableName.' (scope, scope_id, path, `value`) values ("'.$item['scope'].'", '.$item['scope_id'].', "'.$item['path'].'", "'.addslashes($item['value']).'");';
        echo $query."\n";
    }
}