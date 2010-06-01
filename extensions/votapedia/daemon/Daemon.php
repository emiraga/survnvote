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
require_once("$vgPath/DAO/Survey.php");

$page_cache = array();
$surveydao = new SurveyDAO();

// Generate a random character string
function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
{
    $chars_length = strlen($chars) - 1;
    $string = '';
    for ($i = 1; $i < $length; $i++)
        $string .= $chars[rand(0, $chars_length)];
    return $string;
}

function do_sms_action()
{
    global $vgSmsChoiceLen;
    
    $new = Sms::getNewSms();
    foreach($new as $sms)
    {
        //load user
        $username = UserphonesDAO::getNameFromPhone($sms['from']);
        if($username == false)
        {
            $username = rand_str();
            $now = vfDate();
            $vgDB->Execute("INSERT INTO {$vgDBPrefix}userphones (username, phonenumber, status, dateadded) VALUES (?,?,?,?)",
                    array($username, $sms['from'],vPHONE_UNKNOWN, $now));
        }
        //process SMS
        $text = $sms['text'];
        while(strlen($text) < $vgSmsChoiceLen)
            $text = '0'+$text;
        $text = substr($text, 0, $vgSmsChoiceLen);

        //find page and vote
        try
        {
            //load PageVO
            $result = $vgDB->Execute("SELECT surveyID, choiceID FROM {$vgDBPrefix}surveychoice WHERE SMS = ?", array($sms));
            if($result == false)
                throw new SurveyException("SurveyID not found.");
            $surveyid = $result['surveyID'];
            $choiceid = $result['choiceID'];
            $pageid = $vgDB->GetOne("SELECT pageID FROM {$vgDBPrefix}survey WHERE surveyID = ?", array($surveyid));
            if($pageid == false)
                throw new SurveyException("PageID not found.");
            $page =& $surveydao->findByPageID($pageid, false);

            //vote
            $votedao = new VoteDAO($page, $username);
            $votevo = $votedao->newFromPage('SMS', $surveyid, $choiceid );
            $votedao->vote($votevo);
        }
        catch(Exception $e)
        {
            
        }
    }
}

//get command line parameters
$args = $_SERVER['argv'];
if($args['daemon'] == '1')
{
    //run as a daemon
    while(1)
    {
        do_sms_action();
        sleep(2);
    }
}
else
{
    do_sms_action();
}

?>