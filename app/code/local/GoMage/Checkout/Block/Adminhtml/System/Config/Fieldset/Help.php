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

class GoMage_Checkout_Block_Adminhtml_System_Config_Fieldset_Help
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected function _getHeaderHtml($element)
    {
        if (method_exists($this, '_getHeaderTitleHtml')){
            if ($element->getIsNested()) {
                $html = '<tr class="nested"><td colspan="4"><div class="' . $this->_getFrontendClass($element) . '">';
            } else {
                $html = '<div class="' . $this->_getFrontendClass($element) . '">';
            }
            $html .= $this->_getHeaderTitleHtml($element);

            $html .= '<input id="'.$element->getHtmlId() . '-state" name="config_state[' . $element->getId()
                . ']" type="hidden" value="' . (int)$this->_getCollapseState($element) . '" />';
            $html .= '<fieldset class="' . $this->_getFieldsetCss($element) . '" id="' . $element->getHtmlId() . '">';
            $html.= '<button style="margin-right:25px; margin-top:-33px;  float: right;" onclick="window.open(\'http://wiki.gomage.com/display/lc/Home\')" class="scalable go" type="button" id="glc_help"><span>'.$this->__('GoMage LightCheckout Wiki').'</span></button>';

            $html .= '<legend>' . $element->getLegend() . '</legend>';

            $html .= $this->_getHeaderCommentHtml($element);

            // field label column
            $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
            if ($this->getRequest()->getParam('website') || $this->getRequest()->getParam('store')) {
                $html .= '<colgroup class="use-default" />';
            }
            $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        }else{
            $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
            $html = '<div  class="entry-edit-head collapseable" >';
            $html.= '<button style="margin-right:25px;float: right;" onclick="window.open(\'http://wiki.gomage.com/display/lc/Home\')" class="scalable go" type="button" id="glc_help"><span>'.$this->__('GoMage LightCheckout Wiki').'</span></button>';
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
        }
        return $html;
    }

    protected function _getFieldsetCss($element = null)
    {
        $configCss = (string)$this->getGroup($element)->fieldset_css;
        return 'config collapseable'.($configCss ? ' ' . $configCss : '');
    }

}
