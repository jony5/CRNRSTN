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
// CLASS :: crnrstn_environmentals
// AUTHOR :: Jonathan 'J5' Harris <jharris@evifweb.com>
// VERSION :: 1.0.0
*/
class crnrstn_environmentals {
	public $configSerial;
	
	private static $oLogger;
	
	public $log_profl_ARRAY = array();
	public $log_endpt_ARRAY = array();
	
	private static $resourceKey;
	
	public $oCRNRSTN_IPSECURITY_MGR;
	public $oSESSION_MGR;
	public $oCOOKIE_MGR;
	public $oMYSQLI_CONN_MGR;
	public $oHTTP_MGR;
	
	private static $sess_env_param_ARRAY = array();
	
	private static $requestProtocol;
	
	public $debugStr = "";
	public $debugMode;
			
	public function __construct($oCRNRSTN,$instanceType=NULL) {
		
		//
		// ROLL OVER DEBUG TRACE FROM CRNRSTN OBJECT AND THEN CONTINUE TO APPEND
		$this->debugMode = $oCRNRSTN->getDebugMode();
		self::$oLogger = new crnrstn_logging($this->debugMode);
		
		self::$oLogger->debugStr = $oCRNRSTN->getDebugStr();
		$oCRNRSTN->clearDebugStr();

		$this->configSerial = $oCRNRSTN->configSerial;
		$this->log_profl_ARRAY = $oCRNRSTN->get_log_profl_ARRAY();
		$this->log_endpt_ARRAY = $oCRNRSTN->get_log_endpt_ARRAY();
		
		$this->oCOOKIE_MGR = new crnrstn_cookie_manager();
		$this->oHTTP_MGR = new crnrstn_http_manager();
		
		if(!($instanceType=='simple_configcheck')){
			
			try{
				
					//
					//	DETERMINE KEY DESIGNATING THE RUNNING ENVIRONMENT, WHERE KEY = CRC32(env key)
					self::$resourceKey = $oCRNRSTN->getServerEnv();
					self::$oLogger->debugStr .= $oCRNRSTN->getDebugStr();
					$oCRNRSTN->clearDebugStr();

					if(self::$resourceKey==""){
						
						//
						// WE DON'T HAVE THE ENVIRONMENT DETECTED. THROW EXCEPTION.
						// HOOOSTON...VE HAF PROBLEM!
						throw new Exception('CRNRSTN environmental configuration error :: unable to detect environment on server '.$_SERVER['SERVER_NAME'].' ('.$_SERVER['SERVER_ADDR'].').');
					}else{
						
						$this->oSESSION_MGR = new crnrstn_session_manager($this);						
						
						$this->oCRNRSTN_IPSECURITY_MGR = clone $oCRNRSTN->oCRNRSTN_IPSECURITY_MGR;
						unset($oCRNRSTN->oCRNRSTN_IPSECURITY_MGR);
						
						// 
						// WE HAVE SELECTED ENVIRONMENT KEY. INITIALIZE. CONFIG KEY AND ENV KEY.
						// FLASH CONFIG KEY AND ENV KEY TO SESSION.
						$this->initRuntimeConfig();
						
						//
						// INITIALIZE ERROR REPORTING FOR THIS ENVIRONMENT
						$this->initializeErrorReporting($oCRNRSTN);						
						
						//
						// INITIALIZE ENVIRONMENTAL LOGGING BEHAVIOR
						$this->initEnvLoggingProfile();
						
						//
						// INITIALIZE IP ADDRESS RESTRICTIONS from grantExclusiveAccess()
						if(isset($oCRNRSTN->grant_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey])){
							$this->initExclusiveAccess($oCRNRSTN);
						}
																		
						//
						// INITIALIZE IP ADDRESS RESTRICTIONS from denyAccess()
						if(isset($oCRNRSTN->deny_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey])){
							$this->initDenyAccess($oCRNRSTN);
						}
						
						//
						// INITIALIZE DATABASE
						$this->oMYSQLI_CONN_MGR = clone $oCRNRSTN->oMYSQLI_CONN_MGR;
						$this->oMYSQLI_CONN_MGR->setEnvironment($this);
						
						//
						// INITIALIZE SESSION ENCRYPTION
						if(isset($oCRNRSTN->opensslSessEncryptCipher[crc32($this->configSerial)][self::$resourceKey])){
							$this->initSessionEncryption($oCRNRSTN);
						}
						
						//
						// INITIALIZE COOKIE ENCRYPTION
						if(isset($oCRNRSTN->opensslCookieEncryptCipher[crc32($this->configSerial)][self::$resourceKey])){
							$this->initCookieEncryption($oCRNRSTN);
							
						}
						
						//
						// BEFORE ALLOCATING ADDITIONAL MEMORY RESOURCES, PROCESS IP AUTHENTICATION
						if(isset($oCRNRSTN->grant_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey]) || isset($oCRNRSTN->deny_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey])){
							self::$oLogger->logDebug("crnrstn_environmentals :: we have IP restrictions to process and apply for CRNRSTN config serial [".$this->configSerial."] and environment key [".self::$resourceKey."].");
							if(!$this->oCRNRSTN_IPSECURITY_MGR->authorizeEnvAccess($this, self::$resourceKey)){
							
								//
								// WE COULD PERHAPS USE A MORE GRACEFUL WAY TO TRANSITION TO ERR...BUT THIS WORKS
								// THE METHOD returnSrvrRespStatus() CONTAINS SOME CUSTOM HTML FOR OUTPUT IF YOU WANT TO TWEAK ITS DESIGN
								// PERHAPS SOME FUTURE RELEASE OF CRNRSTN CAN 
								$this->returnSrvrRespStatus(403);
								exit();
							}
						}else{
							self::$oLogger->logDebug("crnrstn_environmentals :: there are NO IP restrictions to process and apply for CRNRSTN config serial [".$this->configSerial."] and environment key [".self::$resourceKey."].");
						}
						
						//
						// FLASH CRNRSTN CONFIG DEFINED ENV RESOURCES FOR THE DETECTED ENV TO SESSION MEMORY
						$this->initEnvResources($oCRNRSTN);
						
						//
						// END OF CRNRSTN ENVIRONMENTAL CONFIG OPERATION
						self::$oLogger->logDebug("crnrstn_environmentals :: You have reached the end of the CRNRSTN environmental detection and configuration process.");
						
					}
			
				} catch( Exception $e ) {
				
				//
				// SEND THIS THROUGH THE LOGGER OBJECT
				self::$oLogger->captureNotice('oCRNRSTN_ENV->__construct()', LOG_ALERT, $e->getMessage());
				
			}


		}else{
			
			//
			// THIS IS A SIMPLE CONFIG CHECK.
			self::$oLogger->logDebug("crnrstn_environmentals :: __construct() performing simple config check prior to loading of defineEnvResource() in the CRNRSTN config file.");
			
		}
	}
	
	public function isConfigured($oCRNRSTN){
		
		//
		// INSTANTIATE SESSION MANAGER
		if(!isset($this->oSESSION_MGR)){
			$this->oSESSION_MGR = new crnrstn_session_manager($this);
		}
		
		//
		// DO WE HAVE SESSION DATA IN LINE WITH THE CONFIGURATION SERIALIZATION
		if(isset($_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_RESOURCE_KEY'])){
			self::$oLogger->logDebug("crnrstn_environmentals :: sessionid[".session_id()."] has been initialized by CRNRSTN. current value of CRNRSTN_RESOURCE_KEY [".$_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_RESOURCE_KEY']."].");

			//
			// SESSION IS SET
			try{
					//
					// DETERMINE KEY DESIGNATING THE RUNNING ENVIRONMENT, WHERE KEY = CRC32(env key)
					$oCRNRSTN->setServerEnv();
					self::$resourceKey = $oCRNRSTN->getServerEnv();
					self::$oLogger->debugStr .= $oCRNRSTN->getDebugStr();
					$oCRNRSTN->clearDebugStr();

					if(self::$resourceKey==""){
						
						//
						// WE DON'T HAVE THE ENVIRONMENT DETECTED. THROW EXCEPTION.
						// HOOOSTON...VE HAF PROBLEM!
						self::$oLogger->logDebug("crnrstn_environmentals :: ERROR :: unable to detect environment on server.");
						return false;
					}else{
						
						$this->oCRNRSTN_IPSECURITY_MGR = clone $oCRNRSTN->oCRNRSTN_IPSECURITY_MGR;
						unset($oCRNRSTN->oCRNRSTN_IPSECURITY_MGR);

						//					
						// WE HAVE SELECTED ENVIRONMENT KEY. INITIALIZE. CONFIG KEY AND ENV KEY.
						// FLASH CONFIG KEY AND ENV KEY TO SESSION.
						$this->initRuntimeConfig();
						
						//
						// INITIALIZE ERROR REPORTING FOR THIS ENVIRONMENT
						$this->initializeErrorReporting($oCRNRSTN);
						
						//
						// INITIALIZE IP ADDRESS RESTRICTIONS from grantExclusiveAccess()
						if(isset($_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_GRANT_ACCESS_IP'])){
							$this->initExclusiveAccessFromSession();
						}
																		
						//
						// INITIALIZE IP ADDRESS RESTRICTIONS from denyAccess()
						if(isset($_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_DENY_ACCESS_IP'])){
							$this->initDenyAccessFromSession();
						}
						
						//
						// INITIALIZE DATABASE
						$this->oMYSQLI_CONN_MGR = clone $oCRNRSTN->oMYSQLI_CONN_MGR;
						$this->oMYSQLI_CONN_MGR->setEnvironment($this);
						
						//
						// BEFORE ALLOCATING ADDITIONAL MEMORY RESOURCES, PROCESS IP AUTHENTICATION
						if(isset($_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_GRANT_ACCESS_IP']) || isset($_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_DENY_ACCESS_IP'])){
							self::$oLogger->logDebug("crnrstn_environmentals :: we have (from session[".session_id()."]) IP restrictions to process and apply for CRNRSTN config serial [".$this->configSerial."] and environment key [".self::$resourceKey."].");
							if(!$this->oCRNRSTN_IPSECURITY_MGR->authorizeEnvAccess($this, self::$resourceKey)){
							
								//
								// WE COULD PERHAPS USE A MORE GRACEFUL WAY TO TRANSITION TO ERR...BUT THIS WORKS
								// THE METHOD returnSrvrRespStatus() CONTAINS SOME CUSTOM HTML FOR OUTPUT IF YOU WANT TO TWEAK ITS DESIGN
								$this->returnSrvrRespStatus(403);
								exit();
							}
						}else{
							self::$oLogger->logDebug("crnrstn_environmentals :: there are NO IP restrictions to process and apply for CRNRSTN config serial [".$this->configSerial."] and environment key [".self::$resourceKey."].");
						}
						
						//
						// END OF CRNRSTN ENVIRONMENTAL CONFIG OPERATION
						self::$oLogger->logDebug("crnrstn_environmentals :: You have reached the end of the CRNRSTN environmental detection and configuration process. All remaining config data exists in (and will be pulled from) session[".session_id()."] for optimized loading experience.");
						
						return true;
						
					}
			
				} catch( Exception $e ) {
				
				//
				// SEND THIS THROUGH THE LOGGER OBJECT
				self::$oLogger->captureNotice('oCRNRSTN_ENV->isConfigured()', LOG_ALERT, $e->getMessage());
				
			}
			
		}else{
			
			//
			// NO SESSION SET
			self::$oLogger->logDebug("crnrstn_environmentals :: session[".session_id()."] has not been initialized with CRNRSTN configuration yet. process all config parameters and initialize.");
			return false;
		}
		
	}
	
	public function getEnvKey(){
		return self::$resourceKey;
	}
	
	public function getEnvSerial(){
		return 	$this->configSerial;
	}
	
	
	private function initEnvLoggingProfile(){

		//
		// INITIALIZE SESSION PARAMS FOR LOGGING FUNCTIONALITY 
		$_SESSION["CRNRSTN_".crc32($this->configSerial)]["CRNRSTN_".self::$resourceKey]["_CRNRSTN_LOG_PROFILE"] = $this->log_profl_ARRAY[crc32($this->configSerial)][self::$resourceKey];
		$_SESSION["CRNRSTN_".crc32($this->configSerial)]["CRNRSTN_".self::$resourceKey]["_CRNRSTN_LOG_ENDPOINT"] = $this->log_endpt_ARRAY[crc32($this->configSerial)][self::$resourceKey];
		
		self::$oLogger->logDebug("crnrstn_environmentals :: CRNRSTN logging initialized to sessionid[".session_id()."] as _CRNRSTN_LOG_PROFILE[".$this->log_profl_ARRAY[crc32($this->configSerial)][self::$resourceKey]."]  _CRNRSTN_LOG_ENDPOINT[".$this->log_endpt_ARRAY[crc32($this->configSerial)][self::$resourceKey]."].");
	}
	
	
	private function initRuntimeConfig(){
		
		//
		// INITIALIZE CONFIG AND ENV KEYS.
		$_SESSION['CRNRSTN_CONFIG_SERIAL'] = $this->configSerial;
		$_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_RESOURCE_KEY'] = self::$resourceKey;		
		self::$oLogger->logDebug("crnrstn_environmentals :: initialize session[".session_id()."] with CRNRSTN config serial [".$this->configSerial."] and environmental resource key [".self::$resourceKey."].");
		
	}
	
	private function initializeErrorReporting($oCRNRSTN){
		self::$oLogger->logDebug("crnrstn_environmentals :: initialize server error_reporting() to [".$oCRNRSTN->handle_env_ARRAY[crc32($this->configSerial)][self::$resourceKey]."].");
		error_reporting($oCRNRSTN->handle_env_ARRAY[crc32($this->configSerial)][self::$resourceKey]);
	}
	
	private function initExclusiveAccess($oCRNRSTN){
		
		//
		// PROCESS IP ADDRESS ACCESS AND RESTRICTION FOR SELECTED ENVIRONMENT
		if(is_file($oCRNRSTN->grant_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey])){
			
			//
			// SAVE TO SESSION FOR FUTURE RETRIEVAL
			$_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_GRANT_ACCESS_IP'] = $oCRNRSTN->grant_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey];

			//
			// EXTRACT ACCESS-BY-IP AUTHORIZATION PROFILE FROM FILE
			self::$oLogger->logDebug("crnrstn_environmentals :: we have a file to include and process for exclusive access IP restrictions at [".$oCRNRSTN->grant_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey]."].");
			include_once($oCRNRSTN->grant_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey]);
			
		}else{
			
			//
			// DO WE HAVE ANY IP DATA TO PROCESS
			if($oCRNRSTN->grant_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey] != ""){
				
				//
				// SAVE TO SESSION FOR FUTURE RETRIEVAL
				$_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_GRANT_ACCESS_IP'] = $oCRNRSTN->grant_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey];
				self::$oLogger->logDebug("crnrstn_environmentals :: process grant exclusive access IP[".$oCRNRSTN->grant_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey]."] for this connection after updating session.");
				$this->oCRNRSTN_IPSECURITY_MGR->grantAccessWKey(self::$resourceKey, $oCRNRSTN->grant_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey]);
			}else{
					//
					// NO IP ADDRESSES PROVIDED. NOTHING TO DO HERE.
			}
		}
	}
	
	private function initExclusiveAccessFromSession(){
		
		//
		// PROCESS IP ADDRESS ACCESS AND RESTRICTION FOR SELECTED ENVIRONMENT
		if(is_file($_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_GRANT_ACCESS_IP'])){
			
			//
			// EXTRACT ACCESS-BY-IP AUTHORIZATION PROFILE FROM FILE
			self::$oLogger->logDebug("crnrstn_environmentals :: (from session[".session_id()."]) we have a file to include and process for exclusive access IP restrictions at [".$_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_GRANT_ACCESS_IP']."].");
			include_once($_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_GRANT_ACCESS_IP']);
			
		}else{
			
			// 
			// DO WE HAVE IP DATA TO PROESS
			if($_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_GRANT_ACCESS_IP'] != ""){
				self::$oLogger->logDebug("crnrstn_environmentals :: (from session[".session_id()."]) process grant exclusive access IP[".$_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_GRANT_ACCESS_IP']."] for this connection.");
				$this->oCRNRSTN_IPSECURITY_MGR->grantAccessWKey(self::$resourceKey, $_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_GRANT_ACCESS_IP']);
			}else{
				//
				// NO IP ADDRESSES PROVIDED. NOTHING TO DO HERE.
			}
		}
	}
	
	private function initDenyAccess($oCRNRSTN){
		if(is_file($oCRNRSTN->deny_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey])){
			
			//
			// SAVE TO SESSION FOR LATER RETRIEVAL
			$_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_DENY_ACCESS_IP'] = $oCRNRSTN->deny_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey];
			
			//
			// EXTRACT ACCESS-BY-IP AUTHORIZATION PROFILE FROM FILE
			self::$oLogger->logDebug("crnrstn_environmentals :: we have a file to include and process for deny access IP restrictions at [".$oCRNRSTN->deny_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey]."].");
			include_once($oCRNRSTN->deny_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey]);
				
		}else{
			if($oCRNRSTN->deny_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey] != ""){
				
				//
				// SAVE TO SESSION FOR LATER RETRIEVAL
				$_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_DENY_ACCESS_IP'] = $oCRNRSTN->deny_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey];
				
				self::$oLogger->logDebug("crnrstn_environmentals :: process deny access IP[".$oCRNRSTN->deny_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey]."] for this connection.");
				$this->oCRNRSTN_IPSECURITY_MGR->denyAccessWKey(self::$resourceKey, $oCRNRSTN->deny_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey]);
			}else{
				
				//
				// NO IP ADDRESSES PROVIDED. NOTHING TO DO HERE.
			}		
		}		
	}
	
	private function initDenyAccessFromSession(){
		if(is_file($_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_DENY_ACCESS_IP'])){
			
			//
			// EXTRACT ACCESS-BY-IP AUTHORIZATION PROFILE FROM FILE
			self::$oLogger->logDebug("crnrstn_environmentals :: we have (from session[".session_id()."]) a file to include and process for deny access IP restrictions at [".$_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_DENY_ACCESS_IP']."].");
			include_once($_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_DENY_ACCESS_IP']);
				
		}else{
			if($_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_DENY_ACCESS_IP'] != ""){
				self::$oLogger->logDebug("crnrstn_environmentals :: process (from session[".session_id()."]) deny access IP[".$_SESSION['CRNRSTN_'.crc32($this->configSerial)]['CRNRSTN_DENY_ACCESS_IP']."] for this connection.");
				$this->oCRNRSTN_IPSECURITY_MGR->denyAccessWKey(self::$resourceKey, $oCRNRSTN->deny_accessIP_ARRAY[crc32($this->configSerial)][self::$resourceKey]);
			}else{
				//
				// NO IP ADDRESSES PROVIDED. NOTHING TO DO HERE.
			}		
		}		
	}	
	
	public function returnResouceKey(){
		
		//
		// RETURN RESOURCE KEY FOR DETECTED ENVIRONMENT
		return 	self::$resourceKey;
	}
	
	public function initSessionEncryption($oCRNRSTN){
		
		//
		// TRANSFER SESSION ENCRYPT PARAMS TO SESSION
		$_SESSION["CRNRSTN_".crc32($this->configSerial)]["CRNRSTN_".self::$resourceKey]["_CRNRSTN_SESS_ENCRYPT_CIPHER"] = $oCRNRSTN->opensslSessEncryptCipher[crc32($this->configSerial)][self::$resourceKey];
		$_SESSION["CRNRSTN_".crc32($this->configSerial)]["CRNRSTN_".self::$resourceKey]["_CRNRSTN_SESS_ENCRYPT_SECRET_KEY"] = $oCRNRSTN->opensslSessEncryptSecretKey[crc32($this->configSerial)][self::$resourceKey];
		$_SESSION["CRNRSTN_".crc32($this->configSerial)]["CRNRSTN_".self::$resourceKey]["_CRNRSTN_SESS_ENCRYPT_OPTIONS"] = $oCRNRSTN->opensslSessEncryptOptions[crc32($this->configSerial)][self::$resourceKey];
		$_SESSION["CRNRSTN_".crc32($this->configSerial)]["CRNRSTN_".self::$resourceKey]["_CRNRSTN_SESS_ENCRYPT_HMAC_ALG"] = $oCRNRSTN->sessHmac_algorithm[crc32($this->configSerial)][self::$resourceKey];
		
		self::$oLogger->logDebug("crnrstn_environmentals :: session encryption configured to _CRNRSTN_SESS_ENCRYPT_CIPHER[".$oCRNRSTN->opensslSessEncryptCipher[crc32($this->configSerial)][self::$resourceKey]."] _CRNRSTN_SESS_ENCRYPT_HMAC_ALG[".$oCRNRSTN->sessHmac_algorithm[crc32($this->configSerial)][self::$resourceKey]."].");
		
	}
	
	public function initCookieEncryption($oCRNRSTN){
		
		//
		// TRANSFER COOKIE ENCRYPT PARAMS TO SESSION
		$_SESSION["CRNRSTN_".crc32($this->configSerial)]["CRNRSTN_".self::$resourceKey]["_CRNRSTN_COOKIE_ENCRYPT_CIPHER"] = $oCRNRSTN->opensslCookieEncryptCipher[crc32($this->configSerial)][self::$resourceKey];
		$_SESSION["CRNRSTN_".crc32($this->configSerial)]["CRNRSTN_".self::$resourceKey]["_CRNRSTN_COOKIE_ENCRYPT_SECRET_KEY"] = $oCRNRSTN->opensslCookieEncryptSecretKey[crc32($this->configSerial)][self::$resourceKey];
		$_SESSION["CRNRSTN_".crc32($this->configSerial)]["CRNRSTN_".self::$resourceKey]["_CRNRSTN_COOKIE_ENCRYPT_OPTIONS"] = $oCRNRSTN->opensslCookieEncryptOptions[crc32($this->configSerial)][self::$resourceKey];
		$_SESSION["CRNRSTN_".crc32($this->configSerial)]["CRNRSTN_".self::$resourceKey]["_CRNRSTN_COOKIE_ENCRYPT_HMAC_ALG"] = $oCRNRSTN->cookieHmac_algorithm[crc32($this->configSerial)][self::$resourceKey];
		
		self::$oLogger->logDebug("crnrstn_environmentals :: cookie encryption configured to _CRNRSTN_COOKIE_ENCRYPT_CIPHER[".$oCRNRSTN->opensslCookieEncryptCipher[crc32($this->configSerial)][self::$resourceKey]."] _CRNRSTN_COOKIE_ENCRYPT_HMAC_ALG[".$oCRNRSTN->cookieHmac_algorithm[crc32($this->configSerial)][self::$resourceKey]."].");
		
	}
	
	private function initEnvResources($oCRNRSTN){
		
		//
		// ITERATE THROUGH handle_resource_ARRAY TO EXTRACT ENV SPECIFIC USER DEFINED PARAMS
		// TRANSFER DATA (JUST FOR THE RUNNING ENV) FROM oCRNRSTN RESOURCE ARRAY TO SESSION
		$this->getHandle_resource_ARRAY = $oCRNRSTN->getHandle_resource_ARRAY();
		$tmp_envkey = $this->oSESSION_MGR->getSessionKey();
		foreach($this->getHandle_resource_ARRAY[crc32($this->configSerial)][$tmp_envkey] as $key=>$value){
			self::$oLogger->logDebug("crnrstn_environmentals :: initializing session[".session_id()."] with resource [".$key."] receiving value [".$value."] for environmental key [".$tmp_envkey."].");
			$this->oSESSION_MGR->setSessionParam($key, $value);
		}
		
		//
		// INITIALIZE SESSION WITH ANY WILDCARDS
		if(isset($this->getHandle_resource_ARRAY[crc32($this->configSerial)][crc32('*')])){
			foreach($this->getHandle_resource_ARRAY[crc32($this->configSerial)][crc32('*')] as $key=>$value){
				self::$oLogger->logDebug("crnrstn_environmentals :: initializing session[".session_id()."] with resource [*] receiving value [".$value."] for environmental key [".$tmp_envkey."].");
				$this->oSESSION_MGR->setSessionParam($key, $value);
			}
		}
		
	}
	
	public function returnSrvrRespStatus($errorCode){
		
		//
		// Source: http://php.net/manual/en/function.http-response-code.php
		// Source of source: Wikipedia "List_of_HTTP_status_codes"
		$http_status_codes = array(100 => "Continue", 101 => "Switching Protocols", 102 => "Processing", 200 => "OK", 201 => "Created", 202 => "Accepted", 203 => "Non-Authoritative Information", 204 => "No Content", 205 => "Reset Content", 206 => "Partial Content", 207 => "Multi-Status", 300 => "Multiple Choices", 301 => "Moved Permanently", 302 => "Found", 303 => "See Other", 304 => "Not Modified", 305 => "Use Proxy", 306 => "(Unused)", 307 => "Temporary Redirect", 308 => "Permanent Redirect", 400 => "Bad Request", 401 => "Unauthorized", 402 => "Payment Required", 403 => "Forbidden", 404 => "Not Found", 405 => "Method Not Allowed", 406 => "Not Acceptable", 407 => "Proxy Authentication Required", 408 => "Request Timeout", 409 => "Conflict", 410 => "Gone", 411 => "Length Required", 412 => "Precondition Failed", 413 => "Request Entity Too Large", 414 => "Request-URI Too Long", 415 => "Unsupported Media Type", 416 => "Requested Range Not Satisfiable", 417 => "Expectation Failed", 418 => "I'm a teapot", 419 => "Authentication Timeout", 420 => "Enhance Your Calm", 422 => "Unprocessable Entity", 423 => "Locked", 424 => "Failed Dependency", 424 => "Method Failure", 425 => "Unordered Collection", 426 => "Upgrade Required", 428 => "Precondition Required", 429 => "Too Many Requests", 431 => "Request Header Fields Too Large", 444 => "No Response", 449 => "Retry With", 450 => "Blocked by Windows Parental Controls", 451 => "Unavailable For Legal Reasons", 494 => "Request Header Too Large", 495 => "Cert Error", 496 => "No Cert", 497 => "HTTP to HTTPS", 499 => "Client Closed Request", 500 => "Internal Server Error", 501 => "Not Implemented", 502 => "Bad Gateway", 503 => "Service Unavailable", 504 => "Gateway Timeout", 505 => "HTTP Version Not Supported", 506 => "Variant Also Negotiates", 507 => "Insufficient Storage", 508 => "Loop Detected", 509 => "Bandwidth Limit Exceeded", 510 => "Not Extended", 511 => "Network Authentication Required", 598 => "Network read timeout error", 599 => "Network connect timeout error");
		
		header('HTTP/1.1 '.$errorCode.' '.$http_status_codes[$errorCode]);
		
		echo '<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>'.$errorCode.' '.$http_status_codes[$errorCode].'</title>
<style type="text/css">
<!--
body{margin:0;font-size:.7em;font-family:Verdana, Arial, Helvetica, sans-serif;background:#EEEEEE;}
fieldset{padding:0 15px 10px 15px;} 
h1{font-size:2.4em;margin:0;color:#FFF;}
h2{font-size:1.7em;margin:0;color:#CC0000;} 
h3{font-size:1.2em;margin:10px 0 0 0;color:#000000;} 
#header{width:96%;margin:0 0 0 0;padding:6px 2% 6px 2%;font-family:"trebuchet MS", Verdana, sans-serif;color:#FFF;
background-color:#555555;}
#content{margin:0 0 0 2%;position:relative;}
.content-container{background:#FFF;width:96%;margin-top:8px;padding:10px;position:relative;}
-->
</style>
</head>
<body>
<div id="header"><h1>Server Error</h1></div>
<div id="content">
 <div class="content-container"><fieldset>
  <h2>'.$errorCode.' '.$http_status_codes[$errorCode].'</h2>
 </fieldset></div>
</div>
</body>
</html>';
		
		exit();
	}
	
	public function getEnvParam($paramName){
		
		if(!isset(self::$sess_env_param_ARRAY[$paramName])){
			self::$sess_env_param_ARRAY[$paramName] = $this->pullFromSession($paramName);
		}
		
		return self::$sess_env_param_ARRAY[$paramName];
	}
	
	private function pullFromSession($key) {

		//
		// PULL FROM SESSION CACHE
		return $this->oSESSION_MGR->getSessionParam($key);
	}

	/**
	* @see http://php.net/manual/en/function.openssl-encrypt.php
	*/
	public function openssl_get_cipher_methods(){
		$ciphers             = openssl_get_cipher_methods();
		$ciphers_and_aliases = openssl_get_cipher_methods(true);
		$cipher_aliases      = array_diff($ciphers_and_aliases, $ciphers);
		
		//
		// ECB MODE SHOULD BE AVOIDED
		$ciphers = array_filter( $ciphers, function($n) { return stripos($n,"ecb")===FALSE; } );
		
		//
		// AT LEAST AS EARLY AS AUG 2016, OPENSSL DECLARED THE FOLLOWING WEAK: RC2, RC4, DES, 3DES, MD5 based
		$ciphers = array_filter( $ciphers, function($c) { return stripos($c,"des")===FALSE; } );
		$ciphers = array_filter( $ciphers, function($c) { return stripos($c,"rc2")===FALSE; } );
		$ciphers = array_filter( $ciphers, function($c) { return stripos($c,"rc4")===FALSE; } );
		$ciphers = array_filter( $ciphers, function($c) { return stripos($c,"md5")===FALSE; } );
		$cipher_aliases = array_filter($cipher_aliases,function($c) { return stripos($c,"des")===FALSE; } );
		$cipher_aliases = array_filter($cipher_aliases,function($c) { return stripos($c,"rc2")===FALSE; } );
		$mergedCiphers = array_merge($ciphers,$cipher_aliases);
		
		return $mergedCiphers;
		
	}
	
	//
	// RETURN HTTP/S PATH OF CURRENT SCRIPT
	public function currentLocation(){
		if(isset($_SERVER['HTTPS'])){
			if($_SERVER['HTTPS']){
				self::$requestProtocol='https://';
			}else{
				self::$requestProtocol='http://';
			}
		}else{
			self::$requestProtocol='http://';
		}
		
		return self::$requestProtocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	
	public function getDebug(){
		
		return 	self::$oLogger->debugStr;
	}
	
	public function __destruct() {
		
	}
}

?>