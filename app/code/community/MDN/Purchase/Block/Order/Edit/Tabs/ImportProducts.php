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
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Purchase_Block_Order_Edit_Tabs_ImportProducts extends Mage_Adminhtml_Block_Widget_Form {

    private $_order = null;


    //Form IDs
    const fieldPurchaseOrderId = 'po_num';
    const fieldFile = 'import_products';
    const fieldOptionDelimiter = 'delimiter';

    /**
     * Load template
     *
     */
    public function __construct() {

        parent::__construct();

        $this->setTemplate('Purchase/Order/Edit/Tab/ImportProducts.phtml');
    }

    /**
     * Return the current Purchase Order Object
     *
     * @return unknown
     */
    public function getOrder() {
        if ($this->_order == null) {
            $po_num = Mage::app()->getRequest()->getParam('po_num', false);
            $model = Mage::getModel('Purchase/Order');
            $this->_order = $model->load($po_num);
        }
        return $this->_order;
    }

    public function getFormPoId()
	{
        return self::fieldPurchaseOrderId;
    }

    public function getFormFileId()
	{
        return self::fieldFile;
    }

    public function getControllerUrl(){
        return Mage::getUrl('adminhtml/Purchase_Orders/ImportProducts', array());
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

