<?php
	$IP = '/xampp/htdocs/new';
	define('MEDIAWIKI', true);
	define('VOTAPEDIA_TEST', true);
	
	ini_set('include_path',ini_get('include_path').';C:\\xampp\\php\\PEAR\\');
	require_once("./votapedia.php");
	require_once("$gvPath/empty_database.php");

	if(true) /* Test choiceVO */
	{
		echo '.';
		require_once("./survey/VO/ChoiceVO.php");
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
		require_once("./survey/VO/PresentationVO.php");
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
		require_once('./survey/VO/VoteVO.php');
		$vote = new VoteVO();
		assert( ! $vote->getChoiceID() );
		assert( ! $vote->getInvalidAllowed() );
		assert( 1 == $vote->getPresentationID() );
		assert( ! $vote->getSurveyID() );
		assert( ! $vote->getVoteDate() );
		assert( ! $vote->getVoteType() );
		assert( ! $vote->getVoterID() );
		assert( 1 == $vote->getVotesAllowed() );
		
		$vote->setChoiceID(34);
		$vote->setInvalidAllowed(true);
		$vote->setPresentationID(34);
		$vote->setSurveyID(978);
		$vote->setVoteDate('2001');
		$vote->setVoteType('WEB');
		$vote->setVoterID(3);
		$vote->setVotesAllowed(6);

		assert( 34 == $vote->getChoiceID() );
		assert( true == $vote->getInvalidAllowed() );
		assert( 34 == $vote->getPresentationID() );
		assert( 978 == $vote->getSurveyID() );
		assert( '2001' == $vote->getVoteDate() );
		assert( 'WEB' == $vote->getVoteType() );
		assert( 3 == $vote->getVoterID() );
		assert( 6 == $vote->getVotesAllowed() );

		$vote->setInvalidAllowed(false);
		assert( false == $vote->getInvalidAllowed() );
		
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
		require_once('./survey/VO/surveyVO.php');
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
		assert( '1' == $survey->isInvalidAllowed() );
		
		$survey->setPageID(34);
		$survey->setSurveyID(45);
		$survey->setQuestion(" test ");
		$survey->setAnswer("34");
		$survey->setPoints("73");
		$survey->setType( 2 );
		//@todo setChoices are for scenario testing
		//@todo setPresentations are for scenario testing
		$survey->setInvalidAllowed(false);
		$survey->setVotesAllowed(1923);
		
		assert( $survey->getPageID() == 34 );
		assert( $survey->getSurveyID() == 45 );
		assert( $survey->getQuestion() == 'test'  );
		assert( $survey->getAnswer() == '34'  );
		assert( $survey->getPoints() == '73'  );
		assert( $survey->getType() == '2'  );
		assert( '0' == $survey->isInvalidAllowed() );
		assert( 1923 == $survey->getVotesAllowed() );
		
		try { $survey->setQuestion("  "); assert(false); } catch (Exception $e) {}
		assert( $survey->getQuestion() == 'test'  );
		try { $survey->setAnswer("34a"); assert(false); } catch (Exception $e) {}
		assert( $survey->getAnswer() == '34'  );
		try { $survey->setPoints("73a"); assert(false); } catch (Exception $e) {}
		assert( $survey->getPoints() == '73'  );
		assert( strlen($survey->toXML()) > 10);
		
		//@todo test getChoiceIDByReceiver
		//@todo test getActivePresentationID
	}
	
	if( true ) /* test PageVO */
	{
		echo '.';
		require_once('./survey/VO/PageVO.php');
		$page = new PageVO();
		assert( ! $page->getPageID() );
		assert( ! $page->getTitle() );
		assert( $page->getPhone() == '000' );
		assert( $page->getAuthor() == 'UnknownUser' );
		assert( $page->getStartTime() == '2000-01-01 00:00:00' );
		assert( $page->getDuration() == 60 );
		assert( $page->getEndTime() == '2000-01-01 01:00:00' );
		// assert( ! $page->getCreateTime() );
		assert( $page->isInvalidAllowed() == '1' );
		assert( $page->isSMSRequired() == '0' );
		assert( $page->getTeleVoteAllowed() == '1' );
		assert( $page->isAnonymousAllowed() == '1' );
		assert( $page->isShowGraph() == '1' );
		assert( $page->getType() == 1 );
		assert( $page->getDisplayTop() == 0 );
		assert( $page->getVotesAllowed() == 1 );
		assert( $page->isSubtractWrong() == '0' );
		assert( ! $page->getSurveys() );
		// @todo test getSurveyBySurveyID()
		// @todo test validateDate();
		assert( $page->getNumOfSurveys() == 0 );
		
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
		$page->setTeleVoteAllowed(false);
		
		try{ $page->setTitle(' 	'); assert(false); } catch(Exception $e){}
		assert($page->getTitle() == 'page1');
		
		$page->setStartTime('2001-01-01 00:00:00');
		$page->setDuration(240);
		assert( $page->getEndTime() == '2001-01-01 04:00:00' );
		assert($page->getPageID() == 45);
		$page->setEndTime('2001-01-01 03:00:00');
		assert( $page->getDuration() == 180 );
		assert( $page->getAuthor() == 'Admin' );
		assert( $page->getPhone() == '+060197654321' );
		
		$page->setAnonymousAllowed(false);
		assert( $page->isAnonymousAllowed() == false );
		$page->setAnonymousAllowed(true);
		assert( $page->isAnonymousAllowed() == true );
		assert($page->getDisplayTop() == 14);
		assert($page->getVotesAllowed() == 7);
		assert($page->isSMSRequired() == '1');
		assert( $page->isShowGraph() == '0' );
		assert( $page->isSubtractWrong() == '1' );
		assert( $page->getType() == 2 );
		assert( $page->getTeleVoteAllowed() == '0' );
	}
		
	if( true ) /* testing SurveyRecordVO */
	{
		echo '.';
		require_once('./survey/VO/SurveyRecordVO.php');
		$sr = new SurveyRecordVO();
		assert( ! $sr->getChoiceID() );
		assert( $sr->getPresentationID() == 1 );
		assert( ! $sr->getSurveyID()  );
		assert( strlen($sr->getVoteDate())>10 ); // date("Y-m-d H:i:s")
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
		
		try{$sr->setVoteType('KJS');assert(false);}
		catch(Exception $e) {}
		assert('CALL' == $sr->getVoteType());
	}
	
	if( true ) /* testing SurveyRecordDAO */
	{
		echo '.';
		require_once('./survey/SurveyRecordDAO.php');
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
		require_once('./survey/VO/CallVO.php');
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
		echo '.';
		require_once('./survey/VO/SmsVO.php');
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
		echo '.';
		require_once('./survey/Telephone.php');
		$t = new Telephone();
		assert( count($t->getAvailablePhones()) == count($t->getAllPhones()) );

		$p = new PageVO();
		$p->setPageID(1);
		$p->setTitle('q1');
		$p->setStartTime(date("Y-m-d H:i:s"));
		
		if(true)
		{
			$s = new SurveyVO();
			$s->setPageID(1);
			$s->setSurveyID(1);
			$s->setQuestion('How are you?');
			if (true)
			{
				$c = new ChoiceVO();
				$c->setSurveyID(1);
				$c->setChoiceID(1);
				
				$c->setChoice('yes');
				$s->setChoices(array($c));
			}
			$p->setSurveys(array($s));
		}
		$t->setupReceivers(&$p);
		foreach($p as &$s)
		{
			foreach($s as &$c)
			{
				echo "Chosen: ".$c->getReceiver()."\n";
			}
		}
	}
	
	if( false ) /* testing Usr */
	{
		echo '.';
		require_once("./survey/Usr.php");
		$user = new Usr('TestUser');
	}
	
	if( true ) /* testing */
	{
		;
	}
	
	if( true ) /* testing */
	{
		;
	}
	
	if( true ) /* testing */
	{
		;
	}
	
	die("\nDone with testing.\n");

	/* ********************************************************************************** */
	/**
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
	//var_dump($p2choices);
	//var_dump( $p2surveys[0]->getChoiceByNum(1)->getReceiver() );
	//$user->vote( $p2surveys[0]->getSurveyID(), 1 );
	
	/*
	 * Test phone
	 * 
	 */
	
	require_once('./survey/Telephone.php');
	
	$telephone = new Telephone();
	echo count( $telephone->getAvailablePhone() ) ."\n";
	foreach( $page->getSurveys() as $sry  )
	{
		echo "numch:".count($sry->getChoices())."\n";
		echo "ID:".$sry->getSurveyID()."\n";
		foreach($sry->getChoices() as $choice)
		{
			echo "ch survey id:". $choice->getSurveyID() . "\n";
		}
	}
	//echo count()."\n";
	//var_dump( $telephone->setupReceivers($page) );
	//echo count( $telephone->getAvailablePhone() ) ."\n";
	
	assert(true);
	$cn->Close();
?>