<?php
if (!defined('MEDIAWIKI')) die();

global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/Sms.php");
require_once("$vgPath/graph/Graph.php");

/**
 * Special page Create Survey
 *
 * @author Emir Habul
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
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param $par
     */
    function execute( $par = null )
    {
        global $wgUser, $vgEnableSMS, $wgOut, $wgRequest;
        $gr = $wgUser->getGroups();
        $admin = $wgUser->isLoggedIn() && in_array("bureaucrat", $gr) || in_array("sysop", $gr);

        if(! $vgEnableSMS)
        {
            $wgOut->showErrorPage('smsnot', 'smsnot-desc');
            return;
        }
        $wgOut->setPageTitle("SMS Report");
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
            if(!$admin)
                $number = substr($number, 0, strlen($number) - 3) . "<font color=gray>XXX</font>";
            $out .= "|-\n";
            $out .= "| Pending || $number || $sms[date]";
            if($admin)
                $out.="|| $sms[text]";
            $out.="\n";
        }
        $report = Sms::getReport();
        foreach($report as $sms)
        {
            $number = $sms['number'];
            if(!$admin)
                $number = substr($number, 0, strlen($number) - 3) . "<font color=gray>XXX</font>";
            if( strstr($sms['status'], 'Error') )
                $statcolor = "style=\"background: #FFA0A0\" | ";
            elseif( strstr($sms['status'], 'OK') )
                $statcolor = "style=\"background: #A0FFA0\" | ";
            else
                $statcolor = '';
            $status = preg_replace("/([a-z])([A-Z])/", '$1 $2', $sms['status']);
            $status = preg_replace("/OK/", 'OK, ', $status);

            $out .= "|-\n";
            $out .= "| $statcolor $status || $number || $sms[date]";
            if($admin)
                $out.="|| $sms[text]";
            $out.="\n";
        }
        $out .= '|}';

        if($admin)
        {
            $out .="\n\n== Balance Reports ==\n";
            $out .= "{| class=\"wikitable sortable\" style=\"width: 100%\"\n! Date !! Balance !! Text\n";
            $bal = Sms::getBalanceReports();
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
            $gs = new GraphSeries('');
            $tmax = $tmin = strtotime($bal[0]['date']);
            foreach($bal as $sms)
            {
                $time = strtotime($sms['date']);
                if($tmin > $time)
                    $tmin = $time;
                if($tmax < $time)
                    $tmax = $time;
            }
            foreach($bal as $sms)
            {
                $time = strtotime($sms['date']);
                $time = ($time - $tmin)/($tmax - $tmin)*5;
                $balance = preg_replace("/RM/", '', $sms['balance']);
                $gs->addItem($time, $balance,'');
            }
            $gr = new Graph('linexy');
            $gr->setXLabel( date('Y-m-d',$tmin)
                       .'|'.date('Y-m-d',$tmin+($tmax-$tmin)/2)
                       .'|'.date('Y-m-d',$tmax) );
            $gr->addSeries($gs);
            $wgOut->addHTML('<center>'.$gr->getHTMLImage().'</center>');
        }
    }
}
