<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SmsIntegration
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/misc/Common.php");
require_once("$vgPath/Sms.php");

/**
 * Special page Latest incoming SMS and phone calls
 *
 * @author Emir Habul
 * @package SmsIntegration
 */
class LatestIncoming extends SpecialPage
{
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('LatestIncoming');
        wfLoadExtensionMessages('Votapedia');
        $this->includable( true );
        $this->target = Skin::makeSpecialUrl('LatestIncoming');
        $this->setGroup('LatestIncoming', 'votapedia');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        global $wgOut, $wgRequest;
        $wgOut->setPageTitle( wfMsg('latestincoming') );
        if($par == 'long')
        {
            $wgOut->addHTML($this->getContents(1));
        }
        else
        {
            $cache =& wfGetMainCache();
            $contents =& $cache->get('vp:latestincoming');
            if(! $contents) $cache->set('vp:latestincoming', $contents =& $this->getContents(0), 10);
            $wgOut->addHTML($contents);
        }
    }
    /**
     * Get the list of incoming calls.
     *
     * @param Boolean $long output long or short list
     * @return String HTML Code
     */
    private function &getContents($long)
    {
        global $vgEnableSMS;
        if($long)
        {
            $isadmin = vfUser()->isAdmin();
        }
        $out = '';
        $out .= '<table style="width: 100%; margin: 0;" class="wikitable">';
        # $wgOut->addHTML('<caption>'.wfMsg('latestincoming').'</caption>');
        $out .= '<tr><th>Type<th>From<th></tr>';

        if($vgEnableSMS)
        {
            $in = Sms::getIncoming($long?30:7);
            foreach($in as $sms)
            {
                $out .= '<tr><td align=center>SMS<td>'.
                        vfColorizePhone($sms['number'], false, !$long || !$isadmin)
                        .'<td align=center>'
                        .vfPrettyDate($sms['date']);
                if($long && $isadmin)
                {
                    $out .= '<td width=500px>'.htmlspecialchars( $sms['text'] );
                }
                $out .= '</tr>';
            }
        }
        $out .= '</table>';
        return $out;
    }
}

