<?php
/*
 * PHP API Lib for drop.io
 * Copyright (C) 2008, David Billingham
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 */
class DROPIO_Transfer{
	const API_KEY='YOUR API KEY';
	const USERAGENT='dropio-php-lib';
	
	const RESPONSE_TYPE_XML='xml';
	const RESPONSE_TYPE_JSON='json';
	
	private $response_type='';
	
	public function __construct(){
		if(function_exists('json_decode')){
			$this->response_type=self::RESPONSE_TYPE_JSON;
		}elseif(function_exists('simplexml_load_string')){
			$this->response_type=self::RESPONSE_TYPE_XML;
		}else{
			throw new Exception('proper decoding extension not installed. (json or simple xml)');
		}
	}
	
	public function connect(DROPIO_ApiCall $call){
		$call->api_key=DROPIO_Transfer::API_KEY;
		$call->format=$this->response_type;
		
		$call->isReady();
		
		$ch = curl_init($call->getApiEndPoint());
		curl_setopt($ch, CURLOPT_POSTFIELDS, $call->getPostArgs());

		switch($call->getHTTPRequestType()){
			case 'POST':
				curl_setopt ($ch, CURLOPT_POST, true);
				break;
			case 'DELETE':
				curl_setopt($ch, CURLOPT_POSTFIELDS, $call->getPostArgs());
				break;
			case 'PUT':
				//curl_setopt ($ch, CURLOPT_PUT, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				break;
			case 'GET':
				curl_setopt ($ch, CURLOPT_HTTPGET, true);
				curl_setopt ($ch, CURLOPT_URL, $call->getApiEndPoint().'?'.$call->getPostArgs());
		}

		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, DROPIO_Transfer::USERAGENT);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		//$response=explode("\r\n\r\n",curl_exec($ch),2);
		$response = explode("\r\n\r\n", curl_exec($ch), 2);
		
		curl_close($ch);
		
		return $this->processResponse($response[1]);
	}
	
	private function processResponse($resp){
		$value = array();
		switch($this->response_type){
			case self::RESPONSE_TYPE_JSON:
				$value=$this->postProcessJson($resp);
			case self::RESPONSE_TYPE_XML:
				$value=$this->postProcessXml($resp);
		}
		return $value;
	}
	
	private function postProcessXml($xml){
		//print_r(htmlspecialchars($xml));
		$arr=get_object_vars(simplexml_load_string($xml));
		return $arr;
	}
	
	private function postProcessJson($json){
		return json_decode($json);
	}
}


abstract class DROPIO_ApiCall{
	protected $HTTP_REQUEST_TYPE='GET';
	protected $API_ENDPOINT_URL='http://api.drop.io/';
	protected $params=array('format'=>'json');
	protected $required_params=array('api_key','format');
	protected $uri_params=array();
	abstract function buildURI();
	protected function addRequiredParam($name){
		if ( !in_array($name,$this->required_params)){
			$this->required_params[]=$name;
		}
	}
	protected function addURIParam($name){
		if ( !in_array($name,$this->uri_params)){
			$this->uri_params[]=$name;
		}
	}
	protected function setHTTPRequestType($type){
		$this->HTTP_REQUEST_TYPE=$type;
	}
	public function __set($property,$value){
		$this->params[$property]=$value;
	}
	
	public function getHTTPRequestType(){
		return $this->HTTP_REQUEST_TYPE;
	}
	
	public function getPostArgs(){
		$qs=array();
		foreach($this->params AS $key=>$value){
			$qs[]=$key.'='.urlencode($value);
		}
		return implode('&',$qs);
	}
	
	public function getApiEndPoint(){
		return $this->API_ENDPOINT_URL.$this->buildURI();
	}
	
	public function isReady(){
		foreach ( $this->required_params AS $v ) {
			if (	!in_array($v,$this->uri_params) 
					&& 	!array_key_exists($v,$this->params)){
				throw new Exception(__CLASS__.' requires param "'.$v.'"');
			}
		}
	}
	
	protected function connectAndSend(){
		$t = new DROPIO_Transfer();
		return $t->connect($this);
	}
	abstract function send();
}

class DROPIO_CreateDrop extends DROPIO_ApiCall{
	function __construct(){
		parent::setHTTPRequestType('POST');
	}
	function buildURI(){
		return 'drops';
	}
	function send(){
		return new DROPIO_Drop(parent::connectAndSend());
	}
}

class DROPIO_GetDrop extends DROPIO_ApiCall{
	function __construct(){
		parent::addURIParam('name');
		parent::setHTTPRequestType('GET');
	}
	function buildURI(){
		return 'drops/'.$this->params['name'];
	}
	function send(){
		return new DROPIO_Drop(parent::connectAndSend());
	}
}

class DROPIO_UpdateDrop extends DROPIO_ApiCall{
	function __construct(){
		parent::addURIParam('name');
		parent::addRequiredParam('token');
		parent::setHTTPRequestType('PUT');
	}
	function buildURI(){
		return 'drops/'.$this->params['name'];
	}
	function send(){
		return new DROPIO_Drop(parent::connectAndSend());
	}
}

class DROPIO_DeleteDrop extends DROPIO_ApiCall{
	function __construct(){
		parent::addURIParam('name');
		parent::addRequiredParam('token');
		parent::setHTTPRequestType('DELETE');
	}
	function buildURI(){
		return 'drops/'.$this->params['name'];
	}
	function send(){
		return new DROPIO_Drop(parent::connectAndSend());
	}
}

class DROPIO_CreateAsset_Link extends DROPIO_ApiCall{
	function __construct(){
		parent::setHTTPRequestType('POST');
		parent::addURIParam('drop_name');
		parent::addRequiredParam('url');
	}
	function buildURI(){
		return 'drops/'.$this->params['drop_name'].'/assets';
	}
	function send(){
		return new DROPIO_Asset(parent::connectAndSend());
	}
}

class DROPIO_CreateAsset_Note extends DROPIO_ApiCall{
	function __construct(){
		parent::setHTTPRequestType('POST');
		parent::addURIParam('drop_name');
		parent::addRequiredParam('contents');
	}
	function buildURI(){
		return 'drops/'.$this->params['drop_name'].'/assets';
	}
	function send(){
		return new DROPIO_Asset(parent::connectAndSend());
	}
}

class DROPIO_GetAsset extends DROPIO_ApiCall{
	function __construct(){
		parent::addURIParam('drop_name');
		parent::addURIParam('name');
		parent::setHTTPRequestType('GET');
	}
	function buildURI(){
		return 'drops/'.$this->params['drop_name'].'/assets/'.$this->params['name'];
	}
	function send(){
		return new DROPIO_Asset(parent::connectAndSend());
	}
}

class DROPIO_GetAssets extends DROPIO_ApiCall{
	function __construct(){
		parent::addURIParam('drop_name');
		parent::setHTTPRequestType('GET');
	}
	function buildURI(){
		return 'drops/'.$this->params['drop_name'].'/assets';
	}
	function send(){
		return parent::connectAndSend();
	}
}



abstract class DROPIO_Resource{
	private $values=array();
	private $created_at=NULL;
	function __construct($array){
		foreach($array AS $k=>$v){
			$k=str_replace('-','_',$k);
			if(is_object($v)){
				$this->values[$k]=get_object_vars($v);
			}else{
				$this->values[$k]=$v;
			}
		}
		$this->created_at=microtime(true);
	}
	function __get($key){
		return isset($this->values[$key])?$this->values[$key]:NULL;
	}
}

class DROPIO_Drop extends DROPIO_Resource{}

class DROPIO_Asset extends DROPIO_Resource{}

class DROPIO_Comment extends DROPIO_Resource{}



?>