<?php
old_stuff();
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpCreateSurvey";


function wfExtensionSpCreateSurvey() {
	global $IP, $wgMessageCache;
	require_once( "$IP/includes/SpecialPage.php" );

	// Here you should define the article name that contains the Special Page's Title as shown in [[Special:Specialpages]]
	// Where 'specialpagename' will be MediaWiki:<specialpagename> eg. Special:Allpages might be 'allpages'
	// The part after '=>' is the default value of the title so again, using Special:Allpages as an example you would have...
	// 'allpages' => 'All Pages';
	// the part BEFORE the => must be all Lowercase.
	$wgMessageCache->addMessages(array('CreateSurvey' => 'CreateSurvey'));

class SpCreateSurveyPage extends SpecialPage {
	function SpCreateSurveyPage() {
		SpecialPage::SpecialPage( 'CreateSurvey' );
		$this->includable( true );
	}

	function execute( $par = null ) {
		global $wgOut;
		global $wgUser;
		$wgOut->setArticleFlag(false);
		$wgOut->setPageTitle("Create New Survey");
		$userName=$wgUser->getName();

		if(!$wgUser->isLoggedIn())
		{
			$wgOut->addHTML('<pre>   Notice: You have to login to create a survey. <p><form name="userlogin" id="userlogin" method="post" action="index.php?title=Special:Userlogin&amp;action=submitlogin&amp;returnto=Create_Survey"><input tabindex="1" type="Hidden" name="wpName" id="wpName" value="Test" /><input tabindex="2" type="Hidden" name="wpPassword" id="wpPassword" value="" />   If the survey is only for testing, you can <input tabindex="4" type="submit" name="wpLoginattempt" value="Log in" /> as the <strong><a href="index.php?title=User:Test">Test</a></strong> user.</form></p></pre>');
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
		//<h2>Create a new survey</h2>
		$wgOut->addHTML('<FIELDSET>
		<legend>New survey</legend>
  	<table>
    <FORM ACTION="database/createSurvey.php?" METHOD="post">
    	<TR>
      		<TD nowrap="nowrap" valign="top"><strong>Title or question:</strong></TD>
      		<TD><input type="text" name="TITLE" value="" size="80"/><br />
			<span style="color:#999999">e.g. "What is the capital of Australia?". This will be the title of your survey page. The following characters are not allowed in the title: #, +, &, <, >, [, ], {, }, |, / . </span>
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
                      </select><br /><span style="color:#999999">  Your survey then would be added into the chosen category, and would be listed under that category.</span><span><a href="index.php?title=Details_of_Survey_Category"><img src="info.gif">Learn more</a></span><br /></TD>
      	</TR>');
		$wgOut->addHTML('<TR>
      		<TD valign="top" ><strong>Choices:</strong></TD>
      		<TD>Type choices here, one per line.<br /><textarea name="CHOICES" cols="50" rows="10"></textarea><br />
			<span style="color:#999999">  Once you start the survey, each choice will be assigned with a telephone number, audiences can ring this number, send SMS or visit the survey page to enter their vote.</span>
			<span><a href="index.php?title=Details_of_Survey_Procedure"><img src="info.gif">Learn more</a></span>
			<br />
			<span style="color:#999999">  The choices can contain wiki markup language and you can add, delete or modify them later in the survey page.</span><span><a href="index.php?title=Details_of_Editing_Surveys"><img src="info.gif">Learn more</a></span></TD>
      	</TR>');
		if($interfaceType=='simple')
		{
			$wgOut->addHTML('<tr>
	  <td>&nbsp;</td>
	  <td><input type="checkbox" name="AllowInvalidVotes" value="true" /> I am outside Australia.<br />
	  <span style="color:#999999">  If checked, phones calling from outside Australia are able to vote but multiple voting is possible. </span>
	  <span><a href="index.php?title=Details_of_Multiple_Voting"><img src="info.gif">Learn more</a></span>
	  </td>');
		}
		if($interfaceType=='advanced')
		{
			$mobileNumber='';
			if(isset($_COOKIE['mobileNumber']))
				$mobileNumber=$_COOKIE['mobileNumber'];
			$wgOut->addHTML('<tr><td colspan="2"><hr style="color:aca899" /></td></tr>
			<TR>
            <TD valign="top"><strong>Duration:</strong></TD>
      		<TD><input type="text" name="DURATION" value="1"/> hours.<br />
			<span style="color:#999999">Once you start the survey, it will run for this amount of time and stop automatically.</span>
			<span><a href="index.php?title=Details_of_Duration"><img src="info.gif">Learn more</a></span>
			</TD>
			</TR>
			<tr><td colspan="2"><hr style="color:aca899" /></td></tr>
			<tr>
	  <td valign="top"><strong>Voter identity:</strong></td>
	  <td><input type="checkbox" name="AllowInvalidVotes" value="true"/> Enable unidentified voters. Compulsory for phone surveys from outside Australia.<br />
	  <span style="color:#999999">  CallerID is used to stop multiple voting. Only the calls with a CallerID is regarded as a valid vote. Phones with CallerID disabled or calling from outside Australia will not be able to vote if unchecked. </span>
	  <span><a href="index.php?title=Details_of_Multiple_Voting"><img src="info.gif">Learn more</a></span>
	  </td>
	  </tr>
	  <tr>
	  <td>&nbsp;</td>
	  <td><input type="checkbox" name="AllowAnonymousVotes" value="true" CHECKED/> Enable anonymous web voting. <br />
	  <span style="color:#999999">  If unchecked, only registered votApedia users will be allowed to vote on the survey page.</span>
	  <span><a href="index.php?title=Details_of_Anonymous_Voting"><img src="info.gif">Learn more</a></span>
	  </td>
	  </tr>
	  <tr><td colspan="2"><hr style="color:aca899" /></td></tr>
	  <tr>
	  <td valign="top"><strong>Multiple Voting:</strong></td>
	  <td><input type="text" name="votesallowed" value="1" /> votes per person is allowed. <br /> <span style="color:#999999">  This specifies how many votes are allowed per person in this survey. Notice that this means voters can put multiple votes on the same choice.</span></td>
	  </tr>
	  </TR>');
          /*<tr><td colspan="2"><hr style="color:aca899" /></td></tr>
	  <tr>
	  <td valign="top"><strong>My mobile phone:</strong></td>
	  <td>
	    <input name="mobileNumber" type="text" size="50" value="'.$mobileNumber.'" />
		<br />
		<span style="color:#999999">  Providing mobile phone allows you to start your survey by dialing a number and receive the survey result by SMS.</span>
		<span><a href="index.php?title=Details_of_Mobile_Phone_Interaction"><img src="info.gif">Learn more</a></span>
		</td>
	  </tr>
	  <TR>
	  <td>&nbsp;</td>
	  <TD>
	  <input type="radio" name="SMSRequired" value="no" checked="checked"/>Do not send the survey result by SMS.<br />
	  <input type="radio" name="SMSRequired" value="author" />Send the survey result to  the author by SMS.<br />
	  <input type="radio" name="SMSRequired" value="all" />Send the survey result to all mobile phones that participates in the survey, including the author.
	  </TD>
	  </TR>*/
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
			<input type="radio" name="VOTINGTYPE" value="both" checked="checked"/>Telephone&Web voting. <span style="color:#999999">The survey can run up to 8 hours. Users can vote by mobile phone, SMS or the survey page.</span><br />
			<input type="radio" name="VOTINGTYPE" value="telephone" />Telephone voting. <span style="color:#999999">The survey can run up to 8 hours. Users can only vote by phone.</span><br />
			<input type="radio" name="VOTINGTYPE" value="web" />Web voting. <span style="color:#999999">The survey can run up to 30 days. Users can only vote by visiting the survey page.</span>
			</TD>
			</TR>
			<TR>
			<tr><td colspan="2"><hr style="color:aca899" /></td></tr>
			<TD valign="top"><strong>Graph Options:</strong></TD>
			<TD><input type="checkbox" name="resultsAtEnd" value="yes" /> Only show results at the end. <br /> <span style="color:#999999">  If checked, the survey result will only be shown after the survey finishes. Otherwise, voters will see the partial result after they vote.</span>
			Show only the TOP <input type="text" name="displaytop" value="" /> choices on the graph. <br /> <span style="color:#999999">  If a number is specified, the graph will only display the top few choices on the graph. Otherwise, voters will see all the choices no matter how many votes they have got.</span>
			</TD>
			</TR>');
		}
		else
		{
			$wgOut->addHTML('<INPUT TYPE="Hidden" NAME="VOTINGTYPE" VALUE="telephone" /><INPUT TYPE="Hidden" NAME="DURATION" VALUE="1" /><INPUT TYPE="Hidden" NAME="AllowAnonymousVotes" VALUE="true" />');
		}
		$wgOut->addHTML('<TR>
	  <td>&nbsp;</td>
	  <TD>
	      <INPUT TYPE="Submit" VALUE="Create my survey." NAME="submit"/>
	  </TD>
	  </TR>');
$wgOut->addHTML("<INPUT TYPE=\"Hidden\" NAME=\"AUTHOR\" VALUE=\"$userName\" />");
$wgOut->addHTML("<INPUT TYPE=\"Hidden\" NAME=\"NUMCHOICES\" VALUE=\"2\" />");
$wgOut->addHTML('</FORM>
	</table></FIELDSET>');

  	if($interfaceType=='advanced')
	{
		$wgOut->addHTML('Switch to the <a href="manualSelectCreateSurveyInterface.php?interfaceType=simple">Simple Survey Creation</a> interface');
	}
	else
	{
  		$wgOut->addHTML('<span style="color:#999999">Switch to the  <a href="manualSelectCreateSurveyInterface.php?interfaceType=advanced">Advanced Survey Creation</a> interface so that you can:
		  <ul>
		  <li>Only allow registered users to vote.</li>
		  <li>Start the survey by dialing a number.</li>
		  <li>Receive the survey result by SMS.</li>
		  <li>Create surveys that only allow web voting.</li>
		  <li>Specify the duration of the survey.</li>
		  </ul>
</span>');
	}

	}//end function execute
}//end class SpCreateSurveyPage

SpecialPage::addPage( new SpCreateSurveyPage );
}
?>