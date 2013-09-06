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

if (Mage::helper ('gomage_payone')->isGoMage_PayOneEnabled()) {
	class GoMage_PayOne_Block_JsincludeBase extends Mage_Payone_Block_Jsinclude{
	  public function __construct() {
			if (Mage::helper ('gomage_payone')->isGoMage_PayOneEnabled()) {
				$this->setTemplate('payone/jsinclude.phtml');
			}
			parent::__construct();
		}
	}
}else{
	class GoMage_PayOne_Block_JsincludeBase extends Mage_Core_Block_Template{
	}
}

class GoMage_PayOne_Block_Jsinclude extends GoMage_PayOne_Block_JsincludeBase {
		
}