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


# syslog() Priorities (in descending order)
# Constant		Description
# LOG_EMERG		system is unusable. 
# LOG_ALERT		action must be taken immediately
# LOG_CRIT		critical conditions
# LOG_ERR		error conditions
# LOG_WARNING	warning conditions
# LOG_NOTICE	normal, but significant, condition
# LOG_INFO		informational message
# LOG_DEBUG		debug-level message

#		$errortype = array (
#			E_ERROR              => 'Error',
#			E_WARNING            => 'Warning',
#			E_PARSE              => 'Parsing Error',
#			E_NOTICE             => 'Notice',
#			E_CORE_ERROR         => 'Core Error',
#			E_CORE_WARNING       => 'Core Warning',
#			E_COMPILE_ERROR      => 'Compile Error',
#			E_COMPILE_WARNING    => 'Compile Warning',
#			E_USER_ERROR         => 'User Error',
#			E_USER_WARNING       => 'User Warning',
#			E_USER_NOTICE        => 'User Notice',
#			E_STRICT             => 'Runtime Notice',
#			E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
#		);

/*
// CLASS :: crnrstn_logging
// AUTHOR :: Jonathan 'J5' Harris <jharris@evifweb.com>
// VERSION :: 1.0.0
*/
class crnrstn_logging {
	public $crnrstn_mailer;
	public $emailDataElements = array();
	public $msg_delivery_status;
	
	private static $debugMode;
	public $debugStr;
	
	public function __construct($debugMode=NULL) {
		if(isset($debugMode)){
			self::$debugMode = (int) $debugMode;
		}
	}
					
	public function captureNotice($logSource, $logPriority, $msg){
		$tmp_key = $_SESSION['CRNRSTN_'.crc32($_SESSION['CRNRSTN_CONFIG_SERIAL'])]['CRNRSTN_RESOURCE_KEY'];
		$tmp_configserial = $_SESSION['CRNRSTN_CONFIG_SERIAL'];
		
		switch($logPriority){
			case 0:
				$tmp_priority = "LOG_EMERG :: system is unusable.";
			break;
			case 1:
				$tmp_priority = "LOG_ALERT :: action must be taken immediately";
			break;
			case 2:
				$tmp_priority = "LOG_CRIT :: critical conditions encountered";
			break;
			case 3:
				$tmp_priority = "LOG_ERR :: error conditions encountered";
			break;
			case 4:
				$tmp_priority = "LOG_WARNING :: warning conditions encountered";
			break;
			case 5:
				$tmp_priority = "LOG_NOTICE :: normal, but significant, condition encountered";
			break; 
			case 6:
				$tmp_priority = "LOG_INFO :: informational message";
			break;
			case 7:
				$tmp_priority = "LOG_DEBUG :: debug-level message";
			break;
			default:
				$tmp_priority = "UNKNOWN";
			break;
		}
		
		switch($_SESSION["CRNRSTN_".crc32($tmp_configserial)]["CRNRSTN_".$tmp_key]["_CRNRSTN_LOG_PROFILE"]){
			case 'EMAIL':
				$tmp_email_ARRAY = explode(",", $_SESSION["CRNRSTN_".crc32($tmp_configserial)]["CRNRSTN_".$tmp_key]["_CRNRSTN_LOG_ENDPOINT"]);
				$this->emailDataElements['logSource'] = $logSource;
				$this->emailDataElements['logPriority'] = $tmp_priority;
				$this->emailDataElements['msg'] = $msg;
				
				foreach($tmp_email_ARRAY as $value){
					$this->emailDataElements['addAddressEmail'] = trim($value);

					if($this->buildSimpleMessage()){
						$this->msg_delivery_status = $this->sendSimpleMessage();
					}
					
					switch($this->msg_delivery_status){
						case 'success':
						
							//
							// GOOD JOB
						break;
						default:
						
							//
							// ERROR SENDING EMAIL. LOG TO DEFAULT SYS.
							error_log('Email send fail. Notice output dump-> Source: '.$this->emailDataElements['logSource'].'|| Priority: '.$this->emailDataElements['logPriority'].'|| Message: '.$this->emailDataElements['msg']);
						break;
						
					}
					
					unset($emailDataElements);
					unset($this->msg_delivery_status);
					
				}

			break;
			case 'SCREEN':
				
				print "<br>".$this->getmicrotime()."<br>";
				print $logSource;
				print "<br>";
				print $tmp_priority;
				print "<br>";
				print $msg;
				print "<br>----<br>";
			break;
			case 'FILE':
				$tmp_file_path = $_SESSION["CRNRSTN_".crc32($tmp_configserial)]["CRNRSTN_".$tmp_key]["_CRNRSTN_LOG_ENDPOINT"];
				
				//
				// YOU CAN CUSTOMIZE THE FORMAT OF THIS LOGGING OUTPUT
				$logDataToWrite = $this->getmicrotime()." :: ".'Source: '.$logSource.' || Priority: '.$tmp_priority.' || Message: '.$msg.'
';

				$fp = fopen($tmp_file_path, 'a');
				fwrite($fp, $logDataToWrite);
				fclose($fp);
				
			break;
			default:
				error_log(" :: ".'Source: '.$logSource.' || Priority: '.$tmp_priority.' || Message: '.$msg);
			break;
		}
		
		return true;
	}
	
	public function logDebug($str){
		if(self::$debugMode>0){
			$this->debugStr .= $this->buildDebugOutput($str);
		}
	}
	
	public function clearDebug(){
		
		$this->debugStr = "";
	}
	
	public function transferDebug($str){
		
		//
		// MOVE DEBUG BACK TO CRNRSTN.
		$this->debugStr = $str;
		
	}
	
	public function appendDebug($str){
		$this->debugStr .= $str;
	}
	
	private function buildDebugOutput($str){
		return $this->getmicrotime()." :: ".$str."\n";
	}
	
	private function buildSimpleMessage(){
		
		$this->emailDataElements['subject'] = 'CRNRSTN Suite :: Logging Notice Captured on '.$_SERVER['REMOTE_ADDR'];
		$this->emailDataElements['text'] = 'This is a triggered Logging notification from the CRNRSTN Suite ::.

Information about the notice:\r\n
- - - - - - - - - - - - - - - - - - - -
Source: '.$this->emailDataElements['logSource'].'
Priority: '.$this->emailDataElements['logPriority'].'
Message: '.$this->emailDataElements['msg'].'

- - - - - - - - - - - - - - - - - - - - 

Sending IP Address: '.$_SERVER['REMOTE_ADDR'].'

Please note that this information has not been saved anywhere.
You may want to keep this email for your records.

Cheers!
J5';
			
		$this->emailDataElements['headers']  = "From: System Notice < noreply@".$_SERVER['SERVER_NAME']." >\n";
		$this->emailDataElements['headers'] .= "X-Sender: System Notice < noreply@".$_SERVER['SERVER_NAME']." >\n";
		$this->emailDataElements['headers'] .= 'X-Mailer: PHP/' . phpversion();
		$this->emailDataElements['headers'] .= "X-Priority: 1\n"; // Urgent message!
		$this->emailDataElements['headers'] .= "Return-Path: noreply@".$_SERVER['SERVER_NAME']."\n";
		$this->emailDataElements['headers'] .= "Reply-To: noreply@".$_SERVER['SERVER_NAME']."\n";// Return path for errors
		$this->emailDataElements['headers'] .= "MIME-Version: 1.0\r\n";
		$this->emailDataElements['headers'] .= "Content-Type: text/plain; charset=UTF-8\n";
		
		return true;
		
	}
	
	private function sendSimpleMessage(){
		if(mail($this->emailDataElements['addAddressEmail'], $this->emailDataElements['subject'], $this->emailDataElements['text'], $this->emailDataElements['headers'])){
			
			return "success";
		}else{
			
			return "mailsend error";	
		}
		
		
	}
	
	//
	// METHOD TAKEN FROM NUSOAP.PHP - http://sourceforge.net/projects/nusoap/
	/**
    * returns the time in ODBC canonical form with microseconds
    *
    * @return string The time in ODBC canonical form with microseconds
    * @access public
    */
	private function getmicrotime() {
		if (function_exists('gettimeofday')) {
			$tod = gettimeofday();
			$sec = $tod['sec'];
			$usec = $tod['usec'];
		} else {
			$sec = time();
			$usec = 0;
		}
		return strftime('%Y-%m-%d %H:%M:%S', $sec) . '.' . sprintf('%06d', $usec);
	}
	
	public function __destruct() {
		
	}
}

?>