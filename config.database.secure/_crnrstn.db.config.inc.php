<?php
//
// INITIALIZE DATABASE FUNCTIONALITY FOR EACH ENVIRONMENT.
# $this->oMYSQLI_CONN_MGR->addConnection([environment-key], [db-host], [db-user-name], [db-user-pswd], [db-database-name], [optional-db-port]);
#$this->oMYSQLI_CONN_MGR->addConnection('LOCALHOST_PC', '127.0.0.4', 'crnrstn_demo3_un', 'FZZ88X3EU5s8vFAC', 'crnrstn_demo3');
#$this->oMYSQLI_CONN_MGR->addConnection('LOCALHOST_PC', '127.0.0.3', 'crnrstn_demo2_un', 'PwdBNBvuFHrwMqCS', 'crnrstn_demo2');
$this->oMYSQLI_CONN_MGR->addConnection('LOCALHOST_PC', 'localhost', 'crnrstn_stage', 'KNUcSHWCARrZUsaZ', 'crnrstn_stage','3306');
$this->oMYSQLI_CONN_MGR->addConnection('LOCALHOST_MAC', 'localhost', 'crnrstn_stage', 'J7mBqBWGt5wag1kz', 'crnrstn_stage','3306');
$this->oMYSQLI_CONN_MGR->addConnection('BLUEHOST_2018', 'localhost', 'evifwebc_prod', 'jhQhek3c9khw', 'evifwebc_prod','3306');
#$this->oMYSQLI_CONN_MGR->addConnection('LOCALHOST_PC', 'localhost', 'crnrstn_demo', 'aXNTPxGPeLRwYzTS', 'crnrstn_demo', 3306);

?>