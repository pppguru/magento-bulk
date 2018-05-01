<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Followupemail
 * @version    3.5.8
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

$installer = $this;
$installer->startSetup();
try {
    $installer->run("
        CREATE TABLE IF NOT EXISTS {$this->getTable('followupemail/unsubscribe')} (
          `id` int(11) unsigned NOT NULL auto_increment,
          `customer_id` int(11) NOT NULL,
          `customer_email` varchar(255) NOT NULL,
          `store_id` varchar(255) NOT NULL,
          `rule_id` varchar(128) NOT NULL default '',
          `is_unsubscribed` tinyint(1) NOT NULL default '0',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        ALTER TABLE {$this->getTable('followupemail/rule')} MODIFY `sku` TEXT;
        ALTER TABLE {$this->getTable('followupemail/queue')} ADD `template_styles` TEXT NULL AFTER `content`;
    ");
} catch (Exception $e) {
    Mage::logException($e);
}
$installer->endSetup();

/**
 * Adding new template 'Review request'
 */

$templateResource = Mage::getResourceModel('newsletter/template');
$modelTemplate = Mage::getModel('newsletter/template');
$templateResource->loadByCode($modelTemplate, 'AW Review Request Per Product');
if ($modelTemplate->getData() == array()) {
    $template = array(
        'template_code'         => 'AW Review Request Per Product',
        'template_subject'      => 'Hello {{var customer_name}}, please come back to us and write a review',
        'template_sender_name'  => 'AW',
        'template_sender_email' => 'aw@aw.com',
        'template_text'         => '<h1>Dear {{var customer_name}}!</h1>
<p>On {{var order.updated_at|formatDateTime:F j, Y}} an order status has been changed to {{var order.status}}</p>
<p>The order contains the following items:</p>
<table border="1" cellspacing="1" cellpadding="5" width="100%">
    <tbody>
        <tr>
            <th>No</th> <th>Product</th> <th>Image</th> <th>Description</th> <th>Price</th> <th>Qty</th> <th>Row Total</th> <th>Leave a Review</th>
        </tr>
        {{foreach var="$order.getAllVisibleItems()" template="nsltr:AW Review Request Per Product Row"}}
        <tr>
            <th colspan="4" align="left">Rows total: {{var row_item_row_number}}</th> <th colspan="4">
                <table style="background-color: yellow;" cellspacing="0" cellpadding="10" width="100%">
                    <tbody>
                        <tr>
                            <td align="left">Discount:</td>
                            <td align="right">{{var order.discount_amount|formatPrice}}</td>
                        </tr>
                        <tr>
                            <td align="left">Total:</td>
                            <td align="right">{{var order.grand_total|formatPrice}}</td>
                        </tr>
                    </tbody>
                </table>
            </th>
        </tr>
    </tbody>
</table>
<p>Thanks in advance!</p>
<p><a href="{{store url=""}}">{{store url=""}}</a></p>
<hr/>'
    );

    $modelTemplate->setData($template)
        ->setTemplateType(Mage_Newsletter_Model_Template::TYPE_TEXT)
        ->setTemplateActual(1)
        ->save();
}

$templateResource = Mage::getResourceModel('newsletter/template');
$modelTemplate = Mage::getModel('newsletter/template');
$templateResource->loadByCode($modelTemplate, 'AW Review Request Per Product Row');
if ($modelTemplate->getData() == array()) {
    $template = array(
        'template_code'         => 'AW Review Request Per Product Row',
        'template_subject'      => 'AW Review Request Per Product Row',
        'template_sender_name'  => 'AW',
        'template_sender_email' => 'aw@aw.com',
        'template_text'         => '<tr>
    <td>{{var row_item_row_number}}</td>
    <td><a href="{{store url="catalog/product/view" id="$row_item.product.id"}}">{{var row_item.name}}</a></td>
    <td align="center"><img src="{{thumbnail size="75" source="row_item.product"}}" alt="" /></td>
    <td>{{var row_item.product.description}}&nbsp;</td>
    <td align="right">{{var row_item.price_incl_tax|formatPrice}}</td>
    <td align="right">{{var row_item.qty_ordered|formatDecimal}}</td>
    <td align="right">{{var row_item.row_total_incl_tax|formatPrice}}</td>
    <td align="right"><a href="{{store url="review/product/list" id="$row_item.product.id"}}">Leave a review</a></td>
</tr>'
    );

    $modelTemplate->setData($template)
        ->setTemplateType(Mage_Newsletter_Model_Template::TYPE_TEXT)
        ->setTemplateActual(1)
        ->save();
}
