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
 * @since        Class available since Release 1.0
 */

class GoMage_DeliveryDate_Model_Observer
{

    public function coreBlockAbstractPrepareLayoutBefore($event)
    {
        if (Mage::helper('gomage_checkout')->getConfigData('deliverydate/deliverydate') > 0) {

            if ($event->getBlock()->getNameInLayout() == 'sales_order.grid') {
                if (Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 4, 0)) {

                    $event->getBlock()->addColumnAfter('gomage_deliverydate', array(
                            'header' => Mage::helper('sales')->__('Delivery Date'),
                            'type'   => 'datetime',
                            'index'  => 'gomage_deliverydate',
                            'width'  => '160px',
                        ), 'grand_total'
                    );

                } else {

                    $event->getBlock()->addColumn('gomage_deliverydate', array(
                            'header' => Mage::helper('sales')->__('Delivery Date'),
                            'type'   => 'datetime',
                            'index'  => 'gomage_deliverydate',
                            'width'  => '160px',
                        )
                    );

                }
            }

        }
    }


    public function saveQuoteData($event)
    {
        $request = Mage::app()->getRequest();
        $quote   = $event->getQuote();
        $helper  = Mage::helper('gomage_deliverydate');

        $shipping_method = $request->getPost('shipping_method', false);
        if ($shipping_method && strpos($shipping_method, 'matrixrate_matrixrate') === 0) {
            $shipping_method = 'matrixrate_matrixrate';
        }

        if (($deliverydate = $request->getPost('deliverydate', false)) && $quote &&
            $helper->isEnableDeliveryDate() &&
            in_array($shipping_method, Mage::helper('gomage_deliverydate')->getShippingMethods())
        ) {

            if (is_array($deliverydate)) {

                $matches = array();

                preg_match_all('/(\d{1,2})\.(\d{1,2})\.(\d{4})/', strval($deliverydate['date']), $matches);

                if (!empty($matches) && count($matches) == 4) {

                    switch (intval(Mage::helper('gomage_checkout')->getConfigData('deliverydate/dateformat'))) {
                        case GoMage_DeliveryDate_Model_Adminhtml_System_Config_Source_Dateformat::EUROPEAN:
                            $date = array(
                                'y' => $matches[3][0],
                                'm' => (strlen($matches[2][0]) == 1 ? '0' . $matches[2][0] : $matches[2][0]),
                                'd' => (strlen($matches[1][0]) == 1 ? '0' . $matches[1][0] : $matches[1][0])
                            );
                            break;
                        default:
                            $date = array(
                                'y' => $matches[3][0],
                                'm' => (strlen($matches[1][0]) == 1 ? '0' . $matches[1][0] : $matches[1][0]),
                                'd' => (strlen($matches[2][0]) == 1 ? '0' . $matches[2][0] : $matches[2][0])
                            );
                    }

                    $mysql_date = implode('', $date);
                    if (isset($deliverydate['time']) && $deliverydate['time']) {
                        $time = explode(':', strval($deliverydate['time']));
                        if (count($time)) {
                            $mysql_time = sprintf('%02d%02d00', $time[0], (isset($time[1]) ? $time[1] : 0));
                        } else {
                            $mysql_time = '000000';
                        }
                    } else {
                        $mysql_time = '000000';
                    }

                    $value     = date('YmdHis', (strtotime($mysql_date . $mysql_time) + intval(@$deliverydate['customer_offset'])));
                    $timestamp = strtotime($mysql_date . $mysql_time) + intval(@$deliverydate['customer_offset']);

                    if ($value) {
                        $quote->setData('gomage_deliverydate', date('Y-m-d H:i:s', $timestamp));
                        $formated_value = Mage::app()->getLocale()->date($value, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString(Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM));
                        $quote->setData('gomage_deliverydate_formated', $formated_value);
                        $quote->save();
                    }
                }

            }
        }

    }

}