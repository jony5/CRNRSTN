# CRNRSTN ::
CRNRSTN :: is an open source PHP class library that facilitates the 
operation of an application across multiple server environments (e.g. localhost, stage, 
preprod, production) effectively and properly joining the "wall of SERVER" to the 
"wall of application"...creating the two into one house. With this tool, data and 
functionality with characteristics that inherently create distinctions from one 
environment to the next...such as IP address restrictions, error logging profiles, 
and database authentication credentials...can all be managed...all together...from one place.

The CRNRSTN Suite :: can be configured for your environment(s) within a short amount of 
time. Only FTP access to a "web accessible" directory for each environment is needed. This guide 
will convey learnings that I have gained from working with CRNRSTN :: over the last 4 years and 
will help to reduce the amount of friction experienced during the configuration/installation 
process. In general, if all hosting environments have been defined and configured correctly 
within the CRNRSTN :: config file, you will be good to go. Forgetting to replace DEMO config data 
and placeholders with real data, incorrect paths to resources, and inserting typos are the 
three (3) main sources of CRNRSTN :: configuration errors.

For a detailed step by step demo/walk-through of the construction of the CRNRSTN :: configuration 
file, traverse the left navigation of the CRNRSTN Suite :: documentation web site from top to 
bottom...beginning with documentation for the CRNRSTN :: constructor here: 
http://crnrstn.evifweb.com/documentation/classes/crnrstn/__construct/?ns=crnrstn

...and ending with the presentation of a complete configuration file here:
http://crnrstn.evifweb.com/documentation/classes/environmentals/?ns=crnrstn|environmentals

Overview of configuration/installation steps ::
1) Download the CRNRSTN :: repository from GitHub.

2) Promote all CRNRSTN :: files to all hosting environments where your application will be running.
   
3) Update the CRNRSTN :: configuration file, _crnrstn.config.inc.php, to have it represent the full 
   landscape of hosting environments within which your application will be running. Use the 
   $_SERVER data presented by crnrstn_config_debug.php within each environment from Step 2 above 
   (see working debug example at http://crnrstn.evifweb.com/crnrstn_config_debug.php to review debug 
   output of CRNRSTN :: when fully configured). The CRNRSTN :: debugger assumes that $_SERVER param 
   "SERVER_NAME" has been defined for each environment...so at least define that one (1) SERVER 
   param through defineEnvResource() for each hosting environment to support the environmental 
   detection services of CRNRSTN ::. When keying off of SERVER_NAME, WWW and non-WWW domain 
   formats will indicate different environments...so perhaps make a separate profile for each in 
   the configuration...or key off another data point like server IP.
   
4) Promote this updated CRNRSTN :: configuration file from Step 3 along with any configured 
   configuration include files (for *database connectivity and *IP authorization) to all of your 
   environments. 
   
   *Optional Database include: /config.database.secure/_crnrstn.db.config.inc.php
   
   *Optional IP Auth include: /config.ipauthmgr.secure/denyaccess/_crnrstn.ipauthmgr.config.inc.php
   
   *Optional IP Auth include: /config.ipauthmgr.secure/grantexclusiveaccess/_crnrstn.ipauthmgr.config.inc.php
   
5) Confirm successful CRNRSTN :: configuration in each environment by running the debug file, 
   crnrstn_config_debug.php within each respective environment to ensure that SERVER_NAME is 
   returned in the blue-bordered CRNRSTN SUITE :: CONFIGURATION CONFIRMATION :: section of the 
   debugger. Display of the correct SERVER_NAME will indicate that environmental detection is 
   working for that environment. Use the "clear session data" link on the debug page to 
   refresh $_SESSION with any updated CRNRSTN :: configuration data.
   
   
