<?php
if(isset($_SERVER['HOST'])) die('Must be run from command line.');
/**
 * Background process which monitors the incoming SMS and phone CALLS.
 * 
 * @package Daemon
 */

/** Configuration */
//Set this path to MediaWiki installation
$IP = dirname(__FILE__).'/../../..';

define('VOTAPEDIA_DAEMON',true);
define('MEDIAWIKI',true);
@require_once("$IP/LocalSettings.php");
@include_once("$IP/AdminSettings.php");

$vgDBUserName = $wgDBadminuser; //Set this to database user that has priviledges to access votapedia
$vgDBUserPassword = $wgDBadminpassword;

/** Include dependencies */
require_once("$vgPath/misc/Common.php");

if($vgDebug)
    $vgDB->enableOutput();
else
    $vgDB->debug = false;

require_once("$vgPath/Sms.php");
require_once("$vgPath/DAO/VoteDAO.php");
require_once("$vgPath/DAO/UserphonesDAO.php");
require_once("$vgPath/DAO/PageDAO.php");
require_once("$vgPath/DAO/UserDAO.php");
require_once("$vgPath/API/AutocreateUsers.php");
require_once("$vgPath/misc/UserPermissions.php");
require_once("$vgPath/special/VotapediaStats.php");

$vgDaemonDebug = false;

/**
 * Get an userId and send SMS if necessary.
 *
 * @param String $phone
 * @param Boolean $check_status is user check his/hers status
 * @return UserVO
 */
function vfDaemonGetUserSendSMS($phone, $check_status = false)
{
    global $vgDaemonDebug;
    $userID = UserphonesDAO::getUserIDFromPhone($phone);
    $userdao = new UserDAO();

    if($userID == false)
    {
        //create user
        $user = $userdao->newFromPhone($phone);
        
        Sms::sendSMS($phone, sprintf(Sms::$msgCreateUser, $user->username, $user->password));

        if($vgDaemonDebug)
            echo "New userID=$userID from phone $phone\n";
    }
    else
    {
        $user = $userdao->findByID($userID);
        if($check_status)
        {
            if(strlen($user->password) > 2)
                Sms::sendSMS($phone, sprintf(Sms::$msgCreateUser, $user->username, $user->password));
            else
                Sms::sendSMS($phone, sprintf(Sms::$msgCreateUserNoPass, $user->username));
        }
    }
    return $user;
}

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
        
        $sms['text'] = trim($sms['text']);

        //is it a check command?
        if( strncasecmp($sms['text'], Sms::$cmdCheck, strlen(Sms::$cmdCheck) ) == 0 )
        {
            //this user wants to check account status
            $user = vfDaemonGetUserSendSMS($sms['from'], true);
            if($vgDaemonDebug)
                echo "Check account {$user->userID}\n";
        }
        //is it a confirm command?
        elseif( strncasecmp($sms['text'], Sms::$cmdConfirm, strlen(Sms::$cmdConfirm) ) == 0 )
        {
            $confirmcode = trim(substr($sms['text'], strlen(Sms::$cmdConfirm)));
            $userdao = new UserDAO();
            $user = $userdao->checkValidConfirmCode($confirmcode);
            if($user)
            {
                $phonedao = new UserphonesDAO($user);
                $phonedao->addVerifiedPhone($sms['from']);
                if($vgDaemonDebug)
                    echo "Confirmed phone $sms[from] account {$user->userID}\n";
            }
            else
            {
                if($vgDaemonDebug)
                    echo "Confirm INVALID\n";
            }
        }
        else
        {
            //this user is voting
            $user = vfDaemonGetUserSendSMS( $sms['from'] );
            
            if($vgDaemonDebug)
                echo "Found {$user->userID}\n";
            
            $numbers = preg_split("/[^0-9]+/", $sms['text']);

            foreach($numbers as $choice)
            {
                if(strlen($choice) == 0 || strlen($choice) > $vgSmsChoiceLen)
                    continue;

                //process SMS
                while(strlen($choice) < $vgSmsChoiceLen)
                    $choice = '0' . $choice;
                try
                {
                    vfVoteFromDaemon($choice, $user);
                }
                catch(Exception $e)
                {
                    //We do not care about these Exceptions.
                    //Since this is error of a voter.
                    if($vgDaemonDebug)
                        echo "Exception: ".$e->getMessage()."\n";
                }
            }
        }
        if($vgDaemonDebug)
            echo "Sms is processed\n";
        Sms::processed($sms['id']);
    }
}

/**
 * Find if such choice exists and vote for it.
 *
 * @param String $choice choice that user has coosen
 * @param UserVO $user
 */
function vfVoteFromDaemon($choice, UserVO &$user)
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

    $status = $page->getStatus($page->getCurrentPresentationID());

    $userperm = new UserPermissions($user);
    if($status == 'active' && $userperm->canVote($page, 'phone'))
    {
        //Save vote
        $votedao = new VoteDAO($page, $user->userID);
        $votevo = $votedao->newFromPage('SMS', $pageid, $surveyid, $choiceid, $page->getCurrentPresentationID() );
        $votedao->vote($votevo);
        //update statistics
        VotapediaStatsUpdate::addSmsVote();
    }
}

/* get command line parameters */
$args = $_SERVER['argv'];

if(!isset($args[1]) || $args[1] == 'debug' || $args[1] == 'daemon')
{
    if(isset($args[1]) && $args[1] == 'debug')
    {
        $vgDaemonDebug = true;
        echo "Debug enabled\n";
    }
    
    /* Run as a daemon */
    while(1)
    {
        if($vgDaemonDebug)
            echo ".";
        $tel = new Telephone();
        try
        {
            $tel->releaseReceivers();
            if($vgEnableSMS)
            {
                vfDaemonSmsAction();
            }
        }
        catch(Exception $e)
        {
            error_log('Votapedia daemon error: '.$e->getMessage() . ' ' .$e->getFile().' '.$e->getLine());
            error_log($e->getTraceAsString());
        }
        sleep(1);
    }
}
else if($args[1] == 'help')
{
    die("\nUsage: php $args[0] [debug] | daemon | help | fakevote [votes]+ | checkbalance | reportbalance | masstest\n");
}
else if($args[1] == 'fakevote') /*used for testing*/
{
    $vgDaemonDebug = true;
    $tel = new Telephone();
    $tel->releaseReceivers();

    global $vgSmsChoiceLen;
    $userID = 1000 * 1000 + rand();
    for($i=2;$i<count($args);$i++)
    {
        $choice = $args[$i];
        while(strlen($choice) < $vgSmsChoiceLen)
            $choice = '0' . $choice;
        try
        {
            $user = new UserVO();
            $user->userID = $userID;
            $user->isAnon = false;
            $user->username = $userID;
            
            vfVoteFromDaemon($choice, $user);
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

