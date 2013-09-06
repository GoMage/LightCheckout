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
	
class GoMage_PayOne_Block_Head extends Mage_Core_Block_Template{
	
	protected function _prepareLayout()
    { 
        parent::_prepareLayout(); 
        
        if(Mage::helper('gomage_payone')->isGoMage_PayOneEnabled() && $this->getLayout()->getBlock('head'))
        {                     
            $this->getLayout()->getBlock('head')->addItem('js', 'gomage/processing_payone.js');                               
        }
    } 
	
}