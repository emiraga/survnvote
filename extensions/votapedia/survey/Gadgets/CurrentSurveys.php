<?php
require_once("../SurveyDAO.php");

$num = is_null($_GET["num"])?1:$_GET["num"];

if ($num<=0)
	$num=1;	
	
$surveyDAO = new SurveyDAO();
$surveys = $surveyDAO->findCurrentSurveys($num);

if(sizeof($surveys)>0)
{
	$xml ="<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
	$xml =$xml."<?xml-stylesheet type=\"text/xsl\" href=\"currentSurveys.xsl\" ?>"; 
	$xml =$xml."<surveys>";
	foreach($surveys as $survey)
	{
	  	$xml = $xml.$survey->toXML();
	}
	$xml = $xml."</surveys>";
	echo $xml;
}
?>	