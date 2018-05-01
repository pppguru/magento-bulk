<?php

class MDN_Purchase_Adminhtml_Purchase_TaxController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * List the tax rates
	 *
	 */
	public function ListAction()
	{
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Taxes'));

        $this->renderLayout();
	}
	
	/**
	 * Edit a  tax rate
	 *
	 */
	public function EditAction()
	{
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit Tax Rate'));

        $this->renderLayout();
	}
	
	/**
	 * new tax rate
	 *
	 */
	public function NewAction()
	{
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('New Tax Rate'));

        $this->renderLayout();
	}
	
	/**
	 * Create a tax rate
	 *
	 */
	public function CreateAction()
	{

        $model = Mage::getModel('Purchase/TaxRates');
		$TaxRate = $model->setptr_name($this->getRequest()->getPost('ptr_name'))
						->setptr_value($this->getRequest()->getPost('ptr_value'))
						->save();
				
		    	
    	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));
    	
    	$this->_redirect('adminhtml/Purchase_Tax/Edit/ptr_id/'.$TaxRate->getId());
	}
	
	/**
	 * Save a tax rate
	 *
	 */
	public function SaveAction()
	{
		$ptr_id = $this->getRequest()->getPost('ptr_id');

        $model = Mage::getModel('Purchase/TaxRates');
		$TaxRate = $model->load($ptr_id);
		$TaxRate->setptr_name($this->getRequest()->getPost('ptr_name'))
				->setptr_value($this->getRequest()->getPost('ptr_value'))
				->save();
				
		    	
    	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));
    	
    	$this->_redirect('adminhtml/Purchase_Tax/Edit/ptr_id/'.$TaxRate->getId());
	}
	
	/**
	 * Delete a tax rate
	 *
	 */
	public function DeleteAction()
	{
		$ptr_id = $this->getRequest()->getParam('ptr_id');

        $taxRate = Mage::getModel('Purchase/TaxRates')->load($ptr_id);

		if($taxRate){
            $taxRate->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Tax Rate deleted'));
        }
    	
    	$this->_redirect('adminhtml/Purchase_Tax/List');
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing');
    }
}