<?php


class MDN_Purchase_Adminhtml_Purchase_RemainingSupplyQuantitiesController extends Mage_Adminhtml_Controller_Action
{
	public function ListAction()
	{
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Remaining supply quantities'));

        $this->renderLayout();
	}
	
	public function exportCsvAction()
	{
    	$fileName   = 'remaining_supply_quantities.csv';
        $content    = $this->getLayout()->createBlock('Purchase/RemainingSupplyQuantities_Grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing');
    }
}