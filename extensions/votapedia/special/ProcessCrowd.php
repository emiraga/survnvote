<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SmsIntegration
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/DAO/CrowdDAO.php");
require_once("$vgPath/FormControl.php");
require_once("$vgPath/DAO/UserphonesDAO.php");

/**
 * Special page Crowd
 *
 * @author Emir Habul
 * @package SmsIntegration
 */
class ProcessCrowd extends SpecialPage
{
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('ProcessCrowd');
        wfLoadExtensionMessages('Votapedia');
        $this->includable( false );
        $this->setGroup('ProcessCrowd', 'votapedia');
        $this->target = Skin::makeSpecialUrl('ProcessCrowd');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        global $wgOut, $wgRequest;
        if(vfUser()->isAnon())
        {
            $wgOut->showErrorPage( 'crowdnologin', 'crowdnologin-desc', array($wgTitle->getPrefixedDBkey()) );
            return;
        }

        if ( ! vfUser()->checkEditToken() )
            die('Edit token is wrong, please try again.');

        try
        {
            if($par)
            {
                $action = $wgRequest->getVal( 'wpSubmit' );
                if($action == wfMsg('add-to-crowd'))
                {
                    $this->addByUsername($par);
                    $this->addByEmail($par);
                    $this->addByPhone($par);

                    $title = Skin::makeSpecialUrlSubpage('Crowd', $par);
                    $wgOut->redirect($title, 302);
                    return;
                }
            }
            else
            {
                $action = $wgRequest->getVal( 'wpSubmit' );
                if($action == wfMsg('create-crowd'))
                {
                    $dao = new CrowdDAO();
                    $cr = new CrowdVO();

                    $name = $wgRequest->getVal('name');
                    $name = preg_replace("/\W/", '_', $name);
                    $name = preg_replace('/_+/', '_', $name);
                    $name = preg_replace('/_$/', '', $name);
                    $name = preg_replace('/^_/', '', $name);

                    $cr->name = $name;
                    $cr->description = $wgRequest->getVal( 'description' );
                    $cr->ownerID = vfUser()->userID();
                    $cr->no_members = 1;
                    $dao->insert($cr);
                    var_dump($cr->crowdID);
                    $dao->addUserToCrowd($cr->crowdID, vfUser()->userID(), true, false);

                    $title = Skin::makeSpecialUrlSubpage('Crowd', $name);
                    $wgOut->redirect($title, 302);
                    return;
                }
            }
        }
        catch(Exception $e)
        {
            $wgOut->addHTML(vfErrorBox($e->getMessage()));
            $wgOut->addWikiText("[[Special:Crowd|Return to crowd management]]");
            return;
        }
    }
    function addByUsername($par)
    {
        global $wgRequest;
        
        $userdao = new UserDAO();
        $crdao = new CrowdDAO();
        $crowd = $crdao->findByName($par);

        $usernames = preg_split("/\n/", $wgRequest->getVal('byusername'));
        foreach($usernames as $name)
        {
            if(strlen($name) < 2)
                continue;
            $name = trim($name);
            $user = $userdao->findByName($name);
            if($user == false)
            {
                $mwuser = User::newFromName($name);
                var_dump($mwuser);
                $mwid = $mwuser->idForName();
                if($mwid == 0)
                {
                    $crdao->addLog( $crowd->crowdID, 'Error, username "'.htmlspecialchars($name).'" not found' );
                    continue;
                }
                $user = new UserVO();
                $user->username = $mwuser->getName();
                $user->password = '';
                $user->isAnon = $mwuser->isAnon();
                $userdao->insert($user);
            }
            $crdao->addUserToCrowd($crowd->crowdID, $user->userID);
        }
    }
    function addByEmail($par)
    {
        ;
    }
    
    function addByPhone($par)
    {
        global $wgRequest;
        $userdao = new UserDAO();
        $crdao = new CrowdDAO();
        $crowd = $crdao->findByName($par);
        $phones = preg_split("/\n/", $wgRequest->getVal('bynumber'));
        foreach($phones as $phone)
        {
            $phone = trim($phone);
            if(strlen($phone) <  5)
                continue;
            $phone = vfProcessNumber($phone);

            $userID = UserphonesDAO::getUserIDFromPhone($phone);
            if($userID == false)
            {
                $user = $userdao->newFromPhone($phone);
                $crdao->addLog($crowd->crowdID, "Phone number: $phone<br>"
                        ."Username: {$user->username}<br>Password: {$user->password}", true);
                $userID = $user->userID;
            }
            $crdao->addUserToCrowd($crowd->crowdID, $userID, false, true);
        }
    }
}

