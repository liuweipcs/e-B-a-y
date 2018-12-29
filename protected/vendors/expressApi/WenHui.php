<?php
class WenHui implements expressUtil{
	private $url = 'http://tracking.directlink.com/responseStatus.php?json=1&site_cd=AC5&lang=en&postal_ref_no=';
	
	private $status = array('undefined', 'Order dispatched', 'Order departed on flight from origin', 'Order arrived at destination airport', 'Order awaiting customs clearance', 'Status updates may be available from destination carrier', 'Warning message', 'Order customs cleared and lodged with local delivery agent', 'Order out for delivery', 'Order delivered', 'Order in transit', 'Order departed from sorting hub', 'Order transferred to delivery processing point', 'Delivery attempt', 'Order received into final destination country', 'Order at pre-departure sorting', 'Item pre-advice received', 'Reserved', 'Item received for processing', 'Resdes', 'Item over labelled at hub', 'Item returned to Direct Link HUB', 'Item could not be delivered, returning to origin', 'Item returned to Direct Link HUB', 'Item returned to Direct Link HUB', 'Item returned to Direct Link HUB', 'Item returned to Direct Link HUB', 'Item returned to Direct Link Local HUB');
	
	public function checkResult($data){
		$content = call_user_func(array('HttpUtil','curlGet'), $this->url.$data);
		$obj = !empty($content)?json_decode($content):'';
		if(empty($obj)) return false;
		if(empty($obj->item_events)) return false;
		$result = array();
		foreach ($obj->item_events as $val){
			$result[$val[0]] = $this->status[$val[1]];
		}
		return $result;
	}
}