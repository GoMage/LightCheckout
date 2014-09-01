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
 * @since        Class available since Release 2.5
 */

class GoMage_DeliveryDate_Block_Adminhtml_Config_Form_Renderer_Select extends Mage_Core_Block_Html_Select
{
   
	public function setInputName($inputName){
		$this->setData('inputname', $inputName);
        return $this;
	}
	
	public function getInputName(){
		return $this->getData('inputname');
	}
	
	public function setColumnName($columnName){
		$this->setData('columnname', $columnName);		
        return $this;
	}
	
	public function getColumnName(){
		return $this->getData('columnname');        
	}
	
	public function setColumn($column){
		$this->setData('column', $column);
        return $this;
	}
	
	public function getColumn(){
		return $this->getData('column');
	}
	
	public function getExtraParams(){
		$column = $this->getColumn(); 
		if ($column && isset($column['style'])){
			return ' style="'.$column['style'].'" ';
		}else{
			return '';
		}				
	}
	
    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
        }

        $html = '<select name="'.$this->getInputName().'" class="'.$this->getClass().'" '.$this->getExtraParams().'>';
            
        $values = $this->getValue();

        if (!is_array($values)){
            if (!is_null($values)) {
                $values = array($values);
            } else {
                $values = array();
            }
        }

        $isArrayOption = true;
        foreach ($this->getOptions() as $key => $option) {
            if ($isArrayOption && is_array($option)) {
                $value  = $option['value'];
                $label  = $option['label'];
                $params = (!empty($option['params'])) ? $option['params'] : array();
            } else {
                $value = $key;
                $label = $option;
                $isArrayOption = false;
                $params = array();
            }

            if (is_array($value)) {
                $html.= '<optgroup label="'.$label.'">';
                foreach ($value as $keyGroup => $optionGroup) {
                    if (!is_array($optionGroup)) {
                        $optionGroup = array(
                            'value' => $keyGroup,
                            'label' => $optionGroup
                        );
                    }
                    $html.= $this->_optionToHtml(
                        $optionGroup,
                        in_array($optionGroup['value'], $values)
                    );
                }
                $html.= '</optgroup>';
            } else {
                $html.= $this->_optionToHtml(array(
                    'value' => $value,
                    'label' => $label,
                    'params' => $params
                ),
                    in_array($value, $values)
                );
            }
        }
        $html.= '</select>';
        return $html;
    }

    /**
     * Return option HTML node
     *
     * @param array $option
     * @param boolean $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected = false)
    {
        $selectedHtml = $selected ? ' selected="selected"' : '';
        if ($this->getIsRenderToJsTemplate() === true) {
            $selectedHtml .= ' #{option_extra_attr_' . self::calcOptionHash($option['value']) . '}';
        }

        $params = '';
        if (!empty($option['params']) && is_array($option['params'])) {
            foreach ($option['params'] as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $keyMulti => $valueMulti) {
                        $params .= sprintf(' %s="%s" ', $keyMulti, $valueMulti);
                    }
                } else {
                    $params .= sprintf(' %s="%s" ', $key, $value);
                }
            }
        }

        return sprintf('<option value="%s"%s %s>%s</option>',
            $this->htmlEscape($option['value']),
            $selectedHtml,
            $params,
            $this->htmlEscape($option['label']));
    }

    public function getHtml()
    {
        return $this->toHtml();
    }
    
	public function calcOptionHash($optionValue)
    {
        return sprintf('%u', crc32($this->getColumnName() . $this->getInputName() . $optionValue));
    }
    
}
