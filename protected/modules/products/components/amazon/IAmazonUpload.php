<?php

/**
 * @package ~.IAmazonProductdata
 * @author mrlina <714480119@qq.com>
 */
interface IAmazonUpload
{
	/**
	 * get the xsd definition xml
	 * 
	 * @param  array  $parmas
	 * @return string
	 */
	public function uploadProduct($id = null);
}