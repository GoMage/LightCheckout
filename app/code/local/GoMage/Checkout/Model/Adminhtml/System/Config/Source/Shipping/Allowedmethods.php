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
 * @since        Class available since Release 1.0
 */

class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Shipping_Allowedmethods
    extends Mage_Adminhtml_Model_System_Config_Source_Shipping_Allmethods
{
    public function toOptionArray($isActiveOnlyFlag=true)
    {      
    $tmpStoreId = null;
          
    $tmpWebSite = Mage::app()->getRequest()->getParam('website');
    $tmpStore   = Mage::app()->getRequest()->getParam('store');
    
    if (!is_null($tmpWebSite)) 
      {
      $tmpStoreId = Mage::getModel('core/website')->load($tmpWebSite, 'code')->getDefaultGroup()->getDefaultStoreId();
      } 
    elseif (!is_null($tmpStore)) 
      {
      $tmpStoreId = Mage::getModel('core/store')->load($tmpStore, 'code')->getId();
      }

    
    $methods = array(array('value'=>'', 'label'=>''));
            
    $carriers = Mage::getSingleton('shipping/config')->getActiveCarriers($tmpStoreId);
  
    foreach ($carriers as $carrierCode=>$carrierModel) 
      {
      if (!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) {
                continue;
            }
            $carrierMethods = $carrierModel->getAllowedMethods();
            if (!$carrierMethods) {
                continue;
            }
            $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
            $methods[$carrierCode] = array(
                'label'   => $carrierTitle,
                'value' => array(),
            );
            foreach ($carrierMethods as $methodCode=>$methodTitle) {
                $methods[$carrierCode]['value'][] = array(
                    'value' => $carrierCode.'_'.$methodCode,
                    'label' => '['.$carrierCode.'] '.$methodTitle,
                );
            }
        }

        return $methods;
    }

}
