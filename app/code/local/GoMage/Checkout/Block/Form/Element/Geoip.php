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
 * @since        Class available since Release 2.0
 */

class GoMage_Checkout_Block_Form_Element_Geoip extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        if (!file_exists(Mage::getBaseDir('media') . "/geoip/GeoLiteCity.dat")) {
            $element->setDisabled(true);
            if ($element->getId() == 'gomage_checkout_geoip_geoip_enabled') {
                $element->setComment($this->__('To use GeoIP you need to upload GeoliteCity.dat file to folder /media/geoip. Read more in the <a target="_blank" href="%s">Installation Guide</a>', 'http://wiki.gomage.com/display/lc/GeoIP+Installation'));
            }
        }
        return parent::_getElementHtml($element);
    }

}