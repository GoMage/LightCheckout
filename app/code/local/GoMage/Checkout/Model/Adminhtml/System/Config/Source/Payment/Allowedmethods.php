<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.2
 * @since        Class available since Release 1.0
 */

class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Payment_Allowedmethods
    
{
    protected function _getPaymentMethods(){
    	
    	return Mage::helper('gomage_checkout')->getActivePaymentMethods();
    	
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
