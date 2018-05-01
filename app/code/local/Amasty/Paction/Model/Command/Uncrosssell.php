<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2011 Amasty (http://www.amasty.com)
* @package Amasty_Paction
*/
class Amasty_Paction_Model_Command_Uncrosssell extends Amasty_Paction_Model_Command_Unrelate
{ 
    public function __construct($type)
    {
        parent::__construct($type);
        $this->_label = 'Remove Cross-Sells';
    }
}
