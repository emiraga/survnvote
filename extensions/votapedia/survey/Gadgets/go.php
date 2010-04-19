<?php
require_once("../connection.php");	
$cn = connectDatabase();

$id=$_GET["id"];
$sql = "select pageid from survey where surveyid=$id";
$cn->SetFetchMode(ADODB_FETCH_ASSOC);
$rs= &$cn->Execute($sql);

$pageid = $rs->fields['pageid'];

$sql1 = "select title from page where pageID=".$pageid;
$cn->SetFetchMode(ADODB_FETCH_ASSOC);
$rs1= &$cn->Execute($sql1);

$title = $rs1->fields['title'];
$cn->Close();

header("Location:http://".$_SERVER['HTTP_HOST']."/index.php?title=$title");
?>