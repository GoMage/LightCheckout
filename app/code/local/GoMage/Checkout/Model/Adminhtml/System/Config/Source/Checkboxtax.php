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
	
class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Checkboxtax{

	const YES_CHECKED = 1;
	const YES_UNCHECKED = 2;
	const NO = 3;
	
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::NO, 'label'=>Mage::helper('gomage_checkout')->__('No')),
            array('value' => self::YES_CHECKED, 'label'=>Mage::helper('gomage_checkout')->__('Yes, checked')),
            array('value' => self::YES_UNCHECKED, 'label'=>Mage::helper('gomage_checkout')->__('Yes, unchecked')),            
        );
    }

}