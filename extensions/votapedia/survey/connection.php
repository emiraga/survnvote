<?php
/**
* This page is used to connect database.
*
* @package DAO of Survey 
*/
    include("adodb/adodb-exceptions.inc.php");
    include_once('adodb/adodb.inc.php');
	#require_once("../SurveySettings.php");
    /**
    * Connect database without parameters
    * return ADOConnection $cn 
    */
    function connectDatabase()
    {
      $cn = &ADONewConnection('mysql');
	  global $gDBUserName;
		global $gDBUserPassword;
		global $gDataSourceName;
      //if ($cn->Connect($gDBserver,$gDBUserName,$gDBUserPassword,$gVotingDBname))
	  if ($cn->Connect('localhost','root','','Voting'))
         return $cn;
      else
      {
         throw new SurveyException("Could not connect database",400);
         return false;
      }
    }
 
?>