<?php
require_once(dirname(__FILE__).'/../../app/Mage.php');

Mage::reset();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$refuseErpEcho = true;

$buffer = '<h3>BEGIN ERP CRON At '.date('Y-m-d H:i:s').'</h3>';
$begin = microtime(true);
$buffer .= '<hr><br><h3>CONSIDERING NEW ORDERS</h3>';

try {
    $buffer .=mage::getModel('AdvancedStock/Observer')->UpdateStocksForOrders($refuseErpEcho, true);
} catch (Exception $e) {
    $buffer .= '<br/>'.$e->getMessage().$e->getTraceAsString();
}


$buffer .='<hr><br><h3>RUNNING BACKGROUND TASKS</h3>';
try {
    $buffer .= mage::helper('BackgroundTask')->ExecuteTasks($refuseErpEcho);
} catch (Exception $e) {
    $buffer .='<br/>'.$e->getMessage().$e->getTraceAsString();
}

$end = microtime(true);
$buffer .='<hr><br><h3>END ERP CRON At '.date('Y-m-d H:i:s').' in '.($end-$begin).'s'.'</h3>';

if(isset($_GET['dbg']) && $_GET['dbg'] == 1)
    echo $buffer;

mage::log($buffer,null,'erp_cron.log');

exit(1);
