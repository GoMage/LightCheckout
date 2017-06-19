<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2016 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use
 * @version      Release: 5.9.2
 * @since        Class available since Release 3.1
 */

class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Payment_Buttons
{
	const PAYPAL = 'paypal';
	const GOOGLE_CHECKOUT = 'google';

	public function toOptionArray()
    {
        $buttons = array(array('value'=>'', 'label'=>''));
        
        $buttons[] = array('value'=>self::PAYPAL, 'label'=>Mage::helper('gomage_checkout')->__('PayPal'));
        $buttons[] = array('value'=>self::GOOGLE_CHECKOUT, 'label'=>Mage::helper('gomage_checkout')->__('Google Checkout'));

        return $buttons;
    }
}
