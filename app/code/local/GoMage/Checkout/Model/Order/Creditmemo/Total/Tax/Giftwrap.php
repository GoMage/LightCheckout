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
 * @since        Class available since Release 5.9
 */
class GoMage_Checkout_Model_Order_Creditmemo_Total_Tax_Giftwrap extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $refunded     = 0;
        $baseRefunded = 0;
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            if (!$creditmemoItem->getQty() || $creditmemoItem->getQty() == 0) {
                continue;
            }
            $orderItem = $creditmemoItem->getOrderItem();
            if ($orderItem->getData('gomage_gift_wrap') && $orderItem->getBaseGomageTaxGiftWrapInvoiced()
                && $orderItem->getBaseGomageTaxGiftWrapInvoiced() != $orderItem->getBaseGomageTaxGiftWrapRefunded()
            ) {
                $orderItem->setBaseGomageTaxGiftWrapRefunded($orderItem->getBaseGomageTaxGiftWrapInvoiced());
                $orderItem->setGomageTaxGiftWrapRefunded($orderItem->getGomageTaxGiftWrapInvoiced());
                $baseRefunded += $orderItem->getBaseGomageTaxGiftWrapInvoiced() * $creditmemoItem->getQty() / $orderItem->getQtyOrdered();
                $refunded += $orderItem->getGomageTaxGiftWrapInvoiced() * $creditmemoItem->getQty() / $orderItem->getQtyOrdered();
            }
        }
        if ($refunded > 0 || $baseRefunded > 0) {
            $order->setBaseGomageTaxGiftWrapRefunded($order->getBaseGomageTaxGiftWrapRefunded() + $baseRefunded);
            $order->setGomageTaxGiftWrapRefunded($order->getGomageTaxGiftWrapRefunded() + $refunded);
            $creditmemo->setBaseGomageTaxGiftWrapAmount($baseRefunded);
            $creditmemo->setGomageTaxGiftWrapAmount($refunded);
        }


        $baseTaxAmount = $creditmemo->getBaseGomageTaxGiftWrapAmount();
        $taxAmount     = $creditmemo->getGomageTaxGiftWrapAmount();

        $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseTaxAmount);
        $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $taxAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTaxAmount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $taxAmount);
        
        return $this;
    }
}
