<?php

/**
 * Customer edit block
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_ProductReturn_Block_Admin_SupplierReturn_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    private $_rsr = null;

    /**
     * Define buttons
     *
     */
    public function __construct()
    {

        $this->_objectId   = 'id';
        $this->_controller = 'Admin_SupplierReturn';
        $this->_blockGroup = 'ProductReturn';

        //init rsr object
        $this->getRsr();

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('ProductReturn')->__('Save'));
        $this->_updateButton('save', 'onclick', 'validateSupplierReturnForm()');
        $this->_removeButton('delete');

        if ($this->getRsr()->getId()) {
            $this->_addButton(
                'print',
                array(
                    'label'   => Mage::helper('ProductReturn')->__('Print'),
                    'onclick' => "window.location.href='" . $this->getUrl('adminhtml/ProductReturn_SupplierReturn/Print', array('rsr_id' => $this->getRsr()->getId())) . "'"
                )
            );
        }
    }

    public function getHeaderText()
    {
        return Mage::helper('ProductReturn')->__('Supplier Return Edit');
    }

    public function getRsr()
    {
        if ($this->_rsr == null) {
            $rsrId = Mage::app()->getRequest()->getParam('rsr_id', false);
            $model = Mage::getModel('ProductReturn/SupplierReturn');

            $this->_rsr = $model->load($rsrId);
            mage::register('current_rsr', $this->_rsr);
        }

        return $this->_rsr;
    }

    public function getSaveUrl()
    {
        return $this->getUrl('adminhtml/ProductReturn_SupplierReturn/Save');
    }

    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/ProductReturn_SupplierReturn/index');
    }
}
