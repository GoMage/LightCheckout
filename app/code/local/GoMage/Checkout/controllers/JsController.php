<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Class available since Release 2.0
 */ 
	
	class GoMage_Checkout_JsController  extends Mage_Checkout_Controller_Action{
		
		public function indexAction(){
			
			
			$this->getResponse()->setHeader('Content-Type', 'text/javascript');
			$this->loadLayout();
			$this->renderLayout();
			
		}
		
	}