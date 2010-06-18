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
        $wgOut->setPageTitle("Crowd management");
        $out = '';
        $out .= "== My Crowds ==\nYou are member of:\n";
        $out .= "{| class=\"wikitable sortable\" style=\"width: 100%\"\n! Crowd Name !! Members !! Join Date || class=\"unsortable\" | \n";

        $user = vfUser()->getUserVO();

        $dao = new CrowdDAO();
        $crowds = $dao->getCrowdsOfUser($user->userID);

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
        $out .= "{| class=\"wikitable sortable\" style=\"width: 100%\"\n! User name !! Real name !! Join Date || class=\"unsortable\" | Phone || class=\"unsortable\" | \n";

        $members = $crowddao->getCrowdMembers($crowd);
        $this->iamamanager = false;
        foreach($members as $member)
        {
            /* @var $member CrowdMemberVO */
            $out .= "|-\n";
            $user = $userdao->findByID($member->userID);
            $mwuser = User::newFromName($user->username);

            $out .= "| [[User:{$user->username}|{$user->username}]] || {$mwuser->getRealName()} || $member->date_added || ";
            $phdao = new UserphonesDAO($user);
            $phonelist = $phdao->getList();
            foreach($phonelist as $phone)
            {
                $out .= vfColorizePhone($phone['number'],false, true ) . "<br>";
            }
            $out .= " || ";
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
        global $wgOut;
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
                        'explanation' => 'Enter a list of users, one per line. These have to be a list of registered users <b>with</b> this web site.'
                ),
                'byemail' => array(
                        'type' => 'textarea',
                        'name' => 'Emails',
                        'cols' => '70',
                        'rows' => '10',
                        'explanation' => 'Enter a list of users\' e-mails, one per line. These users will receive an e-mail containing the login information.'
                ),
                'bynumber' => array(
                        'type' => 'textarea',
                        'name' => 'Numbers',
                        'cols' => '70',
                        'rows' => '10',
                        'explanation' => 'Enter a list of users\' phone numbers one per line. SMS messages will <b>NOT</b> be send to these users but they can call @todo number to obtain a user/password combination.'
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
        $wgOut->addHTML($form->AddPage('by e-mail', array('byemail')));
        $wgOut->addHTML($form->AddPage('by phone number', array('bynumber')));
        $wgOut->addHTML($form->EndForm(wfMsg('add-to-crowd')));
    }
}

