<?php
//set the path to MediaWiki
$IP = '/xampp/htdocs/new';

define('VOTAPEDIA_DAEMON',true);
define('MEDIAWIKI',true);

@require_once("$IP/LocalSettings.php");

$vgDBUserName = 'root'; //Set this to database user that has priviledges to access votapedia
$vgDBUserPassword = '';

require_once("$vgPath/Common.php");
require_once("$vgPath/Sms.php");
require_once("$vgPath/DAO/VoteDAO.php");
require_once("$vgPath/DAO/SurveyDAO.php");
require_once("$vgPath/DAO/UserphonesDAO.php");

$page_cache = array();
$surveydao = new SurveyDAO();

// Generate a random character string
function vfRandStr($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
{
    $chars_length = strlen($chars) - 1;
    $string = '';
    for ($i = 1; $i < $length; $i++)
        $string .= $chars[rand(0, $chars_length)];
    return $string;
}

$a = microtime(true);
function vfMeasureTime($msg = '')
{
    global $a;
    $b = microtime(true);
    printf(">>>> TIMIG TO POINT $msg --> %.6f\n",$b-$a);
    $a = microtime(true);
}

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
            $username = vfRandStr();
            $now = vfDate();
            $vgDB->Execute("INSERT INTO {$vgDBPrefix}userphones (username, phonenumber, status, dateadded) VALUES (?,?,?,?)",
                    array($username, $sms['from'],vPHONE_UNKNOWN, $now));
        }

        $numbers = preg_split("/[^0-9]+/", $sms['text']);

        foreach($numbers as $choice)
        {
            if(strlen($choice) == 0 || strlen($choice) > $vgSmsChoiceLen)continue;

            //process SMS
            while(strlen($choice) < $vgSmsChoiceLen)
                $choice = '0' . $choice;

            //find page and try to vote
            try
            {
                //load PageVO
                $result = $vgDB->Execute("SELECT pageID, surveyID, choiceID FROM {$vgDBPrefix}surveychoice WHERE SMS = ? AND finished = 0",
                        array($choice));
                if($result == false)
                    throw new SurveyException("SurveyID not found.");
                $surveyid = $result->fields['surveyID'];
                $choiceid = $result->fields['choiceID'];
                $pageid = $result->fields['pageID'];
                $page =& $surveydao->findByPageID($pageid, false);
                //vote
                $votedao = new VoteDAO($page, $username);
                $votevo = $votedao->newFromPage('SMS', $pageid, $surveyid, $choiceid );
                $votedao->vote($votevo);
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

//get command line parameters
$args = $_SERVER['argv'];
if(in_array('daemon', $args))
{
    //run as a daemon
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
        sleep(2);
    }
}
else
{
    vfMeasureTime('start');
    vfDaemonSmsAction();
    vfMeasureTime('end');
}
