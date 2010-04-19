<?php

//main wiki text area
$wikiText.="<!--You can write your question inside the ==='''  '''=== mark below, e.g. ==='''Do you like blue sky?'''=== -->\n";
$wikiText.="==='''  '''===\n";
$wikiText.="<choice background=[[Image:Csiro_large.jpg]] multipleVoting=$allowInvalidVotes anonymous=$allowAnonymousVotes votesAllowed=$votesallowed duration=$duration SMSreply=$isSMSRequired telephoneVoting=$telephoneVoting webVoting=$webVoting resultsAtEnd=$resultsAtEnd displayTop=$displaytop>\n";

if(isset($_POST["CHOICES"]))
{
	$wikiText.=$choices;
}
else
{
	for($i=1;$i<=$numChoices;$i++)
	{
		$wikiText.="Choice $i\n";
	}
}

$wikiText.="</choice>\n    Created by $author\n[[Category:Surveys]]\n[[Category:Surveys by $author]]\n[[Category:Surveys in $chcategory]]\n[[Category:Simple Surveys]]";

$category="Category:Surveys by $author";


$wikiText="[[Category:Surveys by author|$author]]";

$wikiText="[[Category:Surveys in subject|$chcategory]]";

if($databaseWritten)
	header("Location: http://$site_location/index.php?title=$encodedTitle");
?>