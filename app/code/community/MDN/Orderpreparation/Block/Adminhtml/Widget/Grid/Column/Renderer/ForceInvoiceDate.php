<?php

/*
* Colonne pour forcer la date de facture
*/
class MDN_Orderpreparation_Block_Adminhtml_Widget_Grid_Column_Renderer_ForceInvoiceDate
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$value = mage::getModel('Orderpreparation/ordertoprepare')->load($row->getId(), 'order_id')->getforce_invoice_date();
    	if ($value == '0000-00-00')
    		$value = '';
    		
		$html = 'Date <input onchange="HideNonSaveButtons()" type="text" id="force_invoice_date_'.$row->getid().'" name="force_invoice_date_'.$row->getid().'" size="8" value="'.$value.'" />';
		
		//rajoute le petit calendrier
		$html .= ' <img src="'.$this->getSkinUrl('images/grid-cal.gif').'" class="v-middle" id="img_calendar_'.$row->getid().'" />';
		$html .= "
		        <script type=\"text/javascript\">
	                Calendar.setup({
	                    inputField : 'force_invoice_date_".$row->getid()."',
	                    ifFormat : '%Y-%m-%e',
	                    button : 'img_calendar_".$row->getid()."',
	                    align : 'Bl',
	                    singleClick : true
	                });
                </script>		
		";
		
		//champs poids
		$html .= '<br>'.$this->__('Weight').' <input onchange="HideNonSaveButtons()" type="text" name="real_weight_'.$row->getid().'" size="2" value="'.$row->getreal_weight().'" />';

		//Nombre de paquets
		$html .= '<br>'.$this->__('Packages').' <input onchange="HideNonSaveButtons()" type="text" size="2" maxlength="2" name="package_count_'.$row->getid().'" size="2" value="'.$row->getpackage_count().'" />';
		
		//Type de produit d'expe
		$CurrentValue = $row->getship_mode();
    	$Carrier = $row->getshipping_method();
    	
    	//cree le menu
    	$retour = '<select onchange="HideNonSaveButtons()" id="ship_product_type_'.$row->getid().'" name="ship_product_type_'.$row->getid().'">';
    	$model = mage::Helper('Orderpreparation')->getCarrierModel($Carrier);
    	if ($model)
    	{
	    	$values = $model->GetProductTypes();
	    	$retour .= '<option value=""></option>';
	    	foreach ($values as $key => $value)
	    	{
				$retour .= '<option value="'.$key.'"';
				if ($key == $CurrentValue)
					$retour .= ' selected ';
				$retour .= '>'.$value.'</option>';
	    	}
    	}
    	else 
    		$retour .= '<option>No carrier for '.$Carrier.' </option>';
    	$retour .= '</select>';
    	$html .= '<br>'.$this->__('Ship Mode').' '.$retour;
		return $html;
    }
    
}