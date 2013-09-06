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

class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Shipping_Allowedmethods
    extends Mage_Adminhtml_Model_System_Config_Source_Shipping_Allmethods
{
    public function toOptionArray($isActiveOnlyFlag=false)
    {
        return parent::toOptionArray(true);
    }

}
