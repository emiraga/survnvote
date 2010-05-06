<?php
old_stuff();
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpCreateTextResponse";


function wfExtensionSpCreateTextResponse() {
	global $IP, $wgMessageCache;
	require_once( "$IP/includes/SpecialPage.php" );

	// Here you should define the article name that contains the Special Page's Title as shown in [[Special:Specialpages]]
	// Where 'specialpagename' will be MediaWiki:<specialpagename> eg. Special:Allpages might be 'allpages'
	// The part after '=>' is the default value of the title so again, using Special:Allpages as an example you would have...
	// 'allpages' => 'All Pages';
	// the part BEFORE the => must be all Lowercase.
	$wgMessageCache->addMessages(array('CreateTextResponse' => 'Create Text Response'));

class SpCreateTextResponsePage extends SpecialPage {
	function SpCreateTextResponsePage() {
		SpecialPage::SpecialPage( 'CreateTextResponse' );
		$this->includable( true );
	}

	function execute( $par = null ) {
		global $wgOut;
		global $wgUser;
		$wgOut->setArticleFlag(false);//overcoming the compatibility problem between the old and new version of mediawiki
		$wgOut->setPageTitle("Create New Text Response");
		$userName=$wgUser->getName();

		if(!$wgUser->isLoggedIn())
		{
			$wgOut->addHTML('<pre>   Notice: You have to login to create a Text Response. <p><form name="userlogin" id="userlogin" method="post" action="/index.php?title=Special:Userlogin&amp;action=submitlogin&amp;returnto=Create_free_text_survey"><input tabindex="1" type="Hidden" name="wpName" id="wpName" value="Test" /><input tabindex="2" type="Hidden" name="wpPassword" id="wpPassword" value="" />   If the survey is only for testing, you can <input tabindex="4" type="submit" name="wpLoginattempt" value="Log in" /> as the <strong><a href="index.php?title=User:Test">Test</a></strong> user.</form></p></pre>');
			global $wgRequest;
			require_once( "SpecialUserlogin.php" );
			$form = new LoginForm( $wgRequest );
			$form->execute();
			return;
		}
		if(!$wgUser->isLoggedIn())
			$userName="NULL";

		//output a warning message for test account
		if($userName=='Test')
		{
			$wgOut->addHTML('<p><strong>Warning:</strong> You are logged in as the <strong><a href="index.php?title=User:Test">Test</a></strong> user, all surveys created using this account will be deleted at the beginning of each month.');
		}
		//<h2>Create a new Text Response survey</h2>
		$wgOut->addHTML('<FIELDSET>
		<legend>New Text Response</legend>
  	<table>
    <FORM ACTION="./database/CreateTextResponse.php?" METHOD="post">
    	<TR>
      		<TD nowrap="nowrap" valign="top"><strong>Title :</strong></TD>
      		<TD><input type="text" name="TITLE" value="" size="80"/><br />
			<span style="color:#999999">e.g. "What did you do on you holidays?" or "Chapter 2 survey". This will be the title of your survey page. </span>
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
                      </select><span style="color:#999999"><br /> Your survey then would be added into the chosen category, and would be listed under that category.</span><span><a href="index.php?title=Details_of_Survey_Category"><img src="info.gif">Learn more</a></span><br /></TD>
      	</TR>');
		
		$wgOut->addHTML('<tr><td colspan="2"><hr style="color:aca899" /></td></tr>
		<tr>
			<td valign="top"><strong>User identity:</strong></td>
			<td><input type="checkbox" name="AllowAnonymousVotes" value="yes"/> Enable anonymous web answering. <br />
			<span style="color:#999999">  If unchecked, only registered votApedia users will be allowed to participate in the quiz.</span>
			<span><a href="index.php?title=Details_of_Anonymous_Voting"><img src="info.gif">Learn more</a></span>
			</td>
			</tr>
			<tr>
			<td colspan="2"><hr style="color:aca899" /></td>
			</tr>
			<TR>
      		<TD valign="top"><strong>Survey mode:</strong></TD>
			<TD>
			<input type="radio" name="VOTINGTYPE" value="teleweb" checked="checked"/>Telephone&Web. <span style="color:#999999">The survey can run up to 8 hours. Users can enter their answers by SMS or on the web page.</span><br />
			<input type="radio" name="VOTINGTYPE" value="telephone" />Telephone. <span style="color:#999999">The survey can run up to 8 hours. Users can enter their answers only by SMS.</span><br />
			</TD>
			</TR>
			<TR>
			<TD valign="top"><strong>Duration:</strong></TD>
      		<TD><input type="text" name="DURATION" value="1"/> hours.<br />
			<span style="color:#999999">Once you start the survey, it will run for this amount of time and stop automatically.</span>
			<span><a href="index.php?title=Details_of_Duration"><img src="info.gif">Learn more</a></span>
			</TD>
			</TR>');
		
		$wgOut->addHTML('<TR>
	  <td>&nbsp;</td>
	  <TD>
	      <INPUT TYPE="Submit" VALUE="Create my survey." NAME="submit"/>
	  </TD>
	  </TR>');
$wgOut->addHTML("<INPUT TYPE=\"Hidden\" NAME=\"AUTHOR\" VALUE=\"$userName\" />");
$wgOut->addHTML("<INPUT TYPE=\"Hidden\" NAME=\"AUTHOR\" VALUE=\"$userName\" />");
$wgOut->addHTML('</FORM>
	</table></FIELDSET>');

	}
}

SpecialPage::addPage( new SpCreateTextResponsePage );
}
?>