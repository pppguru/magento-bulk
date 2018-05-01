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
class MDN_Purchase_Block_Order_Edit_Tabs_Accounting extends Mage_Adminhtml_Block_Widget_Form {

    private $_order = null;

    /**
     * Constructeur: on charge
     *
     */
    public function __construct() {

        $this->_blockGroup = 'Purchase';
        $this->_objectId = 'id';
        $this->_controller = 'order';

        parent::__construct();

        $this->setTemplate('Purchase/Order/Edit/Tab/Accounting.phtml');
    }

    /**
     * D�finit l'order
     *
     */
    public function setOrderId($value) {
        $this->_order = mage::getModel('Purchase/Order')->load($value);
        return $this;
    }

    /**
     * Retourne l'objet
     *
     * @return unknown
     */
    public function getOrder() {
        return $this->_order;
    }

    public function getBooleanCombo($name, $defaultValue) {
        $values = array('0' => $this->__('No'), '1' => $this->__('yes'));
        $html = '<select name="' . $name . '" id="' . $name . '">';
        foreach ($values as $key => $label) {
            $selected = '';
            if ($key == $defaultValue)
                $selected = ' selected ';
            $html .= '<option value="' . $key . '" ' . $selected . '>' . $label . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

}
