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
	        'label'             => 'Delivery Date',
	        'input'             => 'text',
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

	$installer->addAttribute('order', 'gomage_deliverydate', $attribute_data);
	$installer->addAttribute('order', 'gomage_deliverydate_formated', $attribute_data);

}else{
	
	try{
	
		$installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order_grid')}` ADD `gomage_deliverydate` DATETIME COMMENT 'Delivery Date';");
		
	}catch(Exception $e){
		if(strpos($e, 'Column already exists') === false){
			throw $e;
		}
	}
	
}
try{

	$installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote')}` ADD `gomage_deliverydate` DATETIME COMMENT 'Delivery Date';");
	$installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote')}` ADD `gomage_deliverydate_formated` VARCHAR(128) COMMENT 'Delivery Date Formated';");
	
}catch(Exception $e){
	if(strpos($e, 'Column already exists') === false){
		throw $e;
	}
}

try{
	if(!Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 4, 1)){
		$installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `gomage_deliverydate` DATETIME COMMENT 'Delivery Date';");
		$installer->run("ALTER TABLE `{$installer->getTable('sales_order')}` ADD `gomage_deliverydate_formated` VARCHAR(128) COMMENT 'Delivery Date Formated';");
	}else{
		$installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `gomage_deliverydate` DATETIME COMMENT 'Delivery Date';");
		$installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `gomage_deliverydate_formated` VARCHAR(128) COMMENT 'Delivery Date Formated';");
	}

}catch(Exception $e){
	if(strpos($e, 'Column already exists') === false){
		throw $e;
	}
}
$installer->endSetup();