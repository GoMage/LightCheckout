<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2016 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use
 * @version      Release: 5.9.2
 * @since        Class available since Release 4.0
 */

$installer = $this;
$installer->startSetup();

if(Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 4, 1)){
	$installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice')}` ADD `gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
	$installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice')}` ADD `base_gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Amount'");
	
	$installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice_item')}` ADD `gomage_gift_wrap` tinyint(1) unsigned NOT NULL default '0' COMMENT 'Is Gift Wrap'");
	$installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice_item')}` ADD `gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Amount'");
	$installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice_item')}` ADD `base_gomage_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Amount'");
}

$installer->endSetup();