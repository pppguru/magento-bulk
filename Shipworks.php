<?php
    ob_start();

    require_once 'app/Mage.php';
    umask(0);
    
    $errorMessage = '';
    try
    {
        $storeCode = (isset($_POST['store']) ? $_POST['store'] : '');
        Mage::app($storeCode);

        $data = (count($_POST) > 0 ? $_POST : $_GET);
        Mage::Helper('Shipworks/Request')->process($data);
    }
    catch(Exception $ex)
    {
        $errorMessage = $ex->getMessage();
    }
    
    $output = ob_get_contents();
    if (Mage::getStoreConfig('shipworks/general/enable_log'))
        Mage::helper('Shipworks/Log')->Log($data, $output, $errorMessage);
    
    
    ob_end_flush();