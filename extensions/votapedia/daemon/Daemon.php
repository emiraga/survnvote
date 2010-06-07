<?php
if(isset($_SERVER['HOST'])) die('Must be run from command line.');

//Set this path to MediaWiki
$IP = '/xampp/htdocs/new';

define('VOTAPEDIA_DAEMON',true);
define('MEDIAWIKI',true);
@require_once("$IP/LocalSettings.php");
@require_once("$IP/AdminSettings.php");
$vgDBUserName = $wgDBadminuser; //Set this to database user that has priviledges to access votapedia
$vgDBUserPassword = $wgDBadminpassword;

require_once("$vgPath/Common.php");
require_once("$vgPath/Sms.php");
require_once("$vgPath/DAO/VoteDAO.php");
require_once("$vgPath/DAO/SurveyDAO.php");
require_once("$vgPath/DAO/UserphonesDAO.php");

$page_cache = array();
$surveydao = new SurveyDAO();

/**
 * Create a new user by performing a POST request to the MediaWiki.
 * This is a very ugly hack. Needs to be improved.
 *
 * @return Boolean success true of false
 * @package Daemon
 */
function vfRequestNewUser($username, $password, $realname)
{
    //@todo *BUG* this part is very fragile, captcha extension can prevent this from working
    global $wgServer, $wgScriptPath, $wgScriptExtension;
    $url = "{$wgServer}{$wgScriptPath}/index$wgScriptExtension?title=Special:UserLogin&action=submitlogin&type=signup";

    $post = "wpName=".urlencode($username);
    $post .= "&wpPassword=".urlencode($password);
    $post .= "&wpRetype=".urlencode($password);
    $post .= "&wpRealName=".urlencode($realname);
    $post .= "&wpCreateaccount=Create+account";

    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec ($ch);
    curl_close ($ch);
    return !strstr($data, 'errorbox');
}
/**
 * Pick a new username, create that account and send an SMS.
 *
 * @param $phonenumber String
 * @package Daemon
 */
function vfDaemonNewUser($phonenumber)
{
    global $vgDB, $vgDBPrefix;
    $password = rand(1000,9999);
    
    for($i=0;$i<50;$i++)
    {
        $name = $vgDB->GetOne("SELECT name FROM {$vgDBPrefix}names WHERE taken = 0");
        if($name == false)
            $name = rand(100000, 999999);
        else
        {
            $vgDB->Execute("UPDATE {$vgDBPrefix}names SET taken = 1 WHERE name = ?", array($name));
            //wiki names start with capital letter
            $name[0] = strtoupper($name[0]);
        }
        if(vfRequestNewUser($name, $password, $phonenumber))
        {
            Sms::sendSMS($phonenumber, sprintf(Sms::$msgCreateUser, $name, $password));
            UserphonesDAO::addVerifiedPhone($name, $phonenumber);
            return $name;
        }
    }
    throw new Exception('Could not create a new user');
}
/**
 * Do whatever is needed to process new incoming SMS.
 * @package Daemon
 */
function vfDaemonSmsAction()
{
    global $surveydao, $vgSmsChoiceLen, $vgDB, $vgDBPrefix, $vgEnableSMS;

    if(! $vgEnableSMS)
        return;

    $new = Sms::getNewSms();
    foreach($new as $sms)
    {
        //load user
        $username = UserphonesDAO::getNameFromPhone($sms['from']);
        
        if($username == false)
        {
            $username = vfDaemonNewUser($sms['from']);
        }

        $numbers = preg_split("/[^0-9]+/", $sms['text']);

        foreach($numbers as $choice)
        {
            if(strlen($choice) == 0 || strlen($choice) > $vgSmsChoiceLen)continue;

            //process SMS
            while(strlen($choice) < $vgSmsChoiceLen)
                $choice = '0' . $choice;

            try
            {
                vfVoteFromDaemon($choice, $username);
            }
            catch(Exception $e)
            {
                //We do not care about these Exceptions.
                //Since this is error of a voter.
            }
        }
        Sms::processed($sms['id']);
    }
}
/**
 * Find if such choice exists and vote for it.
 *
 * @param $choice String choice that user has coosen
 * @param $username String name of user
 * @package Daemon
 */
function vfVoteFromDaemon($choice, $username)
{
    global $vgDBPrefix, $vgDB, $surveydao;
    //load PageVO
    $result = $vgDB->Execute("SELECT pageID, surveyID, choiceID FROM {$vgDBPrefix}surveychoice WHERE SMS = ? AND finished = 0",
        array($choice));
    if($result == false)
        throw new SurveyException("SurveyID not found.");
    $surveyid = $result->fields['surveyID'];
    $choiceid = $result->fields['choiceID'];
    $pageid = $result->fields['pageID'];
    $page =& $surveydao->findByPageID($pageid, false);
    //Save vote
    $votedao = new VoteDAO($page, $username);
    $votevo = $votedao->newFromPage('SMS', $pageid, $surveyid, $choiceid );
    $votedao->vote($votevo);
}

/**
 * Generate a random character string
 * 
 * @param $length Integer length of output string
 * @param $chars String used characters, default: all alpha-numeric characters
 * @return String random string
 * @package Daemon
 */
function vfRandStr($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
{
    $chars_length = strlen($chars) - 1;
    $string = '';
    for ($i = 1; $i < $length; $i++)
        $string .= $chars[rand(0, $chars_length)];
    return $string;
}

/* get command line parameters */
$args = $_SERVER['argv'];
if(!isset($args[1]))
{
    die("Usage: php $args[0] daemon | fakevote [votes]+");
}

if($args[1] == 'daemon')
{
    /* Run as a daemon */
    while(1)
    {
        try
        {
            vfDaemonSmsAction();
        }
        catch(Exception $e)
        {
            error_log('Votapedia daemon error: '.$e->getMessage() . ' ' .$e->getFile().' '.$e->getLine());
            error_log($e->getTraceAsString());
        }
        sleep(1);
    }
}
else if($args[1] == 'fakevote') /*used for testing*/
{
    global $vgSmsChoiceLen;
    $name = 'fake:'.vfRandStr(8);
    for($i=2;$i<count($args);$i++)
    {
        $choice = $args[$i];
        while(strlen($choice) < $vgSmsChoiceLen)
            $choice = '0' . $choice;
        try
        {
            vfVoteFromDaemon($choice, $name);
            echo "Fake vote: $choice\n";
        }
        catch(Exception $e)
        {
            echo $e->getMessage()."\n";
        }
    }
}
