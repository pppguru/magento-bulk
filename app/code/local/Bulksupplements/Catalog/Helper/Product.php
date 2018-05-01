<?php
/**
* @category   Mage
* @package    Mage_Catalog
* @author     Mohin
*/
class Bulksupplements_Catalog_Helper_Product extends Mage_Catalog_Helper_Product
{
 
    /**
     * Determines the product weight, type for 25kg display feature described in mantis#363
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  array
     */
    public function getWeightAndType($product)
    {
        $productName = $product->getName();
		$targetProducts = array('10kg', '20kg', '25kg', '50lbs');
		$foundProduct = '';
		$weightAndType = array('weight'=>1, 'type'=>'', 'qtyType'=>'each');
		$pWeight = 1;
		$pUnitName = '';
		foreach($targetProducts as $prod){
			if(strpos($productName, $prod) !== false){
				//$foundProduct = $prod;
				$weightAndType['weight'] = substr($prod, 0, 2);
				$weightAndType['type'] = substr($prod, 2);
				$weightAndType['qtyType'] = '';
				break;
			}
		}
		return $weightAndType;
    }
	
	/**
     * Reformats the tier prices according to the 25kg display feature described in mantis#363
     */
	public function retouchTierPricing($weightAndType, $tierPrices)
	{
		$newTierPrices = array();
		foreach ($tierPrices as $index => $price)
		{
			$price['price_qty_str'] = ($price['price_qty']*$weightAndType['weight']).$weightAndType['type'];
			$price['formated_price'] = $this->getReformattedPrice($weightAndType, $price['formated_price']);
			$price['formated_price_incl_tax'] = $this->getReformattedPrice($weightAndType, $price['formated_price_incl_tax']);
			$price['formated_price_incl_weee'] = $this->getReformattedPrice($weightAndType, $price['formated_price_incl_weee']);
			$price['formated_price_incl_weee_only'] = $this->getReformattedPrice($weightAndType, $price['formated_price_incl_weee_only']);
			$newTierPrices[$index] = $price;
		}
		return $newTierPrices;
	}
	
	private function getReformattedPrice($weightAndType, $formattedPrice)
	{		
		$part1 = substr($formattedPrice, 0, strpos($formattedPrice, '$'));
		$part2 = preg_replace('/,/', '', substr($formattedPrice, (strpos($formattedPrice, '$'))+1, (strpos($formattedPrice, '</span>'))-1));
		$part2 = Mage::helper('core')->currency($part2/$weightAndType['weight'], true, false).' / '.substr($weightAndType['type'], 0, 2);
		$part3 = '</span>';
		return $part1.$part2.$part3;
	}
 
}
?>
