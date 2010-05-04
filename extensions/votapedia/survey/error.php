<?php
old_stuff();

if (!defined('MEDIAWIKI')) die();
/**
 * This page is used to error handle function.
 *
 * @package DAO of Survey
 */

/**
 * Must return true, or system will halt it automatically.
 * Please reference PHP Manual
 *
 */

class SurveyException extends Exception
{
	// public function getFile();
	// public function getLine();
	// public function getMessage();
	// public function getCode();
	//  public function getTrace();
	//  public function getTraceAsString();
	
	/**
	 * Names of PHP warnings
	 * 
	 * @var $warning_names
	 */
	var $warning_names = array(
		E_WARNING=>'E_WARNING',
		E_USER_WARNING=>'E_USER_WARNING',
		E_COMPILE_WARNING=>'E_COMPILE_WARNING',
		E_CORE_WARNING=>'E_CORE_WARNING',
		E_USER_ERROR=>'E_USER_ERROR'
	);
	
	/**
	 * Shows error on the output and exits the script.
	 */
	public function showError()
	{
		$message = "[". date('n/j/Y g:i a') . "] ";
		$message = $this->getMessage() . $this->warning_names[$this->getCode()];
		$position ="[". $this->getFile()." on ". $this->getLine()." ]\n";

		$errorCode = $this->getCode();
		$title = "Error_Code_$errorCode";
		echo "<pre>\n\n";
		echo $title."\n";
		echo $message."\n";
		echo $position."\n";
		debug_print_backtrace();
		exit;
	}
}

/**
 * It is used to output error information to Screen or file
 *
 * @param $errorMsgs
 */

function errorLogger($errorMsgs)
{
	//print_r($errorMsgs);
	//Parsing ErrorMsg
	$messages = array();
	$messages[]="\n".date("Y-m-d H:i:s")."\n";
	$i=1;
	foreach($errorMsgs as $errorMsg)
	{

		$file = $errorMsg["file"];
		$line = $errorMsg["line"];

		$function = $errorMsg["function"];
		$class = $errorMsg["class"];
		$method = "";
		if (is_null($class))
		$method ="$function";
		else
		$method ="$class -> $function";

		if (stripos($file,"adodb") && $function=="adodb_throw")
		{
			//The 3rd Args is _errorMsg
			$messages[] = "REASON:".$errorMsg["args"][3]."\n";
		}
		else if (stripos($file,"adodb")==false)
		{
			$param = "";
			$args = $errorMsg["args"];
			foreach($args as $arg)
			$param = $param.$arg.",";
			$param = substr($param, 0, -1);	//Get rid of final ','
			$messages[]="$i.$file [line:$line]\n   $method($param)\n";
			$i++;
		}
	}//Error Messges parsed done
	 
	writeToScreen($messages);
	//writeToLog($messages);
	die();
}

/**
 * Write error message into file
 *
 * @param $messages
 */
function writeToLog($messages)
{

	$directory =  $_SERVER["DOCUMENT_ROOT"]."/survey/";
	$file = "errorlog";
	$ext = ".txt";
	//echo "file:".$directory.$file.$ext;
	if (file_exists($directory.$file.$ext))
	{
		if (filesize($directory.$file.$ext)>5000)
		rename($directory.$file.$ext,$directory.$file.date("YmdHis").$ext);
	}
	$log = "";
	foreach($messages as $message)
	$log = $log.$message;
	error_log($log, 3, $directory.$file.$ext);
}
/**
 * output error information to screen
 *
 * @param $messages
 */
function writeToScreen($messages)
{
	foreach($messages as $message)
	echo $message."<br>";

}

?>
