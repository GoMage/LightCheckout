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
        if ($address->getAddressType() != Mage_Sales_Model_Quote_Address::TYPE_SHIPPING) {
            return $this;
        }

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $address->getQuote();

        /** @var Mage_Core_Model_Store $store */
        $store = $quote->getStore();

        $price = Mage::helper('gomage_checkout/giftwrap')->getPrice();

        $gomage_gift_wrap_amount      = 0;
        $base_gomage_gift_wrap_amount = 0;

        foreach ($items as $item) {
            if (!$item->getData('gomage_gift_wrap')) {
                $item->setBaseGomageGiftWrapAmount(0);
                $item->setGomageGiftWrapAmount(0);
            } else {
                $base_gw_amount = $price * $item->getQty();
                $gw_amount      = $store->convertPrice($base_gw_amount);

                $item->setGomageGiftWrapAmount($gw_amount);
                $item->setBaseGomageGiftWrapAmount($base_gw_amount);

                $gomage_gift_wrap_amount += $gw_amount;
                $base_gomage_gift_wrap_amount += $base_gw_amount;
            }
        }

        $address->setGomageGiftWrapAmount($gomage_gift_wrap_amount);
        $address->setBaseGomageGiftWrapAmount($base_gomage_gift_wrap_amount);

        $address->setGrandTotal($address->getGrandTotal() + $address->getGomageGiftWrapAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseGomageGiftWrapAmount());

        if ($quote->getIsNewGomageGiftWrapCollecting()) {
            $quote->setGomageGiftWrapAmount(0);
            $quote->setBaseGomageGiftWrapAmount(0);
            $quote->setIsNewGomageGiftWrapCollecting(false);
        }

        $quote->setGomageGiftWrapAmount($address->getGomageGiftWrapAmount() + $quote->getGomageGiftWrapAmount());
        $quote->setBaseGomageGiftWrapAmount($address->getBaseGomageGiftWrapAmount() + $quote->getBaseGomageGiftWrapAmount());

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $address->addTotal(array(
                'code'                         => $this->getCode(),
                'gomage_gift_wrap_amount'      => $address->getGomageGiftWrapAmount(),
                'base_gomage_gift_wrap_amount' => $address->getBaseGomageGiftWrapAmount(),
            )
        );
        return $this;
    }
}
