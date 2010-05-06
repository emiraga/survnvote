<?php
if (!defined('MEDIAWIKI')) die();

/**
 * This page includes class TeleNumber which is in care of
 * allocating available telephones to surveys based on
 * diffent stategies.
 * 
 * @package DAO of Survey
 */

require_once("$gvPath/survey/VO/SurveyVO.php");
/**
 * Blocks are groups of phones,
 * dynamic block is a separate group
 *
 */
class Telephone
{
	private $sizeOfBlock = 5;
	private $numberOfDynamic = 25;
	private $numberOfPhones;
	private $numberOfBlocks;
	private $blocks = array();
	private $dynamicBlock = array();
	private $allPhones = array();

	/**
	 *  Set up Blocks, which telephone numbers are read directly from database
	 *   - read telephone numbers from database
	 *   - write to $this->dynamicBlock $this->blocks
	 *   - count elements for $numberOfBlocks $numberOfDynamic
	 */
	function __construct()
	{
		global $gvDB, $gvDBPrefix;
		
		$sql = "select telenumber from {$gvDBPrefix}telenumber order by telenumber";
		// Force indexing by name not number
		$gvDB->SetFetchMode(ADODB_FETCH_ASSOC);
		$rs= &$gvDB->Execute($sql);

		$this->allPhones = array();
		if ($rs->RecordCount()>0)
		{
			while(!$rs->EOF)
			{
				$this->allPhones[]=$rs->fields['telenumber'];
				$rs->MoveNext();
			}
		}
		$rs->Close();

		$this->numberOfPhones = count($this->allPhones);

		$n = $this->numberOfPhones - $this->numberOfDynamic;
		for($i=0;$i<$n;)
		{
			$block = array();
			for ($j=0;$j<$this->sizeOfBlock;$j++)
			{
				$block[] = $this->allPhones[ $i ];
				$i++;
			}
			$this->blocks[]=$block;
		}
		$this->numberOfBlocks = count( $this->blocks );

		for (;$i<$this->numberOfPhones;$i++)
		{
			$this->dynamicBlock[]=$this->allPhones[$i];
		}
		
		$this->numberOfDynamic = count( $this->dynamicBlock );
	}
	
	/**
	 * Initiate connection before using this function
	 * Gets available telephones
	 * @return Array $telephone the array of available telephone numbers
	 */
	function getAvailablePhones()
	{
		global $gvDB, $gvDBPrefix;

		/* CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER 
		 * VIEW `view_available_telephone` AS select sql_no_cache  `telenumber`.`telenumber` AS `telenumber` from  `telenumber` 
		 * where not (
		 * 				`telenumber` in (
		 *					select sql_no_cache `view_current_survey`.`receiver` AS `receiver` from  `view_current_survey` 
		 * 					where (`view_current_survey`.`receiver` is not null)
		 * 				 )
		 *			 );
		 */
		/*
		 * CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER 
		 * VIEW  `view_current_survey` AS select sql_no_cache  `page`.`pageID` AS `pageID`, `page`.`title` AS `title`, 
		 * `page`.`startTime` AS `startTime`, `page`.`endTime` AS `endTime`, `page`.`duration` AS `duration`, 
		 * `page`.`author` AS `author`, `page`.`phone` AS `phone`, `page`.`createTime` AS `createTime`, 
		 * `page`.`invalidAllowed` AS `invalidAllowed`, `page`.`smsRequired` AS `smsRequired`, 
		 * `page`.`teleVoteAllowed` AS `teleVoteAllowed`, `page`.`anonymousAllowed` AS `anonymousAllowed`, 
		 * `page`.`surveyType` AS `surveyType`, `page`.`displayTop` AS `displayTop`, `page`.`votesAllowed` AS `votesAllowed`, 
		 * `survey`.`surveyID` AS `surveyID`, `survey`.`question` AS `question`, `survey`.`answer` AS `answer`, 
		 * `survey`.`points` AS `points`, `surveychoice`.`choiceID` AS `choiceID`, `surveychoice`.`choice` AS `choice`, 
		 * `surveychoice`.`receiver` AS `receiver`, `surveychoice`.`SMS` AS `sms`, `surveychoice`.`vote` AS `vote`, 
		 * `presentation`.`presentationID` AS `presentationID`, `presentation`.`active` AS `active` from 
		 *  (
		 *  	(
		 *  		(
		 *  			`page` join  `survey` on
		 *  				(
		 *  					( `page`.`pageID` =  `survey`.`pageID`)
		 *  				)
		 *  		)
		 *  		left join  `surveychoice` on
		 *  		(
		 *  			(
		 *  				`survey`.`surveyID` =  `surveychoice`.`surveyID`
		 *  			)
		 *  		)
		 *  	)
		 *  	left join `presentation` on
		 *  	(
		 *  		(
		 *  			(`survey`.`surveyID` =  `presentation`.`surveyID`) 
		 *  			and 
		 *  			( `presentation`.`active` = 1)
		 *  		)
		 *  	)
		 *  ) where (
		 *  			( `page`.`startTime` < now() ) 
		 *  			and ( `page`.`endTime` > now())
		 *  		);
		 */
		
		// Get available phones directly from view_available_telephone
		$sql = "select telenumber from {$gvDBPrefix}view_available_telephone order by telenumber";
		// Force indexing by name not number
		$gvDB->SetFetchMode(ADODB_FETCH_ASSOC);
		$rs= &$gvDB->Execute($sql);

		$telephones = array();
		if ($rs->RecordCount()>0)
		{
			while(!$rs->EOF)
			{
				//Access by name, some database may not support this
				// small case
				$telephones[]=$rs->fields['telenumber'];
				$rs->MoveNext();
			}
			$rs->Close();
		}
		else
		{
			throw new SurveyException("No available phones!", 203);
			return false;
		}
		return $telephones;
	}
	
	/**
	 * Get a list of all phones from database
	 * 
	 * @return list of all phones
	 */
	function getAllPhones()
	{
		return $this->allPhones;
	}
	
	/**
	 * Get blocks which are entirely available.
	 * Works even for a variable size of block
	 * Return Index of available blocks
	 * 
	 * @param $availablePhones array
	 */
	function getAvailableBlocks($availablePhones)
	{
		$result = array();
		
		for($i=0; $i<$this->numberOfBlocks; $i++)
		{
			$available = true;
			foreach( $this->blocks[$i] as $phone )
			{
				if( ! in_array($phone, $availablePhones) )
				{
					$available = false;
				}
			}
			if($available)
			{
				$result[] = $i;
			}
		}
		return $result;
	}
	/**
	 * Returns the history of usage of telephones
	 * First elements are those which have not been used
	 * followed by least used to most used at the end.
	 * 
	 * @return Array $usedBlocks the array of available telephone numbers
	 */
	function getBlockHistory()
	{
		global $gvDB, $gvDBPrefix;
		
		$sql="select receiver, count(receiver) as number from {$gvDBPrefix}surveychoice";
		$sql.=" group by receiver order by number desc, receiver ";

		$gvDB->SetFetchMode(ADODB_FETCH_ASSOC);
		$rs= &$gvDB->Execute($sql);

		$usedBlocks = array();
		while(!$rs->EOF)
		{
			$usedBlocks[]= trim($rs->fields["receiver"]);
			$rs->MoveNext();
		}
		$rs->Close();

		//Get the reversed useing frequency and unique phones.
		$results = array_reverse(array_unique(
			array_merge($usedBlocks,array_reverse($this->allPhones))
		));
		return $results;
	}
	/**
	 * Get the list of phones which are used by this author, order by used times.
	 * 
	 * @param $username name/ID of a user
	 * @return Array $usedPhones
	 */
	public function getUsedPhonesByUser($username)
	{
		global $gvDB, $gvDBPrefix;

		$sql  = "SELECT {$gvDBPrefix}surveychoice.receiver, count({$gvDBPrefix}surveychoice.receiver) as number ";
		$sql .= "FROM {$gvDBPrefix}page INNER JOIN {$gvDBPrefix}survey ON page.pageID = {$gvDBPrefix}survey.pageID INNER JOIN ";
		$sql .= "{$gvDBPrefix}surveychoice ON {$gvDBPrefix}survey.surveyID = {$gvDBPrefix}surveychoice.surveyID WHERE ";
		$sql .= "({$gvDBPrefix}page.author = ?) group by {$gvDBPrefix}surveychoice.receiver order by number desc";
		
		$gvDB->SetFetchMode(ADODB_FETCH_ASSOC);
		$rs= &$gvDB->Execute($sql,array( $username ));
		$usedPhones = array();
		while(!$rs->EOF)
		{
			$usedPhones[] =  $rs->fields["receiver"];
			$rs->MoveNext();
		}
		$rs->Close();
		return $usedPhones;
	}
	/**
	 * Calculate next sequencial phone number
	 * 
	 * @param $phone string phone
	 * @return next phone in sequene
	 */
	private function nextPhone($phone)
	{
		for($i=strlen($phone)-1; $i>=0; $i--)
		{
			if( $phone[$i] != '9' )
			{
				$phone[$i] = chr( ord($phone[$i]) + 1 );
				break;
			}
			$phone[$i] = '0';
		}
		return $phone;
	}
	/**
	 * Categorize sequential telephones to groups
	 * 
	 * @param $telephones a list of phones
	 * @return a list of groups of sequential phones
	 */
	private function makeGroups($telephones)
	{
		$seqPhones = array();
		$temp = array($telephones[0]);
		$init = nextPhone( $telephones[0] );

		for ($i=1;$i<count($telephones);$i++)
		{
			if ($init == $telephones[$i])
			{
				$temp[] = $init;
				$init = nextPhone($init);
			}
			else
			{
				$seqPhones[]=$temp;
				$temp = array($telephones[$i]);
				$init = nextPhone($telephones[$i]);
			}
		}
		$seqPhones[]=$temp;
		return $seqPhones;
	}
	/**
	 * Filter the sequential phone groups,
	 * get the number of telephone is >= the requiring telephone number
	 * 
	 * @param $seqPhones list of groups of phones
	 * @param $num limit for a size of group to filter
	 */
	function filterPhoneList($seqPhones, $num)
	{
		$filteredArray = array();
		foreach($seqPhones as $seqPhone)
		{
			if (count($seqPhone)>=$num)
			{
				$filteredArray[] = $seqPhone;
			}
		}
		/*
		if (count($filteredArray)==0)
		{
			throw new SurveyException("Available phones less than required!",203);
			return false;
		}
		*/
		return $filteredArray;
	}
	/**
	 * First, look for a available block that contains a phone which this user has already used.
	 * If there is an available block used by a user, return it.
	 * Otherwise, take least a used block.
	 * 
	 * Condition: Available blocks must exist.
	 * 
	 * @param @availableBlocks which blocks are availbable now
	 * @param @usedPhones the list of phones the authoer used before,
	 * program is trying to get the most used phones of the author firstly
	 * @param @historyBlock the history of block usage, DESC oder by occurence.
	 * If getting the most used phones of the authoer fails, 
	 * try to get the least used block.
	 */
	function retrieveBlock($availableBlocks, $usedPhones, $historyBlock)
	{
		if (count($availableBlocks)>0)
		{
			foreach ($usedPhones as $phone)
			{
				for ($i=0;$i<count($availableBlocks);$i++)
				{
					if ($phone >= $this->blocks[$availableBlocks[$i]][0] && $phone <=$this->blocks[$availableBlocks[$i]][$this->sizeOfBlock-1])
						return $this->blocks[$availableBlocks[$i]]; // Program jumps out if found a block
				}
			}
			// Deal with new users or all used blocks have been occupied
			// Pick up a least used block for this user
			foreach($historyBlock as $phone)
			{
				for ($i=0;$i<count($availableBlocks);$i++)
				{
					if ($phone >= $this->blocks[$availableBlocks[$i]][0] && $phone <=$this->blocks[$availableBlocks[$i]][$this->sizeOfBlock-1])
					return $this->blocks[$availableBlocks[$i]];
				}
			}
		}
		return false;
	}
	/**
	 * Retrived the requried phones from an available group.
	 * If $num matches the size of a group, it is immediatelly returned.
	 * Size of a $group must be larger that or equal to $identifiedPhone
	 * 
	 * @param $group contains sequential telephone numbers
	 * @param $num how many telephones is wanted to be gotten
	 * @param $identifiedPhone phone we are looking for
	 */
	function retrievePhonesFromGroup($group, $num, $identifiedPhone)
	{
		if (count($group)==$num)
		{
			return $group;
		}
		else
		{
			$mark = 0; // keep the position of the first available telephone matches the condition
			//Locate the position of $identifiedPhone
			for($i=0;$i<count($group);$i++)
			{
				if ($identifiedPhone == $group[$i])
				{
					$mark = $i;
					break;
				}
			}

			$min = 0;
			$max = count($group)-1;
			if (($max - $mark + 1)>=$num)
			{
				$results = array_slice($group,$mark,$num);
			}
			else
			{
				$results = array_slice($group,$max-$num,$num);
			}
			return $results;
		}
	}
	/**
	 * Retrieve the possible telephone group for this user.
	 * 
	 * @param $filteredPhones represents the number of sequential telephone
	 * numbers is larger than the requesting number.
	 * Array[1][2], 1 represents telephone number; 2 represents how many teles in this group
	 * @param $usedPhones represents the telephone numbers used by this user,
	 * order by the frequency of the telephone numbers.
	 * Arrary,  represents the telephone number.
	 * @param $num how many telephones are being requested.
	 */
	function retrieveGroup($filteredPhones, $usedPhones, $num)
	{
		$usingPhones = array();
		$possiblePhones = array();
		$identity=0;  //indicates which telephone in $usedPhones is listed in $filtetedPhones.

		for ($i=0,$cntused=count($usedPhones); $i<$cntused; $i++)
		{
			foreach($filteredPhones as $filteredPhone)
			{
				$upper = count($filteredPhone)-1;
				if ($usedPhones[$i]>=$filteredPhone[0] && $usedPhones[$i]<=$filteredPhone[$upper])
				{
					$possiblePhones =  $filteredPhone;
					$identity = $usedPhones[$i];
					break 2; // Jump up 2 levels , out "for"
				}
			}
		}

		if (count($possiblePhones)>0)
		{
			return $this->retrievePhonesFromGroup($possiblePhones,$num,$identity);
		}
		else
		{
			$possiblePhones = array_slice($filteredPhones[count($filteredPhones)-1],0,$num);
			return $possiblePhones;
		}
	}
	/**
	 * For a given survey allocate available phones.
	 * 
	 * @param $survey
	 * @param $listedPhones represents the history of used phones history by the author
	 * @param $history represents the history of used block
	 * @param $availablePhones  available phones now
	 */
	public function allocatePhones(SurveyVO $survey, $listedPhones, $history, $availablePhones)
	{
		$number = $survey->getNumOfChoices();

		$availableBlocks = $this->getAvailableBlocks($availablePhones);

		// Number of choices is not more then $this->sizeOfBlock, and
		// there are available blocks, then pick up phones from fixed blocks
		if ($number <= $this->sizeOfBlock && count($availableBlocks)>0)
		{
			//$history = $this->getBlockHistory($cn);
			$phones = $this->retrieveBlock($availableBlocks ,$listedPhones,$history);
			return $phones;
		}
		// Number of choices is more then $this->sizeOfBlock, or no available blocks,then pick up phones from Dynamic block
		else if($number<=$this->numberOfDynamic)
		{
			//Get the numbers in dynamic block which is still in available phones
			$availablePossiblePhones=array_intersect($this->dynamicBlock,$availablePhones);
			$sortedPhones = $this->makeGroups($availablePossiblePhones);
			$filteredPhones = $this->filterPhoneList($sortedPhones, $number);
			$results = $this->retrieveGroup($filteredPhones, $listedPhones ,$number);

			//Problem is here when there is only 1 numbers left...

			// If the survey cannot get the continous numbers from dynamic block,
			// then try to get all possible numbers in this block
			if (count($results)==0)
			{
				$results =  array_slice($availablePossiblePhones,0,$number);
			}
			// If the survey cannot get the numbers from dynamic block,
			// then try to get possible numbers in available phones.
			if (count($results)==0)
			{
				$results =  array_slice($availablePhones,0,$number);
			}
			return $results;
		}
		// Number of choices is more then 25:number of dynamic block
		// then just try to get telephones, no sequential number requirement.
		// Still have problem which would affect the normal block allocation.
		else
		{
			return array_slice($availablePhones,0,$number);
		}
	}
	/**
	 * Request receivers for choices in surveys contained in PageVO
	 *
	 * @param $page PageVO
	 * @return boolean
	 */
	function setupReceivers(PageVO &$page)
	{
		global $gvDB, $gvDBPrefix;
		
		$listedPhones = $this->getUsedPhonesByUser($page->getAuthor());
		//Set up Blocks, which telephone numbers are read directly from database
		$history = $this->getBlockHistory();

		$surveys = &$page->getSurveys();
		
		$gvDB->Execute("LOCK TABLES {$gvDBPrefix}view_available_telephone READ, {$gvDBPrefix}page WRITE");
		$availablePhones = $this->getAvailablePhones();
		foreach($surveys as &$survey)
		{
			//Allocate Phones into a survey
			$results = $this->allocatePhones($survey, $listedPhones, $history, $availablePhones);

			$number = $survey->getNumOfChoices();
			if (count($results)< $number)
			{
				$gvDB->Execute("UNLOCK TABLES");
				throw new SurveyException("No available phones!",203);
			}
			else
			{
				for($i=0;$i< $number;$i++)
				{
					$survey->getChoiceByNum($i)->setReceiver($results[$i]);
					$survey->getChoiceByNum($i)->setSMS(substr($results[$i],-2));
				}
				//Get rid of the occupied numbers froma availablePhones
				$availablePhones = array_diff($availablePhones, $results);
			}
		}

		//update startTime and EndTime
		$sql = "update {$gvDBPrefix}page set startTime = ?, endTime = ? where pageID = ?";
		$res = $gvDB->Prepare($sql);
		$gvDB->Execute($res,array( $page->getStartTime(), $page->getEndTime(), $page->getPageID() ));

		$gvDB->Execute("UNLOCK TABLES");
		
		//Update the receivers
		$sqlChoice = "update {$gvDBPrefix}surveychoice set receiver = ?, sms = ? where surveyID = ? and choiceID = ?";
		$resChoice = $gvDB->Prepare($sqlChoice);
		foreach($surveys as &$survey)
		{
			$surveyChoices = $survey->getChoices();
			$num = 0;
			foreach($surveyChoices as &$surveyChoice)
			{
				$num++;
				$param = array(
					$surveyChoice->getReceiver(),
					$surveyChoice->getSMS(),
					$surveyChoice->getSurveyID(),
					$num
				);
				$gvDB->Execute($resChoice, $param);
			}
		}
	}
}
?>