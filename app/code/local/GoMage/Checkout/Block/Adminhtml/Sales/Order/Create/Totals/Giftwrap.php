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
 * @since        Class available since Release 2.4
 */
class GoMage_Checkout_Block_Adminhtml_Sales_Order_Create_Totals_Giftwrap extends Mage_Adminhtml_Block_Sales_Order_Create_Totals_Default
{
    public function displayBoth()
    {
        return Mage::helper('gomage_checkout/giftwrap')->displayBoth();
    }
}
