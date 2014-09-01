<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2014 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.7
 * @since        Class available since Release 5.0
 */

$installer = $this;
$installer->startSetup();

/**
 * mysql4-install-0.0.1
 */
if(!Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 4, 1)){
    $attribute_data = array(
        'group'             => 'General',
        'type'              => 'static',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Customer Comment',
        'input'             => 'textarea',
        'class'             => '',
        'source'            => '',
        'global'            => true,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
    );
    $installer->addAttribute('order', 'gomage_checkout_customer_comment', $attribute_data);
}

//$installer->addAttribute('quote', 'gomage_checkout_customer_comment', $attribute_data);

try{
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote')}` ADD `gomage_checkout_customer_comment` TEXT COMMENT 'Customer Comment'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_address')}` ADD `is_valid_vat` SMALLINT(1) DEFAULT NULL COMMENT 'Is valid vat number'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_address')}` ADD `buy_without_vat` SMALLINT(1) DEFAULT NULL COMMENT 'Without vat'");
}catch(Exception $e){
    if(strpos($e, 'Column already exists') === false){
        throw $e;
    }
}

try{
    if(!Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 4, 1)){
        $installer->run("
		ALTER TABLE `{$installer->getTable('sales_order')}` ADD `gomage_checkout_customer_comment` TEXT COMMENT 'Customer Comment';
		");
    }else{
        $installer->run("
		ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `gomage_checkout_customer_comment` TEXT COMMENT 'Customer Comment';

		");

    }
}catch(Exception $e){
    if(strpos($e, 'Column already exists') === false){
        throw $e;
    }
}

/**
 * mysql4-upgrade-0.0.1-0.0.2.4
 */
try{
    if(!Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 4, 1)){
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `gomage_gift_wrap_canceled` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Canceled Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `gomage_gift_wrap_invoiced` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Invoiced Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `gomage_gift_wrap_refunded` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Refunded Wrap Amount'");

        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `base_gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `base_gomage_gift_wrap_canceled` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Canceled Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `base_gomage_gift_wrap_invoiced` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Invoiced Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `base_gomage_gift_wrap_refunded` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Refunded Wrap Amount'");

    }else{
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `gomage_gift_wrap_canceled` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Canceled Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `gomage_gift_wrap_invoiced` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Invoiced Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `gomage_gift_wrap_refunded` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Refunded Wrap Amount'");

        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `base_gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `base_gomage_gift_wrap_canceled` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Canceled Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `base_gomage_gift_wrap_invoiced` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Invoiced Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `base_gomage_gift_wrap_refunded` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Refunded Wrap Amount'");
    }

}catch(Exception $e){
    if(strpos($e, 'Column already exists') === false){
        throw $e;
    }
}
try{
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_item')}` ADD `gomage_gift_wrap` tinyint(1) unsigned NOT NULL default '0' COMMENT 'Is Gift Wrap'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_item')}` ADD `gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_item')}` ADD `base_gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Amount'");

    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order_item')}` ADD `gomage_gift_wrap` tinyint(1) unsigned NOT NULL default '0' COMMENT 'Is Gift Wrap'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order_item')}` ADD `gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order_item')}` ADD `base_gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Amount'");

    if(Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 4, 1)){
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_creditmemo_item')}` ADD `gomage_gift_wrap` tinyint(1) unsigned NOT NULL default '0' COMMENT 'Is Gift Wrap'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_creditmemo_item')}` ADD `gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_creditmemo_item')}` ADD `base_gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Amount'");
    }

    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_address')}` ADD `gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_address')}` ADD `base_gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Amount'");

    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_address_item')}` ADD `gomage_gift_wrap` tinyint(1) unsigned NOT NULL default '0' COMMENT 'Is Gift Wrap'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_address_item')}` ADD `gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_address_item')}` ADD `base_gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Amount'");

}catch(Exception $e){
    if(strpos($e, 'Column already exists') === false){
        throw $e;
    }
}

/**
 * mysql4-upgrade-0.0.3.2-0.0.4.0
 */

if(Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 4, 1)){
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice')}` ADD `gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice')}` ADD `base_gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Amount'");

    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice_item')}` ADD `gomage_gift_wrap` tinyint(1) unsigned NOT NULL default '0' COMMENT 'Is Gift Wrap'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice_item')}` ADD `gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice_item')}` ADD `base_gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Amount'");
}


$installer->endSetup();