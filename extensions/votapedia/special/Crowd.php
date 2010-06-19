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
    /** @var CrowdVO */  protected $crowd;
    /** @var CrowdDAO */ protected $crowddao;
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
        $this->crowddao = new CrowdDAO();
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        global $wgOut, $wgRequest;
        # $wgOut->addWikiText( vfAdapter()->findByEmail('emiraga@gmail.com'));

        if(vfUser()->isAnon())
        {
            $wgOut->showErrorPage( 'crowdnologin', 'crowdnologin-desc', array($wgTitle->getPrefixedDBkey()) );
            return;
        }
        try
        {
            if($par)
            {
                $this->crowd = $this->crowddao->findByName($par);
                if($this->crowd == false)
                {
                    throw new Exception("No such Crowd");
                }
                $wgOut->setPageTitle("Crowd ".str_replace('_', ' ', $this->crowd->name));

                if($wgRequest->getVal('showlog') == 'true')
                {
                    if(! $this->crowddao->isManager($this->crowd->crowdID, vfUser()->userID()))
                    {
                        throw new Exception('Not authorized');
                    }
                    $this->showPrintLog();
                }
                else
                {
                    $this->iamamanager = false;
                    $this->showMembersList();
                    if($this->iamamanager)
                    {
                        $this->addUsersForm();
                        $this->showLog();
                    }
                }
            }
            else
            {
                $this->showCrowdList();
                $this->newCrowdForm();
            }
        }
        catch(Exception $e)
        {
            $wgOut->setPageTitle("Crowd Error");
            $wgOut->addWikiText( vfErrorBox( $e->getMessage() ) );
            $wgOut->addReturnTo( Title::newFromText('Special:Crowd') );
            return;
        }
    }
    function showCrowdList()
    {
        global $wgOut;

        $user = vfUser()->getUserVO();
        $crowds = $this->crowddao->getCrowdsOfUser($user->userID);
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
    function showMembersList()
    {
        global $wgOut;

        $userdao = new UserDAO();
        $user = vfUser()->getUserVO();

        $out = '';
        $out .= "{| class=\"wikitable sortable\" style=\"width: 100%\"\n! User name !! Real name || E-mail || Phone !! Join Date || class=\"unsortable\" | \n";

        $members = $this->crowddao->getCrowdMembers($this->crowd);
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
        if($this->iamamanager)
        {
            global $vgScript;
            $wgOut->addHTML('<h4><img src="'.$vgScript.'/icons/print.png" /> '
                    .'<a href="'.Skin::makeSpecialUrlSubpage('Crowd', $this->crowd->name, 'showlog=true&printable=true')
                    .'" target=_blank>Print handouts</a></h4> (only newly created usernames for this crowd will be shown)');
        }
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
        $wgOut->addHTML($form->StartForm(Skin::makeSpecialUrlSubpage('ProcessCrowd',$this->crowd->name)));
        $wgOut->addHTML($form->AddPage('by user name', array('byusername')));
        $wgOut->addHTML($form->AddPage('by e-mail', array('byemail','sendemails')));
        $wgOut->addHTML($form->AddPage('by phone number', array('bynumber')));
        $wgOut->addHTML($form->EndForm(wfMsg('add-to-crowd')));
    }
    function showLog()
    {
        global $wgOut;
        $out = "\n\n== Crowd logs (errors, notices, etc.) ==\n";
        $out .= "{| class=\"wikitable sortable\" style=\"width: 100%\"\n! Log text !! Date\n";

        $logs =& $this->crowddao->getLogs($this->crowd->crowdID);
        foreach ($logs as &$log)
        {
            /* @var $log CrowdLogVO */
            $out .= "|-\n";
            $out .= "| {$log->log} || {$log->date_added}\n";
        }
        $out .= '|}';
        $wgOut->addWikiText($out);
    }
    function showPrintLog()
    {
        global $wgOut;
        $out = "{| class=\"wikitable\" style=\"width: 100%\"\n";

        $logs =& $this->crowddao->getLogs($this->crowd->crowdID, true);
        foreach ($logs as &$log)
        {
            /* @var $log CrowdLogVO */
            $out .= "|-\n";
            $out .= "| {$log->log}\n";
            $out .= "|-\n";
            $out .= "| <hr>\n";
        }
        $out .= '|}';
        $wgOut->addWikiText($out);
    }
}

