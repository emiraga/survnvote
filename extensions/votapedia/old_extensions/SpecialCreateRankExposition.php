<?php
old_stuff();
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpCreateRankExposition";


function wfExtensionSpCreateRankExposition() {
	global $IP, $wgMessageCache;
	require_once( "$IP/includes/SpecialPage.php" );

	// Here you should define the article name that contains the Special Page's Title as shown in [[Special:Specialpages]]
	// Where 'specialpagename' will be MediaWiki:<specialpagename> eg. Special:Allpages might be 'allpages'
	// The part after '=>' is the default value of the title so again, using Special:Allpages as an example you would have...
	// 'allpages' => 'All Pages';
	// the part BEFORE the => must be all Lowercase.
	$wgMessageCache->addMessages(array('CreateRankExposition' => 'Create Rank Expositions'));

class SpCreateRankExpositionPage extends SpecialPage {
	function SpCreateRankExpositionPage() {
		SpecialPage::SpecialPage( 'CreateRankExposition' );
		$this->includable( true );
	}

	function execute( $par = null ) {
		global $wgOut;
		global $wgUser;
		$wgOut->setArticleFlag(false);//overcoming the compatibility problem between the old and new version of mediawiki
		$wgOut->setPageTitle("Create New Rank Expositions");
		$userName=$wgUser->getName();

		if(!$wgUser->isLoggedIn())
		{
			$wgOut->addHTML('<pre>   Notice: You have to login to create a Rank Expositions survey. <p><form name="userlogin" id="userlogin" method="post" action="/index.php?title=Special:Userlogin&amp;action=submitlogin&amp;returnto=Create_Rank_Expositions"><input tabindex="1" type="Hidden" name="wpName" id="wpName" value="Test" /><input tabindex="2" type="Hidden" name="wpPassword" id="wpPassword" value="" />   If the survey is only for testing, you can <input tabindex="4" type="submit" name="wpLoginattempt" value="Log in" /> as the <strong><a href="index.php?title=User:Test">Test</a></strong> user.</form></p></pre>');
			global $wgRequest;
			require_once( "SpecialUserlogin.php" );
			$form = new LoginForm( $wgRequest );
			$form->execute();
			return;
		}
		if(!$wgUser->isLoggedIn())
			$userName="NULL";

		$interfaceType='simple';
		if( isset($_COOKIE['interfaceType']) )
		{
			if($_COOKIE['interfaceType']=='simple')
				$interfaceType='simple';
			else if($_COOKIE['interfaceType']=='advanced')
				$interfaceType='advanced';
			else
				$interfaceType='simple';
		}

		//output a warning message for test account
		if($userName=='Test')
		{
			$wgOut->addHTML('<p><strong>Warning:</strong> You are logged in as the <strong><a href="index.php?title=User:Test">Test</a></strong> user, all surveys created using this account will be deleted at the beginning of each month.');
		}
		//<h2>Create a new RankExposition survey</h2>
		$wgOut->addHTML('<FIELDSET>
		<legend>New Expositions Ranking</legend>
  	<table>
    <FORM ACTION="./database/createRankExposition.php?" METHOD="post">
    	<TR>
      		<TD nowrap="nowrap" valign="top"><strong>Title or Question:</strong></TD>
      		<TD><input type="text" name="TITLE" value="" size="80"/><br />
			<span style="color:#999999">e.g. "What do you think about this presentation?". This will be the title of your survey page. </span>
			<span><a href="index.php?title=Details_of_Title_or_Survey_Question"><img src="info.gif">Learn more</a></span>
			</TD>
      	</TR>');
      	        $wgOut->addHTML('<TR>
      		<TD valign="top" ><strong>Category:</strong></TD>
      		<TD><select name="chosencategory" onChange="submit()"><option value="Select">General</option>
                      <option value="Engineering">Engineering</option>
                      <option value="Science">Science</option>
                      <option value="Health">Health</option>
                      <option value="Environment">Environment</option>
                      <option value="Politics">Politics</option>
                      <option value="Economy">Economy</option>
                      <option value="Art">Art</option>
                      <option value="Sport">Sport</option>
                      </select><span style="color:#999999"><br />  Your survey then would be added into the chosen category, and would be listed under that category.</span><span><a href="index.php?title=Details_of_Survey_Category"><img src="info.gif">Learn more</a></span><br /><br /></TD>
      	</TR>');
		$wgOut->addHTML('<TR>
      		<TD valign="top" ><strong>Choices:</strong></TD>
      		<TD>Type choices here, one per line.<br /><textarea name="CHOICES" cols="50" rows="10"></textarea><br />
			<span style="color:#999999">  Once you start the survey, each choice will be assigned with a telephone number, audiences can ring this number, send SMS or visit the Rank Expositions survey page to enter their vote.</span>
			<span><a href="index.php?title=Details_of_Survey_Procedure"><img src="info.gif">Learn more</a></span>
			<br />
			</TD>
      	</TR>');
      	       $wgOut->addHTML('<TR>
      		<TD valign="top" ><strong>Expositions:</strong></TD>
      		<TD>Type in all expositions within this survey, one per line.<br /><textarea name="EXPOSITON" cols="50" rows="10"></textarea><br />  <span style="color:#999999">  The choices and expositions can contain wiki markup language and you can add, delete or modify them later in the survey page.</span><span><a href="index.php?title=Details_of_Editing_Surveys"><img src="info.gif">Learn more</a></span>
                </TD>
      	</TR>');
		if($interfaceType=='simple')
		{
			$wgOut->addHTML('<tr>
	  <td>&nbsp;</td>
	  <td><input type="checkbox" name="AllowInvalidVotes" value="true"/> I am outside Australia.<br />
	  <span style="color:#999999">  If checked, phones calling from outside Australia are able to vote but multiple voting is possible. </span>
	  <span><a href="index.php?title=Details_of_Multiple_Voting"><img src="info.gif">Learn more</a></span>
	  </td>');
		}
		if($interfaceType=='advanced')
		{
			$mobileNumber='';
			if(isset($_COOKIE['mobileNumber']))
				$mobileNumber=$_COOKIE['mobileNumber'];
			$wgOut->addHTML('<tr>
	  <td>&nbsp;</td>
	  <td><input type="checkbox" name="AllowInvalidVotes" value="true" CHECKED/> Enable multiple voting. Compulsory for phone surveys from outside Australia.<br />
	  <span style="color:#999999">  CallerID is used to stop multiple voting. Only the first call from each CallerID is regarded as a valid vote. Phones with CallerID disabled or calling from outside Australia will not be able to vote if unchecked. </span>
	  <span><a href="index.php?title=Details_of_Multiple_Voting"><img src="info.gif">Learn more</a></span>
	  </td>
	  </tr>
	  <tr>
	  <td>&nbsp;</td>
	  <td><input type="checkbox" name="AllowAnonymousVotes" value="true" CHECKED/> Enable anonymous web voting. <br />
	  <span style="color:#999999">  If unchecked, only registered votApedia users will be allowed to vote on the Rank Expositions page.</span>
	  <span><a href="index.php?title=Details_of_Anonymous_Voting"><img src="info.gif">Learn more</a></span>
	  </td>
	  </tr>
	  </TR>');
	  	}
		else
		{

		}
		if($interfaceType=='advanced')
		{
			$wgOut->addHTML('<tr><td colspan="2"><hr style="color:aca899" /></td></tr>
			<TR>
      		<TD valign="top"><strong>Voting Type:</strong></TD>
			<TD>
			<input type="radio" name="VOTINGTYPE" value="telephone" checked="checked"/>Telephone&Web voting. <span style="color:#999999">The survey can run up to 8 hours. Users can vote by mobile phone, SMS or the survey page.</span><br />
			<input type="radio" name="VOTINGTYPE" value="web" />Web voting. <span style="color:#999999">The survey can run up to 30 days. Users can only vote by visiting the survey page.</span>
			</TD>
			</TR>
			
                        <TR>
      		<TD valign="top"><strong>Top expositions displayed:</strong></TD>
			<TD><input type="text" name="displayTop" value="3"/> top expositions.<br />
			<span style="color:#999999">You could specify the number of top expositions being displayed during the running of the survey.</span>
			<span><a href="index.php?title=Details_of_TopDisplayed"><img src="info.gif">Learn more</a></span>
			</TD>
			</TR>

			<TR>
			<TD valign="top"><strong>Duration:</strong></TD>
      		<TD><input type="text" name="DURATION" value="1"/> hours.<br />
			<span style="color:#999999">Once you start the Rank Expositions survey, it will run for this amount of time and stop automatically.</span>
			<span><a href="index.php?title=Details_of_Duration"><img src="info.gif">Learn more</a></span>
			</TD>
			</TR>');
		}
		else
		{
			$wgOut->addHTML('<INPUT TYPE="Hidden" NAME="VOTINGTYPE" VALUE="telephone" /><INPUT TYPE="Hidden" NAME="DURATION" VALUE="1" /><INPUT TYPE="Hidden" NAME="AllowInvalidVotes" VALUE="true" /><INPUT TYPE="Hidden" NAME="AllowAnonymousVotes" VALUE="true" />');
		}
		$wgOut->addHTML('<TR>
	  <td>&nbsp;</td>
	  <TD>
	      <INPUT TYPE="Submit" VALUE="Create my survey." NAME="submit"/>
	  </TD>
	  </TR>');
$wgOut->addHTML("<INPUT TYPE=\"Hidden\" NAME=\"AUTHOR\" VALUE=\"$userName\" />");
$wgOut->addHTML("<INPUT TYPE=\"Hidden\" NAME=\"AUTHOR\" VALUE=\"$userName\" />");
$wgOut->addHTML("<INPUT TYPE=\"Hidden\" NAME=\"NUMCHOICES\" VALUE=\"2\" />");
$wgOut->addHTML("<INPUT TYPE=\"Hidden\" NAME=\"NUMEXPOSITION\" VALUE=\"2\" />");
$wgOut->addHTML('</FORM>
	</table></FIELDSET>');

  	if($interfaceType=='advanced')
	{
		$wgOut->addHTML('Switch to the <a href="manualSelectCreateRankExpositionInterface.php?interfaceType=simple">Simple Presentaion Survey Creation</a> interface');
	}
	else
	{
  		$wgOut->addHTML('<span style="color:#999999">Switch to the  <a href="manualSelectCreateRankExpositionInterface.php?interfaceType=advanced">Advanced Survey Creation</a> interface so that you can:
		  <ul>
		  <li>Only allow registered users to vote.</li>
		  <li>Start the presentation survey by dialing a number.</li>
		  <li>Receive the presentation survey result by SMS.</li>
		  <li>Create surveys that only allow web voting.</li>
		  <li>Specify the duration of the survey.</li>
		  </ul>
</span>');
	}

	}
}

SpecialPage::addPage( new SpCreateRankExpositionPage );
}
?>