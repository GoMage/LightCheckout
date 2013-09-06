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
 * @since        Class available since Release 1.0
 */

$installer = $this;
$installer->startSetup();

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
$installer->endSetup();