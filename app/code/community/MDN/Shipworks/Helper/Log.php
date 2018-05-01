<?php

class MDN_Shipworks_Helper_Log extends Mage_Core_Helper_Abstract {
    
    /**
     * 
     */
    public function Log($inputData, $output, $errorMessage)
    {
        //set path
        $dirPath = $filePath = Mage::getBaseDir('var').DS.'ShipworksLog'.DS;
        if (!is_dir($dirPath))
            mkdir($dirPath);
        $filePath = $dirPath.date('YmdHis').'.log';
        
        //log
        $inputDataString = "\r\n#############################################";
        $inputDataString .= "\r\nINPUT DATA : ";
        foreach($inputData as $k => $v)
        {
            $inputDataString .= "\r\n".$k.' = '.$v;
        }
        $inputDataString .= "\r\n#############################################";
        $inputDataString .= "\r\nOUTPUT : ";

        $inputDataString .= $output;
        $inputDataString .= "\r\n#############################################";
        $inputDataString .= "\r\nERROR MESSAGE : ";
        
        $inputDataString .= $errorMessage;
        
        file_put_contents($filePath, $inputDataString);
        
        $emailLogRecipient = Mage::getStoreConfig('shipworks/general/email_log_recipient');
        if ($emailLogRecipient)
            mail($emailLogRecipient, 'Shipworks Boostmyshop log '.$filePath, $inputDataString);
        
    }
    
}
