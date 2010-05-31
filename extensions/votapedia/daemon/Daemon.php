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
require_once("$vgPath/DAO/VoteDAO.php");

$page_cache = array();

function do_action()
{
    global $smsint;
    $new = Sms::getNewSms();
    foreach($new as $sms)
    {
        ;
    }

    
}

//get command line parameters
$args = $_SERVER['argv'];
if($args['daemon'] == '1')
{
    //run as a daemon
    while(1)
    {
        do_action();
        sleep(2);
    }
}
else
{
    do_action();
}

?>