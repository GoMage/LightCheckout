<?php
	
	class GoMage_Checkout_JsController  extends Mage_Checkout_Controller_Action{
		
		public function indexAction(){
			
			
			$this->getResponse()->setHeader('Content-Type', 'text/javascript');
			$this->loadLayout();
			$this->renderLayout();
			
		}
		
	}