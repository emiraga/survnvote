<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */

global $gvPath;
require_once("$gvPath/VO/SurveyVO.php");
/**
 * This page includes class TeleNumber which is in care of
 * allocating available telephones to surveys based on
 * diffent stategies.
 *
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
		$this->allPhones = vfGetAllNumbers();
		$this->numberOfPhones = count($this->allPhones);
	}
	/**
	 * Initiate connection before using this function
	 * Gets available telephones
	 * 
	 * @return Array $telephone the array of available telephone numbers
	 */
	function getAvailablePhones($locked = false)
	{
		global $vgDB,$vgDBPrefix;
		if(!$locked)
			$sql = "select receiver from {$vgDBPrefix}usedreceivers{$addTable}";
		else
			$sql = "select receiver from {$vgDBPrefix}usedreceivers as {$vgDBPrefix}usedreceivers_r";
		
		$rs= &$vgDB->GetAll($sql);
		$telephones = array();
		foreach($rs as $r)
		{
			$telephones[] = $r[0];
		}
		return array_values( array_diff($this->allPhones, $telephones) );
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
	 * Calculate next sequential phone number
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
	function makeGroups($telephones)
	{
		$seqPhones = array();
		$temp = array($telephones[0]);
		$expect = $this->nextPhone( $telephones[0] );

		for ($i=1;$i<count($telephones);$i++)
		{
			if ($telephones[$i] == $expect)
			{
				$temp[] = $telephones[$i];
			}
			else
			{
				$seqPhones[]=$temp;
				$temp = array($telephones[$i]);
			}
			$expect = $this->nextPhone($telephones[$i]);
		}
		$seqPhones[]=$temp;
		return $seqPhones;
	}
	/**
	 * For a given survey allocate available phones.
	 * 
	 * @param $survey
	 * @param $listedPhones represents the history of used phones history by the author
	 * @param $history represents the history of used block
	 * @param $availablePhones  available phones now
	 */
	public function allocatePhonesSequence(SurveyVO &$survey, &$groups)
	{
		$number = $survey->getNumOfChoices();
		$sortkey = array();
		$sortvalue = array();
		foreach($groups as $gr)
		{
			if(count($gr) >= $number)
			{
				$sortkey[] = count($gr);
				$sortvalue[] = $gr;
			}
		}
		if(count($sortkey) == 0)
			return array();
		array_multisort($sortkey, $sortvalue);
		//return $number of first elements from the smallest group
		return array_slice($sortvalue[0], 0, $number);
	}
	/**
	 * For a given survey allocate available phones.
	 * 
	 * @param $survey
	 * @param $listedPhones represents the history of used phones history by the author
	 * @param $history represents the history of used block
	 * @param $availablePhones  available phones now
	 */
	public function allocatePhones(SurveyVO &$survey, &$availablePhones)
	{
		$groups = $this->makeGroups($availablePhones);
		$number = $survey->getNumOfChoices();
		//Try to find a sequential block
		$seq = $this->allocatePhonesSequence($survey, $groups);
		if(count($seq) == $number)
			return $seq;
		//If that fails, take first X numbers
		return array_slice($availablePhones, 0, $number);
	}
	/**
	 * Request receivers for choices in surveys contained in PageVO
	 *
	 * @param $page PageVO
	 * @return boolean
	 */
	function setupReceivers(PageVO &$page)
	{
		global $vgDB, $vgDBPrefix;
		global $vgSmsChoiceLen;
		$pr = $vgDBPrefix;
		
		$surveys = &$page->getSurveys();
		$sqlChoice = "update {$pr}surveychoice set receiver = ?, sms = ? where surveyID = ? and choiceID = ?";
		$resChoice = $vgDB->Prepare($sqlChoice);
		
		$success = $vgDB->Execute("LOCK TABLES {$pr}usedreceivers WRITE, {$pr}usedreceivers AS {$pr}usedreceivers_r READ, surveychoice WRITE");
		if(! $success)
			throw new SurveyException('Failed to lock the usedreceivers table');

		$available = $this->getAvailablePhones(true);
		foreach($surveys as &$survey)
		{
			sort($available);
			//Allocate Phones into a survey
			$results = $this->allocatePhones($survey, $available);
			
			$number = $survey->getNumOfChoices();
			if (count($results)< $number)
				throw new SurveyException("No available phones!", 203);
			
			$i = 0;
			$surveyChoices =& $survey->getChoices();
			foreach($surveyChoices as &$surveyChoice)
			{
				$success = $vgDB->Execute("INSERT INTO {$pr}usedreceivers VALUES(?)", array($results[$i]));
				if($success == false)
				{
					$vgDB->Execute("UNLOCK TABLES"); 
					throw new SurveyException("Duplicate phone found, even though table was locked.");
				}
				#echo 'Phone added '.$results[$i]." for ". $surveyChoice->getChoice() ."\n";
				
				$surveyChoice->setReceiver($results[$i]);
				$surveyChoice->setSMS(substr($results[$i], -$vgSmsChoiceLen));
				
				$param = array(
					$surveyChoice->getReceiver(),
					$surveyChoice->getSMS(),
					$surveyChoice->getSurveyID(),
					$surveyChoice->getChoiceID(),
				);
				$vgDB->Execute($resChoice, $param);
				$i++;
			}
			//Get rid of the occupied numbers from the availablePhones
			$available = array_values( array_diff($available, $results) );
		}//foreach survey
		$vgDB->Execute("UNLOCK TABLES"); 
	}
}
?>