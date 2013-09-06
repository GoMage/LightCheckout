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

class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Payment_Allowedmethods
    
{
    protected function _getPaymentMethods(){
    
    $tmpStoreId = null;
          
    $tmpWebSite = Mage::app()->getRequest()->getParam('website');
    $tmpStore   = Mage::app()->getRequest()->getParam('store');
    
    if (!is_null($tmpWebSite)) 
      {
      $tmpStoreId = Mage::getModel('core/website')->load($tmpWebSite, 'code')->getDefaultGroup()->getDefaultStoreId();
      } 
    elseif (!is_null($tmpStore)) 
      {
      $tmpStoreId = Mage::getModel('core/store')->load($tmpStore, 'code')->getId();
      }
    	
    	return Mage::helper('gomage_checkout')->getActivePaymentMethods($tmpStoreId);
    	
    }
	public function toOptionArray()
    {
        $methods = array(array('value'=>'', 'label'=>''));
        foreach ($this->_getPaymentMethods() as $paymentCode=>$paymentModel) {
        	
        	if($paymentModel->getData('group') == 'mbookers'){
        	
        	$paymentTitle = 'Moneybookers - '.Mage::getStoreConfig('moneybookers/'.$paymentCode.'/title');
        	
        	}else{
        	
            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
            
            }
            
            $methods[$paymentCode] = array(
                'label'   => $paymentTitle,
                'value' => $paymentCode,
            );
        }

        return $methods;
    }
}
