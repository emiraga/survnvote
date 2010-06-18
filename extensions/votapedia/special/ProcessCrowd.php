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
                $wgOut->addWikiText($par);
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

                    $cr->name = $name;
                    $cr->description = $wgRequest->getVal( 'description' );
                    $cr->ownerID = vfUser()->userID();
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
}

