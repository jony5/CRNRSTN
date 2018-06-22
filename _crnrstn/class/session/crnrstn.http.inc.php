<?php
/*
// J5
// Code is Poetry */
#  CRNRSTN Suite :: An Open Source PHP Class Library to facilitate the operation of an application across multiple hosting environments.
#  Copyright (C) 2012-2018 Evifweb Development
#  VERSION :: 1.0.0
#  RELEASE DATE :: July 4, 2018 Happy Independence Day from my dog and I to you...wherever and whenever you are.
#  AUTHOR :: Jonathan 'J5' Harris, Lead Full Stack Developer
#  URI :: http://crnrstn.evifweb.com/
#  OVERVIEW :: CRNRSTN is an open source PHP class library that facilitates the operation of an application within multiple server 
#			   environments (e.g. localhost, stage, preprod, and production). With this tool, data and functionality with 
#			   characteristics that inherently create distinctions from one environment to the next...such as IP address restrictions, 
#			   error logging profiles, and database authentication credentials...can all be managed through one framework for an entire 
#			   application. Once CRNRSTN has been configured for your different hosting environments, seamlessly release a web 
#			   application from one environment to the next without having to change your code-base to account for environmentally 
#			   specific parameters; and manage this all from one place within the CRNRSTN Suite ::

#  LICENSE :: This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
#			  License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any 
#			  later version.
#
#  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty 
#  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License along with this program. This license can also be downloaded from
#  http://crnrstn.evifweb.com/license.txt.  If not, see <http://www.gnu.org/licenses/>

/*
// CLASS :: crnrstn_http_manager
// AUTHOR :: Jonathan 'J5' Harris <jharris@evifweb.com>
// VERSION :: 1.0.0
*/
class crnrstn_http_manager {
	public $httpHeaders;
	private static $httpHeader_ARRAY = array();
	private static $postHttpData;
	private static $getHttpData;
	
	public function __construct() {
	

	}
	
	public function extractData($requestMethod, $name){

		if(isset($requestMethod[$name])){
			return trim($requestMethod[$name]);
		}else{
			return "";	
		}
	}
	
	public function getHeaders ($returnType=NULL){
		self::$httpHeader_ARRAY=headers_list();
		
		switch(strtolower($returnType)){
			case 'array':
				return self::$httpHeader_ARRAY;
			break;		
			default:
				$httpHeaders = "";
				for($i=0;$i<sizeof(self::$httpHeader_ARRAY);$i++){
					$httpHeaders .= self::$httpHeader_ARRAY[$i].',';
				}
				
				// 
				// STRIP TRAILING COMMA
				$httpHeaders = rtrim($httpHeaders, ',');
		
				return $httpHeaders;
			break;
		}
	}
	
	public function issetHTTP ($superGlobal){		
		if(sizeof($superGlobal)>0){
			return true;
		}else{
			return false;
		}
	}
	
	public function issetParam($superGlobal, $param){
		if(isset($superGlobal[$param])){
			if(strlen($superGlobal[$param])>0){
				return true;
			}else{
				return false;
			}
		}else{
			return false;	
		}
	
	}

	public function __destruct() {

	}
}

?>