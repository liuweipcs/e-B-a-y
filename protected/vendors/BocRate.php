<?php

require_once( dirname(__FILE__).'/simple_html_dom/simple_html_dom.php' );
class BocRate{
    /**
     * [$getCode description]
     * @var array
     */
    public $getCode = array();

    public function setCurrency($arr){
        // if(!set($arr) && is_string($arr) ) $arr = (array) $arr;
        $this->getCode = $arr;
    }

    /**
     *Retrieve info from boc website using simple html dom.
     *
     *Simple Html Dom Parser document and download: http://simplehtmldom.sourceforge.net/
     */
    function get_boc_exchange_rate_table(){
    	
    	//Get web page by simple html dom parser
    	$html = file_get_html( 'http://www.boc.cn/sourcedb/whpj/enindex.html' );
    	
    	//Control the items in the currency list.
    	//$allowed_currency = array( 'TWD', 'GBP', 'HKD', 'USD', 'CHF', 'SGD', 'SEK', 'DKK', 'NOK', 'JPY', 'CAD', 'AUD', 'MYR', 'EUR', 'MOP', 'PHP', 'THB', 'NZD', 'KRW', 'RUB' );
    	$allowed_currency = $this->getCode;
    	//Stores the final data
    	$exchange_rates = array();
    	
    	foreach( $html->find('table tr[align=center]') as $tr ){
    		$currency_name = $tr->children(0)->plaintext;
    		if( in_array( $currency_name, $allowed_currency ) ){
    			$exchange_rates[ $currency_name ]['currency_name'] = $currency_name;
    			$exchange_rates[ $currency_name ]['buying_rate'] = $tr->children(1)->plaintext;
    			$exchange_rates[ $currency_name ]['cash_buying_rate'] = $tr->children(2)->plaintext;
    			$exchange_rates[ $currency_name ]['selling_rate'] = $tr->children(3)->plaintext;
    			$exchange_rates[ $currency_name ]['cash_selling_rate'] = $tr->children(4)->plaintext;
    			$exchange_rates[ $currency_name ]['middle_rate'] = $tr->children(5)->plaintext;
    			$exchange_rates[ $currency_name ]['pub_time'] = str_replace("&nbsp;", '',$tr->children(6)->plaintext);
    			try {
    				$datetime = new DateTime( $exchange_rates[ $currency_name ]['pub_time'] );
    			} catch( Exception $e ){
    				echo $e->getMessage();
    			}
    			$exchange_rates[ $currency_name ]['pub_time'] = $datetime->format('Y-m-d H:i:s');
    		}                    
    	}
    	return $exchange_rates;
    }

    //Format data and output it.
    function show_boc_exchage_rate_table(){
    	$exchange_rates = $this->get_boc_exchange_rate_table();
    	$str = '';
    	foreach( $exchange_rates as $key => $rateinfo ){
    		$str.= '<ul class="exchange-rate currency-'.strtolower( $key ).'">';
    		$str .= '<li>Currency Name: ' .$rateinfo['currency_name']. '</li>';
    		$str .= '<li>Buying Rate: ' .$rateinfo['buying_rate']. '</li>';
    		$str .= '<li>Cashing Buying Rate: ' .$rateinfo['cash_buying_rate']. '</li>';
    		$str .= '<li>Selling Rate: ' .$rateinfo['selling_rate']. '</li>';
    		$str .= '<li>Cash Selling Rate: ' .$rateinfo['cash_selling_rate']. '</li>';
    		$str .= '<li>Middle Rate ' .$rateinfo['middle_rate']. '</li>';
    		$str .= '<li>Pub Time: ' .$rateinfo['pub_time']. '</li>';
    		$str .= '</ul>';
    	}
    	return $str;
    }
}
//Run the output function just for testing.
// show_boc_exchage_rate_table();

?>