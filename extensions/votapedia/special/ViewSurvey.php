<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ControlSurvey
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/Common.php" );
require_once("$vgPath/DAO/UserDAO.php" );
require_once("$vgPath/UserPermissions.php" );

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

            $buttons = new SurveyButtons();
            $buttons->setDetailsButton(false);

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
            
            $tag = new SurveyView($uservo, $page_id, $parser, $buttons);
            $buttons->setType($tag->getPage()->getTypeName());
            
            if($liveshow)
            {
                $wgOut->addHTML($tag->getHTML(false));
                return;
            }
            
            if($wgRequest->getCheck('getliveshow'))
            {
                $userperm = new UserPermissions($uservo);
                if($userperm->canControlSurvey($tag->getPage()))
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

                $userauthor = $userdao->findByID($tag->getPage()->getAuthor());
                $author = $userauthor->username;
                $author = MwUser::convertDisplayName($author);

                $text = '';
                $text .= "== More information ==\n";
                $text .= "* Author: [[User:$author|$author]]\n";
                $text .= "* Creation date: {$tag->getPage()->getCreateTime()}\n";
                $text .= "* Status: {$tag->getPage()->getStatus( $tag->getPage()->getCurrentPresentationID() )}\n";
                $text .= "* Type: {$tag->getPage()->getTypeName()}\n";
                $text .= "* Privacy: {$tag->getPage()->getPrivacyByName()}\n";
                $text .= "* Phone voting: {$tag->getPage()->getPhoneVoting()}\n";
                $text .= "* Web voting: {$tag->getPage()->getWebVoting()}\n";
                $text .= "== Inclusion ==\n";
                $text .= "* Use following text to include this {$tag->getPage()->getTypeName()} into a wiki page:\n";
                $text .= " <code><nowiki>{{#{$tag->getPage()->getTypeName()}:$page_id}}</nowiki></code>\n";
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
    }
}

