<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author     Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_AdvancedStock_Block_Transfer_Edit_Tabs_ImportProducts extends Mage_Adminhtml_Block_Widget_Form {

   
    //Form IDs
    const fieldTransferId = 'st_id';
    const fieldFile = 'import_products';
    const fieldOptionDelimiter = 'delimiter';
   


    public function __construct()
	{
		parent::__construct();
		$this->setTemplate('AdvancedStock/Transfer/Edit/Tab/ImportProducts.phtml');
	}

    public function getFormTransfertId()
	{
        return self::fieldTransferId;
    }
    
    public function getFormFileId()
	{
        return self::fieldFile;
    }

    public function getControllerUrl(){
        return Mage::getUrl('adminhtml/AdvancedStock_Transfer/ImportProducts', array());
    }

    public function getTransfer() {
        return mage::registry('current_transfer');
    }
    
    /**
	 * Return a combobox to choose to the CSV file delimiter
	 *
	 * @param unknown_type $name
	 */
	public function getDelimiter()
	{
        $name = self::fieldOptionDelimiter;

        $value = $this->getRequest()->getParam($name);

        $delimiters = array(
            ',' => 44,
            ';' => 59,
            '|' => 124,
            'Tab'=> 9
        );

		$html = '<select name="'.$name.'" id="'.$name.'">';

            foreach($delimiters as $delimiterLabel => $delimiterValue){
                $selected = '';
                if ($value == $delimiterValue)
                    $selected = ' selected ';
                $html .= '<option value="'.$delimiterValue.'" '.$selected.'>'.$delimiterLabel.'</option>';
            }

		$html .= '</select>';

		return $html;
	}
    
    
}
