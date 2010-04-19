<?php

     /**
     * This page includes class TeleNumber which is in chare of
     * allocating available telephones to surveys based on
     * diffent stategies.
     * @package DAO of Survey 
     */

    //include_once("SurveyVO.php");
    require_once("error.php");
    include_once("connection.php");
    
    class Telephone
    {
          private $numberOfPhones = 200;
          private $sizeOfBlock = 5;
          private $numberOfBlock = 35;
          private $numberOfDynamic = 25;
          //private $init = 7300;
          private $blocks = array();
          private $dynamicBlock = array();
          private $allPhones = array();
        
        /**
        *  Define the blocks
        */
       function setupBlocks($cn)
        {
         $sql = "select telenumber from telenumber";
         // Force indexing by name not number
         $cn->SetFetchMode(ADODB_FETCH_ASSOC);
         $rs= &$cn->Execute($sql);

         $telephones = array();
         if ($rs->RecordCount()>0)
            {
              while(!$rs->EOF)
	           {
                 $telephones[]=$rs->fields['telenumber'];
                 $this->allPhones[] = $rs->fields['telenumber'];
   	  	          $rs->MoveNext();
	             }  
            }
         $rs->Close();   

         $this->numberOfPhones = count($telephones);
         
          $n = ($this->numberOfPhones - $this->numberOfDynamic)/$this->sizeOfBlock;

          for($i=0;$i<$n;$i++)
          {
            $block = array();
            for ($j=0;$j<$this->sizeOfBlock;$j++)
            {
              $block[] = $telephones[$i*$this->sizeOfBlock+$j];
            }
            $this->blocks[]=$block;
          }

          for ($i=$this->numberOfPhones-$this->numberOfDynamic ;$i<$this->numberOfPhones;$i++)
              $this->dynamicBlock[]=$telephones[$i];
		 
        }

        /**
        * Get available blocks.
        * It may not work at variable size of block
        * Return Index of available blocks
        */
        function getAvailableBlocks($availablePhones)
        {
             // print_r("<br>Available:<br>");
              //print_r($availablePhones);

             // print_r("<br>blocks:<br>");
              //print_r($this->blocks);

              $availableBlocks=array();
               // if a telephone of a block is available, the whole block is available
              foreach ( $availablePhones as $phone)
                 {
                    $begin = 0; //mark the next position of blocks
                    //Cannot from 0; must be from this first element of $availablePhones
                    for($j=$begin;$j<$this->numberOfBlock;$j++)
                       {
                           if ($phone>=$this->blocks[$j][0] && $phone<=$this->blocks[$j][$this->sizeOfBlock-1])
                            {
                              $availableBlocks[]=$j;
                              $begin = $j;
                              break;
                            }

                       }
                 }

              // Only all numbers of a block are availalble, then the block is available
              //statictic the times which the same value occurs
              // [0]->2; [3]->5
              $temp = array_count_values($availableBlocks);
              //Get the occuring times = sizeOfBlock (5)
              $results = array();
              foreach ($temp as $value=>$times)
                 if ( $times == $this->sizeOfBlock)
                     $results[]=$value;
              return $results;
        }
        /**
        * Attention: Available blocks must exist.
        * @param @availableBlocks which blocks are availbable now
        * @param @usedPhones the list of phones the authoer used before,
        * program is trying to get the most used phones of the author firstly
        * @param @historyBlock the history of block usage, DESC oder by occurence.
        * If getting the most used phones of the authoer fails, try to get the least used block.
        */
        function retrieveBlock($availableBlocks,$usedPhones,$historyBlock)
        {
           if (count($availableBlocks)>0)
               { foreach ($usedPhones as $phone)
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
        * Initiate connection before using this function
        * Gets the using history of telephones
        * @return Array $usedBlocks the array of available telephone numbers
        */
        function getBlockHistory($cn)
        {

         $sql="select receiver,count(surveyChoice.receiver) as number from surveychoice";
         $sql=$sql." group by receiver order by number desc,receiver ";
  
         $cn->SetFetchMode(ADODB_FETCH_ASSOC);
         $rs= &$cn->Execute($sql);
         $usedBlocks = array();
         $usedBlocks[]=null;
         while(!$rs->EOF)
         {
            //$list = array();
            //$list[]=  $rs->fields["receiver"];
            //$list[]=  $rs->fields["number"];
            //$usedBlocks[] = $list;
              $usedBlocks[]= trim($rs->fields["receiver"]);
            $rs->MoveNext();
         }
         $rs->Close();

         //produce the whole phone list in Blocks
         // reverse the order for easy handling
       //  $phones = array();
        // for($i=$this->init+$this->sizeOfBlock*$this->numberOfBlock;$i>$this->init;$i--)
          //   $phones[]=$i-1;
         //Get the reversed useing frequency and uniqure phones.

         $results = array_reverse( array_unique(array_merge($usedBlocks,array_reverse($this->allPhones))));
         return $results;
        }

        /**
        * Initiate connection before using this function
        * Gets available telephones
        * @param Connection $cn
        * @param Datetime $expiredTime
        * @deprecated parameter $expiredTime
        * @return Array $telephone the array of available telephone numbers
        */

       function getAvailablePhone($cn,$expiredTime)
        {
          //  $sql = "select telenumber from teleNumber where expiredtime<='";
          //  $sql = $sql . $expiredTime."' order by telenumber";

          // Get available phones directly from view_available_telephone
          $sql = "select telenumber from view_available_telephone";
            // Force indexing by name not number
            $cn->SetFetchMode(ADODB_FETCH_ASSOC);
            $rs= &$cn->Execute($sql);

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
                 //$cn->Close();
                 throw new SurveyException("No available phones!",203);
                 return false;
               }
            
            return $telephones;
        }
        /**
        *  categorize sequential telephones to groups
        *
        */
        function sortPhoneArray($telephones)
        {
           $init = $telephones[0];

           $seqPhones = array();
           $temp = array();
           for ($i=0;$i<count($telephones);$i++)
           {
              if ($init == $telephones[$i])
              {
                  $temp[] = $init;
                  $init = $init+1;
               }
              else
              {
                    $seqPhones[]=$temp;
                    unset($temp);
                    $temp = array();
                    $temp[] = $telephones[$i];
                    $init = $telephones[$i]+1  ;
               }
               if ($i == count($telephones)-1)
               {
                  $seqPhones[] = $temp;
               }
           }
	   return $seqPhones;
        }
        /**
        * Filter the sequential phone groups,
        * get the number of telephone is >= the requiring telephone number
        */
        function filterPhoneList($seqPhones,$num)
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
        * retrieve the possible telephone group for this user
        * @param Array $filteredPhones represents the number of sequential telephone
        * numbers is larger than the requesting number.  
        * Array[1][2], 1 represents telephone number; 2 represents how many teles in this group
        * @param Array $userPhones represents the telephone numbers used by this user,
        * order by the frequency of the telephone numbers.
        * Arrary,  represents the telephone number.
        * @param $num how many telephones are being requested.
        */
        function retrieveGroup($filteredPhones,$usedPhones,$num)
        {
           $usingPhones = array();
           $possiblePhones = array();
           $identity=0;  //indicates which telephone in $usedPhones is listed in $filtetedPhones.

           echo "beginning....................\n" ;
           /*
           // Show Userd Phones
           foreach($usedPhones as $usedPhone)
               echo $usedPhone[0].":";
           echo "<br>";
           */
           echo "group phones show<br>";
           foreach($filteredPhones as $filteredPhone)
           {
             foreach($filteredPhone as $telephone)
	        {
                  echo $telephone." - ";
                }
                echo "new group<br>";
           }
           //Locate which group matches the request
           for ($i=0;$i<count($usedPhones);$i++)
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
             	 return $this->retrievePhones($possiblePhones,$num,$identity);
             }
           else
             {
               // for new users.
               $possiblePhones = array_slice($filteredPhones[count($filteredPhones)-1],0,$num);
               return $possiblePhones;
             }
         }
         
         /**
         * Retrived the requried phones from an available group
         * @param Arrary $group contains sequential telephone numbers
         * @param Integer $num how many telephones is wanted to be gotten
         */
         function retrievePhones($group,$num,$identifiedPhone)
         {
             $mark = 0; // keep the position of the first available telephone matches the condition
             if (count($group)==$num)
                return $group;
             else
                {
                   //Locate the position of
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
                    $results = array();
                    if (($max - $mark + 1)>=$num)
                      {
                         $results = array_slice($group,$mark,$num);
                      }
                    else 
                      {
                        $begin = $max-$num+1;
                        $results = array_slice($group,$begin-1,$num);
                      }
                      return $results;
                }
         }
        /**
        * param $listPhones represents the history of used phones history by the author
        * param $history represents the history of used block
        * param $availablePhones  available phones now                \
        */
        public function allocatePhones(SurveyVO $survey,$listedPhones,$history,$availablePhones)
        {
			
            $number = $survey->getNumOfChoices();

            $availableBlocks = $this->getAvailableBlocks($availablePhones);
            
            // Number of choices is not more then 5, and
            // available blocks are available, then pick up phones from fixed blocks
            if ($number<=$this->sizeOfBlock && count($availableBlocks)>0)
            {
                 //$history = $this->getBlockHistory($cn);
                 $phones = $this->retrieveBlock($availableBlocks ,$listedPhones,$history);
                 return $phones;
            }
            // Number of choices is more then 5, or no available blocks,then pick up phones from Dynamic block
            // So far, the phones > $init+$numberOfPhones-$numberOfDynamic:
            // $phone>7300+100-25 = 7375
            else if($number<=$this->numberOfDynamic)
            {
              //Get the numbers in dynamic block which is still in available phones
               $availablePossiblePhones=array_intersect($this->dynamicBlock,$availablePhones);
              $sortedPhones = $this->sortPhoneArray($availablePossiblePhones);
              $filteredPhones = $this->filterPhoneList($sortedPhones,$number);
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
          * @param PageVO $page
          * @return boolean 
          */
        function setupReceivers(PageVO $page)
        {
            require_once("usr.php");
            $user = new Usr($page->getAuthor());
            $listedPhones = $user->getUsedPhoneLists();

            try{
               
               $cn = connectDatabase();
               $scheduleTime = $page->getStartTime();
               //Set up Blocks, which telephone numbers are read directly from database
               $this->setupBlocks($cn);
               
               $availablePhones = $this->getAvailablePhone($cn,$scheduleTime);
               $history = $this->getBlockHistory($cn);
            
               $surveys = $page->getSurveys();
               
               foreach($surveys as $survey)
               {
               		//Allocate Phones into a survey
               		$results = $this->allocatePhones($survey,$listedPhones,$history,$availablePhones);
                    
               		$number = $survey->getNumOfChoices();
               		if (count($results)< $number)
                  		{
                    		$cn->Close();
                    		throw new SurveyException("No available phones!",203);
                    		return false;
                  		}
               		else
                  		{ 
                    	for($i=0;$i< $number;$i++)
                    		{
                     			$survey->getChoiceByNum($i)->setReceiver($results[$i]);
                     			$survey->getChoiceByNum($i)->setSMS(substr($results[$i],-2));
                    		}
                    	//Get rid of the occupied numbers froma availablePhones
                    	$availablePhones = array_diff($availablePhones,$results);
	
                  		}
               }		
               $cn->StartTrans();

               //update startTime and EndTime
               $sql = "update page set startTime=?,endTime=? where pageID = ?";
               $res = $cn->Prepare($sql);
               $param = array($page->getStartTime(),
  			                  $page->getEndTime(),
			                  $page->getPageID()
			                  );
               $cn->Execute($res,$param);
               
               //Update the receivers
               $sqlChoice = "update surveyChoice set receiver = ?,sms=? where surveyID =? and choiceID=?";
               $resChoice = $cn->Prepare($sqlChoice);
               foreach($surveys as $survey)
               {
               	 $surveyChoices = $survey->getChoices();
               	 $num = 0;
               	 foreach($surveyChoices as $surveyChoice)
               	 {
               	 	$num = $num+1;
               	 	$param = array($surveyChoice->getReceiver(),
                                       $surveyChoice->getSMS(),
               	 	               $surveyChoice->getSurveyID(),
               	 	               $num 
               	 	               );
               	 	               
               	 	$cn->Execute($resChoice,$param);              
               	 }
               }

                      
               $cn->CompleteTrans();
               if ($cn->HasFailedTrans()) {
                       // Something went wrong
                       $cn->Close();
                       throw new SurveyException("ODBC Commit error:.$message",400);
                        }
                    else
                    {
                        $cn->Close();
                        return true;
                     }
              }
         catch (SurveyException $e)
		 {
			if ($cn->IsConnected())
			   $cn->Close();
            $e->showError();
		   	return true;
		 }
	     catch (Exception $e)
		 {
            $cn->Close(); 
		 	$errorMsgs = $e->getTrace();
            errorLogger($errorMsgs);
            return true;
          }

     }  //setupReceivers done.

}
?>
