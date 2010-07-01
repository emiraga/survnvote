<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ControlSurvey
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/misc/Common.php" );
require_once("$vgPath/misc/UserPermissions.php");
require_once("$vgPath/DAO/UserDAO.php" );
require_once("$vgPath/survey/SurveyBody.php" );
require_once("$vgPath/survey/SurveyButtons.php" );

/**
 * Special page View Survey
 *
 * @author Emir Habul
 * @package ControlSurvey
 */
class ViewSurvey extends SpecialPage
{
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('ViewSurvey');
        wfLoadExtensionMessages('Votapedia');
        $this->includable( false );
        $this->setGroup('ViewSurvey', 'votapedia');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        wfProfileIn( __METHOD__ );

        global $wgOut, $wgParser, $wgRequest;
        $wgOut->setPageTitle( wfMsg('title-view-survey') );
        $wgOut->setArticleFlag(false);

        if($wgRequest->getCheck('liveshow'))
        {
            global $vgScript;
            $liveshow = $wgRequest->getVal('liveshow');
            $wgOut->addStyle($vgScript.'/liveshow.css');
            $wgRequest->setVal('printable', true);
            $wgOut->setPageTitle( '' );
        }
        else
        {
            $liveshow = false;
        }
        
        $userdao = new UserDAO();
        try
        {
            $page_id = intval($wgRequest->getVal('id'));
            $parser = new MwParser($wgParser, $wgOut->ParserOptions());

            $buttons = new RealSurveyButtons();
            $buttons->setDetailsButton(false);

            global $vgScript;
            $wgOut->addStyle($vgScript.'/survey.css');

            if($liveshow)
            {
                //check if every value is correct
                $userID = $wgRequest->getInt('userID');
                if($userID == 0)
                    throw new Exception('UserID not specified.');

                $uservo =& $userdao->findByID($userID);
                if(! $uservo)
                    throw new Exception('User does not exist.');

                if($liveshow != $uservo->getTemporaryKey($page_id))
                    throw new Exception('Wrong key.');
                $uservo->isTemporary = true;

                //in presentation settings 'edit' and 'powerpoint' buttons are not visible
                $buttons->setLiveShowButton(false);
                $buttons->setEditButton(false);
            }
            else
            {
                $uservo = vfUser()->getUserVO();
            }

            $pagedao = new PageDAO();
            $page =& $pagedao->findByPageID( $page_id );

            $bodyfactory = new SurveyBodyFactory($page, $uservo, $parser);
            $tag = new SurveyView($uservo, $page, $parser, $buttons, $bodyfactory->getBody());
            $buttons->setType($page->getTypeName());

            if($liveshow)
            {
                $wgOut->addHTML($tag->getHTML(false));
            }
            elseif($wgRequest->getCheck('getliveshow'))
            {
                $userperm = new UserPermissions($uservo);
                if($userperm->canControlSurvey($page))
                {
                    $wgOut->setPageTitle( 'Get Powerpoint link' );
                    $wgOut->addHTML('Use following link to embed this survey into a Powerpoint:');

                    $t = Title::newFromText('Special:ViewSurvey');
                    $url = $t->getFullURL('liveshow='.$uservo->getTemporaryKey($page_id).'&id='.$page_id.'&userID='.$uservo->userID).'#survey_id_'.$page_id;
                    $wgOut->addHTML('<textarea style="font-size: large" onclick="javascript:this.focus();this.select();">'.$url.'</textarea>');
                    $wgOut->addHTML('Or, simply copy <a href="'.$url.'" target=_blank>this link</a>.');
                    $wgOut->addHTML('<p>Note: <i>Keep this link secure from others, since it can be used to control this survey.</i></p>');
                    $wgOut->returnToMain();
                }
            }
            else
            {
                $wgOut->addHTML($tag->getHTML(true));
                
                $userauthor = $userdao->findByID($page->getAuthor());
                $author = $userauthor->username;
                $author = MwUser::convertDisplayName($author);
                
                $text = '';
                $text .= "== More information ==\n";
                $text .= "* Author: [[User:$author|$author]]\n";
                $text .= "* Creation date: {$page->getCreateTime()}\n";
                $text .= "* Status: {$page->getStatus( $page->getCurrentPresentationID() )}\n";
                $text .= "* Type: {$page->getTypeName()}\n";
                $text .= "* Privacy: {$page->getPrivacyByName()}\n";
                $text .= "* Phone voting: {$page->getPhoneVoting()}\n";
                $text .= "* Web voting: {$page->getWebVoting()}\n";
                $text .= "== Inclusion ==\n";
                $text .= "* Use following text to include this {$page->getTypeName()} into a wiki page:\n";
                $text .= " <code><nowiki>{{#{$page->getTypeName()}:$page_id}}</nowiki></code>\n";
                $text .= "\n== ".wfMsg('page-links')." ==\n";
                $text .= wfMsg('pages-include')."\n";
                $pages = vfAdapter()->getSubCategories( wfMsg('cat-survey-name', $page_id) );
                
                foreach($pages as $name)
                {
                    $text.="* [[$name#survey_id_$page_id|$name]]\n";
                }
                $wgOut->addWikiText($text);
            }
        }
        catch(Exception $e)
        {
            $wgOut->addHTML(vfErrorBox('Error: '.$e->getMessage()));
        }
        
        wfProfileOut( __METHOD__ );
    }
}

