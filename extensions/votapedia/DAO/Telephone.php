<?php
if (!defined('MEDIAWIKI')) die();
/**
 * This package contains all data access objects.
 *
 * @package DataAccessObject
 */
global $vgPath;
require_once("$vgPath/VO/SurveyVO.php");

/**
 * Exception type for Telephone
 *
 * @package DataAccessObject
 */
class TelephoneException extends Exception
{

}
/**
 * This page includes class TeleNumber which is in care of
 * allocating available telephones to surveys based on
 * diffent stategies.
 * 
 * @package DataAccessObject
 */
class Telephone
{
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
     * @param Boolean $locked is this table locked, default false
     * @return Array $telephone the array of available telephone numbers
     */
    function getAvailablePhones()
    {
        global $vgDB,$vgDBPrefix;
        $sql = "select receiver from {$vgDBPrefix}usedreceivers";
        $rs= &$vgDB->GetAll($sql);
        $telephones = array();
        foreach($rs as $r)
        {
            $telephones[] = $r["receiver"];
        }
        return array_values( array_diff($this->allPhones, $telephones) );
    }
    /**
     * Get a list of all phones from database
     *
     * @return Array list of all phones
     */
    function getAllPhones()
    {
        return $this->allPhones;
    }
    /**
     * Calculate next sequential phone number
     *
     * @param String $phone phone
     * @return String next phone in sequence
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
     * @param Array $telephones a list of phones
     * @return Array a list of groups of sequential phones
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
     * @param SurveyVO $survey
     * @param Array $groups
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
     * @param SurveyVO $survey
     * @param Array $availablePhones available phones
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
     * Request receivers for choices in surveys contained in PageVO.
     * Updates database as well.
     *
     * @param PageVO $page
     * @return Boolean true
     */
    function setupReceivers(PageVO &$page)
    {
        $this->releaseReceivers(); //first delete unused receivers
        $milisec = 1;
        while(true)
        {
            try
            {
                return $this->setupReceiversTryOnce($page);
            }
            catch(TelephoneException $e)
            {
                //someone else has taken these numbers, we try again
                $this->deleteReceivers($page);
                //In case we are food-fighting with someone, sleep randomly
                usleep(rand(0, $milisec * 1000));
                $milisec *= 2; //exponential backoff
                if($milisec > 17000) //die after approximatelly 16 seconds
                    throw new SurveyException('Could not allocate receivers for phone, too many collisions.');
            }
        }
    }
    /**
     * Request receivers for choices in surveys contained in PageVO
     * Updates database as well.
     * Throws TelephoneException in case of collision.
     *
     * @param PageVO $page
     * @return Boolean true
     */
    private function setupReceiversTryOnce(PageVO &$page)
    {
        global $vgDB, $vgDBPrefix;
        global $vgSmsChoiceLen;

        $surveys = &$page->getSurveys();
        $available = $this->getAvailablePhones();

        $receivers = array();
        foreach($surveys as &$survey)
        {
            sort($available);
            //Allocate Phones into a survey
            $results = $this->allocatePhones($survey, $available);

            $number = $survey->getNumOfChoices();
            if (count($results)< $number)
                throw new SurveyException("No available phones at the moment.", 203);

            $i = 0;
            $surveyChoices =& $survey->getChoices();
            foreach($surveyChoices as &$surveyChoice)
            {
                $receivers[] = array($results[$i]);
                $surveyChoice->setReceiver($results[$i]);
                $surveyChoice->setSMS(substr($results[$i], -$vgSmsChoiceLen));
                $i++;
            }
            //Get rid of the occupied numbers from the availablePhones
            $available = array_values( array_diff($available, $results) );
        }//foreach survey

        //insert into database
        try
        {
            $success = $vgDB->Execute("INSERT INTO {$vgDBPrefix}usedreceivers (receiver) VALUES(?)", $receivers);
        }
        catch(Exception $e)
        {
            $success = false;
        }
        if(! $success)
            throw new TelephoneException("Duplicate receiver found.");
        return true;
    }
    /**
     * Delete receivers from a given PageVO
     *
     * @param PageVO $page
     * @return Boolean true
     */
    function deleteReceivers(PageVO &$page)
    {
        global $vgDB, $vgDBPrefix;
        $surveysid = array();
        $surveys = &$page->getSurveys();
        foreach($surveys as &$survey)
        {
            /* @var $survey SurveyVO  */
            $surveyChoices =& $survey->getChoices();
            foreach($surveyChoices as &$surveyChoice)
            {
                if($surveyChoice->getReceiver())
                {
                    $success = $vgDB->Execute("DELETE FROM {$vgDBPrefix}usedreceivers WHERE receiver = ?", array($surveyChoice->getReceiver()));
                    if(! $success)
                        throw new SurveyException("Failed to delete used receivers");
                }
                $surveyChoice->setReceiver(NULL);
                $surveyChoice->setSMS(NULL);
            }
            $surveysid[] = array($survey->getSurveyID());
        }
        $success = $vgDB->Execute("UPDATE {$vgDBPrefix}surveychoice SET finished = 1 WHERE surveyID = ?", $surveysid );
        if(! $success)
            throw new SurveyException("Could not mark surveychoice as finished");
        return true;
    }

    /**
     * Release receivers which have not been released this far.
     * Find and mark as receivers pages (surveys) which have expired.
     *
     * @return Array array of integers specifying pageID of pages which have been finalized
     */
    public function releaseReceivers()
    {
        global $vgDB, $vgDBPrefix;
        $now = vfDate();
        $surveydao = new SurveyDAO();
        $pages = $surveydao->getPages("WHERE endTime <= ? and receivers_released = 0", array($now));
        $ret = array();
        foreach ($pages as $page)
        {
            /* @var $page PageVO */
            $this->deleteReceivers($page);
            $vgDB->Execute("UPDATE {$vgDBPrefix}page SET receivers_released = 1 WHERE pageID = ?", array($page->getPageID()));
            $ret[] = $page->getPageID();
        }
        return $ret;
    }
}

