<?php
 /**
 * GoMage.com
 *
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.0
 * @since        Class available since Release 1.0
 */
	
	class GoMage_Checkout_Block_Onepage extends GoMage_Checkout_Block_Onepage_Abstract{
		/*
		public function __construct(){
			
			parent::__construct();
			
			if(!$this->getCustomer()->getId()){
			
				
				if(!$this->getQuote()->getBillingAddress()->getCountryId()){
					
					$countryId = Mage::getSingleton('checkout/type_onepage')->getDefaultCountryId();
			        $this->getQuote()->getBillingAddress()->setCountryId($countryId);
			        $this->getQuote()->collectTotals();
				}
				
				if(!$this->getQuote()->getShippingAddress()->getCountryId()){
					
					$countryId = Mage::getSingleton('checkout/type_onepage')->getDefaultCountryId();
			        $this->getQuote()->getShippingAddress()->setCountryId($countryId)->setCollectShippingRates(true);
					$this->getQuote()->collectTotals();
				}
				
			}
		}*/
		public function getContent(){
			return nl2br($this->helper->getConfigData('general/content'));
		}
		
		protected function _prepareLayout(){
			
			$this->_layout->getBlock('head')->setTitle(':)');
			
		}
		
	}