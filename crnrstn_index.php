<?php
/* 
// J5
// Code is Poetry */
require('_crnrstn.root.inc.php');
include_once($CRNRSTN_ROOT . '_crnrstn.config.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>CRNRSTN Suite :: Welcome</title>
    <style type="text/css">
	* 							{ font-family:Arial, Helvetica, sans-serif; color:#333; }
	p 							{ font-size: 13px; }
	.the_R						{ color:#F00; }
	.success 					{ color:#4A9E64; }
	</style>
</head>

<body>
<h1>Welcome to C<span class="the_R">R</span>NRSTN ::</h1>
<p><a href="./crnrstn_config_debug.php" target="_self">Click here</a> to view C<span class="the_R">R</span>NRSTN :: debug page so that the data contained therein can be used to configure C<span class="the_R">R</span>NRSTN :: for this environment.</p>
<p><a href="./_crnrstn_config_purge.php" target="_self">Click here</a> to purge session data so that any configuration file changes can be applied upon next page refresh.</p>
<p><a href="http://crnrstn.evifweb.com" target="_blank">Click here</a> to visit the documentation web site.</p>
<?php
if($_SERVER['SERVER_NAME']==$oCRNRSTN_ENV->getEnvParam('SERVER_NAME')){
	echo '<h3><span class="success">SUCCESS</span> :: C<span class="the_R">R</span>NRSTN :: is successfully configured for this environment.</h3>';

}else{
	echo '<h3><span class="the_R">ERROR</span> :: C<span class="the_R">R</span>NRSTN :: is not yet properly configured for this environment...this test is looking for SERVER_NAME.</h3>';

}
?>
<br>
<p><em>Response completed in <?php echo $oCRNRSTN_ENV->wallTime(); ?> seconds.</em></p>
</body>
</html>