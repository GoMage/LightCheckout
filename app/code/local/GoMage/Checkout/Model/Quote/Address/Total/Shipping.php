<?php
 /**
 * GoMage.com
 *
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2014 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.7
 * @since        Class available since Release 2.4
 */


class GoMage_Checkout_Model_Quote_Address_Total_Shipping extends Mage_Sales_Model_Quote_Address_Total_Shipping
{
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        if (Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/enable')>0 &&
            $address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING){
            
            $items = $this->_getAddressItems($address);
            if (!count($items)) {
                return $this;
            }
            $addressQty = 0;
            foreach ($items as $item) {
                if ($item->getData('gomage_gift_wrap')){
                	$addressQty += $item->getTotalQty();	
                }  
            }                
            if (!$addressQty) {
                return $this;
            }
            
            $gift_wrap_price = Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/price');
            
            $this->_addAmount(Mage::helper('gomage_checkout')->getGiftWrapTaxAmount($address, $gift_wrap_price*$addressQty));
            $this->_addBaseAmount(Mage::helper('gomage_checkout')->getGiftWrapTaxAmount($address, $gift_wrap_price*$addressQty));
            
            $shippingDescription = $address->getShippingDescription();
            if ($shippingDescription) $shippingDescription .= ' + ';
            $shippingDescription .= Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/title');
            $address->setShippingDescription($shippingDescription);
        }

        return $this;
    }
    
}
