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

class GoMage_Checkout_Block_Onepage_Abstract extends Mage_Checkout_Block_Onepage_Abstract
{

    protected $mode;
    protected $helper;
    protected $default_address_template = 'gomage/checkout/html/address/field/default.phtml';
    protected $field_code_to_label;

    public function __construct()
    {
        $this->helper              = Mage::helper('gomage_checkout');
        $this->field_code_to_label = array('company'    => 'Company',
                                           'street'     => 'Street',
                                           'city'       => 'City',
                                           'telephone'  => 'Telephone',
                                           'fax'        => 'Fax',
                                           'postcode'   => 'Zip/Postal',
                                           'country_id' => 'Country',
                                           'region'     => 'State/Province',
        );
    }

    public function getCustomerComment()
    {
        return $this->getQuote()->getGomageCheckoutCustomerComment();
    }

    public function getCheckoutMode()
    {
        if (is_null($this->mode)) {
            $this->mode = intval($this->helper->getCheckoutMode());
        }

        return $this->mode;
    }

    public function getConfigData($node)
    {
        return $this->helper->getConfigData($node);
    }

    public function isEnabled($node)
    {
        return (bool)$this->getConfigData('address_fields/' . $node);
    }

    public function getDefaultCountryId()
    {
        return $this->helper->getDefaultCountryId();
    }

    public function getDefaultShippingMethod()
    {
        return $this->helper->getDefaultShippingMethod();
    }

    public function getDefaultPaymentMethod()
    {
        return $this->helper->getDefaultPaymentMethod();
    }

    public function getSortedFields()
    {
        $address_fields = array('company', 'street', 'city', 'telephone', 'fax', 'postcode', 'country_id', 'region');
        $rows           = array();

        foreach (Mage::getStoreConfig('gomage_checkout/address_fields') as $field_name => $status) {
            if ($status != false) {
                if (in_array($field_name, $address_fields)) {
                    $order = intval(Mage::getStoreConfig('gomage_checkout/address_sort/' . $field_name . '_order'));
                    if (!isset($rows[$order]) || count($rows[$order]) < 2) {

                        if ($field_name == 'postcode' && isset($rows[$order][0]) && $rows[$order][0] == 'region') {
                            array_unshift($rows[$order], $field_name);
                        } else {
                            $rows[$order][] = $field_name;
                        }

                    } else {
                        $rows[] = array($field_name);
                    }
                }
            }
        }

        ksort($rows);

        return $this->_renderFields($rows);
    }

    protected function _renderFields($fields)
    {
        $html = '';
        foreach ($fields as $_fields) {
            if (is_array($_fields)) {
                if (count($_fields) > 1) {

                    $_html     = '';
                    $i         = 0;
                    $row_class = array();

                    foreach ($_fields as $field_code) {
                        $data = array(
                            'prefix'     => $this->prefix,
                            'value'      => $this->getAddress()->getData($field_code),
                            'label'      => @$this->field_code_to_label[$field_code],
                            'input_name' => $this->prefix . '[' . $field_code . ']',
                            'input_id'   => $this->prefix . '_' . $field_code,
                        );

                        if ($this->getConfigData('address_fields/' . $field_code) == 'req') {
                            $data['is_required'] = true;
                        }

                        if (!($template = $this->getData($field_code . '_template'))) {
                            $template = $this->default_address_template;
                        }

                        $_html .= '<div class="field field-' . $field_code . ' ' . ($i % 2 == 0 ? ' field-first ' : ' field-last ') . '">' . $this->getLayout()->createBlock('gomage_checkout/onepage_' . $this->prefix)->setTemplate($template)->addData($data)->toHtml() . '</div>';

                        $row_class[] = $field_code;

                        if (++$i == 2) {
                            break;
                        }
                    }

                    $html .= '<li class="fields ' . implode('-', $row_class) . '">' . $_html . '</li>';

                } else {

                    $field_code = array_shift($_fields);

                    $data = array(
                        'prefix'         => $this->prefix,
                        'address_prefix' => $this->prefix,
                        'value'          => $this->getAddress()->getData($field_code),
                        'label'          => @$this->field_code_to_label[$field_code],
                        'input_name'     => $this->prefix . '[' . $field_code . ']',
                        'input_id'       => $this->prefix . '_' . $field_code,
                    );


                    if ($this->getConfigData('address_fields/' . $field_code) == 'req') {
                        $data['is_required'] = true;
                    }

                    if (!($template = $this->getData($field_code . '_template'))) {
                        $template = $this->default_address_template;
                    }

                    $html .= '<li>' . $this->getLayout()->createBlock('gomage_checkout/onepage_' . $this->prefix)->setTemplate($template)->addData($data)->toHtml() . '</li>';
                }

            }
        }

        return $html;
    }

    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }

            $addressId = $this->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                if ($type == 'billing') {
                    $address = $this->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $this->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type . '_address_id')
                ->setId($type . '-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange="checkout.loadAddress(\'' . $type . '\', this.value, \'' . $this->getUrl('gomage_checkout/onepage/ajax', array('action' => 'load_address')) . '\')"')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }
        return '';
    }

}