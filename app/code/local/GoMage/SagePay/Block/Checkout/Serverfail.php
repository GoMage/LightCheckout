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