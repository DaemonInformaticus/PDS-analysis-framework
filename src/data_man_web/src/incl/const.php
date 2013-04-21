<?php

$envName = "development";
// $envName = "live";

// $SYSTEM_CONST['development'][''] = "";
/**************** Development environment: *****************************/
$SYSTEM_CONST['development']['dbHost']  = "localhost";
$SYSTEM_CONST['development']['dbUser']  = "root";
$SYSTEM_CONST['development']['dbPass']  = "";
$SYSTEM_CONST['development']['dbName']  = "dbPDSData";
$SYSTEM_CONST['development']['md5salt'] = "osodfis8fhosd";

$SYSTEM_CONST['development']['title']       = "Space Apps Development of PDS parser - Dev";
$SYSTEM_CONST['development']['banner']      = "";
$SYSTEM_CONST['development']['background']  = "";
$SYSTEM_CONST['development']['bgcolor']     = "#ffffff";

$SYSTEM_CONST['development']['default_language_id']   = "1";
$SYSTEM_CONST['development']['register_state']        = "1";

$SYSTEM_CONST['development']['lock_max_length'] = 24 * 3600; // Maximum time a user can lock a record (in seconds).

/********************* Live environment: *******************************/
$SYSTEM_CONST['live']['dbHost']   = "localhost";
$SYSTEM_CONST['live']['dbUser']   = "root";
$SYSTEM_CONST['live']['dbPass']   = "";
$SYSTEM_CONST['live']['dbName']   = "dbPDSData";
$SYSTEM_CONST['live']['md5salt']  = "osodfis8fhosd";

$SYSTEM_CONST['live']['title']      = "Space Apps Development of PDS parser - Live";
$SYSTEM_CONST['live']['banner']     = "";
$SYSTEM_CONST['live']['background'] = "";
$SYSTEM_CONST['live']['bgcolor']    = "#ffffff";

$SYSTEM_CONST['live']['default_language_id']  = "1";
$SYSTEM_CONST['live']['register_state']       = "1";

$SYSTEM_CONST['live']['lock_max_length'] = 24 * 3600; // Maximum time a user can lock a record (in seconds).



$env = $SYSTEM_CONST[$envName];

$env['dbConn'] = mysql_connect($env['dbHost'], $env['dbUser'], $env['dbPass']) or die("cannot connect");
mysql_select_db($env['dbName'], $env['dbConn']) or die("cannot select database");
?>