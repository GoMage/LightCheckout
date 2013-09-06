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
 * @since        Class available since Release 1.0
 */ 
	
	class GoMage_Checkout_Block_Onepage extends GoMage_Checkout_Block_Onepage_Abstract{
		
		public function getContent(){
			return nl2br($this->helper->getConfigData('general/content'));
		}
		
	}