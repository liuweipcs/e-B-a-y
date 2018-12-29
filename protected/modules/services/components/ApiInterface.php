<?php
/**
 *   api interface
 * 
 * @package Ueb.modules.services.modules.components
 * @auther Bob <zunfengke@gmail.com>
 */
interface ApiInterface  {
    
    /**
     * set request
     */
    public function setRequest();

    /**
     * get request
     * 
     *  @return object get request.
     */
    public function getRequest();

    /**
     * send http request
     * 
     *  @return object send http response.
     */
     public function sendHttpRequest();
     
     /**
      * get response
      * @return object get response
      */
     public function getResponse();
    
}

