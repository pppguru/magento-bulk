<?php
/**
 * Order information tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_Orderpreparation_Block_Adminhtml_Sales_Order_View_Tab_Preparation
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	
	public $OrderToPrepare = null;
	
    protected function _construct()
    {
        parent::_construct();
        
        $this->setTemplate('sales/order/view/tab/Preparation.phtml');
    }
	
    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Retrieve source model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getOrder();
    }
    	
	/**
	 * Retourne l'url pour imprimer la liste de la commande avec les commentaires & r�servations
	 *
	 */
	public function getPrintUrl()
	{
		return $this->getUrl('adminhtml/OrderPreparation_OrderPreparation/PrintComments/', array('order_id' => $this->GetOrder()->getid()));
	}
	
	/**
	 * Return url to release all products
	 *
	 */
	public function getReleaseAllProductsUrl()
	{
		return $this->getUrl('adminhtml/OrderPreparation_SalesOrder/ReleaseAllProducts/', array('order_id' => $this->GetOrder()->getid()));
	}
		
	/**
	 * Return url to reserve all products
	 *
	 */
	public function getReserveAllProductsUrl()
	{
		return $this->getUrl('adminhtml/OrderPreparation_SalesOrder/ReserveAllProducts/', array('order_id' => $this->GetOrder()->getid()));
	}
	
	/**
	 * 
	 *
	 */
	public function getOrderToPrepare()
	{
		if ($this->OrderToPrepare == null)
		{
			$this->OrderToPrepare = mage::getModel('Orderpreparation/ordertoprepare')->load($this->getOrder()->getId(), 'order_id');
			if (!$this->OrderToPrepare->getId())
				$this->OrderToPrepare = null;
		}
		return $this->OrderToPrepare;
	}
	
	/**
	 * 
	 *
	 */
	public function getOrderShipments()
	{
		$collection = null;
		if ($this->getOrder())
		{
			$collection = $this->getOrder()->getShipmentsCollection();			
		}
		return $collection;
	}
		
	/**
	 * 
	 *
	 */
	public function getOrderInvoices()
	{
		$collection = null;
		if ($this->getOrder())
		{
			$collection = $this->getOrder()->getInvoiceCollection();			
		}
		return $collection;		
	}
    
	public function getShipmentsAsCombo($name, $value)
	{
		$ComboShipment = '<select id="'.$name.'" name="'.$name.'">';
		$ComboShipment .= '<option value=""></option>';
		$collection = $this->getOrderShipments();
		foreach ($collection as $shipment)
		{
			$selected = '';
			if ($shipment->getincrement_id() == $value)
				$selected = ' selected ';
			$ComboShipment .= '<option value="'.$shipment->getincrement_id().'" '.$selected.'>'.$shipment->getincrement_id().' ('.$shipment->getcreated_at().')</option>';
		}
		$ComboShipment .= '</select>';
		return $ComboShipment;
	}

        public function getShippingMethodsAsCombo($name)
        {
            $methods = mage::helper('Orderpreparation/ShippingMethods')->getArray();
            $html = '<select name="'.$name.'" id="'.$name.'">';

            $html .= '<option value=""></option>';
            foreach($methods as $key => $label)
            {
                $html .= '<option value="'.$key.'">'.$label.'</option>';
            }

            $html .= '</select>';
            return $html;
        }
        
	public function getInvoicesAsCombo($name, $value)
	{
		$ComboInvoice = '<select id="'.$name.'" name="'.$name.'">';
		$ComboInvoice .= '<option value=""></option>';
		$collection = $this->getOrderInvoices();
		foreach ($collection as $invoice)
		{
			$selected = '';
			if ($invoice->getincrement_id() == $value)
				$selected = ' selected ';
			$ComboInvoice .= '<option value="'.$invoice->getincrement_id().'"'.$selected.'>'.$invoice->getincrement_id().' ('.$invoice->getcreated_at().')</option>';
		}
		$ComboInvoice .= '</select>';
		
		return $ComboInvoice;
	}

	public function getReservedColumnHtml($orderItem)
	{
        $html = '';

    	$reservedQty = $orderItem->getreserved_qty();
    	$productId = $orderItem->getproduct_id();

    	if ($this->itemManageStock($orderItem))
		{
            $remainingQty = (int)$orderItem->getqty_ordered() - $orderItem->getRealShippedQty();
            if($remainingQty<0){
                $remainingQty = 0;
            }

			if ($remainingQty == 0)
			{
                $html = $this->__('Shipped');
			}
			else 
			{
				if (($reservedQty < $remainingQty) && ($remainingQty > 0)) {
					$reserveUrl = Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_SalesOrder/ReserveProduct', array('product_id' => $productId, 'order_id' => $orderItem->getOrderId(), 'return_to_order' => 1, 'order_item_id' => $orderItem->getId()));
					$html .= '<a href="' . $reserveUrl . '">' . mage::helper('purchase')->__('Reserve') . '</a><br>';
				}
			}
		}
		else 
		{
            $html = "<font color=\"red\">".$this->__('No Stock Management')."</font>";
		}
		
        return $html;
	}
		
	public function itemManageStock($item)
	{
    	$product = mage::getModel('catalog/product')->load($item->getproduct_id());
		return $product->getStockItem()->getManageStock();
	}
	
	/**
	 * Return product location in sales order item
	 */
	public function getProductLocation($item)
	{
		return $item->getShelfLocation();
	}
	
	/**
	 * Return warehouse associated to sales order item
	 */
	public function getPreparationWarehouseCombo($item)
	{
		$name = 'data['.$item->getId().'][preparation_warehouse]';
        $disabled = ($item->getRealShippedQty()>0)?'DISABLED':'';
		$html = '<select name="'.$name.'" id="'.$name.' '.$disabled.'">';
		foreach($this->getWarehouses() as $warehouse)
		{
			$selected = '';
			if ($item->getpreparation_warehouse() == $warehouse->getId())
				$selected = ' selected ';
			$html .= '<option value="'.$warehouse->getId().'" '.$selected.'  '.$disabled.'>'.$warehouse->getstock_name().'</option>';
		}
		$html .= '<select>';
		return $html;
	}

	/**
	 * "Singleton" for warehouses
	 */
	protected function getWarehouses()
	{
		if ($this->_warehouses == null)
		{
			$this->_warehouses = mage::getModel('AdvancedStock/Warehouse')->getCollection()->setOrder('stock_name', 'asc');
		}
		return $this->_warehouses;
	}

	public function getReservedQty($item)
	{
		$color = 'black';
		if ($item->getreserved_qty() < $item->getRemainToShipQty())
			$color = 'red';
		else
			$color = 'green';
			
		return '<font color="'.$color.'">'.((int)$item->getreserved_qty()).'</font>';
	}

	
    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('sales')->__('Preparation');
    }

    public function getTabTitle()
    {
        return Mage::helper('sales')->__('Preparation');
    }

    public function canShowTab()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/erp_tabs/preparation');
    }

    public function isHidden()
    {
        return false;
    }
}