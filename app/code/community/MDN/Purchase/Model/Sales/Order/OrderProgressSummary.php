<?php

/**
 * Classe permettant d'expliquer l'état d'avancement d'une commande
 *
 */
class MDN_Purchase_Model_Sales_Order_OrderProgressSummary
{
	public $_order = null;
	
	/**
	 * Définit la commande
	 *
	 * @param unknown_type $order
	 */
    public function setOrder($order)
    {
    	$this->_order = $order;
    }
    
    /**
     * Retourne la date a partir de laquelle on prend en ciompte la commande (en fonction du paiement)
     *
     */
    public function getStartDateTimestamp()
    {
    	$retour_rs = strtotime($this->_order->getcreated_at());
    	if (($retour_rs == '') || ($retour_rs == null))
		    $retour_rs = time();
    	return $retour_rs;
    }
    
    /**
     * Retourne les explication sur l'état de la commande
     *
     */
    public function getSummary()
    {
    	//$planning = $this->_order->getPlanning();
    	
    	//informations retournées
    	$retour = array();
    	$retour['msg'] = null;
    	$retour['ship_date'] = null;
    	$retour['ship_date_ts'] = null;
    	$retour['delivery_date'] = null;
    	$retour['preparation_date'] = null;
    	$retour['preparation_date_ts'] = null;
    	$retour['supply_percent'] = 0;
    	$retour['preparation_percent'] = null;
    	$retour['assembly_duration'] = null;
    	$retour['current_step'] = null;
    	$retour['payment_validated'] = false;
    	$retour['ok_to_process'] = true;

	    //Définit l'état du paiement
		$retour['payment_validated'] = false; 
		if ($this->_order->getpayment_validated() == 1)
			$retour['payment_validated'] = true;
    	
    	//Définit le temps de préparation
		$PreparationDuration = Mage::getStoreConfig('orderpreparation/general/order_management_delay');
		$retour['assembly_duration'] = $PreparationDuration;
					
		//En fonction de l'etat de la commande
    	switch($this->_order->status)
    	{
    		case 'pending':
				if ($retour['ok_to_process'])
    			{

	    			//Parcourt les produits pour voir on ou en est
	    			$missingProducts = '';
	    			$AllProductReserved = true;
	    			$WorthSupplyDelay = null;
	    			$OrderDateTimestamp =  strtotime($this->_order->getcreated_at());
	    			if (($OrderDateTimestamp == '') || ($OrderDateTimestamp == null))
		    					$OrderDateTimestamp = time();
	    			$ProductCount = 0;
	    			$MissingProductCount = 0;
	    			foreach($this->_order->getItemsCollection() as $item)
	    			{
	    				$ProductCount += 1;
	    				$remaining_qty = $item->getRemainToShipQty();
	    				if ($remaining_qty > 0)
	    				{
			    			//recupere le produit
			    			$productid = $item->getproduct_id();
			    			$product = mage::getModel('catalog/product')->load($productid);
			    			
			    			//Si pas en stock et pas reservé
			    			if ($item->getreserved_qty() == 0)
			    			{
				    			$SM = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productid);
				    			if ($SM->getManageStock())
				    			{
				    				$AllProductReserved = false;
				    				$availableQty = mage::helper('AdvancedStock/Product_Base')->getAvailableQty($productid, $this->_order->getStore()->getwebsite_id());
				    				if ($remaining_qty > $product->GetAvailableQty())
				    				{
				    					$MissingProductCount += 1;
				    					$missingProducts .= '<li> - ';
				    					$missingProducts .= $product->getName();
				    					//rajoute la date d'appro si existe
										$date_reappro = $product->getsupply_date();	
										if (($date_reappro != "") && (strtotime($date_reappro) >= strtotime(date('Y-m-d'))))
										{
											$DateReapproTimeStamp = strtotime($date_reappro);
											//Commenté car on part du principe qu'on a pas d'appro un jour férié...
											//$DateReapproTimeStamp = $this->get_next_open_day($DateReapproTimeStamp);
											$missingProducts .= " (Approvisionnement pr&eacute;vu au ";
											$missingProducts .= mage::helper('core')->formatDate(date('Y-m-d', $DateReapproTimeStamp), 'medium');
											$missingProducts .= ")";
											//verifie si c le discriminant le plus important										
											if ($DateReapproTimeStamp > $WorthSupplyDelay)
												$WorthSupplyDelay = $DateReapproTimeStamp;
										}
										else 
										{
											//dispo par défaut
											$missingProducts .= " (D&eacute;lai d'approvisionnement : ";
											$missingProducts .= $product->getsupply_delay().'j';
											$missingProducts .= ")";
											//verifie si c le discriminant le plus important
											$maxDay = $product->getsupply_delay();
											//rajoute les jours fermés
											$DateReapproTimeStamp = $this->get_final_date($this->getStartDateTimestamp(), $maxDay);
											if ($DateReapproTimeStamp > $WorthSupplyDelay)
												$WorthSupplyDelay = $DateReapproTimeStamp;
										}
				    					$missingProducts .= '</li>';
				    				}
				    				else 
				    				{
				    					//Produit non réservé mais disponible (cas un peu bizarre)
										$maxDay = $product->getsupply_delay();
										//rajoute les jours fermés
										$DateReapproTimeStamp = $this->get_final_date($this->getStartDateTimestamp(), $maxDay);
										if ($DateReapproTimeStamp > $WorthSupplyDelay)
											$WorthSupplyDelay = $DateReapproTimeStamp;
										$MissingProductCount += 1;
				    				}
				    			}
			    			}		    			
	    				}
	    			}
	    			if ($missingProducts != '')
	    			{
	    				$retour['msg'] = 'Votre commande est en attente car nous sommes en cours d\'approvisionnement sur le(s) produit(s) suivant(s) : <ul type="square">'.$missingProducts.'</ul>';
	    				//Définit et rajoute a la date prévue de livraison le temps de preparation
	    				$retour['preparation_date_ts'] = $WorthSupplyDelay;
	    				$retour['preparation_date'] = date('Y-m-d', $WorthSupplyDelay);
						$WorthSupplyDelay = $this->get_final_date($WorthSupplyDelay, $PreparationDuration);

	    				$retour['ship_date'] = date('Y-m-d', $WorthSupplyDelay);
	    				$retour['ship_date_ts'] = $WorthSupplyDelay;

						//rajoute la date prévue de livraison
						if ($this->_order->getShippingAddress() != null)
						{
							$DeliveryDate = $this->getEstimatedDeliveryDate(date('Y-m-d', $retour['ship_date_ts']), $this->_order->getshipping_method(), $this->_order->getShippingAddress()->getcountry());
							if ($DeliveryDate != null)
								$retour['delivery_date'] = $DeliveryDate;	
							$retour['supply_percent'] = 100 - round(100 / $ProductCount * $MissingProductCount, 0);	
						}
						else 
						{
							$retour['supply_percent'] = 100;
						}
	    			}
	    			else 
	    			{
		    			if ($AllProductReserved)
		    			{
		    				$retour['supply_percent'] = '100';
		    				//Définit la date de traitement (en fonction de l'heure de passage de la commande)
		    				$ProcessDelay = 0;
		    				$OrderDateTimestamp =  strtotime($this->_order->getcreated_at());
		    				if (($OrderDateTimestamp == '') || ($OrderDateTimestamp == null))
		    					$OrderDateTimestamp = time();
		    				//recupere la date de fullstock pour la commande
		    				$FullStockDate = strtotime($this->_order->getfullstock_date());
		    				if ($this->getStartDateTimestamp() > $FullStockDate)
		    					$FullStockDate = $this->getStartDateTimestamp();
		   					//Si commande passée aujourdui, on joue sur l'heure pour éventuellement décaler la prépa a demain
		   					$retour['msg'] = '';	    				
		    				//Définit la date de préparation
		    				$retour['preparation_date_ts'] = $FullStockDate;
		    				$retour['preparation_date'] = date('Y-m-d', $FullStockDate);
		    				//rajoute le temps de preparation de la commande
		    				$ProcessDelay += $PreparationDuration;
							//rajoute les jours fériés
							$EstimatedShipDate = $this->get_final_date($FullStockDate, $ProcessDelay);
							$retour['msg'] .= 'Tous les produits de votre commande ont &eacute;t&eacute; r&eacute;unis.';	

							$retour['ship_date'] = date('Y-m-d', $EstimatedShipDate);	
							$retour['ship_date_ts'] = $EstimatedShipDate;		

							//rajoute la date prévue de livraison
							if ($this->_order->getShippingAddress() != null)
							{
								$DeliveryDate = $this->getEstimatedDeliveryDate(date('Y-m-d', $retour['ship_date_ts']), $this->_order->getshipping_method(), $this->_order->getShippingAddress()->getcountry());
								if ($DeliveryDate != null)
									$retour['delivery_date'] = $DeliveryDate;		
							}
		    			}
		    			else 
		    			{
		    				//Si ya pas de produit manquant mais qu'ils sont pas réservés
		    				$retour['msg'] = 'Votre commande est sur le point d\'être complète';
		    				//Définit et rajoute a la date prévue de livraison le temps de preparation
		    				$retour['preparation_date_ts'] = $WorthSupplyDelay;
		    				$retour['preparation_date'] = date('Y-m-d', $WorthSupplyDelay);
							$WorthSupplyDelay = $this->get_final_date($WorthSupplyDelay, $PreparationDuration);

		    				$retour['ship_date'] = date('Y-m-d', $WorthSupplyDelay);
		    				$retour['ship_date_ts'] = $WorthSupplyDelay;

							//rajoute la date prévue de livraison
							if ($this->_order->getShippingAddress() != null)
							{
								$DeliveryDate = $this->getEstimatedDeliveryDate(date('Y-m-d', $retour['ship_date_ts']), $this->_order->getshipping_method(), $this->_order->getShippingAddress()->getcountry());
								if ($DeliveryDate != null)
									$retour['delivery_date'] = $DeliveryDate;	
								$retour['supply_percent'] = 100 - round(100 / $ProductCount * $MissingProductCount, 0);
							}
							else 
							{
								$retour['supply_percent'] = 100;
							}
		    			}
	    			}
    			}
    			break;
    		case 'complete':
    			//affiche la date d'expedition
    			$Shipment = null;
    			$retour['supply_percent'] = 100;
    			$retour['preparation_percent'] = 100;
    			foreach ($this->_order->getShipmentsCollection() as $item)
    			{
    				$Shipment = $item;
    				break;
    			}
    			if ($Shipment != null)
    			{
    				//affiche la date d'expe
    				$dateExpe = $Shipment->getcreated_at();
    				$retour['msg'] = 'Votre commande a &eacute;t&eacute; exp&eacute;di&eacute;e le '.mage::helper('core')->formatDate($dateExpe, 'medium');
    				//rajoute la date prévisionnelle d'arrivée en fonction du transporteur
    				if ($this->_order->getShippingAddress() != null)
    				{
	    				$DeliveryDate = $this->getEstimatedDeliveryDate($dateExpe, $this->_order->getshipping_method(), $this->_order->getShippingAddress()->getcountry());
	    				if ($DeliveryDate != null)
	    				{
	    					$retour['delivery_date'] = $DeliveryDate;
	    				}
    				}
    			}
    			
    	}
    	
    	//Calcul le % d'avancement de la préparation
    	if ($this->_order->status != 'complete')
    	{
	    	if ($this->_order->getassembly_duration() > 0)
	    	{
	    		//Si on a une date de préparation
		    	if ($retour['preparation_date_ts'] != null)
		    	{
			    	$ElapsedTime = time() - $retour['preparation_date_ts'];
			    	if ($ElapsedTime < 0)
			    		$retour['preparation_percent'] = 0;
			    	else 
			    	{
			    		if (time() > $retour['ship_date_ts'])
					    	$retour['preparation_percent'] = 100;
			    		else 
			    		{
			    			//calcul le % d'avancement
			    			$TotalTime = $retour['ship_date_ts'] - $retour['preparation_date_ts'];
			    			$Purcent = $ElapsedTime / $TotalTime * 100;
			    			$retour['preparation_percent'] = (int)$Purcent;
			    		}
			    	}
			    }
			    else 
			    	$retour['preparation_percent'] = 0;
	    	}
	    	else
		    	$retour['preparation_percent'] = 100;
    	}
    	else 
    	{
    		//Si commande complete
    		$retour['preparation_percent'] = 100;
    		$retour['supply_percent'] = 100;
    	}
    	
    	//Definit l'etape courante
    	try 
    	{
		    //Définit l'étape courante	
		    if (($retour['payment_validated'] == false))
		    {
		    	$retour['current_step'] = 'payment';
		    }
		    else 
		    {
				if ($retour['supply_percent'] < 100)
					$retour['current_step'] = 'supply';
				else 
				{
					if ($retour['preparation_percent'] < 100)
						$retour['current_step'] = 'assembly';
					else 
					{
						//Recupere le shipment
						$Shipment = null;
		    			foreach ($this->_order->getShipmentsCollection() as $item)
		    			{
		    				$Shipment = $item;
		    				break;
		    			}
						if ($Shipment == null)
							$retour['current_step'] = 'shipping';
						else 
							$retour['current_step'] = 'delivery';
					}
				}
		    }
    	}
    	catch (Exception $ex)
    	{
    		$retour['current_step'] = 'Undefined';
    	}
    	
    	return $retour;
    }
    
    /**
     * Retourne la date prévue d'arrivée d'un colis en fonction du transporter, de la date d'expedition et du pays
     *
     */
    public function getEstimatedDeliveryDate($ShippingDate, $Carrier, $Country)
    {
    		//Recupere la durée de livraison en fonction du transporteur
    		$DeliveryDelay = 0;
	    	$model = mage::Helper('Orderpreparation')->getCarrierModel($Carrier);
	    	if ($model)
	    	{
	    		$DeliveryDelay = $model->getDeliveryDelay($Country);
	    	}
    		
			//calcul la date de livraison
			return date("Y-m-d", $this->get_final_date(strtotime($ShippingDate), $DeliveryDelay));
    		
    }
    
    /**
     * retourne la date prévue en prenant en compte les jours ouvré
     */
    function get_final_date($date_start, $nb_jour)
    {
    	if ($nb_jour > 0)
    	{
	    	//Rajoute un jour tant que le nb de jour ouvrés n'est pas suffisant
	    	$final_date = $date_start + 86400;
			while ($this->get_nb_open_days($date_start, $final_date) < $nb_jour)
			{
				$final_date += 86400;
			}
    	}
    	else 
    		$final_date = $date_start;
		return $final_date;
    }
    
    /**
     * Retourne une date antérieure permettant d'avoir $nb_jour ouvrés
     *
     * @param unknown_type $date_start
     * @param unknown_type $nb_jour
     */
    function get_final_date_reverse($date_start, $nb_jour)
    {
    	if ($nb_jour > 0)
    	{
	    	//Rajoute un jour tant que le nb de jour ouvrés n'est pas suffisant
	    	$final_date = $date_start - 86400;
			while ($this->get_nb_open_days($final_date, $date_start) < $nb_jour)
			{
				$final_date -= 86400;
			}
    	}
    	else 
    		$final_date = $date_start;
		return $final_date;
    }
    
    //retourne le jour ouvré le plus proche pour une date (peut etre la date elle meme)
    function get_next_open_day($date_start)
    {
    	$final_date = $date_start;
		while ($this->get_nb_open_days($date_start, $final_date) < 1)
		{
			$final_date = mktime(0,0,0,date("m", $final_date),date("d",$final_date)+1,date("Y",$final_date));
		}
		return $final_date;
    }
    
    // Fonction permettant de compter le nombre de jours ouvrés entre deux dates
	function get_nb_open_days($date_start, $date_stop) {	
		$arr_bank_holidays = array(); // Tableau des jours feriés	
		
		// On boucle dans le cas où l'année de départ serait différente de l'année d'arrivée
		$sav_date_start = $date_start;
		$diff_year = date('Y', $date_stop) - date('Y', $date_start);
		for ($i = 0; $i <= $diff_year; $i++) {			
			$year = (int)date('Y', $date_start) + $i;
			// Liste des jours feriés
			$arr_bank_holidays[] = '1_1_'.$year; // Jour de l'an
			$arr_bank_holidays[] = '1_5_'.$year; // Fete du travail
			$arr_bank_holidays[] = '8_5_'.$year; // Victoire 1945
			$arr_bank_holidays[] = '14_7_'.$year; // Fete nationale
			$arr_bank_holidays[] = '15_8_'.$year; // Assomption
			$arr_bank_holidays[] = '1_11_'.$year; // Toussaint
			$arr_bank_holidays[] = '11_11_'.$year; // Armistice 1918
			$arr_bank_holidays[] = '25_12_'.$year; // Noel
					
			// Récupération de paques. Permet ensuite d'obtenir le jour de l'ascension et celui de la pentecote	
			$easter = easter_date($year);
			$arr_bank_holidays[] = date('j_n_'.$year, $easter + 86400); // Paques
			$arr_bank_holidays[] = date('j_n_'.$year, $easter + (86400*39)); // Ascension
			$arr_bank_holidays[] = date('j_n_'.$year, $easter + (86400*50)); // Pentecote	
		}
		//print_r($arr_bank_holidays);
		$nb_days_open = 0;	
		$date_start += 86400;	
		while ($date_start <= $date_stop) {
			// Si le jour suivant n'est ni un dimanche (0) ou un samedi (6), ni un jour férié, on incrémente les jours ouvrés	
			if (!in_array(date('w', $date_start), array(0, 6)) 
			&& !in_array(date('j_n_'.date('Y', $date_start), $date_start), $arr_bank_holidays)) {
				$nb_days_open++;		
			}
			$date_start += 86400;			
		}		
		return $nb_days_open;
	}
	
	//Nombre de jour fermé entre 2 dates
	public function get_nb_holiday_day($date_start, $date_stop)
	{
		//recupere le nombre de jour ouverts
		$nb_open_days = $this->get_nb_open_days($date_start, $date_stop);
		//recupere le nombre de jour entre les 2
		$nb_days = $this->date_diff($date_start, $date_stop, (3600 * 24));
		
		return $nb_days - $nb_open_days;
	}

	//difference entre 2 dates
	public function date_diff($date1,$date2,$mode) 
	{
      $P1=explode(" ",date("d m Y H i s",$date1));
      $P2=explode(" ",date("d m Y H i s",$date2));
      return (round((mktime($P2[3],$P2[4],$P2[5],$P2[1],$P2[0],$P2[2]) -
                mktime($P1[3],$P1[4],$P1[5],$P1[1],$P1[0],$P1[2]))/$mode));
	}

	/**
	 * Stock les dates pour la commande
	 *
	 */
	public function StoreDate()
	{

		//Si tous les produits de la commande sont reserve
		$AllProductReserved = true;
		foreach($this->_order->getItemsCollection() as $item)
		{
			//Si pas reservé
			if (($item->getreserved_qty() == 0) || ($item->getreserved_qty() == ''))
			{
				//Si ya gesiton de stock
    			$SM = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getproduct_id());
    			if ($SM->getManageStock())
				{
					$AllProductReserved = false;
					break;						
				}
			}
		}
		if ($AllProductReserved)
		{
			if ($this->_order->getfullstock_date() == '')
				$this->_order->setfullstock_date(date('Y-m-d'));
		}
		else 
		{
			$this->_order->setfullstock_date('');
		}
		
		//Stock les autres infos
		$t_info = $this->getSummary();
		$this->_order->setestimated_shipping_date($t_info['ship_date']);
		$this->_order->save();

	}
	
	/**
	 * Retourne les explications textuelles sur le planning de la commande
	 * c'est la mise en forme des infos retournées par la méthode getSummary
	 *
	 * @return unknown
	 */
	public function GetTextualExplanations()
	{
		//affiche l'explication sur l'etat de la commande
		$retour = '';
		if ($this->_order->getstocks_updated() == 1)
		{
			$t_summary = $this->getSummary();
			$retour .= $t_summary['msg'];
			$retour .= "<br>Pourcentage d'avancement des approvisionnements : ".$t_summary['supply_percent'].' %';
			if ($t_summary['preparation_percent'] != null)
			{
				$retour .= "<br>Pourcentage d'avancement de la pr&eacute;paration : ".$t_summary['preparation_percent'].' %';
				$retour .= " (".$t_summary['assembly_duration']."j)";
			}
			if ($t_summary['preparation_date'] != null)
				$retour .= "<br>Date pr&eacute;visionnelle d'assemblage et param&eacute;trage : ".$t_summary['preparation_date'];
			if ($t_summary['ship_date'] != null)
				$retour .= "<br>Date pr&eacute;visionnelle d'exp&eacute;dition : ".$t_summary['ship_date'];
			if ($t_summary['delivery_date']  != null)
				$retour .= "<br>Vous devriez recevoir votre commande le : ".$t_summary['delivery_date'];
		}
		else 
		{
			$retour = 'Votre commande est prise en compte. Les informations d\'avancement seront disponibles dans quelques minutes.';
		}
		return $retour;
	}

}