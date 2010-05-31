<?php
if($_SERVER['HOST']) die('Must be run from command line.');

$IP = '/xampp/htdocs/new';
define('VOTAPEDIA_TEST', true);
define('MEDIAWIKI', true);

ini_set('include_path',ini_get('include_path').';C:\\xampp\\php\\PEAR\\');

$vgPath = "$IP/extensions/votapedia";

echo "Starting unit testing.\n";
require_once("./votapedia/votapedia.php");

$vgDBName = "unittest_setup";
$vgDBUserName       = 'root';
$vgDBUserPassword   = '';
$vgDBPrefix = '';

require_once("$vgPath/Common.php");
require_once("$vgPath/../votapedia.setup.php");

$a = microtime(true);
function pointTime($msg = '')
{
    global $a;
    $b = microtime(true);
    printf(">>>> TIMIG TO POINT $msg --> %.6f\n",$b-$a);
    $a = microtime(true);
}

if(true) /* Test choiceVO */
{
    echo '.';
    require_once("$vgPath/VO/ChoiceVO.php");
    $choice = new ChoiceVO();

    assert(! $choice->getChoiceID() );
    assert(! $choice->getChoice() );
    assert(! $choice->getPoints() );
    assert(! $choice->getReceiver() );
    assert(! $choice->getSMS() ); //what? EE represents error
    assert(! $choice->getSurveyID() );
    assert(! $choice->getVote() );

    $choice->setChoice("Yes");
    $choice->setChoiceID(5);
    $choice->setSurveyID(6);
    $choice->setReceiver("+060102999325");
    $choice->setSMS("25");
    $choice->setVote(3);
    $choice->setPoints(4);

    assert( $choice->getChoice() == 'Yes' );
    assert( $choice->getChoiceID() == 5 );
    assert( $choice->getSurveyID() == 6 );
    assert( $choice->getReceiver() == '+060102999325'  );
    assert( $choice->getSMS() == '25' );
    assert( $choice->getVote() == 3  );
    assert( $choice->getPoints() == 4  );

}
if(true) /* test PresentationVO */
{
    echo '.';
    require_once("$vgPath/VO/PresentationVO.php");
    $present = new PresentationVO();

    assert(! $present->getSurveyID() );
    assert( $present->getPresentationID() == 0 ); /* default value is 0 */
    assert(! $present->getPresentation() );
    assert(! $present->getActive() );
    assert(! $present->getMark() );

    $present->setSurveyID(4);
    $present->setPresentationID(5);
    $present->setPresentation('presentation name');
    $present->setActive(true);
    $present->setMark(8);

    assert( $present->getSurveyID() == 4 );
    assert( $present->getPresentationID() == 5 );
    assert( $present->getPresentation() == 'presentation name' );
    assert( $present->getActive() == '1' );
    assert( $present->getMark() == 8 );

    $present->setActive(false);
    assert( $present->getActive() == '0' );
}

if(true) /* test VoteVO */
{
    echo '.';
    require_once("$vgPath/VO/VoteVO.php");
    $vote = new VoteVO();
    assert( ! $vote->getChoiceID() );
    assert( 0 == $vote->getPresentationID() );
    assert( ! $vote->getSurveyID() );
    assert( ! $vote->getVoteDate() );
    assert( ! $vote->getVoteType() );
    assert( ! $vote->getVoterID() );
    assert( 1 == $vote->getVotesAllowed() );

    $vote->setChoiceID(34);
    $vote->setPresentationID(34);
    $vote->setSurveyID(978);
    $vote->setVoteDate('2001');
    $vote->setVoteType('WEB');
    $vote->setVoterID(3);
    $vote->setVotesAllowed(6);

    assert( 34 == $vote->getChoiceID() );
    assert( 34 == $vote->getPresentationID() );
    assert( 978 == $vote->getSurveyID() );
    assert( '2001' == $vote->getVoteDate() );
    assert( 'WEB' == $vote->getVoteType() );
    assert( 3 == $vote->getVoterID() );
    assert( 6 == $vote->getVotesAllowed() );
    try
    {
        $vote->setVoteType('GHE');
        assert(false);
    }
    catch(SurveyException $e)
    {
        assert( $e->getMessage() == "Invalid vote type" );
    }
}

if(true) /* test SurveyVO */
{
    echo '.';
    require_once("$vgPath/VO/surveyVO.php");
    $survey = new SurveyVO();
    assert( 0 == $survey->getActivePresentationID() );
    assert( 0 ==  $survey->getAnswer() );
    assert(!  $survey->getChoiceByNum(0) );
    assert( ! $survey->getChoiceIDByReceiver('010') );
    assert( array()==  $survey->getChoices() );
    assert( 0 ==  $survey->getNumOfChoices() );
    assert( 0 == $survey->getNumOfPresentations() );
    assert( 0 ==  $survey->getPoints() );
    assert(!  $survey->getPageID() );
    assert(!  $survey->getPresentationByNum(0) );
    assert( array() ==  $survey->getPresentations() );
    assert(!  $survey->getQuestion() );
    assert(!  $survey->getSurveyID() );
    assert( 1 ==  $survey->getType() );
    assert( 1 == $survey->getVotesAllowed() );

    $survey->setPageID(34);
    $survey->setSurveyID(45);
    $survey->setQuestion(" test ");
    $survey->setAnswer("34");
    $survey->setPoints("73");
    $survey->setType( 2 );
    //@todo setChoices are for scenario testing
    //@todo setPresentations are for scenario testing
    $survey->setVotesAllowed(1923);

    assert( $survey->getPageID() == 34 );
    assert( $survey->getSurveyID() == 45 );
    assert( $survey->getQuestion() == 'test'  );
    assert( $survey->getAnswer() == '34'  );
    assert( $survey->getPoints() == '73'  );
    assert( $survey->getType() == '2'  );
    assert( 1923 == $survey->getVotesAllowed() );

    try
    {
        $survey->setQuestion("  ");
        assert(false);
    } catch (Exception $e)
    {

    }
    assert( $survey->getQuestion() == 'test'  );
    try
    {
        $survey->setAnswer("34a");
        assert(false);
    } catch (Exception $e)
    {

    }
    assert( $survey->getAnswer() == '34'  );
    try
    {
        $survey->setPoints("73a");
        assert(false);
    } catch (Exception $e)
    {

    }
    assert( $survey->getPoints() == '73'  );
    assert( strlen($survey->toXML()) > 10);

    //@todo test getChoiceIDByReceiver
    //@todo test getActivePresentationID
}

if( true ) /* test PageVO */
{
    echo '.';
    require_once("$vgPath/VO/PageVO.php");
    $page = new PageVO();
    assert( ! $page->getPageID() );
    assert( ! $page->getTitle() );
    assert( $page->getPhone() == '000' );
    assert( $page->getAuthor() == 'UnknownUser' );
    assert( $page->getStartTime() == '2999-01-01 00:00:00' );
    assert( $page->getDuration() == 60 );
    assert( $page->getEndTime() == '2999-01-01 00:00:00' );
    // assert( ! $page->getCreateTime() );
    assert( $page->isSMSRequired() == '0' );

    assert( $page->isShowGraph() == '1' );
    assert( $page->getType() == 1 );
    assert( $page->getDisplayTop() == 0 );
    assert( $page->getVotesAllowed() == 1 );
    assert( $page->isSubtractWrong() == '0' );
    assert( ! $page->getSurveys() );
    assert( $page->getPrivacy() == 1);
    // @todo test getSurveyBySurveyID()
    // @todo test validateDate();
    assert( $page->getNumOfSurveys() == 0 );
    assert( $page->getPhoneVoting() == 'anon' );
    assert( $page->getWebVoting() == 'anon' );

    $page->setTitle('page1');
    $page->setPageID(45);
    $page->setAuthor('Admin');
    $page->setPhone('+060197654321');//for activation
    $page->setDisplayTop(14);
    $page->setVotesAllowed(7);
    $page->setSMSRequired(true);
    $page->setShowGraph(false);
    $page->setSubtractWrong(true);
    $page->setType(2);

    $page->setPrivacy(3);
    $page->setPhoneVoting('no');
    $page->setWebVoting('no');

    try
    {
        $page->setTitle(' 	');
        assert(false);
    } catch(Exception $e)
    {

    }
    assert($page->getTitle() == 'page1');

    $page->setStartTime('2001-01-01 00:00:00');
    $page->setDuration(240);
    assert( $page->getEndTime() == '2001-01-01 04:00:00' );
    assert($page->getPageID() == 45);
    $page->setEndTime('2001-01-01 03:00:00');
    assert( $page->getAuthor() == 'Admin' );
    assert( $page->getPhone() == '+060197654321' );
    assert( $page->getPrivacy() == 3);
    assert($page->getDisplayTop() == 14);
    assert($page->getVotesAllowed() == 7);
    assert($page->isSMSRequired() == '1');
    assert( $page->isShowGraph() == '0' );
    assert( $page->isSubtractWrong() == '1' );
    assert( $page->getType() == 2 );
    assert( $page->getPhoneVoting() == 'no' );
    assert( $page->getWebVoting() == 'no' );

    //This will throw exception if something is wrong
    $page->setPrivacy(vPRIVACY_LOW);
    $page->setPrivacyByName( $page->getPrivacyByName() );
    $page->setPrivacy(vPRIVACY_MEDIUM);
    $page->setPrivacyByName( $page->getPrivacyByName() );
    $page->setPrivacy(vPRIVACY_HIGH);
    $page->setPrivacyByName( $page->getPrivacyByName() );
}

if( true ) /* testing SurveyRecordVO */
{
    echo '.';
    require_once("$vgPath/VO/SurveyRecordVO.php");
    $sr = new SurveyRecordVO();
    assert( ! $sr->getChoiceID() );
    assert( $sr->getPresentationID() == 1 );
    assert( ! $sr->getSurveyID()  );
    assert( strlen($sr->getVoteDate())>10 ); // vfDate()
    assert( ! $sr->getVoteType() );
    assert( ! $sr->getVoterID() );

    $sr->setChoiceID( 45);
    $sr->setPresentationID( 235);
    $sr->setSurveyID( 2356 );
    $sr->setVoteDate( '2002' );
    $sr->setVoteType('CALL');
    $sr->setVoterID( 'Admin' );

    assert(45 == $sr->getChoiceID());
    assert(235 == $sr->getPresentationID());
    assert(2356 == $sr->getSurveyID());
    assert('2002' == $sr->getVoteDate());
    assert('CALL' == $sr->getVoteType());
    assert('Admin' == $sr->getVoterID());

    try
    {
        $sr->setVoteType('KJS');
        assert(false);
    }
    catch(Exception $e)
    {

    }
    assert('CALL' == $sr->getVoteType());
}

if( true ) /* testing SurveyRecordDAO */
{
    echo '.';
    require_once("$vgPath/DAO/SurveyRecordDAO.php");
    $srdao = new SurveyRecordDAO();

    $sr = new SurveyRecordVO();
    $sr->setChoiceID(1);
    $sr->setPresentationID(1);
    $sr->setSurveyID(1);
    $sr->setVoteType('SMS');
    $sr->setVoterID( 'Admin' );
    assert( $srdao->isMultipleVote( $sr->getSurveyID(), $sr->getVoterID() ) == false  );
    $srdao->insertRecord($sr);
    assert( $srdao->isMultipleVote( $sr->getSurveyID(), $sr->getVoterID() ) == true  );
    #@todo $srdao->isFirstVoting();
}

if( true ) /* testing CallVO */
{
    echo '.';
    require_once("$vgPath/VO/CallVO.php");
    $call = new CallVO();
    assert( !$call->getCallID() );
    assert( !$call->getDate() );
    assert( !$call->getFrom() );
    assert( !$call->getErrorCode() );
    assert( !$call->getTo() );

    $call->setCallID(2);
    $call->setDate('2001-01-01 04:00:00');
    $call->setFrom('01023456');
    $call->setErrorCode(3);
    $call->setTo('032434');

    assert(2 == $call->getCallID() );
    assert('2001-01-01 04:00:00' == $call->getDate() );
    assert('01023456' == $call->getFrom() );
    assert(3 == $call->getErrorCode() );
    assert('032434' == $call->getTo() );
}

if( true ) /* testing SmsVO */
{
    echo ".";
    require_once("$vgPath/VO/SmsVO.php");
    $sms = new SmsVO();
    assert(! $sms->getDate() );
    assert(! $sms->getErrorCode() );
    assert(! $sms->getSmsID() );
    assert(! $sms->getFrom() );
    assert(! $sms->getText() );

    $sms->setDate('2001-01-01 04:00:00');
    $sms->setErrorCode(34);
    $sms->setFrom('8768768');
    $sms->setSmsID(67);
    $sms->setText('text test text');

    assert('2001-01-01 04:00:00' == $sms->getDate() );
    assert(34 == $sms->getErrorCode() );
    assert(67 == $sms->getSmsID() );
    assert('8768768' == $sms->getFrom() );
    assert('text test text' == $sms->getText() );
}

if( true ) /* testing Telephone */
{
    require_once("$vgPath/DAO/Telephone.php");

    $p = new PageVO();
    $p->setTitle('Question 1');
    $p->setStartTime(vfDate());
    if(true)
    {
        $s1 = new SurveyVO();
        $s1->setQuestion('How are you?');
        $c1 = new ChoiceVO();
        $c1->setChoice('good');
        $c2 = new ChoiceVO();
        $c2->setChoice('bad');
        $s1->setChoices(array($c1, $c2));

        $s2 = new SurveyVO();
        $s2->setQuestion('What day is today?');
        $c1 = new ChoiceVO();
        $c1->setChoice('mon');
        $c2 = new ChoiceVO();
        $c2->setChoice('tue');
        $c3 = new ChoiceVO();
        $c3->setChoice('wed');
        $c4 = new ChoiceVO();
        $c4->setChoice('thu');
        $c5 = new ChoiceVO();
        $c5->setChoice('fri');
        $s2->setChoices(array($c1, $c2, $c3, $c4, $c5));

        $p->setSurveys(array($s1, $s2));
    }
    require_once("$vgPath/DAO/surveyDAO.php");

    $surdao = new SurveyDAO();
    $surdao->insertPage($p);
    $s = $p->getSurveys();
    assert(count($s) == 2);
    assert($s[0]->getSurveyID() == 1 && $s[1]->getSurveyID() == 2);

    $c1 = $s[0]->getChoices();
    $c2 = $s[1]->getChoices();
    assert(count($c1) == 2 && count($c2) == 5);
    assert($c1[0]->getChoiceID() == 1);
    assert($c2[4]->getChoiceID() == 5);

    $t = new Telephone();
    $g = $t->makeGroups(array('11','12','24','37','38','39'));
    assert(count($g) == 3 && count($g[0]) == 2 && count($g[1]) == 1 && count($g[2]) == 3);
    $g = $t->makeGroups(array('+011','+012','+024','+037','+038','+039'));
    assert(count($g) == 3 && count($g[0]) == 2 && count($g[1]) == 1 && count($g[2]) == 3);

    assert( count($t->getAvailablePhones()) == count($t->getAllPhones()) );
    global $vgDB;
    $vgDB->Execute("INSERT INTO {$vgDBPrefix}usedreceivers VALUES ('+60102911310')");
    $vgDB->Execute("INSERT INTO {$vgDBPrefix}usedreceivers VALUES ('+60102911319')");
    assert( count($t->getAvailablePhones()) == count($t->getAllPhones())-2 );
    $g = $t->makeGroups($t->getAvailablePhones());

    assert(count($g) == 3 && count($g[0]) == 10 && count($g[1]) == 8);
    assert(count($g[2]) == count($t->getAllPhones()) - 2 - 10 - 8);
    $vgDB->Execute("INSERT INTO {$vgDBPrefix}usedreceivers VALUES ('+60102911305')");

    $g = $t->makeGroups($t->getAvailablePhones());
    assert(count($g) == 4);

    $t->setupReceivers($p);
    $surdao->updateReceiversSMS($p);

    $g = $t->makeGroups($t->getAvailablePhones());
    assert(count($g) == 3);
    assert( count($t->getAvailablePhones()) == count($t->getAllPhones())-3-2-5 );
}

if( false ) /* testing Usr */
{
    echo '.';
    require_once("$vgPath/DAO/Usr.php");
    $user = new Usr('TestUser');
}

if( true ) /* testing DAO/UserphonesDAO */
{
    echo '.';
    require_once("$vgPath/DAO/UserphonesDAO.php");
    require_once("$vgPath/MwAdapter.php");

    /* Mocking object*/
    class MwUserMock extends MwUser
    {
        public function __construct()
        {
            //parent::__construct(); don't call parent
        }
        function isAnon() { return false; }
        function getName() { return 'TestUser'; }
    };
    $user = new MwUserMock();
    $p = new UserphonesDAO( $user );
    $p->addNewPhone('123456');
    $l = $p->getList();
    assert(count($l) == 1);
    assert($l[0]['status'] == vPHONE_NEW);
    assert($l[0]['number'] == '123456');
    assert($p->checkConfirmAllowed());
    $code = $p->getConfirmCode($l[0]['id']);
    assert(! $p->checkConfirmAllowed());
    try
    {
        $p->verifyCode($l[0]['id'], $code.'A');
        assert(false);
    }
    catch(Exception $e)
    {
        assert(true);
    }
    $l = $p->getList();
    assert(count($l) == 1);
    assert($l[0]['id'] == 1);
    assert($l[0]['status'] == vPHONE_SENT_CODE);
    assert($p->verifyCode($l[0]['id'], $code));
    $l = $p->getList();
    assert(count($l) == 1);
    assert($l[0]['status'] == vPHONE_VERIFIED);
    assert($p->deletePhone($l[0]['id']));
    $l = $p->getList();
    assert(count($l) == 1);
    assert($l[0]['status'] == vPHONE_DELETED);
    $p->addNewPhone('123456');

}

if( true ) /* testing */
{
    ;
}

if( true ) /* testing */
{
    ;
}
echo "\n";
pointTime('the end');
die("\nDone with testing.\n");

/* ********************************************************************************** */
/* ********************************************************************************** */
/* ********************************************************************************** */
/* ********************************************************************************** */
/* ********************************************************************************** */
/*
 * These are scenario testing
 */
require_once("./survey/surveyDAO.php");
require_once("./survey/Usr.php");

$q1 = "Test question 1";
$q2 = "Test question 2";
$author = "Emir Habul";

/*
	 * Test #1
	 *
	 * Insert two pages
	 * set page2 to displaytop=10
	 * set duration time to 51 minutes
*/

//create a new Page
$page = new PageVO();
$page->setTitle($q1);
$page->setAuthor($author);

//Write data into Database
$surveyDAO = new SurveyDAO();
$databaseWritten=true;
assert($surveyDAO->insertPage($page));

/*
	 * add some choices to first question
*/

$post_questions = array( 'question1', 'question2' );
$question_choices = array( 
        'q1choice' => array( 'yes', 'no' ),
        'q2choice' => array( 'da', 'ne' ),
);

$questions = array();
$questionIndex=0;
foreach($post_questions as $question)
{
    $question=stripslashes($question);
    $survey = new SurveyVO();
    $survey->setQuestion($question);
    $questionIndex++;
    $choices = array();
    $choiceIndex=0;
    foreach($question_choices["q$questionIndex".'choice'] as $choice)
    {
        $choice=stripslashes($choice);
        if ($choice)
        {
            $choiceVO = new ChoiceVO();
            $choiceVO->setChoice($choice);
            $choices[] = $choiceVO;
        }
    }
    // Insert $choices into Survey
    $survey->setChoices($choices);
    $questions[]=$survey;
}


$page = $surveyDAO->findByPage( $page->getTitle() );
foreach( $questions as $sry  )
{
    echo "numch:".count($sry->getChoices())."\n";
    echo "ID:".$sry->getSurveyID()."\n";
    foreach($sry->getChoices() as $choice)
    {
        echo "ch survey id:". $choice->getSurveyID() . "\n";
    }
}

//$page->setSurveys($questions);
//$surveyDAO->updatePage($page);
exit;

$page2 = new PageVO();
$page2->setTitle($q2);
$page2->setAuthor($author);
assert($surveyDAO->insertPage($page2));
$page2 = $surveyDAO->findByPage($q2);

assert($page2->getAuthor() == $author);
assert($page2->getPageID() == "2");

$page2->setDisplayTop(2);
$surveyDAO->updatePage($page2);

assert(strtotime($page2->getEndTime()) - strtotime($page2->getStartTime()) == $page2->getDuration() * 60);
$page2->setDuration(51);
assert(strtotime($page2->getEndTime()) - strtotime($page2->getStartTime()) == $page2->getDuration() * 60);

/*
	 * add quiz for second question
*/

$post_questions = array( 'question1', 'question2' );
$question_choices = array(
        'q1comment' => ' my comment 1',
        'q1point' => 4,
        'q1answer' => 2,
        'q1choice' => array('one','two'),

        'q2comment' => ' my comment 2',
        'q2point' => 40,
        'q2answer' => 1,
        'q2choice' => array('three','four'),
);

$questions = array();
$questionIndex=0;
foreach($post_questions as $question)
{
    $question=stripslashes($question);
    $survey = new SurveyVO();
    $questionIndex++;
    $comment='';
    if(isset($question_choices["q$questionIndex".'comment']))
        $comment=$question_choices["q$questionIndex".'comment'];
    $comment=stripslashes($comment);
    $survey->setQuestion($question.$comment);
    $point=1;
    if(isset($question_choices["q$questionIndex".'point']))
        $point=$question_choices["q$questionIndex".'point'];
    $answer=0;
    if(isset($question_choices["q$questionIndex".'answer']))
        $answer=$question_choices["q$questionIndex".'answer'];
    $choices = array();
    $choiceIndex=0;
    foreach($question_choices["q$questionIndex".'choice'] as $choice)
    {
        $choice=stripslashes($choice);
        //create each Choice.
        if ($choice)
        {
            $choiceVO = new ChoiceVO();
            $choiceVO->setChoice($choice);
            $choiceVO->setSurveyID( $survey->getSurveyID() );
            $choices[] = $choiceVO;
        }
    }
    // Insert $choices into Survey
    $survey->setChoices($choices);
    $survey->setAnswer($answer);
    $survey->setPoints($point);
    $questions[]=$survey;
} 
$page2->setSurveys($questions);
//print_r($page);
$surveyDAO->updatePage($page2);

/*
	 * User "User1"
*/
$user = new Usr("User1");
//$user->vote(  );
$p2surveys = $page2->getSurveys();
assert($p2surveys[0]->getSurveyID() == 3);
assert( count( $p2surveys[0]->getChoices() ) == 2 );
$p2choices = $p2surveys[0]->getChoices();

assert(true);

?>