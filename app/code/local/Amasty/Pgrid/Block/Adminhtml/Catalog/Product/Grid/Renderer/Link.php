<?php
class  Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Link
extends Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Abstract
{
/**
* Format variables pattern
*
* @var string
*/


/**
* Renders grid column
*
* @param Varien_Object $row
* @return mixed
*/

    public function render(Varien_Object $row)
    {
//       $product = Mage::helper('catalog/product')->getProduct($row["entity_id"], Mage::app()->getStore()->getId());
       $product = Mage::getModel('catalog/product')->load($row["entity_id"]);

       $url = Mage::getUrl($product->getUrlPath());
       $imageUrl = Mage::getDesign()->getSkinUrl('images/ampgrid/ico-amasty-product.png');
       return "<a href='$url' target='blank'><img src=".$imageUrl."></a>";
    }

}
