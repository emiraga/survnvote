<?php

$voterID = $_GET['voterID'];
$surveyID = $_GET['surveyID'];

include_once("../connection.php");
$cn = connectDatabase();
$sql ="select * from surveyrecord where voterID = '$voterID' and surveyID = $surveyID ";
$rs = $cn->Execute($sql);

$times = $rs->RecordCount();
$cn->Close();
echo $times;
return $times;
?>