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
$vgDB->debug = true;
require_once("$vgPath/Sms.php");
require_once("$vgPath/DAO/VoteDAO.php");
require_once("$vgPath/DAO/UserphonesDAO.php");
require_once("$vgPath/DAO/PageDAO.php");
require_once("$vgPath/DAO/UserDAO.php");
$vgDaemonDebug = false;

/**
 * Do whatever is needed to process new incoming SMS.
 */
function vfDaemonSmsAction()
{
    global $vgSmsChoiceLen, $vgDB, $vgDBPrefix, $vgEnableSMS, $vgDaemonDebug;

    if(! $vgEnableSMS)
        return;

    $new = Sms::getNewSms();
    foreach($new as $sms)
    {
        if($vgDaemonDebug)
            echo "New sms: $sms[from] $sms[text]\n";
        
        //load user
        $userID = UserphonesDAO::getUserIDFromPhone($sms['from']);
        
        if($userID == false)
        {
            $userdao = new UserDAO();
            $user = $userdao->newFromPhone($sms['from'], true);
            $userID = $user->userID;
            if($vgDaemonDebug)
                echo "New userID=$userID from phone $sms[from]\n";
        }
        if($vgDaemonDebug)
            echo "Found $userID\n";
        $numbers = preg_split("/[^0-9]+/", $sms['text']);

        foreach($numbers as $choice)
        {
            if(strlen($choice) == 0 || strlen($choice) > $vgSmsChoiceLen)continue;

            //process SMS
            while(strlen($choice) < $vgSmsChoiceLen)
                $choice = '0' . $choice;
            try
            {
                vfVoteFromDaemon($choice, $userID);
            }
            catch(Exception $e)
            {
                //We do not care about these Exceptions.
                //Since this is error of a voter.
                if($vgDaemonDebug)
                    echo "Exception: ".$e->getMessage()."\n";
            }
        }
        Sms::processed($sms['id']);
    }
}
/**
 * Find if such choice exists and vote for it.
 *
 * @param String $choice choice that user has coosen
 * @param String $userID ID of user
 */
function vfVoteFromDaemon($choice, $userID)
{
    global $vgDBPrefix, $vgDB;
    //load PageVO
    $pagedao = new PageDAO();
    $result = $vgDB->Execute("SELECT pageID, surveyID, choiceID FROM {$vgDBPrefix}choice WHERE SMS = ? AND finished = 0",
        array($choice));
    if($result == false)
        throw new SurveyException("Survey not found.");
    $surveyid = $result->fields['surveyID'];
    $choiceid = $result->fields['choiceID'];
    $pageid = $result->fields['pageID'];
    $page =& $pagedao->findByPageID($pageid, false);
    //Save vote
    $votedao = new VoteDAO($page, $userID);
    $votevo = $votedao->newFromPage('SMS', $pageid, $surveyid, $choiceid, $page->getCurrentPresentationID() );
    $votedao->vote($votevo);
}

/* get command line parameters */
$args = $_SERVER['argv'];
if(!isset($args[1]))
{
    die("\nUsage: php $args[0] daemon [debug] | fakevote [votes]+ | checkbalance | reportbalance\n");
}

if($args[1] == 'daemon')
{
    if(isset($args[2]) && $args[2] == 'debug')
    {
        $vgDaemonDebug = true;
        echo "Debug enabled\n";
    }
    
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
    $userID = 1000 * 1000 + rand();
    for($i=2;$i<count($args);$i++)
    {
        $choice = $args[$i];
        while(strlen($choice) < $vgSmsChoiceLen)
            $choice = '0' . $choice;
        try
        {
            vfVoteFromDaemon($choice, $userID);
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

