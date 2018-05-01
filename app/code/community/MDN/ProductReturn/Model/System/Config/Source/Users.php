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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Model_System_Config_Source_Users extends Mage_Core_Model_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $users = mage::getModel('admin/user')->getCollection();
            foreach ($users as $user):
                $this->_options[] = array('value' => $user->getId(), 'label' => $user->getUsername().'&nbsp;');
            endforeach;
        }

        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    public function toArray(array $arrAttributes = array())
    {
        $users = mage::getModel('admin/user')->getCollection();
        foreach ($users as $user):
            $returnusers[$user->getId()] =  $user->getUsername().'&nbsp;';
        endforeach;
        return $returnusers;
    }
}