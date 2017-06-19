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
 * @since Class available since Release 5.9
 */
class GoMage_SagePay_Block_Checkout_Serverfail extends Ebizmarts_SagePaySuite_Block_Checkout_Serverfail
{
    protected function _toHtml()
    {
        $html = parent::_toHtml();

        $html = str_replace("window.parent.$('checkout-review-submit').show();",
            "window.parent.$('checkout-review-submit').show();
             window.parent.$('gcheckout-onepage-form').enable();",
            $html
        );

        return $html;
    }

}