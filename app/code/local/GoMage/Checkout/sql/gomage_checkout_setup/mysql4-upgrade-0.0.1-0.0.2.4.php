<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.0
 * @since        Class available since Release 2.4
 */

$installer = $this;
$installer->startSetup();
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
$installer->endSetup();
