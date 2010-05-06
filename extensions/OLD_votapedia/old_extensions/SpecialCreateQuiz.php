<?php
old_stuff();
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpCreateQuiz";


function wfExtensionSpCreateQuiz() {
	global $IP, $wgMessageCache;
	require_once( "$IP/includes/SpecialPage.php" );

	// Here you should define the article name that contains the Special Page's Title as shown in [[Special:Specialpages]]
	// Where 'specialpagename' will be MediaWiki:<specialpagename> eg. Special:Allpages might be 'allpages'
	// The part after '=>' is the default value of the title so again, using Special:Allpages as an example you would have...
	// 'allpages' => 'All Pages';
	// the part BEFORE the => must be all Lowercase.
	$wgMessageCache->addMessages(array('CreateQuiz' => 'CreateQuiz'));

class SpCreateQuizPage extends SpecialPage {
	function SpCreateQuizPage() {
		SpecialPage::SpecialPage( 'CreateQuiz' );
		$this->includable( true );
	}

	function execute( $par = null ) {
		global $wgOut;
		global $wgUser;
		$wgOut->setArticleFlag(false);//overcoming the compatibility problem between the old and new version of mediawiki
		$wgOut->setPageTitle("Create New Quiz");
		$userName=$wgUser->getName();

		if(!$wgUser->isLoggedIn())
		{
			$wgOut->addHTML('<pre>   Notice: You have to login to create a quiz. <p><form name="userlogin" id="userlogin" method="post" action="/index.php?title=Special:Userlogin&amp;action=submitlogin&amp;returnto=Create_Quiz"><input tabindex="1" type="Hidden" name="wpName" id="wpName" value="Test" /><input tabindex="2" type="Hidden" name="wpPassword" id="wpPassword" value="" />   If the quiz is only for testing, you can <input tabindex="4" type="submit" name="wpLoginattempt" value="Log in" /> as the <strong><a href="index.php?title=User:Test">Test</a></strong> user.</form></p></pre>');
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
			$wgOut->addHTML('<p><strong>Warning:</strong> You are logged in as the <strong><a href="index.php?title=User:Test">Test</a></strong> user, all quizzes created using this account will be deleted at the beginning of each month.');
		}
		//<h2>Create a new Quiz survey</h2>
		$wgOut->addHTML('<FIELDSET>
		<legend>New Quiz</legend>
  	<table>
    <FORM ACTION="database/createQuiz.php?" METHOD="post">
    	<TR>
      		<TD nowrap="nowrap" valign="top"><strong>Title :</strong></TD>
      		<TD><input type="text" name="TITLE" value="" size="80"/><br />
			<span style="color:#999999">e.g. "Chapter 2 Quiz". This will be the title of your quiz page. </span>
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
                      </select><span style="color:#999999"><br /> Your survey then would be added into the chosen category, and would be listed under that category.</span><span><a href="index.php?title=Details_of_Survey_Category"><img src="info.gif">Learn more</a></span><br /><br /></TD>
      	</TR>');

	/*	if($interfaceType=='simple')
		{
			$wgOut->addHTML('<tr>
   <td>&nbsp;</td>
	  <td><input type="checkbox" name="AllowInvalidVotes" value="true"/> I am outside Australia.<br />
	  <span style="color:#999999">  If checked, phones calling from outside Australia are able to vote but multiple voting is possible. </span>
	  <span><a href="index.php?title=Details_of_Multiple_Voting"><img src="info.gif">Learn more</a></span>
	  </td>');
		}*/
		if($interfaceType=='advanced')
		{
			$mobileNumber='';
			if(isset($_COOKIE['mobileNumber']))
				$mobileNumber=$_COOKIE['mobileNumber'];
			$wgOut->addHTML('<tr>
	  <td valign="top"><strong>User identity:</strong></td>

	  <td><input type="checkbox" name="AllowAnonymousVotes" value="yes"/> Enable anonymous web answering. <br />
	  <span style="color:#999999">  If unchecked, only registered votApedia users will be allowed to participate in the quiz.</span>
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
      		<TD valign="top"><strong>Quiz mode:</strong></TD>
			<TD>
			<input type="radio" name="VOTINGTYPE" value="teleweb" checked="checked"/>Telephone&Web. <span style="color:#999999">The quiz can run up to 8 hours. Users can enter their answers by mobile phone, SMS or the quiz page.</span><br />
			<input type="radio" name="VOTINGTYPE" value="telephone" />Telephone. <span style="color:#999999">The quiz can run up to 8 hours. Users can enter their answers by mobile phone or SMS.</span><br />
			<input type="radio" name="VOTINGTYPE" value="web" />Web. <span style="color:#999999">The quiz can run up to 30 days. Users can only enter their answers by visiting the quiz page.</span>
			</TD>
			</TR>
			<TR>
			<TD valign="top"><strong>Duration:</strong></TD>
      		<TD><input type="text" name="DURATION" value="1"/> hours.<br />
			<span style="color:#999999">Once you start the quiz, it will run for this amount of time and stop automatically.</span>
			<span><a href="index.php?title=Details_of_Duration"><img src="info.gif">Learn more</a></span>
			</TD>
			</TR>
                        <TR>
			<TD valign="top"><strong>Marking:</strong></TD>
			<TD><input type="checkbox" name="substractwrong" value="yes" CHECKED/> Subtract wrong answers. <br /> <span style="color:#999999">  If checked, each wrong answer will get minus point. The subtracted point is calculated based on the point of that question divided by the number of choices.</span>
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
	      <INPUT TYPE="Submit" VALUE="Create my quiz." NAME="submit"/>
	  </TD>
	  </TR>');
$wgOut->addHTML("<INPUT TYPE=\"Hidden\" NAME=\"AUTHOR\" VALUE=\"$userName\" />");
$wgOut->addHTML("<INPUT TYPE=\"Hidden\" NAME=\"AUTHOR\" VALUE=\"$userName\" />");
$wgOut->addHTML("<INPUT TYPE=\"Hidden\" NAME=\"NUMCHOICES\" VALUE=\"2\" />");
$wgOut->addHTML("<INPUT TYPE=\"Hidden\" NAME=\"NUMQUESTION\" VALUE=\"2\" />");//liat ini lagi ya.....
$wgOut->addHTML('</FORM>
	</table></FIELDSET>');

  	if($interfaceType=='advanced')
	{
		$wgOut->addHTML('Switch to the <a href="manualSelectCreateQuizInterface.php?interfaceType=simple">Simple Quiz Creation</a> interface');
	}
	else
	{
  		$wgOut->addHTML('<span style="color:#999999">Switch to the  <a href="manualSelectCreateQuizInterface.php?interfaceType=advanced">Advanced Quiz Creation</a> interface so that you can:
		  <ul>
		  <li>Only allow registered users to participate.</li>
		  <li>Start the quiz by dialing a number.</li>
		  <li>Receive the quiz result by SMS.</li>
		  <li>Create quiz that only allow web answering.</li>
		  <li>Specify the duration of the quiz.</li>
		  </ul>
</span>');
	}

	}
}

SpecialPage::addPage( new SpCreateQuizPage );
}
?>