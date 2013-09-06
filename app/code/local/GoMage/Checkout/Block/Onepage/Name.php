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

class GoMage_Checkout_Block_Onepage_Name extends Mage_Customer_Block_Widget_Name
{
    public function _construct()
    {
        parent::_construct();

        // default template location
        $this->setTemplate('gomage/checkout/widget/name.phtml');
    }

}
