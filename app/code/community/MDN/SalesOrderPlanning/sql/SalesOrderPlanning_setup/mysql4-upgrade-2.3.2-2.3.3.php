<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE  {$this->getTable('product_availability')} DROP INDEX  `pa_product_id` ,
ADD UNIQUE  `pa_product_id` (  `pa_product_id` ,  `pa_website_id` )

");

$installer->endSetup();


