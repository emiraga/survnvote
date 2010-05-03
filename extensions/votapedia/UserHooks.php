<?php
/**
 * Add mobilephone option to the user preferences form
 *  also add mobile phone to the registration page
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
$wgHooks['UserCreateForm'][] = 'vpOnUserCreateForm';
function vpOnUserCreateForm(&$template)
{
	global $wgMessageCache;
	$wgMessageCache->addMessages(array( 'mobile-phone' => 'Mobile Phone:' ));

	$template->set('extraInput', array( array('msg' => 'mobile-phone', 'type' => 'text', 'name'=> 'phonenumber' ) ) );
	return true;
}

$wgHooks['AddNewAccount'][] = 'vpOnAddNewAccount';
function vpOnAddNewAccount($user, $b = true)
{
	global $wgUser;
	$mobilenumber = $_POST['phonenumber'];
	if(preg_match("/^[0-9\\+\\-\\/]+$/", $mobilenumber))
	{
		$wgUser->setOption( 'mobilephone', $mobilenumber );
		$wgUser->saveSettings();
	}
	return true;
}
?>