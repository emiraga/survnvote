<?php
header('Cache-Control: no-cache');
//require_once("error.php");
require_once("../usr.php");

$voterID = $_GET['voterID'];
$surveyID = $_GET['surveyID'];
$choiceID = $_GET['choiceID'];
//echo $voterID.$surveyID.$choiceID;

$usr = new Usr($voterID);

$usr->vote($surveyID,$choiceID);

?>
