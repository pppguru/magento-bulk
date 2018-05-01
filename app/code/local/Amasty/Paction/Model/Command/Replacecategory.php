<?php
/**
* @author Amasty Team
* @copyright Copyright (c) Amasty (http://www.amasty.com)
* @package Amasty_Paction
*/
class Amasty_Paction_Model_Command_Replacecategory extends Amasty_Paction_Model_Command_Addcategory 
{ 
    public function __construct($type)
    {
        parent::__construct($type);
        $this->_label      = 'Replace Categories';
        $this->_fieldLabel = 'Category IDs'; 
    }
}
