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
 * @since        Class available since Release 3.1
 */ 
	
class GoMage_PayOne_Block_Head extends Mage_Core_Block_Template{
	
	protected function _prepareLayout()
    { 
        parent::_prepareLayout(); 
        
        if(Mage::helper('gomage_payone')->isGoMage_PayOneEnabled() && $this->getLayout()->getBlock('head'))
        {   
        	$head = $this->getLayout()->getBlock('head');                      
        	$head->addItem('js', 'payone/core/client_api.js');
            $head->addItem('js', 'payone/core/creditcard.js');
            $head->addItem('js', 'payone/core/onlinebanktransfer.js');
            $head->addItem('js', 'payone/core/wallet.js'); 
            if (Mage::getStoreConfig('payone_protect/general/enabled')){
            	$head->addItem('js', 'payone/core/addresscheck.js');
            }
            $head->addItem('js', 'gomage/processing_payone.js');
            
            $child = $this->getLayout()->createBlock('core/template', 'payone_core_clientapi')->setTemplate('payone/core/client_api.phtml');                     
            $head->insert($child); 
            $child = $this->getLayout()->createBlock('core/template', 'payone_core_protect')->setTemplate('payone/core/checkout/protect.phtml');                     
            $head->insert($child);
            $child = $this->getLayout()->createBlock('core/template', 'payone_core_payment')->setTemplate('gomage/payone/init.phtml');                     
            $head->insert($child);        	
        }
    } 
	
}