<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SmsIntegration
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/misc/Common.php");

if(class_exists('SpecialPage'))
{
    /**
     * Special page statistics
     *
     * @author Emir Habul
     * @package SmsIntegration
     */
    class SurvnvoteStats extends SpecialPage
    {
        /**
         * Constructor for SurvnvoteStats
         */
        function __construct()
        {
            parent::__construct('SurvnvoteStats');
            wfLoadExtensionMessages('Survnvote');
            $this->includable( true );
            $this->setGroup('SurvnvoteStats', 'survnvote');
        }
        /**
         * Mandatory execute function for a Special Page
         *
         * @param String $par
         */
        function execute( $par = null )
        {
            global $wgOut, $wgRequest;
            $wgOut->setPageTitle( 'Survnvote statistics' );

            $cache =& wfGetMainCache();
            $contents =& $cache->get('vp:Survnvotestats');
            if(! $contents) $cache->set('vp:Survnvotestats', $contents =& $this->getFrontpage(), 60);
            $wgOut->addHTML($contents);
        }
        /**
         * Purge cache related to this special page.
         */
        static function purgeCache()
        {
            $cache =& wfGetMainCache();
            $cache->delete('vp:Survnvotestats');
        }
        /**
         * Get HTML code which is to be shown on the front page.
         *
         * @return String HTML code
         */
        private function &getFrontpage()
        {
            global $vgScript;

            $out = '<table width="100%"><tr><td>';
            $out .= 'Articles: '.SiteStats::articles().'<br/>';
            $out .= 'Pages: '.SiteStats::pages().'<br/>';
            $out .= 'Page edits: '.SiteStats::edits().'<br/>';
            $out .= 'Registered users: '.SiteStats::users().'<br/>';
            $out .= 'Active users: '.SiteStats::activeUsers().'<br/>';
            $out .= 'Admins: '.SiteStats::numberingroup('sysop');
            $out .= '</td>';
            global $vgDB, $vgDBPrefix;
            $s = $vgDB->Execute("SELECT * FROM {$vgDBPrefix}stats");
            $out .= '<td>&nbsp;</td>';
            $out .= '<td>';
            $out .= 'Surveys: '.$s->fields['pages'].'<br/>';
            $out .= 'Survey runs: '.$s->fields['surveyruns'].'<br/>';
            $out .= 'Crowds: '.$s->fields['crowds'].'<br/>';
            $out .= 'Votes by SMS: '.$s->fields['votes_sms'].'<br/>';
            $out .= 'Votes by phone: '.$s->fields['votes_call'].'<br/>';
            $out .= 'Votes by web: '.$s->fields['votes_web'];
            $out .= '</td>';
            $out .= '</tr></table>';
            return $out;
        }
    }
}

class SurvnvoteStatsUpdate
{
    static function addPage()
    {
        SurvnvoteStatsUpdate::updateCount('pages');
    }
    static function addCrowd()
    {
        SurvnvoteStatsUpdate::updateCount('crowds');
    }
    static function addWebVote()
    {
        SurvnvoteStatsUpdate::updateCount('votes_web');
    }
    static function addSmsVote()
    {
        SurvnvoteStatsUpdate::updateCount('votes_sms');
    }
    static function addCallVote()
    {
        SurvnvoteStatsUpdate::updateCount('votes_call');
    }
    static function addSurveyRun()
    {
        SurvnvoteStatsUpdate::updateCount('surveyruns');
    }
    static protected function updateCount($name)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->Execute("UPDATE {$vgDBPrefix}stats SET $name = $name + 1");
    }
}

