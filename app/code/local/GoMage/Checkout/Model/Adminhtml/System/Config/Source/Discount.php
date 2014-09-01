<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2014 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.7
 * @since        Class available since Release 1.0
 */
	
class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Discount{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('gomage_checkout')->__('No')),
            array('value' => 1, 'label'=>Mage::helper('gomage_checkout')->__('Yes, before Order Review')),
            array('value' => 2, 'label'=>Mage::helper('gomage_checkout')->__('Yes, after Order Review')),
        );
    }

}