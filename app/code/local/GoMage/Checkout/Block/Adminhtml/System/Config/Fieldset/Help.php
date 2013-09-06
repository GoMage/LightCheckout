<?php

 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.4
 * @since        Class available since Release 2.4
 */

class GoMage_Checkout_Block_Adminhtml_System_Config_Fieldset_Help
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected function _getHeaderHtml($element)
    {
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');

        $html = '<div  class="entry-edit-head collapseable" >';
        $html.= '<button style="margin-right:25px;float: right;" onclick="window.open(\'http://www.gomage.com/faq/extensions/lightcheckout/\')" class="scalable go" type="button" id="glc_help"><span>GoMage LightCheckout Help</span></button>';
        $html.= '<a id="'.$element->getHtmlId().'-head" href="#" onclick="Fieldset.toggleCollapse(\''.$element->getHtmlId().'\', \''.$this->getUrl('*/*/state').'\'); return false;">'.$element->getLegend().'</a></div>';
        $html.= '<input id="'.$element->getHtmlId().'-state" name="config_state['.$element->getId().']" type="hidden" value="'.(int)$this->_getCollapseState($element).'" />';
        $html.= '<fieldset class="'.$this->_getFieldsetCss().'" id="'.$element->getHtmlId().'">';
        $html.= '<legend>'.$element->getLegend().'</legend>';

        if ($element->getComment()) {
            $html .= '<div class="comment">'.$element->getComment().'</div>';
        }
        // field label column
        $html.= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html.= '<colgroup class="use-default" />';
        }
        $html.= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';

        return $html;
    }

    protected function _getFieldsetCss()
    {
        $configCss = (string)$this->getGroup()->fieldset_css;
        return 'config collapseable'.($configCss ? ' ' . $configCss : '');
    }
     
}
