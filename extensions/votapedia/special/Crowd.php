<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SmsIntegration
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/DAO/CrowdDAO.php");
require_once("$vgPath/DAO/UserDAO.php");
require_once("$vgPath/DAO/UserphonesDAO.php");
require_once("$vgPath/FormControl.php");

/**
 * Special page Crowd
 *
 * @author Emir Habul
 * @package SmsIntegration
 */
class Crowd extends SpecialPage
{
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('Crowd');
        wfLoadExtensionMessages('Votapedia');
        $this->includable( false );
        $this->setGroup('Crowd', 'votapedia');
        $this->target = Skin::makeSpecialUrl('Crowd');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        global $wgOut;
        # $wgOut->addWikiText( vfAdapter()->findByEmail('emiraga@gmail.com'));

        if(vfUser()->isAnon())
        {
            $wgOut->showErrorPage( 'crowdnologin', 'crowdnologin-desc', array($wgTitle->getPrefixedDBkey()) );
            return;
        }

        if($par)
        {
            $this->iamamanager = false;
            $this->showMembersList($par);
            if($this->iamamanager)
            {
                $this->addUsersForm();
            }
        }
        else
        {
            $this->showCrowdList();
            $this->newCrowdForm();
        }
        /*$this->initItems();
        try
        {
            $user = vfUser()->getUserVO();

            $this->dao = new UserphonesDAO($user);
            global $wgOut, $wgRequest;
            if($wgRequest->getVal('wpSubmit') == wfMsg('add-number'))
            {
                if(! vfUser()->checkEditToken())
                    die('Wrong edit token');
                $phone = vfProcessNumber( $wgRequest->getVal('newnumber') );
            }
        }*/
        /*catch(Exception $e)
        {
            $wgOut->setPageTitle("My Phones Error");
            $wgOut->addWikiText( vfErrorBox( $e->getMessage() ) );
            $wgOut->addReturnTo( Title::newFromText('Special:MyPhones') );
            return;
        }*/
    }
    function showCrowdList()
    {
        global $wgOut;
        $dao = new CrowdDAO();
        $user = vfUser()->getUserVO();
        $crowds = $dao->getCrowdsOfUser($user->userID);
        $wgOut->setPageTitle("Crowd management");
        $out = '';

        if(count($crowds))
        {
            $out .= "== My Crowd ==\nYou are member of:\n";
            $out .= "{| class=\"wikitable sortable\" style=\"width: 100%\"\n! Crowd Name !! Members !! Join Date || class=\"unsortable\" | \n";


            foreach($crowds as $crowd)
            {
                /* @var $crowd CrowdVO */
                $out .= "|-\n";
                $out .= "| [[Special:Crowd/{$crowd->name}|{$crowd->name}]] <br />{$crowd->description} || {$crowd->no_members} || {$crowd->date_added} ||";
                if($crowd->isManager)
                    $out .= "Manager";
                $out .= "\n";
            }
            $out .= '|}';
        }
        $wgOut->addWikiText($out);
    }
    function showMembersList($name)
    {
        global $wgOut;
        $wgOut->setPageTitle("Crowd ".str_replace('_', ' ', $name));

        $crowddao = new CrowdDAO();
        $userdao = new UserDAO();
        $user = vfUser()->getUserVO();
        $crowd = $crowddao->findByName($name);
        if($crowd == false)
        {
            throw new Exception("No such Crowd");
        }
        $out = '';
        $out .= "{| class=\"wikitable sortable\" style=\"width: 100%\"\n! User name !! Real name || E-mail || Phone !! Join Date || class=\"unsortable\" | \n";

        $members = $crowddao->getCrowdMembers($crowd);
        $this->iamamanager = false;
        foreach($members as $member)
        {
            /* @var $member CrowdMemberVO */
            $out .= "|-\n";
            $user = $userdao->findByID($member->userID);
            $mwuser = User::newFromName($user->username);

            $out .= "| [[User:{$user->username}|{$user->username}]] || {$mwuser->getRealName()} || ";
            $out .= vfColorizeEmail($mwuser->getEmail(), true) . " || ";
            $phdao = new UserphonesDAO($user);
            $phonelist = $phdao->getList();
            foreach($phonelist as $phone)
            {
                $out .= vfColorizePhone($phone['number'],false, true ) . "<br>";
            }
            $out .= " || ";
            $out .= " $member->date_added || ";
            
            if($member->is_manager)
            {
                $out .= "Manager";
                if(vfUser()->userID() == $member->userID)
                {
                    $this->iamamanager = true;
                }
            }
            $out .= "\n";
        }
        $out .= '|}';
        $wgOut->addWikiText($out);
    }
    function newCrowdForm()
    {
        global $wgOut;
        $items = array(
                'name' => array(
                        'type' => 'input',
                        'name' => 'Name',
                        'explanation' => 'In name of crowd any characters other than alpha-numberic will be converted to underscore _.'
                ),
                'description' => array(
                        'type' => 'textarea',
                        'name' => 'Description',
                        'explanation' => 'Brief description of this crowd.'
                ),
        );
        $wgOut->addWikiText("== New Crowd ==");
        $form = new FormControl($items);
        $wgOut->addHTML($form->StartForm(Skin::makeSpecialUrl('ProcessCrowd'), '', false));
        $wgOut->addHTML($form->AddPage('Crowd information', array('name','description')));
        $wgOut->addHTML($form->EndForm(wfMsg('create-crowd'), false));
    }
    function addUsersForm()
    {
        global $wgOut, $vgScript;
        $items = array(
                'name' => array(
                        'type' => 'input',
                        'name' => 'Name',
                        'explanation' => 'In name of crowd any characters other than alpha-numberic will be converted to underscore _.'
                ),
                'byusername' => array(
                        'type' => 'textarea',
                        'name' => 'Usernames',
                        'cols' => '70',
                        'rows' => '10',
                        'explanation' => 'Enter a list of users, one per line. These have to be a list of registered users <b>with</b> this web site.',
                        'icon' => $vgScript.'/icons/user_add.png',
                ),
                'byemail' => array(
                        'type' => 'textarea',
                        'name' => 'Emails',
                        'cols' => '70',
                        'rows' => '10',
                        'explanation' => 'Enter a list of users\' e-mails, one per line. These users will receive an e-mail containing the login information.',
                        'icon' => $vgScript.'/icons/mail.png',
                ),
                'sendemails' => array(
                        'type' => 'checkbox',
                        'name' => 'Send emails',
                        'default' => true,
                        'checklabel' => ' Send emails to users containing username and password for login on this web site.',
                        'valid' => function($v,$i,$js)
                        {
                            if($js) return "";
                            return true;
                        },
                        'explanation' => 'If checked, the survey result will only be shown after the survey finishes. Otherwise, voters will see the partial result after they vote.',
                        //'learn_more' => 'Details_of_Anonymous_Voting',
                ),
                'bynumber' => array(
                        'type' => 'textarea',
                        'name' => 'Numbers',
                        'cols' => '70',
                        'rows' => '10',
                        'explanation' => 'Enter a list of users\' phone numbers one per line. SMS messages will <b>NOT</b> be send to these users but they can call @todo number to obtain a user/password combination.',
                        'icon' => $vgScript.'/icons/phone.png',
                ),
                'place' => array(
                        'type' => 'null',
                        'html' => ''
                )
        );
        $wgOut->addWikiText("== Add members ==");
        $form = new FormControl($items);
        $wgOut->addHTML($form->getScriptsIncluded());
        $wgOut->addHTML($form->StartForm(Skin::makeSpecialUrl('ProcessCrowd')));
        $wgOut->addHTML($form->AddPage('by user name', array('byusername')));
        $wgOut->addHTML($form->AddPage('by e-mail', array('byemail','sendemails')));
        $wgOut->addHTML($form->AddPage('by phone number', array('bynumber')));
        $wgOut->addHTML($form->EndForm(wfMsg('add-to-crowd')));
    }
}

