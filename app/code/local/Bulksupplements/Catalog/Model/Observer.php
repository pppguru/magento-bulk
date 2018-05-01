<?php
class Bulksupplements_Catalog_Model_Observer
{
    public function saveConfigurableData($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product instanceof Mage_Catalog_Model_Product) {
            if($product->getTypeId() == "configurable") {                    
                $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product);
                
                // * Save text data from configurable product
                foreach ($childProducts as $simpleProduct) {
                    $simpleProduct->setShortDescription($product->getShortDescription());
                    $simpleProduct->setDescription($product->getDescription());
                    $simpleProduct->setKeyFeatures($product->getKeyFeatures());
                    $simpleProduct->setDirections($product->getDirections());
                    $simpleProduct->setNutritionalInformation($product->getNutritionalInformation());
                    $simpleProduct->setIngredientsFurther($product->getIngredientsFurther());
                    $simpleProduct->setTierPrice($simpleProduct->getTierPrice());
                    
                    $simpleProduct->save();                    
                }
                
                // * Save image data from configurable product
                $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
                $connW = Mage::getSingleton('core/resource')->getConnection('core_write');
                
                $imgAttrIds = array(85,86,87);
                
                $attrMediaGalleryId = 88;
                
                $attrBaseImageId = 85;
                $attrSmallImageId = 86;
                $attrThumbId = 87;
                
                $parentGalImgs = array(); // Parent Gallery images 
                
                /*
                *    Check the existing images
                */                  
//                    $sql = "SELECT * FROM catalog_product_entity_media_gallery WHERE entity_id = ".$product->getId();
                $sql = "SELECT g.*, gv.store_id, gv.label, gv.position, gv.disabled FROM catalog_product_entity_media_gallery g
                    LEFT JOIN catalog_product_entity_media_gallery_value gv ON g.value_id=gv.value_id
                    WHERE g.entity_id = ".$product->getId()."
                    GROUP BY gv.value_id";
                $parentGalleries = $conn->fetchAll($sql);
                foreach ($parentGalleries as $parentGallery)
                    $parentGalImgs[] = $parentGallery['value'];
                
                foreach ($childProducts as $simpleProduct) {                        
                    $sql = "SELECT * FROM catalog_product_entity_media_gallery WHERE entity_id = ".$simpleProduct->getId();
                    $simpleGalleries = $conn->fetchAll($sql);
                    
                    $simpleGalImgs = array(); // Simple Gallery images 
                    foreach ($simpleGalleries as $simpleGallery)
                        $simpleGalImgs[] = $simpleGallery['value'];
                        
                    foreach ($parentGalleries as $parentGallery) {
                        
                        if (!in_array($parentGallery['value'], $simpleGalImgs)) {
                            // Do a insert query
                            $insertGallery = "(".$attrMediaGalleryId.", ".$simpleProduct->getId().", '".$parentGallery['value']."')";
                            $sql = "INSERT INTO catalog_product_entity_media_gallery (attribute_id, entity_id, value) VALUES ".$insertGallery;
                            $connW->query($sql);
                            
                            $sql = "SELECT * FROM catalog_product_entity_media_gallery WHERE entity_id = ".$simpleProduct->getId()." AND value='".$parentGallery['value']."'";
                            $curGalleries = $conn->fetchAll($sql);
                            if ($curGallery = $curGalleries[0]) {
                                $insertGalleryValue = "(".$curGallery['value_id'].", "
                                            .$parentGallery['store_id'].", '"
                                            .$parentGallery['label']."', "
                                            .$parentGallery['position'].", "
                                            .$parentGallery['disabled'].")";    
                                $sql = "INSERT INTO catalog_product_entity_media_gallery_value (value_id, store_id, label, position, disabled) VALUES ".$insertGalleryValue;
                                $connW->query($sql);
                            }                                
                        } else {
                            // Does a update query
                            foreach ($simpleGalleries as $simpleGallery) {
                                if ($parentGallery['value'] == $simpleGallery['value']) {
                                    $udpateGallery = "UPDATE `catalog_product_entity_media_gallery` SET value='".$parentGallery['value']."' WHERE value_id=".$simpleGallery['value_id'];
                                    $udpateGalleryValue = "UPDATE `catalog_product_entity_media_gallery_value` SET ".
                                            "store_id=".$parentGallery['store_id'].", ".
                                            "label='".$parentGallery['label']."', ".
                                            "position=".$parentGallery['position'].", ".
                                            "disabled=".$parentGallery['disabled']." ".
                                            "WHERE value_id=".$simpleGallery['value_id']." and store_id=".$parentGallery['store_id'];
                                    $connW->query($udpateGallery);
                                    $connW->query($udpateGalleryValue);
                                }                                
                            }
                        }
                        
                        // * Base Image, Small Image and Thumbnail Set
                        if ($product->getImage() != $simpleProduct->getImage()) {
                            $sql = "UPDATE `catalog_product_entity_varchar` SET value='".$product->getImage()."' WHERE entity_id=".$simpleProduct->getId()." AND attribute_id=".$attrBaseImageId;
                            $connW->query($sql);                          
                        }
                        if ($product->getSmallImage() != $simpleProduct->getSmallImage()) {
                            $sql = "UPDATE `catalog_product_entity_varchar` SET value='".$product->getSmallImage()."' WHERE entity_id=".$simpleProduct->getId()." AND attribute_id=".$attrSmallImageId;
                            $connW->query($sql);                          
                        }
                        if ($product->getThumbnail() != $simpleProduct->getThumbnail()) {
                            $sql = "UPDATE `catalog_product_entity_varchar` SET value='".$product->getThumbnail()."' WHERE entity_id=".$simpleProduct->getId()." AND attribute_id=".$attrThumbId;
                            $connW->query($sql);                          
                        }
                        
                        /*$insertImageSet[] = "(4, ".$attrBaseImageId.", 0, ".$simpleProduct->getId().", '".$product->getImage()."')";    
                        $insertImageSet[] = "(4, ".$attrSmallImageId.", 0, ".$simpleProduct->getId().", '".$product->getSmallImage()."')";    
                        $insertImageSet[] = "(4, ".$attrThumbId.", 0, ".$simpleProduct->getId().", '".$product->getThumbnail()."')";    */
                    }
                    
                    // * Remove diff images from simple product
                    $simpleDiffGalImgs = array_diff($simpleGalImgs, $parentGalImgs);
                    foreach ($simpleGalleries as $simpleGallery) {
                        if (in_array($simpleGallery['value'], $simpleDiffGalImgs)) {
                            $sql = "DELETE FROM catalog_product_entity_media_gallery WHERE value_id=".$simpleGallery['value_id'];
                            $connW->query($sql);
                            $sql = "DELETE FROM catalog_product_entity_media_gallery_value WHERE value_id=".$simpleGallery['value_id'];
                            $connW->query($sql);
                        }                                
                    }                         
                }

                
                /*if (count($skusToInsert)) {
                    $sql = "INSERT INTO catalog_product_entity_media_gallery (attribute_id, entity_id, value) VALUES ".implode(",",$skusToInsert).";";
                    $connW->query($sql);
                }
                
                if (count($insertData)) {
                    $sql = "INSERT INTO catalog_product_entity_varchar (entity_type_id, attribute_id, store_id, entity_id, value) VALUES ".implode(",",$insertData).";";
                    $connW->query($sql);
                }*/
                
            } // end
        }
        return $this;
    }
}
