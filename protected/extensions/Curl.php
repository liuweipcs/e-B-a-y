<?php

/**
 *  Curl wrapper for Yii
 * @author Bob <Foxzeng>
 */
class Curl extends CComponent {

    private $_ch;
    
    public $options;
    
    private $_config = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_CONNECTTIMEOUT => 600,
        CURLOPT_TIMEOUT => 600,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:5.0) Gecko/20110619 Firefox/5.0'
    );
    
    public function get($url, $params = array()) {
        $this->setOption(CURLOPT_HTTPGET, true);

        return $this->_exec($this->buildUrl($url, $params));
    }

    public function post($url, $data = array()) {
    	if(is_array($data)&&count($data)){
    	    $postBodyString = '';
        	foreach ($data as $k => $v) {  
				if("@" != substr($v, 0, 1)) {
					$postBodyString .= "$k=" . urlencode($v) . "&"; 
				}
				else {
					$postMultipart = true;
				}
			}
			$data = substr($postBodyString,0,-1);
    	}
        $this->setOption(CURLOPT_POST, true);
        $this->setOption(CURLOPT_POSTFIELDS, $data);
        return $this->_exec($url);
    }

    public function put($url, $data, $params = array()) {
        // write to memory/temp
        $f = fopen('php://temp', 'rw+');
        fwrite($f, $data);
        rewind($f);

        $this->setOption(CURLOPT_PUT, true);
        $this->setOption(CURLOPT_INFILE, $f);
        $this->setOption(CURLOPT_INFILESIZE, strlen($data));

        return $this->_exec($this->buildUrl($url, $params));
    }

    public function delete($url, $params = array()) {

        $this->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');

        return $this->_exec($this->buildUrl($url, $params));
    }

    public function buildUrl($url, $data = array()) {
        $parsed = parse_url($url);
        isset($parsed['query']) ? parse_str($parsed['query'], $parsed['query']) : $parsed['query'] = array();
        $params = isset($parsed['query']) ? array_merge($parsed['query'], $data) : $data;
        $parsed['query'] = ($params) ? '?' . http_build_query($params) : '';
        if (!isset($parsed['path']))
            $parsed['path'] = '/';

        return $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'] . $parsed['query'];
    }

    public function setOptions($options = array()){
        curl_setopt_array($this->_ch, $options);

        return $this;
    }

    public function setOption($option, $value) {
        curl_setopt($this->_ch, $option, $value);

        return $this;
    }

    public function setHeaders($header = array()) {
        if ($this->_isAssoc($header)) {
            $out = array();
            foreach ($header as $k => $v) {
                $out[] = $k . ': ' . $v;
            }
            $header = $out;
        }
//        print_r($header);
//        exit();
        $this->setOption(CURLOPT_HTTPHEADER, $header);

        return $this;
    }
    
    private function _exec($url) {
        $this->setOption(CURLOPT_URL, $url);
        $c = curl_exec($this->_ch);

        if (!curl_errno($this->_ch)){
            return $c;
        }else{
            throw new CException(curl_error($this->_ch));
        }
        return false;
    }

    private function _isAssoc($arr) {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function getError() {
        return curl_error($this->_ch);
    }

    public function getInfo() {
        return curl_getinfo($this->_ch);
    }
    // initialize curl
    public function init() {
        try {
            $this->_ch = curl_init();
            $options = is_array($this->options) ? ($this->options + $this->_config) : $this->_config;
            $this->setOptions($options);

            $ch = $this->_ch;

            // close curl on exit
            Yii::app()->onEndRequest = function() use(&$ch) {
                curl_close($ch);
            };
        } catch (Exception $e) {
            throw new CException('Curl not installed');
        }
    }

}