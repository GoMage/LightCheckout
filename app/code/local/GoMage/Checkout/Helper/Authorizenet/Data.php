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
 * @since        Class available since Release 3.2
 */

class GoMage_Checkout_Helper_Authorizenet_Data extends Mage_Authorizenet_Helper_Data{

   /**
     * Retrieve save order url params
     *
     * @param string $controller
     * @return array
     */
    public function getSaveOrderUrlParams($controller)
    {    	
        $route = array();
        switch ($controller) {
           case 'onepage':           	
           	    $h = Mage::helper('gomage_checkout');
        		if(!(bool)$h->getConfigData('general/enabled'))
				{
					$route['action'] = 'saveOrder';
	                $route['controller'] = 'onepage';
	                $route['module'] = 'checkout';
				}
				else 
				{
					$route['action'] = 'save';
	                $route['controller'] = 'onepage';
	                $route['module'] = 'gomage_checkout';		
				}
                break;

            case 'sales_order_create':
            case 'sales_order_edit':
                $route['action'] = 'save';
                $route['controller'] = 'sales_order_create';
                $route['module'] = 'admin';
                break;

            default:
                break;
        }

        return $route;
    }
		
}
