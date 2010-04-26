<?php
/*
$wgHooks['ArticleSaveComplete'][] = 'vpOnArticleSave';
function vpOnArticleSave($article, $user, $text, $summary)
{
//var_dump($article);
//echo $user;
echo $text;
echo $summary;

	die('saving article wtf?');
}

$wgHooks['ArticleUndelete'][] = 'vpOnArticleUndelete';
function vpOnArticleUndelete($article, $user, $text, $summary)
{
//var_dump($article);
//echo $user;
echo $text;
echo $summary;

	die('Undelete article wtf?');
}


$wgHooks['ArticleDeleteComplete'][] = 'vpOnArticleDelete';
function vpOnArticleDelete($article, $user, $text, $summary)
{
//var_dump($article);
//echo $user;
echo $text;
echo $summary;

	die('Delete article wtf?');
}
*/

/**
 *
 * Add mobilephone option to the user preferences form
 *
 */

$wgHooks['PreferencesUserInformationPanel'][] = 'vpOnPreferencesUserInformationPanel';
function vpOnPreferencesUserInformationPanel($prefsform, &$userinfo_html)
{
	$userinfo_html .= 
		$prefsform->tableRow(
			Xml::label( 'Your mobile phone:', 'wpMobilePhone' ),
			Xml::input( 'wpMobilePhone', 25, $prefsform->mMobilePhone, array( 'id' => 'wpMobilePhone' ) ),
			Xml::tags('div', array( 'class' => 'prefsectiontip' ),
				'This phone number is used to identify your votes in Votapedia'
			)
		);
	return true;
}

$wgHooks['InitPreferencesForm'][] = 'vpOnInitPreferencesForm';
function vpOnInitPreferencesForm ($prefsform, $request)
{
	$prefsform->mMobilePhone = $request->getVal( 'wpMobilePhone' );
	return true;
}

$wgHooks['SavePreferences'][] = 'vpOnSavePreferences';
function vpOnSavePreferences($prefsform, $wgUser, &$msg, $oldOptions )
{
	$wgUser->setOption( 'mobilephone', $prefsform->mMobilePhone );
	return true;
}

$wgHooks['ResetPreferences'][] = 'vpOnResetPreferences';
function vpOnResetPreferences($prefsform, $wgUser )
{
	$prefsform->mMobilePhone = $wgUser->getOption( 'mobilephone' );
	return true;
}

/* User register form */
//wfRunHooks( 'UserCreateForm', array( &$template ) );

?>