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
 * @since        Class available since Release 2.2
 */ 
	
class GoMage_SagePay_Block_Head extends Mage_Core_Block_Template{
	
	protected function _prepareLayout()
    { 
        parent::_prepareLayout(); 
        
        if(Mage::helper('gomage_sagepay')->isGoMage_SagePayEnabled() && $this->getLayout()->getBlock('head'))
        {
            $this->getLayout()->getBlock('head')->addItem('skin_css', 'sagepaysuite/css/growler/growler.css'); 
            $this->getLayout()->getBlock('head')->addItem('skin_css', 'sagepaysuite/css/sagePaySuite_Checkout.css');
            $this->getLayout()->getBlock('head')->addItem('skin_js', 'sagepaysuite/js/growler/growler.js');
            $this->getLayout()->getBlock('head')->addItem('js', 'sagepaysuite/direct.js');
            $this->getLayout()->getBlock('head')->addItem('js', 'sagepaysuite/common.js');
            $this->getLayout()->getBlock('head')->addItem('skin_js', 'sagepaysuite/sagePaySuite.js');
            $this->getLayout()->getBlock('head')->addItem('skin_js', 'sagepaysuite/js/sagePaySuite_Checkout.js');
            $this->getLayout()->getBlock('head')->addItem('js', 'sagepaysuite/livepipe/livepipe.js');
            $this->getLayout()->getBlock('head')->addItem('js', 'sagepaysuite/livepipe/window.js');
        }
    } 
	
}