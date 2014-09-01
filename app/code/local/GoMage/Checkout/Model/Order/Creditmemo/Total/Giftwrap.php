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
 * @since        Class available since Release 2.4
 */


class GoMage_Checkout_Model_Order_Creditmemo_Total_Giftwrap extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $creditmemo->setGomageGiftWrapAmount(0);
        $creditmemo->setBaseGomageGiftWrapAmount(0);

        $order = $creditmemo->getOrder();

        $totalGomageGiftWrapAmount = 0;
        $baseTotalGomageGiftWrapAmount = 0;
      
        
        foreach ($creditmemo->getAllItems() as $item) {            
            $orderItemGomageGiftWrap      = (float) $item->getOrderItem()->getGomageGiftWrapAmount();
            $baseOrderItemGomageGiftWrap  = (float) $item->getOrderItem()->getBaseGomageGiftWrapAmount();
            $orderItemQty       = $item->getOrderItem()->getQtyOrdered();

            if ($orderItemGomageGiftWrap && $orderItemQty) {
                $GomageGiftWrap = $orderItemGomageGiftWrap*$item->getQty()/$orderItemQty;
                $baseGomageGiftWrap = $baseOrderItemGomageGiftWrap*$item->getQty()/$orderItemQty;

                $GomageGiftWrap = $creditmemo->getStore()->roundPrice($GomageGiftWrap);
                $baseGomageGiftWrap = $creditmemo->getStore()->roundPrice($baseGomageGiftWrap);

                $item->setGomageGiftWrapAmount($GomageGiftWrap);
                $item->setBaseGomageGiftWrapAmount($baseGomageGiftWrap);

                $totalGomageGiftWrapAmount += $GomageGiftWrap;
                $baseTotalGomageGiftWrapAmount+= $baseGomageGiftWrap;
            }
        }

        $creditmemo->setGomageGiftWrapAmount($totalGomageGiftWrapAmount);
        $creditmemo->setBaseGomageGiftWrapAmount($baseTotalGomageGiftWrapAmount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalGomageGiftWrapAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTotalGomageGiftWrapAmount);
        return $this;
    }
}
