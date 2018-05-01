<?php

class MDN_Organizer_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Retourne un combo avec la liste des utilisateurs
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function getUsersAsCombo($name, $value, $allowNone = false) {
        //recupere la liste des utilisateurs
        $collection = mage::getModel('admin/user')
                ->getCollection()
                ->addFieldToFilter('is_active', 1);

        $html = '<select name="' . $name . '" id="' . $name . '">';
        if ($allowNone)
            $html .= '<option value=""></option>';
        foreach ($collection as $item) {
            $selected = '';
            if ($item->getuser_id() == $value)
                $selected = ' selected ';
            $html .= '<option value="' . $item->getuser_id() . '" ' . $selected . '>' . $item->getusername() . '</option>';
        }

        $html .= '</select>';
        return $html;
    }

    /**
     * Retourne un combo avec la liste des utilisateurs
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function getPriorityAsCombo($name, $value) {
        $value = (!$value)?1:$value;
        $html = '<select name="' . $name . '" id="' . $name . '">';
        for ($i = 1; $i <= 5; $i++) {
            $selected = '';
            if ($i == $value)
                $selected = ' selected ';
            $html .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
        }

        $html .= '</select>';
        return $html;
    }

    /**
     * Retourne un combo avec la liste des categories
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function getCategoryAsCombo($name, $value) {
        //recupere la liste des utilisateurs
        $collection = mage::getModel('Organizer/TaskCategory')
                ->getCollection()
                ->setOrder('otc_name', 'ASC');

        $html = '<select name="' . $name . '" id="' . $name . '">';
        foreach ($collection as $item) {
            $selected = '';
            if ($item->getotc_id() == $value)
                $selected = ' selected ';
            $html .= '<option value="' . $item->getotc_id() . '" ' . $selected . '>' . $item->getotc_name() . '</option>';
        }


        $html .= '</select>';
        return $html;
    }

    /**
     * Retourne un combo avec la liste des origines
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function getOriginAsCombo($name, $value) {
        //recupere la liste des utilisateurs
        $collection = mage::getModel('Organizer/TaskOrigin')
                ->getCollection();

        $html = '<select name="' . $name . '" id="' . $name . '">';
        foreach ($collection as $item) {
            $selected = '';
            if ($item->getoto_id() == $value)
                $selected = ' selected ';
            $html .= '<option value="' . $item->getoto_id() . '" ' . $selected . '>' . $item->getoto_name() . '</option>';
        }


        $html .= '</select>';
        return $html;
    }

    /**
     * Retourne l'id de l'utilisateur courant
     *
     */
    public function getCurrentUserId() {
        return Mage::getSingleton('admin/session')->getUser()->getId();
    }

    /**
     * Enter description here...
     *
     */
    public function getEntityDescription($entityType, $entityId) {
        $retour = '';
        $helper = Mage::helper('Organizer');
        switch ($entityType) {
            case MDN_Organizer_Model_Task::ENTITY_TYPE_ORDER:
                $order = mage::getModel('sales/order')->load($entityId);
                $retour = $helper->__('Order').' #' . $order->getIncrementId();
                break;
            case MDN_Organizer_Model_Task::ENTITY_TYPE_CUSTOMER:
                $customer = mage::getModel('customer/customer')->load($entityId);
                $retour = $customer->getName();
                break;
            case MDN_Organizer_Model_Task::ENTITY_TYPE_RMA:
                $rma = mage::getModel('ProductReturn/Rma')->load($entityId);
                $retour = $helper->__('Rma').' #' . $rma->getrma_ref();
                break;
            case MDN_Organizer_Model_Task::ENTITY_TYPE_PURCHASE_ORDER:
                $order = mage::getModel('Purchase/Order')->load($entityId);
                $retour = $helper->__('PO').' #' . $order->getpo_order_id();
                break;
            case MDN_Organizer_Model_Task::ENTITY_TYPE_PRODUCT:
                $product = mage::getModel('catalog/product')->load($entityId);
                $retour = $product->getname();
                break;
            case MDN_Organizer_Model_Task::ENTITY_TYPE_SUPPLIER:
                $supplier = mage::getModel('Purchase/Supplier')->load($entityId);
                $retour = $supplier->getsup_name();
                break;
            case MDN_Organizer_Model_Task::ENTITY_TYPE_CREDIT_MEMO:
                $credit_memo = mage::getModel('sales/order_creditmemo')->load($entityId);
                $retour = $helper->__('Credit Memo').' #' . $credit_memo->getIncrementId();
                break;
        }
        return $retour;
    }

    /**
     * Return all comments for an entity as test
     *
     * @param unknown_type $entityType
     * @param unknown_type $entityId
     */
    public function getEntityCommentsSummary($entityType, $entityId, $html = false) {
        $collection = Mage::getResourceModel('Organizer/Task_Collection')
                ->getTasksForEntity($entityType, $entityId, '');

        $retour = '';
        foreach ($collection as $item) {
            if ($html)
                $retour .= '<b>' . mage::helper('core')->formatDate($item->getot_created_at(), 'medium') . ' - ' . $item->getot_caption() . '</b> : ' . $item->getot_description() . '<br>';
            else
                $retour .= mage::helper('core')->formatDate($item->getot_created_at(), 'medium') . ' - ' . $item->getot_caption() . ' : ' . $item->getot_description() . "\n";
        }

        return $retour;
    }


    public function getYesNoCombo($name,$value)
    {
        $html = '<select name="'.$name.'" id="'.$name.'">';

        $selected = ($value == 1)?' selected ':'';
        $html .= '<option value="1" '.$selected.'>'.$this->__('Yes').'</option>';

        $selected = ($value == 0)?' selected ':'';
        $html .= '<option value="0" '.$selected.'>'.$this->__('No').'</option>';

        $html .= '</select>';
        return $html;
    }

}

?>