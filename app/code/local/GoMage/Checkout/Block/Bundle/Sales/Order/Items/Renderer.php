<?php
/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.9
 * @since        Class available since Release 5.3
 */

class GoMage_Checkout_Block_Bundle_Sales_Order_Items_Renderer extends Mage_Bundle_Block_Sales_Order_Items_Renderer
{

    public function getItemOptions()
    {
        $result = parent::getItemOptions();

        $item = $this->getOrderItem();

        if ($item && $item->getData('gomage_gift_wrap')) {
            $title = Mage::helper('gomage_checkout/giftwrap')->getTitle();
            $result[] = array("value" => $this->__("Yes"), "label" => $title);
        }
        return $result;
    }

}
