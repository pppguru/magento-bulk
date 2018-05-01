<?php


class MDN_Purchase_Adminhtml_Purchase_ProductsController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Lie un supplier au produit
     *
     */
    public function LinkSupplierAction()
    {
    	//recupere les infos
		$product_id = $this->getRequest()->getParam('product_id');    	
		$supplier_id = $this->getRequest()->getParam('supplier_id');    	
		
		//insere dans la base
		mage::getModel('Purchase/ProductSupplier')
			->setpps_product_id($product_id)
			->setpps_supplier_num($supplier_id)
			->save();
    }
        
    /**
     * Retourne la liste des suppliers associ�s (juste le tableau)
     *
     */
    public function getAssociatedSuppliersAction()
    {
    	//recupere les infos
    	$product_id = Mage::app()->getRequest()->getParam('product_id');
    	
    	//cree le block et le retourne
    	$this->loadLayout();	//Charge le layout pour appliquer le theme pour l'admin
    	$block = $this->getLayout()->createBlock('Purchase/Product_Edit_Tabs_AssociatedSuppliers', 'associatedsuppliers');
    	$block->setProductId($product_id);
    	$block->setTemplate('Purchase/Product/Edit/Tab/AssociatedSuppliers.phtml');
    	
    	$this->getResponse()->setBody($block->toHtml());
    }
    
    /**
     * Supprime l'association avec un supplier
     *
     */
    public function DeleteAssociatedSupplierAction()
    {
    	//recupere l'id
    	$pps_id = Mage::app()->getRequest()->getParam('pps_id');
    	
    	//supprime
		Mage::getModel('Purchase/ProductSupplier')    	
			->load($pps_id)
			->delete();
			
    }
        
    /**
     * Retourne en ajax les informations sur l'association entre un produit et un supplier
     *
     */
    public function GetSupplierInformationAction()
    {
    	
    	//recupere l'objet
    	$object = mage::GetModel('Purchase/ProductSupplier')
    				->load($this->getRequest()->getParam('pps_id'));

        $supplier = mage::getModel('Purchase/Supplier')->load($object->getpps_supplier_num());
        $object->setsup_name($supplier->getsup_name());
        $object->setsup_currency($supplier->getsup_currency());

    	//retourne en ajax
    	$this->getResponse()->setHeader('Content-type', 'application/x-json');
        $this->getResponse()->setBody($object->toJson());
    }
        
    /**
     * Sauvegarde les informations sur un supplier associ� a un produit
     *
     */
    public function SaveAssociatedSupplierAction()
    {
    	//recupere l'id
    	$pps_num = $this->getRequest()->getParam('pps_num');
    	
    	//met a jour & save
    	$object = mage::getModel('Purchase/ProductSupplier')->load($pps_num);
    	$object->setpps_comments($this->getRequest()->getParam('pps_comments'));
    	$object->setpps_reference($this->getRequest()->getParam('pps_reference'));
    	$object->setpps_last_price($this->getRequest()->getParam('pps_last_price'));
    	$object->setpps_last_unit_price($this->getRequest()->getParam('pps_last_unit_price'));
    	$object->setpps_price_position($this->getRequest()->getParam('pps_price_position'));
    	$object->setpps_quantity_product($this->getRequest()->getParam('pps_quantity_product'));
    	$object->setpps_is_default_supplier($this->getRequest()->getParam('pps_is_default_supplier'));
    	$object->setpps_can_dropship($this->getRequest()->getParam('pps_can_dropship'));
    	$object->setpps_discount_level($this->getRequest()->getParam('pps_discount_level'));
    	$object->setpps_last_unit_price_supplier_currency($this->getRequest()->getParam('pps_last_unit_price_supplier_currency'));
    	$object->setpps_product_name($this->getRequest()->getParam('pps_product_name'));

        $supplyDelay = $this->getRequest()->getParam('pps_supply_delay');
        if (!$supplyDelay)
            $supplyDelay = new Zend_Db_Expr('null');
        $object->setpps_supply_delay($supplyDelay);

        $object->save();

        $productId = $object->getpps_product_id();
        if($productId != null && $productId>0) {
            mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->RefreshForOneProduct($productId);
        }

    }
           
    /**
     * Method for associated order ajax refresh
     *
     */
    public function AssociatedOrdersGridAction()
    {
    	$this->loadLayout();
     	$ProductId = $this->getRequest()->getParam('product_id');
     	$product = mage::getModel('catalog/product')->load($ProductId);
		$Block = $this->getLayout()->createBlock('Purchase/Product_Edit_Tabs_AssociatedOrdersGrid');
		$Block->setProduct($product);
        $this->getResponse()->setBody($Block->toHtml());
    }
    
    /**
     * Return product stock details to display in prototype window
     *
     */
    public function productStockDetailsAction()
    {
    	//init vars
    	$productId = $this->getRequest()->getParam('product_id');
     	$product = mage::getModel('catalog/product')->load($productId);
     	
     	//retrieve block hmlt output
     	$block = $this->getLayout()->createBlock('Purchase/Product_Widget_StockDetails');
     	$block->setTemplate('Purchase/Product/StockDetails.phtml');
     	$block->setProduct($product);
     	$html = $block->toHtml();
     	
     	//return html 
     	$this->getResponse()->setBody($html);
    }

    /**
     * Return suppiers grid
     */
    public function AjaxSuppliersAction()
    {
        //init vars
        $productId = $this->getRequest()->getParam('product_id');
        $product = mage::getModel('catalog/product')->load($productId);

     	//retrieve block hmlt output
     	$block = $this->getLayout()->createBlock('Purchase/Product_Edit_Tabs_AssociatedSuppliers');
     	$block->setTemplate('Purchase/Product/Edit/Tab/AssociatedSuppliers.phtml');
     	$block->setProduct($product);
     	$html = $block->toHtml();

     	//return html
     	$this->getResponse()->setBody($html);
    }

    /**
     * Mass a associate a product to a supplier
     */
    public function MassAssociateToSupplierAction(){
        
        $request = $this->getRequest();

        $productList = $request->getPost('ProductsList');
        $supplierId = $request->getPost('suppliers');
        
        $associationCreatedCount = 0;

        if($productList && $supplierId) {
            // for all products, assign them into the action selected
            foreach ($productList as $productId) {               
                $association = mage::getModel('Purchase/ProductSupplier')->loadForProductSupplier($productId, $supplierId);
                
                 //create association if does not exist
                if(!($association->getId()>0)){
                    mage::getModel('Purchase/ProductSupplier')
                        ->setpps_product_id($productId)
                        ->setpps_supplier_num($supplierId)
                        ->save();
                    $associationCreatedCount++;
                }
            }
        }
        $this->_redirect('adminhtml/AdvancedStock_Products/Grid/');
    }

    public function MassRemoveAssociationWithSupplierAction(){

            $request = $this->getRequest();

            $productList = $request->getPost('ProductsList');
            $supplierId = $request->getPost('suppliers');

            $associationRemovedCount = 0;

            if($productList && $supplierId) {
                // for all products, assign them into the action selected
                foreach ($productList as $productId) {
                    $association = mage::getModel('Purchase/ProductSupplier')->loadForProductSupplier($productId, $supplierId);

                    //create association if does not exist
                    if($association->getId()>0){
                        $association->delete();
                        $associationRemovedCount++;
                    }
                }
            }

        if($associationRemovedCount>0){
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s product-supplier link(s) deleted',$associationRemovedCount));
        }else{
           Mage::getSingleton('adminhtml/session')->addError($this->__('%s product-supplier link(s) deleted',$associationRemovedCount));
        }
        
        $this->_redirect('adminhtml/AdvancedStock_Products/Grid/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing');
    }
}