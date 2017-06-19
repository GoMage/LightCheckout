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
 * @since        Class available since Release 2.2
 */

class GoMage_SagePay_Block_Head extends Mage_Core_Block_Template
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (Mage::helper('gomage_sagepay')->isGoMage_SagePayEnabled() && ($head = $this->getLayout()->getBlock('head'))) {
            $head->addItem('skin_css', 'sagepaysuite/css/growler/growler.css');
            $head->addItem('skin_css', 'sagepaysuite/css/sagePaySuite_Checkout.css');
            $head->addItem('skin_js', 'sagepaysuite/js/growler/growler.js');
            $head->addItem('js', 'sagepaysuite/direct.js');
            $head->addItem('js', 'sagepaysuite/common.js');
            $head->addItem('skin_js', 'sagepaysuite/sagePaySuite.js');
            $head->addItem('skin_js', 'sagepaysuite/js/sagePaySuite_Checkout.js');
            $head->addItem('js', 'sagepaysuite/livepipe/livepipe.js');
            $head->addItem('js', 'sagepaysuite/livepipe/window.js');
        }
    }

}