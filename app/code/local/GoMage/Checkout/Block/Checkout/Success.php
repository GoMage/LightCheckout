<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Class available since Release 3.1
 */

class GoMage_Checkout_Block_Checkout_Success extends Mage_Checkout_Block_Onepage_Success
{
   
    public function canPrint()
    {
    	$h = Mage::helper('gomage_checkout');     	
    	if($h->getConfigData('general/order_summary') && $h->getConfigData('general/enabled')){
    		return true;
    	}else{
    		parent::canPrint();
    	}    	  
    }
    
	public function getViewOrder(){
		$h = Mage::helper('gomage_checkout');     	
    	if($h->getConfigData('general/order_summary') && $h->getConfigData('general/enabled')){
    		return true;
    	}else{
    		parent::getViewOrder();
    	}
    }
    
    public function getCanViewOrder(){
    	$h = Mage::helper('gomage_checkout');     	
    	if($h->getConfigData('general/order_summary') && $h->getConfigData('general/enabled')){
    		return true;
    	}else{
    		parent::getCanViewOrder();
    	}
    }
            
    public function getCanPrintOrder(){
    	$h = Mage::helper('gomage_checkout');     	
    	if($h->getConfigData('general/order_summary') && $h->getConfigData('general/enabled')){
    		return true;
    	}else{
    		parent::getCanPrintOrder();
    	}
    }

}
