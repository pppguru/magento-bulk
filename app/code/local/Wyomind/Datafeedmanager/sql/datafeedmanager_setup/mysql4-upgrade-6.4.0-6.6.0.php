<?php

$installer = $this;

$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('datafeedmanager_configurations')} 
 ADD   `datafeedmanager_attribute_sets` varchar(150) default '*';
");


$installer->run('
    INSERT INTO `' . $this->getTable('datafeedmanager_configurations') . '` (`feed_id`, `feed_name`, `feed_type`, `feed_path`, `feed_status`, `feed_updated_at`, `store_id`, `feed_include_header`, `feed_header`, `feed_product`, `feed_footer`, `feed_separator`, `feed_protector`, `feed_required_fields`, `feed_enclose_data`, `datafeedmanager_categories`, `datafeedmanager_type_ids`, `datafeedmanager_visibility`, `datafeedmanager_attributes`) VALUES
    (NULL,\'Yahoo\',3,\'/feeds/\',1,        NULL,1,0,   \'{"header":["path", "id", "name", "code", "price", "sale-price", "headline", "caption", "abstract", "ship-weight", "orderable", "taxable", "gift-certificate", "availability", "page-title", "description", "product-url", "condition"]}\',\'{"product":["{categories,[1],[1],[1]}", "{url_key}", "{name}", "{sku}", "{price,[USD],[0]}", "{special_price,[USD],[0]}", "{short_description}", "{short_description}", "{description}", "{weight,[float],[2],[.]}", "yes", "no", "", "{is_in_stock?[in stock]:[out fo stock]}", "{name}", "{short_description} {description}", "{url parent}", "{condition}"]}\',NULL, \';\', \'\', NULL, 0, NULL, \'simple,configurable,bundle,grouped,virtual,downloadable\', \'1,2,3,4\', \'[]\'),
    (NULL,\'Trovaprezzi\',3,\'/feeds/\',1,NULL,1,0,\'{"header":["Product","Brand","Description","Price","Product Code","Link","Availability","Categories","Image","Shipping Cost","MPN","EAN"]}\',\'{"product":["{name}","{brand}","{description,[inline]}","{price,[EUR],[IT]}","{sku}","{url parent}","{is_in_stock}","{categories,[last]}","{image}","","{mpn}","{ean}<endrecord>"]}\',NULL, \';\', \'\', NULL, 0, NULL, \'simple,configurable,bundle,grouped,virtual,downloadable\', \'1,2,3,4\', \'[]\');');

$installer->run('
INSERT INTO `' . $this->getTable('datafeedmanager_options') . '` (`option_id`,`option_name`,`option_script`,`option_param`) values 
(NULL,\'default_value\',\'if(trim($value)==\'\') return $param[1];\',0),
(NULL,\'default_description\',\'if(trim($value)==\'\')  return $product->getName();\',0);


');

$installer->endSetup();