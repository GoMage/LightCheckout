<?php
/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2014 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.3
 * @since        Class available since Release 5.3
 */

class GoMage_Checkout_Block_Checkout_Success_Additional extends Mage_Core_Block_Template
{

    protected $item;

    public function setItem($item)
    {
        $this->item = $item;
        return $this;
    }

    public function isGiftWrap()
    {
        return $this->item && $this->item->getData('gomage_gift_wrap');
    }

}

