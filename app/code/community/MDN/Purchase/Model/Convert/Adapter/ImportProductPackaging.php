<?php

class MDN_Purchase_Model_Convert_Adapter_ImportProductPackaging extends Mage_Dataflow_Model_Convert_Adapter_Abstract {

    public function load() {
        // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function save() {
        // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function saveRow(array $importData)
    {
        $sku = trim($this->getParam($importData, 'sku'));
        $qty = trim($this->getParam($importData, 'qty'));
        $name = trim($this->getParam($importData, 'name'));
        $mode = trim($this->getParam($importData, 'mode'));


        /*var_dump($importData);
        die();*/

        if ($sku == '')
            throw new Exception('Row skipped, sku is missing ');

        if (($qty == '') || ($qty == 0))
            throw new Exception('Row skipped, qty is invalid qty='.$qty);

        if ($name == '')
            throw new Exception('Row skipped, name is missing');
        
        if ($mode != '' && $mode != 's' && $mode != 'p')
            throw new Exception('Row skipped, mode is missing, accepted values are "p" or "s", current = '.$mode);

        $pid = $this->loadProduct($sku);
        if(!$pid)
             throw new Exception('Row skipped, sku is not matching with an existing product');


        $samePackaging = $this->loadPackagingForProduct($pid,$qty);

        //Replace existing packaging if same qty exist
       
        if($samePackaging->getpp_qty()>0){
            $samePackaging->setpp_name($name);

            if($mode == 'p')
                $samePackaging->setpp_is_default(1);

            if($mode == 's')
                $samePackaging->setpp_is_default_sales(1);

            $samePackaging->save();
                
        }else{            
            $this->createPackagingForProduct($pid,$name,$qty,$mode);
            
        }
    }

    protected function getParam($data, $name)
    {
        if (isset($data[$name]))
            return $data[$name];
        else
            return '';
    }

    protected function loadProduct($sku)
    {
        $productId = mage::getModel('catalog/product')->getIdBySku($sku);
        if ($productId && $productId > 0 )
            return $productId;
        else
            return null;
    }


    protected function createPackagingForProduct($pid,$name,$qty,$mode)
    {
        $p = mage::getModel('Purchase/Packaging');
        $p->setpp_product_id($pid)
            ->setpp_name($name)
            ->setpp_qty($qty);

        if($mode == 'p')
            $p->setpp_is_default(1);

        if($mode == 's')
            $p->setpp_is_default_sales(1);

        $p->save();
    }

    protected function loadPackagingForProduct($pid,$qty)
    {
        $p = mage::getModel('Purchase/Packaging')
                ->getCollection()
                ->AddFieldToFilter('pp_product_id',$pid)
                ->AddFieldToFilter('pp_qty',$qty)
                ->getFirstItem();
        return $p;
    }
}