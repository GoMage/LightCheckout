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
 * @since        Class available since Release 5.3
 */

class GoMage_Checkout_Block_Adminhtml_Sales_Order_View_Items_Bundle_Renderer extends Mage_Bundle_Block_Adminhtml_Sales_Order_View_Items_Renderer
{

    public function getOrderOptions($item = null)
    {
        $result = parent::getOrderOptions($item);

        if (is_null($item)) {
            $item = $this->getItem();
        }

        if ($item && $item->getData('gomage_gift_wrap')) {
            $title    = Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/title');
            $result[] = array("value" => $this->__("Yes"), "label" => $title);
        }
        return $result;
    }

}
