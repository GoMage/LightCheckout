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
if(!class_exists("GoMage_SagePay_Block_JavascriptVarsBase"))
{
if (Mage::helper ('gomage_sagepay')->isGoMage_SagePayEnabled()) {
	class GoMage_SagePay_Block_JavascriptVarsBase extends Ebizmarts_SagePaySuite_Block_JavascriptVars{		
		public function __construct() {
			if (Mage::helper('gomage_sagepay')->isGoMage_SagePayEnabled()) {
				$this->setTemplate('sagepaysuite/payment/SagePaySuiteVars.phtml');
			}
			parent::__construct();
		}	
	}
}else{
	class GoMage_SagePay_Block_JavascriptVarsBase extends Mage_Core_Block_Template{
		
	}
}
}
class GoMage_SagePay_Block_JavascriptVars extends GoMage_SagePay_Block_JavascriptVarsBase {
		
}