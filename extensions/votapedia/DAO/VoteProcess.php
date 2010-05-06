<?php
if (!defined('MEDIAWIKI')) die();

missing_function();
	//Need to be INCLUDED, not REQUIRED
	echo $vote->voteType . $vote->voterID . $vote->surveyID . $vote->choiceID;
	//Check whether have a user associated with the mobile phone
	//If it is, then use his mobilephone as voterID
	if($vote->voteType =='WEB')
	{
		assert(false);
		$sqlMobile = "select * from view_usermobile_only where user_name='".$vote->voterID."'";
		$rsMobile = $cn->Execute($sqlMobile);
		if ($rsMobile->RecordCount()>0)
			$vote->voterID= $rsMobile->fields['user_mobilephone'];
	}
	
	if ($vote->invalidAllowed == 0 && $vote->voterID == '-1')
	{
		// "Invalid vote prohabited";
		if($vote->voteType =='SMS' || $vote->voteType=='CALL')
		$finalSql[] ="update incoming$vote->voteType set Errorcode = '5' where ID = $id";
	}
	// Allows multi-votes
	else if ($vote->invalidAllowed == 1 )
	{
		// invalid allowed and mulitiple votes allowed
		echo "insert all";
		$finalSql[]="insert into surveyRecord
		            	             (voterID,surveyID,choiceID,presentationID,voteDate,voteType) 
		                          values('".$vote->voterID."',$vote->surveyID, $vote->choiceID,
		                          $vote->presentationID,
									    '$vote->voteDate','$vote->voteType')" ;
		                          $finalSql[]="update surveyChoice set vote=vote+1 where surveyID = $surveyID and choiceID = $choiceID";
	}
	else
	{
		// Check whether voted before
		$sql ="select * from surveyrecord where voterID = '".$vote->voterID."'
		                                                    and surveyID = $vote->surveyID
		                                                    and presentationID = $vote->presentationID order by voteDate asc";
		$rs = $cn->Execute($sql);
	
		echo "voted before:".$rs->RecordCount();

		if ($rs->RecordCount()>=$vote->votesAllowed )
		{
			$IDbyOldVote = $rs->fields['ID'];
			$choiceIDbyOldVote = $rs->fields['choiceID'];
			//$choiceIDbyOldVote = $rs->fields['choiceID'];
			$finalSql[]= "update surveyrecord set choiceID = $vote->choiceID , voteDate = '$vote->voteDate' where ID = $IDbyOldVote ";
			$finalSql[]= "update surveyChoice set vote=vote+1 where surveyID = $vote->surveyID and choiceID = $vote->choiceID";
			$finalSql[]= "update surveyChoice set vote=vote-1 where surveyID = $vote->surveyID and choiceID = $choiceIDbyOldVote";
			if($vote->voteType =='SMS' || $vote->voteType=='CALL')
			$finalSql[]= "update incoming$vote->voteType set errorcode=4 where ID=$id";
			echo "update";
		}
		else
		{
			$finalSql[]="insert into surveyRecord
		                         (voterID,surveyID,choiceID,presentationID,voteDate,voteType) 
		                                     values('".$vote->voterID."',$vote->surveyID, $vote->choiceID,
	                                     $vote->presentationID,
									    '$vote->voteDate','$vote->voteType')" ;
	        $finalSql[]="update surveyChoice set vote=vote+1 where surveyID = $surveyID and choiceID = $choiceID";
	        echo "renew";
		}
	}
?>