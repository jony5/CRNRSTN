<?php
/* 
// J5
// Code is Poetry */
if ( ! session_id() ) @ session_start();

//
// CRNRSTN CLASS INCLUDES ::
require('./_crnrstn.root.inc.php');
require($CRNRSTN_ROOT.'/_crnrstn/class/crnrstn/crnrstn.inc.php');			// CRNRSTN
require($CRNRSTN_ROOT.'/_crnrstn/class/logging/crnrstn.log.inc.php');			// LOGGING
require($CRNRSTN_ROOT.'/_crnrstn/class/environmentals/crnrstn.env.inc.php');		// ENVIRONMENTALS
require($CRNRSTN_ROOT.'/_crnrstn/class/security/crnrstn.ipauthmgr.inc.php');		// SECURITY
require($CRNRSTN_ROOT.'/_crnrstn/class/database/mysqli/crnrstn.mysqli.inc.php');	// DATABASE
require($CRNRSTN_ROOT.'/_crnrstn/class/soa/nusoap/nusoap.php');				// NUSOAP (3RD PARTY CLIENT/SERVER SOAP) http://sourceforge.net/projects/nusoap/
require($CRNRSTN_ROOT.'/_crnrstn/class/soa/nusoap/class.wsdlcache.php');		// NUSOAP (3RD PARTY CLIENT/SERVER SOAP) http://sourceforge.net/projects/nusoap/
require($CRNRSTN_ROOT.'/_crnrstn/class/soa/crnrstn.soap.inc.php');			// SOAP MANAGEMENT
require($CRNRSTN_ROOT.'/_crnrstn/class/session/crnrstn.session.inc.php');		// SESSION MANAGEMENT
require($CRNRSTN_ROOT.'/_crnrstn/class/session/crnrstn.cookie.inc.php');		// COOKIE MANAGEMENT
require($CRNRSTN_ROOT.'/_crnrstn/class/session/crnrstn.http.inc.php');			// HTTP MANAGEMENT


//
// SET DEBUG MODE [0=OFF, 1=ON]
$CRNRSTN_debugMode = 1; 

//
// INSTANTIATE AN INSTANCE OF CRNRSTN BY PASSING A SERIALIZATION KEY FOR THIS CONFIG FILE.
$oCRNRSTN = new crnrstn('s3ria1izati0n-k3yz', $CRNRSTN_debugMode);

##
# REFERENCE OF ERROR LEVEL CONSTANTS
# http://php.net/error-reporting
/*
The error level constants are always available as part of the PHP core.
; E_ALL             - All errors and warnings (includes E_STRICT as of PHP 6.0.0)
; E_ERROR           - fatal run-time errors
; E_RECOVERABLE_ERROR  - almost fatal run-time errors
; E_WARNING         - run-time warnings (non-fatal errors)
; E_PARSE           - compile-time parse errors
; E_NOTICE          - run-time notices (these are warnings which often result
;                     from a bug in your code, but it's possible that it was
;                     intentional (e.g., using an uninitialized variable and
;                     relying on the fact it's automatically initialized to an
;                     empty string)
; E_STRICT          - run-time notices, enable to have PHP suggest changes
;                     to your code which will ensure the best interoperability
;                     and forward compatibility of your code
; E_CORE_ERROR      - fatal errors that occur during PHP's initial startup
; E_CORE_WARNING    - warnings (non-fatal errors) that occur during PHP's
;                     initial startup
; E_COMPILE_ERROR   - fatal compile-time errors
; E_COMPILE_WARNING - compile-time warnings (non-fatal errors)
; E_USER_ERROR      - user-generated error message
; E_USER_WARNING    - user-generated warning message
; E_USER_NOTICE     - user-generated notice message
; E_DEPRECATED      - warn about code that will not work in future versions
;                     of PHP
; E_USER_DEPRECATED - user-generated deprecation warnings

; Common Values for error reporting:
;   	E_ALL (Show all errors, warnings and notices including coding standards.)
;   	E_ALL & ~E_NOTICE  (Show all errors, except for notices)
;   	E_ALL & ~E_NOTICE & ~E_STRICT  (Show all errors, except for notices and coding standards warnings.)
;   	E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR  (Show only errors)
;
; Default Value: E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED
; Development Value: E_ALL
; Production Value: E_ALL & ~E_DEPRECATED & ~E_STRICT
*/

# # #
# INITIALIZE A KEY + ERROR REPORTING FOR EACH APPLICATION DEV/HOSTING ENVIRONMENT ::
# PARAMETER 1 [environment-key] = A KEY TO REPRESENT EACH ENVIRONMENT THAT WILL RUN THIS INSTANTIATION OF CRNRSTN
# PARAMETER 2 [error-reporting-constants] = ERROR REPORTING PROFILE
#
#$oCRNRSTN->addEnvironment([environment-key], [error-reporting-constants]);
$oCRNRSTN->addEnvironment('LOCALHOST_PC', E_ALL & ~E_NOTICE & ~E_STRICT);
$oCRNRSTN->addEnvironment('LOCALHOST_MAC', E_ALL);
$oCRNRSTN->addEnvironment('BLUEHOST_2018', E_ALL & ~E_NOTICE & ~E_STRICT);

# # #
# INITIALIZE DATABASE FUNCTIONALITY FOR EACH ENVIRONMENT. 2 WAYS TO USE THIS METHOD.
# FORMAT ONE (1) :: $oCRNRSTN->addDatabase([environment-key], [path-to-db-configuration-file]);
$oCRNRSTN->addDatabase('LOCALHOST_PC', 'C://DATA_GOVT_SURVEILLANCE//_wwwroot//xampp//htdocs//crnrstn//config.database.secure//_crnrstn.db.config.inc.php');
// $oCRNRSTN->addDatabase('LOCALHOST_MAC', '/var/www/html/woodford/config.database.secure/_crnrstn.db.config.inc.php');
$oCRNRSTN->addDatabase('BLUEHOST_2018', '/home3/evifwebc/public_html/woodford.evifweb/config.database.secure/_crnrstn.db.config.inc.php');

# FORMAT TWO (2) :: $oCRNRSTN->addDatabase([environment-key], [db-host], [db-user-name], [db-user-pswd], [db-database-name], [optional-db-port]);
// $oCRNRSTN->addDatabase('LOCALHOST_PC', 'mx.localhost.com', 'crnrstn_assets', '222222222222222', 'db_crnrstn_assets', 80);
// $oCRNRSTN->addDatabase('LOCALHOST_MAC', 'mx.localhost.com', 'crnrstn_posts', '33333333333333', 'db_crnrstn_posts', 80);
$oCRNRSTN->addDatabase('LOCALHOST_MAC', 'localhost', 'crnrstn_stage', 'J7mBqBWGt5wag1kz', 'crnrstn_stage','3306');
// $oCRNRSTN->addDatabase('BLUEHOST_2018', 'mx.localhost.com', 'crnrstn_demo', '44444444444444', 'db_crnrstn_demo', 80);

//
// SESSION OPTIMIZATION FOR ACCELERATION OF CRNRSTN CONFIGURATION
$oCRNRSTN_ENV = new crnrstn_environmentals($oCRNRSTN,'simple_configcheck');
if(!$oCRNRSTN_ENV->isConfigured($oCRNRSTN)){

	//
	// TRANSFER LOG DEBUG OUTPUT TO oCRNTSTN FROM oCRNRSTN_ENV FOR SAFE KEEPING FOR THE TIME BEING
	$oCRNRSTN->debugTransfer($oCRNRSTN_ENV->getDebug());
	unset($oCRNRSTN_ENV);
	
	# # #
	# INITIALIZE LOGGING PROFILE FOR EACH ENVIRONMENT.
	# $oCRNRSTN->initLogging([environment-key], [logging-constant], [additional-logging-detail]);
	#
	# where [logging-constant] = "DEFAULT", "SCREEN", "EMAIL" or "FILE". NULL is DEFAULT.
	
	# e.g. LOGGING TO SCREEN
	# $oCRNRSTN->initLogging('CYEXX_JONY5', 'SCREEN');
	
	# e.g. LOGGING TO EMAIL
	# $oCRNRSTN->initLogging('CYEXX_JONY5', 'EMAIL', 'email_one@address.com, email_two@address.com, email_n@address.com');
	
	# e.g. LOGGING TO FILE (SYSTEM DEFAULT or CUSTOM)
	# $oCRNRSTN->initLogging('CYEXX_JONY5');										// SYSTEM DEFAULT ERROR LOGGING MECHANISMS USED
	# $oCRNRSTN->initLogging('CYEXX_JONY5', 'DEFAULT');								// SYSTEM DEFAULT ERROR LOGGING MECHANISMS USED
	# $oCRNRSTN->initLogging('CYEXX_JONY5', 'FILE', '/var/logFolder/log.txt');		// INCLUDE PATH + FILENAME FOR CUSTOM LOG FILE
	
	$oCRNRSTN->initLogging('BLUEHOST_2018', 'EMAIL','email_one@address.com');						// EMAIL LOG INFO
	$oCRNRSTN->initLogging('LOCALHOST_PC', 'SCREEN');												// OUTPUT LOG INFO TO SCREEN
	# $oCRNRSTN->initLogging('LOCALHOST_PC', 'EMAIL','email1@domain.com,email2@domain.com');		// EMAIL LOG INFO TO LIST OF COMMA DELIMITED EMAIL ACCOUNTS
	# $oCRNRSTN->initLogging('LOCALHOST_MAC', 'FILE','/var/www/html/woodford/customlogs.txt');				// PATH TO FOLDER + FILE WHERE LOG DATA WILL BE APPENDED
	$oCRNRSTN->initLogging('LOCALHOST_MAC', 'DEFAULT');									// SYSTEM DEFAULT ERROR LOGGING
	
	# # #
	# INITIALIZE SECURITY PROTOCOLS FOR EXCLUSIVE RESOURCE ACCESS. 2 FORMATS.
	# FORMAT 1. PASS IN ENVIRONMENT KEY AND PATH TO CONFIGURED CRNRSTN IP AUTHENTICATION MANAGER CONFIG FILE ON THE SERVER.
	# $oCRNRSTN->grantExclusiveAccess([environment-key], [path-to-db-configuration-file]);
	$oCRNRSTN->grantExclusiveAccess('LOCALHOST_PC', 'C://DATA_GOVT_SURVEILLANCE//_wwwroot//xampp//htdocs//crnrstn//config.ipauthmgr.secure//_crnrstn.ipauthmgr.config.inc.php');
	// $oCRNRSTN->grantExclusiveAccess('LOCALHOST_MAC', '/var/www/html/woodford/config.ipauthmgr.secure/grantexclusiveaccess/_crnrstn.ipauthmgr.config.inc.php');
	// $oCRNRSTN->grantExclusiveAccess('BLUEHOST_2018', '/home2/jony5/woodford.jony5.com/config.ipauthmgr.secure/grantexclusiveaccess/_crnrstn.ipauthmgr.config.inc.php');
	
	# FORMAT 2. PASS IN ENVIRONMENT KEY AND IP ADDRESS (OR COMMA DELIMITED LIST OF IPv4 or IPv6 (testing needed) IPs)
	# $oCRNRSTN->grantExclusiveAccess([environment-key], [comma-delimited-list-of-IPs]);
	# $oCRNRSTN->grantExclusiveAccess('LOCALHOST_MAC','192.168.172.*,192.168.173.*,192.168.174.3,172.16.110.1');
	# $oCRNRSTN->grantExclusiveAccess('LOCALHOST_PC','127.*');
	# $oCRNRSTN->grantExclusiveAccess('LOCALHOST_PC','127.0.0.1, 127.*, 130.51.10.*');
	$oCRNRSTN->grantExclusiveAccess('LOCALHOST_PC','127.0.0.1, 130.*, 130.51.10.*, FE80::230:80FF:FEF3:4701');
	
	# # #
	# INITIALIZE SECURITY PROTOCOLS FOR RESOURCE DENIAL. 2 FORMATS.
	# FORMAT 1. PASS IN ENVIRONMENT KEY AND PATH TO THE "DENY ACCESS" CONFIG FILE ON THE SERVER. (see /config.ipauthmgr.secure/denyaccess/_crnrstn.ipauthmgr.config.inc.php)
	# $oCRNRSTN->denyAccess([environment-key], [path-to-ip-authorization-configuration-file]);
	// $oCRNRSTN->denyAccess('LOCALHOST_PC', 'C://DATA_GOVT_SURVEILLANCE//_wwwroot//xampp//htdocs//jony5.com//_crnrstn//config.ipauthmgr.secure//_crnrstn.ipauthmgr.config.inc.php');
	$oCRNRSTN->denyAccess('LOCALHOST_MAC', '/var/www/html/woodford/config.ipauthmgr.secure/denyaccess/_crnrstn.ipauthmgr.config.inc.php');
	
	# FORMAT 2. PASS IN ENVIRONMENT KEY AND IP ADDRESS (OR COMMA DELIMITED LIST OF IPv4 or IPv6 (testing needed) IPs)
	# $oCRNRSTN->denyAccess('BLUEHOST_2018','172.16.110.1');
	# $oCRNRSTN->denyAccess('LOCALHOST_MAC','172.16.110.*');
	$oCRNRSTN->denyAccess('LOCALHOST_PC','127.0.0.10, 127.0.0.2, 127.0.0.3, 127.0.0.4, 127.0.0.5');
	
	# # #
	# INITIALIZATION FOR ENCRYPTION :: CRNRSTN SESSION DATA :: ADVANCED CONFIGURATION PARAMETERS
	/*
	To configure any of your SERVER environments to hide select CRNRSTN configuration session data behind a layer of encryption, 
	run $oCRNRSTN->initSessionEncryption(x,x,x,..)...as defined below...specifying the environmental key for 
	each environment where encryption is desired. CAUTION: This feature will increase server load. CAUTION: CRNRSTN applies a combination 
	of encryption cipher and HMAC keyed hash value data manipulationas and comparisons to store and verify CRNRSTN session data. Some 
	encryption-cipher / HMAC-algoirthm combinations will not be compatible due to how they are applied to the data when encryption is 
	initialized...so please test your encryption configuration before applying to production application.
	
	* Note that the available cipher methods can differ between your dev server and your production server! They will depend on the installation 
	and compilation options used for OpenSSL in your machine(s).
	$oCRNRSTN->initSessionEncryption([environment-key], [openssl-encryption-cipher], [openssl-encryption-key], [openssl-encryption-options], [hmac-algorithm]);
	$oCRNRSTN->initSessionEncryption([environment-key] -> Specify one of your previously defined addEnvironment() environment keys , 
									   [openssl-encryption-cipher] -> For a list of recommended and available openssl cipher methods...run $oCRNRSTN->openssl_get_cipher_methods(), 
									   [openssl-encryption-key] -> specify an encryption key to be used by the CRNRSTN encryption layer for encryptable session data, 
									   [openssl-encryption-options] -> a bitwise disjunction of the flags OPENSSL_RAW_DATA and OPENSSL_ZERO_PADDING, 
									   [hmac-algorithm] -> Specify the algorithm to be used by CRNRSTN when using the HMAC library to generate a keyed hash value. For a list 
														   of available algorithms run hash_algos(). 
									);
	
	CAUTION. If session encryption is enabled and then changed some time later in the running production environment. It is possible for 
	active clients to have session data that was encrypted with a "no-longer-in-production" encryption cipher or HMAC algorithm...and hence 
	be unreadable to the application. Developer needs to take this into consideration and plan for use case where session data is 
	unreadable...with graceful degradation or session reset.
										
	*/
	
	$oCRNRSTN->initSessionEncryption('LOCALHOST_PC', 'AES-192-OFB', 'this-Is-the-encryption-key', OPENSSL_RAW_DATA, 'sha256');
	$oCRNRSTN->initSessionEncryption('LOCALHOST_MAC', 'AES-192-OFB', 'this-Is-the-encryption-key', OPENSSL_RAW_DATA, 'sha256');
	$oCRNRSTN->initSessionEncryption('BLUEHOST_2018', 'AES-256-CTR', 'this-Is-the-encryption-key', OPENSSL_RAW_DATA, 'ripemd256');
	
	# # #
	# INITIALIZATION FOR ENCRYPTION :: CRNRSTN COOKIE DATA :: ADVANCED CONFIGURATION PARAMETERS
	/*
	CAUTION :: Some hash_algos() returned methods will NOT be compatible with hash_hmac() which CRNRSTN uses in validating 
	its decryption. And certain openssl encryption cipher / hash_algos algorithm combinations will not be compatible. 
	Please test the compatibility of your desired combination of encryption cipher and hmac algoritm for each 
	environment...especially before releasing to production code base. 
	
	CAUTION. If cookie encryption is enabled and then changed some time later. It is possible for clients to have cookie data that was
	encrypted with a "no-longer-in-production" encryption cipher or HMAC algorithm...and hence be unreadable to the application. Developer
	needs to take this into consideration and plan for use case where cookie data is unreadable...with graceful degradation or cookie reset.
	*/
	# $oCRNRSTN->initCookieEncryption([environment-key], [openssl-encryption-cipher], [openssl-encryption-key], [openssl-encryption-options], [hmac-algorithm]);
	$oCRNRSTN->initCookieEncryption('LOCALHOST_MAC', 'AES-256-CTR', 'this-Is-the-encryption-key', OPENSSL_RAW_DATA, 'ripemd256');
	$oCRNRSTN->initCookieEncryption('LOCALHOST_PC', 'AES-192-OFB', 'this-Is-the-encryption-key', OPENSSL_RAW_DATA, 'sha256');
	$oCRNRSTN->initCookieEncryption('BLUEHOST_2018', 'AES-256-CTR', 'this-Is-the-encryption-key', OPENSSL_RAW_DATA, 'ripemd256');
	
	//
	// TO ACHIEVE OPTIMIZATION AT FIRST RUNTIME, PASS AN APPROPRIATE INTEGER VALUE TO requiredDetectionMatches(). WHEN THAT QUANTITY OF PROVIDED $_SERVER PARAMETERS MATCH FOR ANY GIVEN 
	// DEFINED ENVIRONMENT'S defineEnvResource() KEYS, THE RUNNING ENVIRONMENT WILL BE FLAGGED. FURTHER PROCESSING OF ANY REMAINING defineEnvResource() KEYS CAN BE STEAMLINED.
	$oCRNRSTN->requiredDetectionMatches(5);

	# # #
	# FOR EACH ENVIRONMENT, DEFINE SERVER[] KEYS FOR DETECTION + ADD ANY CUSTOM KEYS/VALUES OF YOUR OWN
	#
	# HERE ARE EXAMPLES OF SOME CORE/RESERVED SERVER SUPER GLOBAL PARAMETERS
	# SUPER GLOBAL SERVER VALUES FROM THESE DEFINITIONS WILL BE USED TO CONFIGURE CRNRSTN TO ITS RUNNING ENV PER REAL-TIME SERVER SETTINGS
	#___KEY__<--(custom or an existing $_SERVER[] key)  ___EXAMPLE OF VALUE____________________________
	# DOCUMENT_ROOT									(e.g. 'C:\\[path]\\[to]\\[site-root]\\[folder]\\' or '/var/www/')
	# SERVER_NAME									(e.g. 'localhost' or 'stage.mydomain.com' or 'mydomain.com')
	# SERVER_ADDR									(e.g. '265.121.2.110')
	# 
	# For a more complete list of available super global array parameters, please see :: 
	# http://php.net/manual/en/reserved.variables.server.php
			
	//
	// BEGIN RESOURCE DEFINITIONS FOR PRODUCTION ENVIRONMENT. FASTEST DETECTION GOES TO FIRST ENVIRONMENT WITH ALL SERVER[] RESOURCES DEFINED.
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'SERVER_NAME', 'woodford.evifweb.com');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'SERVER_ADDR', '50.87.249.11');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'SERVER_PORT', '80');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'SERVER_PROTOCOL', 'HTTP/1.1');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'DOCUMENT_ROOT', '/home3/evifwebc/public_html/woodford.evifweb');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'DOCUMENT_ROOT_DIR', '');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'ROOT_PATH_CLIENT_HTTP_MSG', 'http://woodford.evifweb.com/');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'ROOT_PATH_CLIENT_HTTP_MSG_DIR', '');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'ROOT_PATH_CLIENT_HTTP', 'http://woodford.evifweb.com/');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'ROOT_PATH_CLIENT_HTTP_DIR', '');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'DOMAIN', 'woodford.evifweb.com');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'SESSION_EXPIRE', 'INTERVAL 30 MINUTE');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'PWD_RESET_LINK_EXPIRE', 'INTERVAL 30 MINUTE');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'ADMIN_NOTIFICATIONS_EMAIL', 'c00000101@gmail.com');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'ADMIN_NOTIFICATIONS_RECIPIENTNAME', 'Evifweb CEO');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'SYSTEM_MSG_FROM_EMAIL', 'jharris@evifweb.com');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'SYSTEM_MSG_FROM_NAME', 'Jonathan J5 Harris');
	$oCRNRSTN->defineEnvResource('BLUEHOST_2018', 'WSDL_URI', 'http://some.domain.com/1.0.0/wsdl/index.php?wsdl');	# WSDL_URI REQUIRED BY CRNRSTN SOAP CLIENT CONNECTION MANAGER IF USING SOAP CLIENT

	//
	// BEGIN RESOURCE DEFINITIONS FOR NEXT ENVIRONMENT
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'SERVER_NAME', '172.16.110.130');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'SERVER_ADDR', '172.16.110.130');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'SERVER_PORT', '80');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'SERVER_PROTOCOL', 'HTTP/1.1');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'DOCUMENT_ROOT', '/var/www/html'); # VALUE FOR YOUR SERVER['DOCUMENT_ROOT']
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'DOCUMENT_ROOT_DIR', '/woodford');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'ROOT_PATH_CLIENT_HTTP', 'http://172.16.110.130/');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'ROOT_PATH_CLIENT_HTTP_DIR', 'woodford/');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'DOMAIN', '172.16.110.130');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'SOA_NAMESPACE', 'http://172.16.110.130/soap/services');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'MAILER_FROM_EMAIL', 'noreply_crnrstn@crnrstn.jony5.com');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'MAILER_FROM_NAME', 'CRNRSTN Suite :: Community Mailer');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'MAILER_AUTHKEY', 'Pv2bduy|>4}zFO~u}D');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'APP_NAME', 'crnrstn');
	$oCRNRSTN->defineEnvResource('LOCALHOST_MAC', 'WSDL_URI', 'http://172.16.110.130/services/soa/crnrstn/1.0.0/wsdl/index.php?wsdl');	# WSDL_URI REQUIRED BY CRNRSTN SOAP CLIENT CONNECTION MANAGER IF USING SOAP CLIENT

	//
	// BEGIN RESOURCE DEFINITIONS FOR NEXT ENVIRONMENT
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'SERVER_NAME', '127.0.0.1');
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'SERVER_ADDR', '127.0.0.1');
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'SERVER_PORT', '80');
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'SERVER_PROTOCOL', 'HTTP/1.1');
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'DOCUMENT_ROOT', 'C:/DATA_GOVT_SURVEILLANCE/_wwwroot/xampp/htdocs/'); # VALUE FOR YOUR SERVER['DOCUMENT_ROOT']
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'DOCUMENT_ROOT_DIR', '');
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'ROOT_PATH_CLIENT_HTTP', 'http://127.0.0.1/');
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'ROOT_PATH_CLIENT_HTTP_DIR', '');
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'DOMAIN', '127.0.0.1');
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'SOA_NAMESPACE', 'http://127.0.0.1/soap/services');
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'MAILER_FROM_EMAIL', 'noreply_crnrstn@crnrstn.jony5.com');
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'MAILER_FROM_NAME', 'CRNRSTN Suite :: Community Mailer');
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'MAILER_AUTHKEY', 'Pv2bduy|>4;cs=fFO~u}D');
	$oCRNRSTN->defineEnvResource('LOCALHOST_PC', 'WSDL_URI', 'http://127.0.0.1/services/soa/crnrstn/1.0.0/wsdl/index.php?wsdl');	# WSDL_URI REQUIRED BY CRNRSTN SOAP CLIENT CONNECTION MANAGER IF USING SOAP CLIENT
	
	//
	// RESOURCES DEFINED FOR ALL ENVIRONMENTS :: AS DESIGNATED BY PASSING '*' AS ENV KEY PARAMETER
	$oCRNRSTN->defineEnvResource('*','WSDL_CACHE_TTL','80');	# WSDL_CACHE_TTL REQUIRED BY CRNRSTN SOAP CLIENT CONNECTION MANAGER IF USING SOAP CLIENT
	$oCRNRSTN->defineEnvResource('*','NUSOAP_USECURL', true);	# NUSOAP_USECURL REQUIRED BY CRNRSTN SOAP CLIENT CONNECTION MANAGER IF USING SOAP CLIENT
	$oCRNRSTN->defineEnvResource('*','SEARCHPAGE_INDEXSIZE','15');
	$oCRNRSTN->defineEnvResource('*','USERPROFILE_EXTERNALURI','3');
	$oCRNRSTN->defineEnvResource('*','AUTOSUGGEST_RESULT_MAX','10');
	
	//
	// INSTANTIATE ENVIRONMENTAL CLASS BASED ON ABOVE DEFINED CRNRSTN CONFIGURATION 
	$oCRNRSTN_ENV = new crnrstn_environmentals($oCRNRSTN);
	unset($oCRNRSTN);

}else{
	unset($oCRNRSTN);
}

# # # # # #
# # # # # #
# # # # # #
# # # # # # 	END OF CRNRSTN CONFIG

?>
