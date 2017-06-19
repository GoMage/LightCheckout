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
 * @since        Class available since Release 5.9
 */

$installer = $this;
$installer->startSetup();

try{
    if(!Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 4, 1)){
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `gomage_tax_gift_wrap_canceled` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Canceled Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `gomage_tax_gift_wrap_invoiced` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Invoiced Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `gomage_tax_gift_wrap_refunded` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Refunded Wrap Tax Amount'");

        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `base_gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `base_gomage_tax_gift_wrap_canceled` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Canceled Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `base_gomage_tax_gift_wrap_invoiced` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Invoiced Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `base_gomage_tax_gift_wrap_refunded` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Refunded Wrap Tax Amount'");

    }else{
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `gomage_tax_gift_wrap_canceled` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Canceled Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `gomage_tax_gift_wrap_invoiced` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Invoiced Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `gomage_tax_gift_wrap_refunded` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Refunded Wrap Tax Amount'");

        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `base_gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `base_gomage_tax_gift_wrap_canceled` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Canceled Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `base_gomage_tax_gift_wrap_invoiced` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Invoiced Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `base_gomage_tax_gift_wrap_refunded` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Refunded Wrap Tax Amount'");
    }

}catch(Exception $e){
    if(strpos($e, 'Column already exists') === false){
        throw $e;
    }
}
try{
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_item')}` ADD `gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Tax Amount'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_item')}` ADD `base_gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Tax Amount'");


    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order_item')}` ADD `gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Tax Amount'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order_item')}` ADD `base_gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Tax Amount'");

    if(Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 4, 1)){
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_creditmemo_item')}` ADD `gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Tax Amount'");
        $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_creditmemo_item')}` ADD `base_gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Tax Amount'");
    }

    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_address')}` ADD `gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Tax Amount'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_address')}` ADD `base_gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Tax Amount'");

    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_address_item')}` ADD `gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Tax Amount'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_address_item')}` ADD `base_gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Tax Amount'");

}catch(Exception $e){
    if(strpos($e, 'Column already exists') === false){
        throw $e;
    }
}

if(Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 4, 1)){
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice')}` ADD `gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Tax Amount'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice')}` ADD `base_gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Tax Amount'");

    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice_item')}` ADD `gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Wrap Tax Amount'");
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_invoice_item')}` ADD `base_gomage_tax_gift_wrap_amount` decimal(12,4) NOT NULL default '0.0000' COMMENT 'Base Wrap Tax Amount'");
}


$installer->endSetup();