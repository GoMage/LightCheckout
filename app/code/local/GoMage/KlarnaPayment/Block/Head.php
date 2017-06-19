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
 * @since        Class available since Release 3.1
 */ 
	
class GoMage_KlarnaPayment_Block_Head extends Mage_Core_Block_Template{
	
	protected function _prepareLayout()
    { 
        parent::_prepareLayout(); 
        
        if(Mage::helper('gomage_klarnapayment')->isGoMage_KlarnaPaymentEnabled() && $this->getLayout()->getBlock('head'))
        {
            $this->getLayout()->getBlock('head')->addItem('skin_css', 'css/vaimo/klarna/global.css');
            $this->getLayout()->getBlock('head')->addItem('skin_css', 'css/vaimo/klarna/checkout.css');
            $this->getLayout()->getBlock('head')->addItem('js', 'vaimo/klarna/klarna.js');
            $this->setTemplate('vaimo/klarna/checkout/header.phtml');
        }
    } 
	
}