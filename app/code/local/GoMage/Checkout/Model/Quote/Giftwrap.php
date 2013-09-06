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
 * @since        Class available since Release 2.4
 */


class GoMage_Checkout_Model_Quote_Giftwrap extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    public function __construct()
    {
        $this->setCode('gomage_gift_wrap');
    }
    
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $items = $address->getAllNonNominalItems();
        if (!count($items)) {
            return $this;
        }

        $gift_wrap_price = Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/price');
        
        foreach ($items as $item) {
            if (!$item->getData('gomage_gift_wrap')) {
                $item->setGomageGiftWrapAmount(0);
                $item->setBaseGomageGiftWrapAmount(0);
            }
            else {
               
                $item->setGomageGiftWrapAmount(Mage::helper('gomage_checkout')->getGiftWrapTaxAmount($address, $gift_wrap_price*$item->getQty()));
                $item->setBaseGomageGiftWrapAmount(Mage::helper('gomage_checkout')->getGiftWrapTaxAmount($address, $gift_wrap_price*$item->getQty()));
                
                $this->_addAmount($item->getGomageGiftWrapAmount());
                $this->_addBaseAmount($item->getBaseGomageGiftWrapAmount());                
            }
        }
       
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getGomageGiftWrapAmount();
        $items = $address->getAllNonNominalItems();
        $add_giftwrap = false;

        if (is_array($items)){
          foreach ($items as $item) {
              if ($item->getData('gomage_gift_wrap')) {
                  $add_giftwrap = true;
                  break;
              }
          }
        }

        if ($add_giftwrap) {

            $title = Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/title');

            $address->addTotal(array(
                'code'  => $this->getCode(),
                'title' => $title,
                'value' => $amount
            ));
        }
        return $this;
    }
}
