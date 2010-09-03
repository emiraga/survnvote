<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SmsIntegration
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/misc/Common.php");
require_once("$vgPath/DAO/PageDAO.php");
require_once("$vgPath/Survey/SurveyTimer.php");
require_once("$vgPath/DAO/CrowdDAO.php");

/**
 * Special page for the List of runing surveys
 *
 * @author Emir Habul
 * @package SmsIntegration
 */
class SurveysList extends SpecialPage
{
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('SurveysList');
        wfLoadExtensionMessages('Survnvote');
        $this->includable( true );
        $this->target = Skin::makeSpecialUrl('SurveysList');
        $this->setGroup('SurveysList', 'survnvote');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        global $wgOut, $wgRequest;
        $wgOut->setPageTitle( 'Surveys list' );

        if($par == 'frontpage')
        {
            $cache =& wfGetMainCache();
            $contents =& $cache->get('vp:surveyslist');
            if(! $contents) $cache->set('vp:surveyslist', $contents =& $this->getFrontpage(), 10);
            $wgOut->addHTML($contents);
        }
        else
        {
            $wgOut->addHTML($par);
        }
    }
    /**
     * Purge cache related to this special page.
     */
    static function purgeCache()
    {
        $cache =& wfGetMainCache();
        $cache->delete('vp:surveyslist');
    }
    /**
     * Get HTML code which is to be shown on the front page.
     *
     * @return String HTML code
     */
    private function &getFrontpage()
    {
        global $vgScript;
        
        $out = '';
        
        $pagedao = new PageDAO();
        $surveys = $pagedao->getActiveSurveys();

        if(count($surveys)==0)
        {
            $out .= 'There are no active surveys at the moment.';
            return $out;
        }
        
        $p = new MwParser( new Parser() );
        $timer = new SurveyTimer();
        $timer->setRefresh(false);

        $out .= '<table style="width: 100%; margin: 0; background-color: rgb(245, 255, 250);" class="wikitable">';
        #$out .= '<caption>'.'Active surveys'.'</caption>';
        $out .= '<tr><th>Type</th><th>Title or question</th><th>Time left</th></tr>';

        foreach($surveys as $page)
        {
            /* @var $page PageVO */
            $wikipage = vfAdapter()->getSubCategories(wfMsg('cat-survey-name', $page->getPageID()));

            if(count($wikipage) == 0)
                $wikipage = Skin::makeSpecialUrl('ViewSurvey', 'id='.$page->getPageID());
            else
                $wikipage = Skin::makeUrl($wikipage[0]); // Show only first wiki page containing the survey

            $out .= '<tr>';
            $out .= '<td width="30px" align="center">';
            if($page->getPhoneVoting() != 'no')
            {
                $out .= "<img class='surlistIcon' height='16' width='16' src=\"$vgScript/icons/phone.png\" title=\"Phone voting enabled\" alt=\"phone\" /> ";
            }
            # $out .= '<br/><br/>';
            if($page->getWebVoting() != 'no')
            {
                $out .= "<img class='surlistIcon' height='16' width='16' src=\"$vgScript/icons/laptop.png\" title=\"Web voting enabled\" alt=\"comp\" />";
            }
            # $out .= '<br/>' . $page->getTypeName();
            $out .= '</td>';
            $out .= '<td>';

            $out .= '<a href="'.$wikipage.'">'. $p->run($page->getTitle(), false) .'</a> ';
            $out .= '<br/>('.vfPrettyDate($page->getStartTime()).')';

            if($page->crowdID != 0)
            {
                $crdao = new CrowdDAO();
                $out .= '<br/>For: '.$crdao->makeLink($page->crowdID);
            }
            $out .= '</td>';
            $out .= '<td>';
            $timeleft = strtotime($page->getEndTime()) - time();
            $id='tl_'.$page->getPageID().'_'.rand();
            $out .= ''.$timer->getJavascript($timeleft, $id);
            $out .= '</td>';
            $out .= '</tr>';
        }

        $out .= '</table><br/>';
        return $out;
    }
}

