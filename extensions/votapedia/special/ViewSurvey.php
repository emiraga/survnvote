<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ControlSurvey
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/Common.php" );

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

        try
        {
            $page_id = intval($wgRequest->getVal('id'));
            $parser = new MwParser($wgParser, $wgOut->ParserOptions());

            $buttons = new SurveyButtons();
            $buttons->setDetailsButton(false);
            $tag = new SurveyView($page_id, $parser, $buttons);
            $buttons->setType($tag->getPage()->getTypeName());
            
            $wgOut->addHTML($tag->getHTML());
            $wgOut->returnToMain();
            $wgOut->addHTML($tag->getDetailsHTML());

            $author = MwUser::displayName($tag->getPage()->getAuthor());

            $text = '';
            $text .= "== More information ==\n";
            $text .= "* Author: [[User:$author|$author]]\n";
            $text .= "* Creation date: {$tag->getPage()->getCreateTime()}\n";
            $text .= "* Status: {$tag->getPage()->getStatus()}\n";
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
        catch(Exception $e)
        {
            $wgOut->addHTML(vfErrorBox($e->getMessage()));
        }
    }
}
