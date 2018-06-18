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
// CLASS :: crnrstn_soap_manager
// AUTHOR :: Jonathan 'J5' Harris <jharris@evifweb.com>
// VERSION :: 1.0.0
*/
class crnrstn_soap_manager {
	public $result;
	
	private static $cache;
	private static $wsdl;
	private static $client;
	private static $err;
	
	private static $tmpWSDL;
	private static $tmpTTL;
	private static $tmpCURL;
	private static $oLogger;
	private static $oSoapEnvironment;
	
	public function __construct($userEnv,$wsdl_uri_key,$cache_ttl_key,$useCURL_key) {
	
		self::$oSoapEnvironment = $userEnv;
		
		//
		// INSTANTIATE LOGGER
		self::$oLogger = new crnrstn_logging();
		
		//
		// INITIALIZE THE WSDL
		self::$tmpWSDL = self::$oSoapEnvironment->getEnvParam($wsdl_uri_key);
		self::$tmpTTL =  self::$oSoapEnvironment->getEnvParam($cache_ttl_key);
		self::$tmpCURL = self::$oSoapEnvironment->getEnvParam($useCURL_key);
		if(self::$tmpWSDL!=self::$oSoapEnvironment->currentLocation()){	// AVOID INIFINITE LOOP WHERE WEB SERVICE STANDS ON CRNRSTN
			try{

				self::$cache = new wsdlcache('.',self::$tmpTTL);
				self::$wsdl = self::$cache->get(self::$tmpWSDL);
				
				if (is_null(self::$wsdl)) {
					self::$wsdl = new wsdl(self::$tmpWSDL);
					
					self::$err = self::$wsdl->getError();
					if (self::$err) {
						
						//
						// HOOOSTON...VE HAF PROBLEM!
						throw new Exception('WSDL Constructor Error :: '.self::$err.' :: WSDL :: '.self::$tmpWSDL);
					}
					
					self::$cache->put(self::$wsdl);
					
				} else {
					self::$wsdl->clearDebug();
					self::$wsdl->debug('Retrieved from cache');
				}
				
				//
				// INSTANTIATE SOAP CLIENT
				self::$client = new nusoap_client(self::$wsdl, true);
	
				self::$err = self::$client->getError();
				if (self::$err) {
					
					//
					// HOOOSTON...VE HAF PROBLEM!
					throw new Exception('SOAP Client Constructor Error :: '.self::$err);
				}
				
				self::$client->setUseCurl(self::$oSoapEnvironment->getEnvParam(self::$tmpCURL));
				
			} catch ( Exception $e ) {
				
				//
				// SEND THIS THROUGH THE LOGGER OBJECT
				self::$oLogger->captureNotice('CRNRSTN Error Notification :: soap initialization failed', LOG_NOTICE, $e->getMessage());
				
				return false;
			}
		}
	}
	
	//
	// RECEIVE METHOD NAME + PARAMETERS AND SEND SOAP REQUEST TO WEB SERVICE
	public function returnContent($methodName,$params){
		
		//
		// SEND SOAP REQUEST
		try{
			$this->result = self::$client->call($methodName, $params);
			
			//
			// CHECK FOR A FAULT
			if (self::$client->fault) {
				
				//
				// HOOOSTON...VE HAF PROBLEM!
				throw new Exception('SOAP Client returnContent() Fault :: '.$this->result);
				
			} else {
				
				//
				// CHECK FOR ERRORS
				self::$err = self::$client->getError();
				
				if (self::$err) {
					
					//
					// HOOOSTON...VE HAF PROBLEM!
					throw new Exception('SOAP Client returnContent() Error :: '.self::$err);
					
				} else {
					
					//
					// RETURN RESULT
					return $this->result;			
				}
			}
			
		}catch( Exception $e ) {
			
			//
			// SEND THIS THROUGH THE LOGGER OBJECT
			self::$oLogger->captureNotice('session_manager->returnContent()', LOG_ERR, $e->getMessage());
		}
		
		return $this->result;
	}
	
	public function returnFault(){
		return self::$client->fault;
	}
	
	public function returnError(){
		return self::$client->getError();
	}
	
	public function returnResult(){
		return $this->result;
	}
	
	public function returnClientRequest(){
		return self::$client->request;
	}
	
	public function returnClientResponse(){
		return self::$client->response;
	}
	
	public function returnClientGetDebug(){
		return self::$client->getDebug();
	}
	
	public function __destruct() {

	}
}

?>