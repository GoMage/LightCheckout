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
 * @since        Class available since Release 1.0
 */

	class GoMage_Checkout_Model_Quote extends Mage_Sales_Model_Quote{
		
		public function setCustomer(Mage_Customer_Model_Customer $customer){
	        $this->_customer = $customer;
	        $this->setCustomerId($customer->getId());
	        
	        $quote_taxvat = false;
	        $quote_dob = false;
	        
	        if($this->getCustomerTaxvat()){
	        	$quote_taxvat = $this->getCustomerTaxvat();
	        }
	        if($this->getCustomerDob()){
	        	$quote_dob = $this->getCustomerDob();
	        }
	        
	        Mage::helper('core')->copyFieldset('customer_account', 'to_quote', $customer, $this);
	        
	        if($quote_taxvat){
	        	$this->setCustomerTaxvat($quote_taxvat);
	        }
	        if($quote_dob){
	        	$this->setCustomerDob($quote_dob);
	        }
	        
	        return $this;
	    }
		
	}