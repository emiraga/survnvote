<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SmsIntegration
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/Sms.php");
require_once("$vgPath/graph/Graph.php");

/**
 * Special page Create Survey
 *
 * @author Emir Habul
 * @package SmsIntegration
 */
class SmsReport extends SpecialPage
{
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('SmsReport');
        wfLoadExtensionMessages('Votapedia');
        $this->includable( false );
        $this->target = Skin::makeSpecialUrl('SmsReport');
        $this->setGroup('SmsReport', 'votapedia');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        global $wgUser, $vgEnableSMS, $wgOut, $wgRequest;
        $admin = vfUser()->isAdmin();

        if(! $vgEnableSMS)
        {
            $wgOut->showErrorPage('smsnot', 'smsnot-desc');
            return;
        }
        $wgOut->setPageTitle("SMS Delivery Report");
        $out = '';
        if($admin)
        {
            $out .="== Account Balance ==\n";
            $out .= '<font size="6" style="text-align:center">'.Sms::getLatestBalance()."</font>\n\n";
            if($wgRequest->getVal('getNewBalance'))
            {
                if( !vfUser()->checkEditToken() )
                    die('Invalid edit token');
                Sms::requestCheckBalance();
                $this->target = Skin::makeSpecialUrl('SmsReport', 'requestPlaced=1');
                $wgOut->redirect($this->target, 302);
                return;
            }

            if($wgRequest->getVal('requestPlaced'))
            {
                $out .= 'Request for new balance has been placed, refresh this page to view the update.';
            }
            else
            {
                $out .= '[{{SERVER}}/'.Skin::makeSpecialUrl('SmsReport', 'getNewBalance=1&wpEditToken='. urlencode( vfUser()->editToken()) ).' Request new balance report]';
            }
            $out .= "\n\n";
        }
        $out .= "== Delivery ==\n";
        $out .= "{| class=\"wikitable sortable\" style=\"width: 100%\"\n! Status !! Number !! Date\n";
        $pending = Sms::getPending();
        foreach($pending as $sms)
        {
            $number = $sms['number'];
            $number = vfColorizePhone($number, false, !$admin);
            $out .= "|-\n";
            $out .= "| Pending || $number || ". vfPrettyDate( $sms['date'] );
            if($admin)
            {
                $out.="|| ". substr( $sms['text'], 0, 60 );
            }
            $out.="\n";
        }
        $report = Sms::getReport(10);
        foreach($report as $sms)
        {
            $number = vfColorizePhone($sms['number'], false, !$admin);
            if( strstr($sms['status'], 'Error') )
                $statcolor = "style=\"background: #FFA0A0\" | ";
            elseif( strstr($sms['status'], 'OK') )
                $statcolor = "style=\"background: #A0FFA0\" | ";
            else
                $statcolor = '';
            $status = preg_replace("/([a-z])([A-Z])/", '$1 $2', $sms['status']);
            $status = preg_replace("/OK/", 'OK, ', $status);

            $out .= "|-\n";
            $out .= "| $statcolor $status || $number || ".vfPrettyDate( $sms['date'] );
            if($admin)
            {
                $out.="|| " . htmlspecialchars( substr( $sms['text'], 0, 60 ) );
            }
            $out.="\n";
        }
        $out .= '|}';

        if($admin)
        {
            $out .="\n\n== Balance Reports ==\n";
            $out .= "{| class=\"wikitable sortable\" style=\"width: 100%\"\n! Date !! Balance !! Text\n";
            $bal = Sms::getBalanceReports(20);
            foreach($bal as $sms)
            {
                $out .= "|-\n";
                $out .= "| $sms[date] || $sms[balance] || $sms[text]\n";
            }
            $out .= '|}';
        }
        $wgOut->addWikiText($out);
        if($admin && count($bal)>1)
        {
            $values = new GraphXYdate('');
            //@todo reduce size of output
            foreach($bal as $sms)
            {
                $balance = preg_replace("/RM/", '', $sms['balance']);
                $values->addPoint($sms['date'], $balance);
            }
            $gr = new GraphLineXY('linexy');
            $gr->setWidth(750);
            $gr->addValues($values);
            $wgOut->addHTML('<center>'.$gr->getHTMLImage('imgsmsreport').'</center>');
        }
    }
}

