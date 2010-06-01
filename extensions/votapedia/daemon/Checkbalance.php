<?php
//set the path to MediaWiki
$IP = '/xampp/htdocs/new';

define('VOTAPEDIA_DAEMON',true);
define('MEDIAWIKI',true);

require_once("$IP/LocalSettings.php");

ini_set('include_path',ini_get('include_path').';C:\\xampp\\php\\PEAR\\');

$vgDBUserName = 'root'; //user that has priviledges to access SMS and votapedia
$vgDBUserPassword = '';

require_once("$vgPath/Common.php");
require_once("$vgPath/Sms.php");

//get command line parameters
$args = $_SERVER['argv'];

if($args[1] == 'check')
{
    Sms::requestCheckBalance();
    echo "Done with request check balance.\n";
}
if($args[1] == 'report')
{
    Sms::getReport();
    echo (Sms::getLatestBalance())."\n";
    echo "Reporting done.\n";
}
