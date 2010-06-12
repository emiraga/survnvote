<?php
if(isset($_SERVER['HOST'])) die('Must be run from command line.');
/**
 * Background process which monitors the incoming SMS and phone CALLS.
 * 
 * @package Daemon
 */

/** Configuration */
//Set this path to MediaWiki
$IP = '/xampp/htdocs/new';

define('VOTAPEDIA_DAEMON',true);
define('MEDIAWIKI',true);
@require_once("$IP/LocalSettings.php");
@include_once("$IP/AdminSettings.php");

$vgDBUserName = $wgDBadminuser; //Set this to database user that has priviledges to access votapedia
$vgDBUserPassword = $wgDBadminpassword;

/** Include dependencies */
require_once("$vgPath/Common.php");
$vgDB->debug = false;
require_once("$vgPath/Sms.php");
require_once("$vgPath/DAO/VoteDAO.php");
require_once("$vgPath/DAO/UserphonesDAO.php");
require_once("$vgPath/DAO/PageDAO.php");
require_once("$vgPath/DAO/UserDAO.php");

/**
 * Do whatever is needed to process new incoming SMS.
 */
function vfDaemonSmsAction()
{
    global $vgSmsChoiceLen, $vgDB, $vgDBPrefix, $vgEnableSMS;

    if(! $vgEnableSMS)
        return;

    $new = Sms::getNewSms();
    foreach($new as $sms)
    {
        //load user
        $username = UserphonesDAO::getNameFromPhone($sms['from']);
        $userdao = new UserDAO();
        
        if($username == false)
        {
            $user = $userdao->newFromPhone($sms['from'], true);
            $username = $user->username;
            echo "New from phone $username\n";
        }
        echo "$username\n";
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
 * @param String $choice choice that user has coosen
 * @param String $username name of user
 */
function vfVoteFromDaemon($choice, $username)
{
    global $vgDBPrefix, $vgDB;
    //load PageVO
    $pagedao = new PageDAO();
    $result = $vgDB->Execute("SELECT pageID, surveyID, choiceID FROM {$vgDBPrefix}surveychoice WHERE SMS = ? AND finished = 0",
        array($choice));
    if($result == false)
        throw new SurveyException("Survey not found.");
    $surveyid = $result->fields['surveyID'];
    $choiceid = $result->fields['choiceID'];
    $pageid = $result->fields['pageID'];
    $page =& $pagedao->findByPageID($pageid, false);
    //Save vote
    $votedao = new VoteDAO($page, $username);
    $votevo = $votedao->newFromPage('SMS', $pageid, $surveyid, $choiceid, $page->getCurrentPresentationID() );
    $votedao->vote($votevo);
}

/**
 * Generate a random character string
 * 
 * @param Integer $length length of output string
 * @param String $chars used characters, default: all alpha-numeric characters
 * @return String random string
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
    die("\nUsage: php $args[0] daemon | fakevote [votes]+ | checkbalance | reportbalance\n");
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
elseif($args[1] == 'checkbalance')
{
    Sms::requestCheckBalance();
    echo "Done with request check balance.\n";
}
elseif($args[1] == 'reportbalance')
{
    Sms::getReport();
    echo (Sms::getLatestBalance())."\n";
    echo "Reporting done.\n";
}
elseif($args[1] == 'masstest')
{
    for($i=0;$i<100000;$i++)
    {
        if($i%1000 == 0)echo "$i\n";
        $now = vfDate();
        $choice = rand(1,7);
        $vgDB->Execute("INSERT INTO {$vgDBPrefix}vote
        (userID,pageID,surveyID,presentationID,voteType,choiceID,voteDate)
            VALUES('FAKE',4,5,2,'FAKE',$choice,'$now')");
    }
}
