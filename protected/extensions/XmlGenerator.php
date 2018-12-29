<?php

/**
 *  XmlGenerator wrapper for Yii
 * @author Bob <Foxzeng>
 */
class XmlGenerator {

    public $xml;
    
    public $indent;
    
    public $stack = array();

    public function XmlWriter($indent = '  ') {
        $this->indent = $indent;
        ob_clean();
        $this->xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        
        return $this;
    }

    public function push($element, $attributes = array()) {
        $this->_indent();
        $this->xml .= '<' . $element;
        foreach ($attributes as $key => $value) {
            $this->xml .= ' ' . $key . '="' . htmlentities($value) . '"';
        }
        $this->xml .= ">\n";
        $this->stack[] = $element;
        
        return $this;
    }

    public function element($element, $content, $attributes = array()) {
        $this->_indent();
        $this->xml .= '<' . $element;
        foreach ($attributes as $key => $value) {
            $this->xml .= ' ' . $key . '="' . htmlentities($value) . '"';
        }
        $this->xml .= '>' . htmlentities($content) . '</' . $element . '>' . "\n";
        
        return $this;
    }

    public function emptyelement($element, $attributes = array()) {
        $this->_indent();
        $this->xml .= '<' . $element;
        foreach ($attributes as $key => $value) {
            $this->xml .= ' ' . $key . '="' . htmlentities($value) . '"';
        }
        $this->xml .= " />\n";
        
        return $this;
    }

    public function pop() {
        $element = array_pop($this->stack);
        $this->_indent();
        $this->xml .= "</$element>\n";
        
        return $this;
    }

    public function getXml() {
        return $this->xml;
    }
    
    /**
     * 
     * @param array $filterarray
     * @param type $tag
     * @param array $attributes
     * @return type
     */
     public function buildXMLFilter($filterarray, $tag = '', $attributes = array()) {
        $this->xml .= $this->_buildXMLFilter($filterarray, $tag, $attributes);
        
        return $this;
    }

    protected function _indent() {
        for ($i = 0, $j = count($this->stack); $i < $j; $i++) {
            $this->xml .= $this->indent;
        }
        
        return $this;
    }
    
    /**
     *  build xml filter
     * @param array $filterarray
     * @param type $tag
     * @param array $attributes
     */
    public function _buildXMLFilter($filterarray, $tag = '', $attributes = array()) {
        $xmlfilter = "";

        foreach ($filterarray as $key => $value) {
            if ($tag) {
                $key = $tag;
            }
            if ( isset($attributes[$key]) ) {
                $attribute = ' ' . $attributes[$key]['name'] . '="' . $attributes[$key]['value'] . '" ';
            } else {
                $attribute = "";
            }
            if (is_array($value)) {
                $xmlfilter .= " <$key $attribute>\n" . $this->_buildXMLFilter($value, '', $attributes) . "</$key>\n";
            } else {
                if (intval($key) != 0 || $key === 0) {
                    $xmlfilter .= $value;
                } else {
                    $xmlfilter .= " <$key $attribute>$value</$key>\n";
                }
            }
        }
        
        return $xmlfilter;
    }
    
    public function buildXMLFilterArr($filterarray, $tag = '', $attributes = array(),$ns='') {
    	$this->xml .= $this->_buildXMLFilterArr($filterarray, $tag, $attributes,$ns);
    
    	return $this;
    }
    
    public function _buildXMLFilterArr($filterarray, $tag = '', $attributes = array(),$ns='') {
    	$xmlfilter = "";
    
    	foreach ($filterarray as $key => $value) {
    		if ($tag) {
    			$key = $tag;
    		}
    		if($ns){
    			$key = $ns.':'.$key;
    		}
    		if ( isset($attributes[$key]) ) {
    			$attribute = ' ' . $attributes[$key]['name'] . '="' . $attributes[$key]['value'] . '" ';
    		} else {
    			$attribute = "";
    		}
    		if (is_array($value)) {
    			$flag = true;
    			foreach( $value as $k => $v ){
    				if(is_numeric($k)){
    					$xmlfilter .= " <$key".$attribute.">\n" . $this->_buildXMLFilterArr($v, '', $attributes,$ns) . "</$key>\n";
    					$flag = false;
    				}
    			}
    			if($flag) $xmlfilter .= " <$key".$attribute.">\n" . $this->_buildXMLFilterArr($value, '', $attributes,$ns) . "</$key>\n";
    		} else {
    			if (intval($key) != 0 || $key === 0) {
    				$xmlfilter .= $value;
    			} else {
    				$xmlfilter .= " <$key".$attribute.">$value</$key>\n";
    			}
    		}
    	}
    
    	return $xmlfilter;
    }

}
?>  
