<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Paction
*/
class Amasty_Paction_Model_Command_Removecategory extends Amasty_Paction_Model_Command_Addcategory
{ 
    public function __construct($type)
    {
        parent::__construct($type);
        $this->_label      = 'Remove Category';
    }
}
