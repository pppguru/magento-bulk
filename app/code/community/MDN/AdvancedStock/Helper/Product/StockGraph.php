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
class MDN_AdvancedStock_Helper_Product_StockGraph extends Mage_Core_Helper_Abstract
{
	private $_width = 800;
	private $_height = 500;
	
	private $_legendSize = 70;
	
	/**
	 * Genere l'image
	 *
	 * @param unknown_type $productId
	 * @param unknown_type $from
	 * @param unknown_type $to
	 * @param unknown_type $groupBy
	 */
	public function getGraphImage($productId, $from, $to, $groupBy, $displayStock, $displayOutgoing, $displayIngoing)
	{
		//base et fond
		$image = imagecreate($this->_width, $this->_height);
		$fond = imagecolorallocate($image,0xEF,0xF2,0xFB);
		$noir = imagecolorallocate($image,0,0,0);
		$red = imagecolorallocate($image,255,0,0);
		$green = imagecolorallocate($image,0,255,0);
		$blue = imagecolorallocate($image,0,0,255);
		imagefill($image,0,0,$fond);
		$font = 2;
		$fontSize = imagefontwidth($font);

		//dessine les axes
		imageline($image, $this->_legendSize, 5, $this->_legendSize, $this->_height - $this->_legendSize, $noir);
		imageline($image, $this->_legendSize, $this->_height - $this->_legendSize, $this->_width - $this->_legendSize - 5, $this->_height - $this->_legendSize, $noir);
		
		//recupere les données
		$periods = $this->getPeriods($from, $to, $groupBy);
		$maxY = 0;
		for ($i=0;$i<count($periods);$i++)
		{
			if ($displayStock)
			{
				$periods[$i]['stock'] = $this->getStockForDate($periods[$i]['reference_date'], $productId);
				if ($periods[$i]['stock'] > $maxY)
					$maxY = $periods[$i]['stock'];				
			}
			if ($displayOutgoing)
			{
				$periods[$i]['outgoing'] = $this->getOutgoingMovement($periods[$i]['period_start'], $periods[$i]['period_stop'], $productId);
				if ($periods[$i]['outgoing'] > $maxY)
					$maxY = $periods[$i]['outgoing'];				
			}
			if ($displayIngoing)
			{
				$periods[$i]['ingoing'] = $this->getIngoingMovement($periods[$i]['period_start'], $periods[$i]['period_stop'], $productId);
				if ($periods[$i]['ingoing'] > $maxY)
					$maxY = $periods[$i]['ingoing'];				
			}
		}

		//dessine les ordonnées
		$ordonneeSize = $this->_height - $this->_legendSize - 5;
		$ordonneeCount = 10;
		$ordonneeStep = $ordonneeSize / $ordonneeCount;
		$unitSize = 0;
		if ($maxY > 0)
		{
			$unitSize = $ordonneeSize / $maxY;
			for($i=0;$i<=$ordonneeCount;$i++)
			{
				$x = 10;
				$y = $this->_height - $this->_legendSize - ($i * $ordonneeStep);
				$value = number_format($i * ($maxY / $ordonneeCount), 1);
				imageline($image, $this->_legendSize - 5, $y, $this->_legendSize, $y, $noir);
				imagestring($image, $font, $x, $y-6, $value, $noir);
			}
		}
				
		//affiche les données
		$abscisseSize = $this->_width - $this->_legendSize - 5;
		$abscisseCount = count($periods);
		$previousStockPoint = null;
		$previousOutgoingPoint = null;
		$previousIngoingPoint = null;
		if ($abscisseCount > 0)
		{
			$abscisseStep = $abscisseSize / ($abscisseCount + 1);
			$i = 0;
			foreach($periods as $item)
			{
				//affiche l'abscisse et sa légende
				$x = $this->_legendSize + $i * $abscisseStep + $abscisseStep / 2;
				imageline($image, $x, $this->_height - $this->_legendSize, $x, $this->_height - $this->_legendSize + 5, $noir);
				$stringWidth = $fontSize * strlen($item['caption']);
				if ($stringWidth < $abscisseStep)				
					imagestring($image, $font, $x - $stringWidth / 2, $this->_height - $this->_legendSize + 5, $item['caption'], $noir);
				else 
					imagestringup($image, $font, $x-7, $this->_height - 5, $item['caption'], $noir);
				//affiche le stock
				if ($displayStock)
				{
					$stockPoint = array();
					$stockPoint['x'] = $x;
					$stockPoint['y'] = $this->_height - $this->_legendSize - ($item['stock'] * $unitSize);
					if ($previousStockPoint != null)
						imageline($image, $previousStockPoint['x'], $previousStockPoint['y'], $stockPoint['x'], $stockPoint['y'], $blue);
					if ($item['stock'] > 0)
						imagestring($image, $font, $x, $stockPoint['y']-10, $item['stock'], $blue);
					$previousStockPoint = $stockPoint;
				}
								
				//affiche les mouvement sortant
				if ($displayOutgoing)
				{
					$outgoingPoint = array();
					$outgoingPoint['x'] = $x;
					$outgoingPoint['y'] = $this->_height - $this->_legendSize - ($item['outgoing'] * $unitSize);
					if ($previousOutgoingPoint != null)
						imageline($image, $previousOutgoingPoint['x'], $previousOutgoingPoint['y'], $outgoingPoint['x'], $outgoingPoint['y'], $red);
					if ($item['outgoing'] > 0)
						imagestring($image, $font, $x, $outgoingPoint['y']-10, $item['outgoing'], $red);
					$previousOutgoingPoint = $outgoingPoint;
				}
								
				//affiche les mouvement entrant
				if ($displayIngoing)
				{
					$ingoingPoint = array();
					$ingoingPoint['x'] = $x;
					$ingoingPoint['y'] = $this->_height - $this->_legendSize - ($item['ingoing'] * $unitSize);
					if ($previousIngoingPoint != null)
						imageline($image, $previousIngoingPoint['x'], $previousIngoingPoint['y'], $ingoingPoint['x'], $ingoingPoint['y'], $green);
					if ($item['ingoing'] > 0)
						imagestring($image, $font, $x, $ingoingPoint['y']-10, $item['ingoing'], $green);
					$previousIngoingPoint = $ingoingPoint;
				}
								
				$i++;
			}
		}
				
		//retourne l'image
		header("Content-Type: image/gif");
		imagegif($image);
		die('');	//die to flush output
	}
	
	public function getPeriods($from, $to, $groupBy)
	{
		$retour = array();
		$fromTimeStamp = strtotime($from);
		$toTimeStamp = strtotime($to);
		switch($groupBy)
		{
			case 'day':
				while ($fromTimeStamp < $toTimeStamp)
				{
					$item = array();
					$item['caption'] = date('d-m-Y', $fromTimeStamp);
					$item['reference_date'] = $fromTimeStamp;
					$item['period_start'] = $fromTimeStamp;
					$item['period_stop'] = $fromTimeStamp;
					$retour[] = $item;
					
					$fromTimeStamp += 3600 * 24;
				}
				break;
			case 'week' :
				while ($fromTimeStamp < $toTimeStamp)
				{
					$item = array();
					$item['caption'] = date('d-m-Y', $fromTimeStamp);
					$item['reference_date'] = $fromTimeStamp;
					$item['period_start'] = $fromTimeStamp;
					$item['period_stop'] = $fromTimeStamp + 3600 * 24 * 7;
					$retour[] = $item;
					
					$fromTimeStamp += 3600 * 24 * 7;
				}
				break;

			case 'month':
				while ($fromTimeStamp < $toTimeStamp)
				{
					$item = array();
					$item['caption'] = date('m-Y', $fromTimeStamp);
					$item['reference_date'] = strtotime(date('Y-m', $fromTimeStamp).'-30');
					$item['period_start'] = strtotime(date('Y-m', $fromTimeStamp).'-1');
					$item['period_stop'] = strtotime(date('Y-m', $fromTimeStamp).'-31');
					$retour[] = $item;
					
					$fromTimeStamp += 3600 * 24 * 31;
				}
				break;
			case 'quarter':
				
				break;
			case 'year':
				while ($fromTimeStamp < $toTimeStamp)
				{
					$item = array();
					$item['caption'] = date('m-Y', $fromTimeStamp);
					$item['reference_date'] = strtotime(date('Y', $fromTimeStamp).'-12-31');
					$item['period_start'] = strtotime(date('Y', $fromTimeStamp).'-1-1');
					$item['period_stop'] = strtotime(date('Y', $fromTimeStamp).'-12-31');
					$retour[] = $item;
					
					$fromTimeStamp += 3600 * 24 * 31 * 12;
				}
				break;
		}
		return $retour;
	}
	
	/**
	 * Return stock level for one date
	 *
	 * @param unknown_type $date
	 */
	public function getStockForDate($date, $productId)
	{
		$value = 0;
		$stocks = mage::helper('AdvancedStock/Product_Base')->getStocks($productId);
		foreach ($stocks as $stock)
			$value += $stock->getQtyFromStockMovement(date('Y-m-d', $date));
		
		return $value;
	}
	
	//todo: mettre a jour suite modification table stock_movement
	public function getOutgoingMovement($from, $to, $productId)
	{
		$tablePrefix = Mage::getConfig()->getTablePrefix();
		$sql = "select sum(sm_qty * sm_coef) from ".$tablePrefix."stock_movement where sm_coef < 0 and sm_product_id=".$productId." and sm_date >= '".date('Y-m-d', $from)."' and sm_date <= '".date('Y-m-d', $to)."'";
		$retour = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
		return abs((int)$retour);
	}
	
	//todo: mettre a jour suite modification table stock_movement
	public function getIngoingMovement($from, $to, $productId)
	{
		$tablePrefix = Mage::getConfig()->getTablePrefix();
		$sql = "select sum(sm_qty * sm_coef) from ".$tablePrefix."stock_movement where sm_coef > 0 and sm_product_id=".$productId." and sm_date >= '".date('Y-m-d', $from)."' and sm_date <= '".date('Y-m-d', $to)."'";
		$retour = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
		return abs((int)$retour);
	}
}