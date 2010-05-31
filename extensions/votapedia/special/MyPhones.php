<?php
if (!defined('MEDIAWIKI')) die();

global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/DAO/UserphonesDAO.php");
require_once("$vgPath/FormControl.php");

/**
 * Special page Create Survey
 *
 * @author Emir Habul
 */
class MyPhones extends SpecialPage
{
    /** @var UserphonesDAO */ private $dao;

    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('MyPhones');
        wfLoadExtensionMessages('Votapedia');
        $this->includable( false );
        global $vgScript;
        
        $this->target = Skin::makeSpecialUrl('MyPhones');
        $this->items = array(
                'empty' => array(
                        'type' => 'null',
                ),
                'newnumber' => array(
                        'type' => 'html',
                        'name' => 'Add phone number',
                        'explanation' => 'Mobile phone number with country prefix, for example: +6019 231 8195.',
                        'learn_more' => 'Details of Phone numbers',
                        'code' => "<form action=\"{$this->target}\" method=\"POST\">"
                                ."<input type=\"input\" name=\"newnumber\">"
                                ."<input type=\"submit\" name=\"wpSubmit\" value=\"".wfMsg('add-number')."\">"
                                .'<input type="hidden" name="wpEditToken" value="'. vfUser()->editToken() .'">'
                                ."</form>",
                        //'icon' => $vgScript.'/icons/mobile.png',
                ),
                'requestcode' => array(
                        'type' => 'html',
                        'name' => 'NUMBER GOES HERE',
                        'explanation' => 'This number has not been verified. You can request confirmation code.',
                        'learn_more' => 'Details of Phone confirmation',
                        'code' => "<form action=\"{$this->target}\" method=\"POST\">"
                                ."<input type=\"hidden\" name=\"id\" value=\"{ID}\">"
                                ."<input type=\"submit\" name=\"wpSubmit\" value=\"".wfMsg('request-code')."\">"
                                .'<input type="hidden" name="wpEditToken" value="'. vfUser()->editToken() .'">'
                                ."</form>",
                        'icon' => $vgScript.'/icons/mobile.png',
                ),
                'entercode' => array(
                        'type' => 'html',
                        'name' => 'NUMBER GOES HERE',
                        'explanation' => 'Confirm your phone number by entering confirmation code.',
                        'learn_more' => 'Details of Phone confirmation',
                        'code' => "<form action=\"{$this->target}\" method=\"POST\">"
                                ."Code: <input type=\"input\" name=\"code\">"
                                ."<input type=\"hidden\" name=\"id\" value=\"{ID}\">"
                                ."<input type=\"submit\" name=\"wpSubmit\" value=\"".wfMsg('submit-code')."\">"
                                .'<input type="hidden" name="wpEditToken" value="'. vfUser()->editToken() .'">'
                                ."</form>",
                        'icon' => $vgScript.'/icons/mobile.png',
                ),
                'verified' => array(
                        'type' => 'html',
                        'name' => 'NUMBER GOES HERE',
                        //'explanation' => 'Confirm your phone number by entering confirmation code.',
                        //'learn_more' => 'Details of Phone confirmation',
                        'code' => vfSuccessBox('Number {NUMBER} has been verified.'),
                        'icon' => $vgScript.'/icons/mobile.png',
                ),
        );
        $this->display = array();
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param $par
     */
    function execute( $par = null )
    {
        global $wgOut;
        try
        {
            $this->dao =& new UserphonesDAO(vfUser());
            global $wgOut, $wgRequest;
            if($wgRequest->getVal('wpSubmit') == wfMsg('add-number'))
            {
                if(! vfUser()->checkEditToken())
                    die('Wrong edit token');
                $phone = $wgRequest->getVal('newnumber');

                $this->dao->addNewPhone($phone);
                $wgOut->redirect($this->target, 302);
            }
            elseif($wgRequest->getVal('wpSubmit') == wfMsg('request-code'))
            {
                if(! vfUser()->checkEditToken())
                    die('Wrong edit token');
                $id = intval($wgRequest->getVal('id'));
                if($this->dao->checkConfirmAllowed())
                {
                    $code = $this->dao->getConfirmCode($id);
                    //@todo send SMS message
                }
                $wgOut->redirect($this->target, 302);
            }
            elseif($wgRequest->getVal('wpSubmit') == wfMsg('submit-code') )
            {
                if(! vfUser()->checkEditToken())
                    die('Wrong edit token');
                $code = $wgRequest->getVal('code');
                $id = $wgRequest->getVal('id');
                $this->dao->verifyCode($id, $code);
                $wgOut->redirect($this->target, 302);
            }
            else
            {
                $wgOut->setPageTitle(wfMsg('myphones'));
                $this->listPhones();
                $form = new FormControl($this->items);
                $this->display[] = 'newnumber';
                $form->AddPage('', $this->display);
            }
        }
        catch(Exception $e)
        {
            /** @var $wgOut WebOutputPage */
            $wgOut->setPageTitle("My Phones Error");
            $wgOut->addWikiText( vfErrorBox( $e->getMessage() ) );
            $wgOut->addReturnTo( Title::newFromText('Special:MyPhones') );
            return;
        }
    }
    function listPhones()
    {
        $list = $this->dao->getList();
        $num = count($list);

        global $wgOut;
        $wgOut->addHTML("<p><i>You have added a total of $num phone number(s).</i></p>");

        $this->display[] = 'empty';
        foreach($list as $phone)
        {
            $id = $phone['id'];
            $this->display[] = $id;
            $this->display[] = 'empty';

            if($phone['status'] == vPHONE_NEW 
                    || ($phone['status'] == vPHONE_SENT_CODE
                        && $this->dao->checkConfirmAllowed()))
            {
                $this->items[ $id ] = $this->items[ 'requestcode' ];
                $this->items[ $id ]['code'] = str_replace('{ID}', $id , $this->items[ $id ]['code']);
            }
            elseif($phone['status'] == vPHONE_SENT_CODE)
            {
                $this->items[ $id ] = $this->items[ 'entercode' ];
                $this->items[ $id ]['code'] = str_replace('{ID}', $id , $this->items[ $id ]['code']);
            }
            elseif($phone['status'] == vPHONE_VERIFIED)
            {
                $this->items[ $id ] = $this->items[ 'verified' ];
                $this->items[ $id ]['code'] = str_replace('{NUMBER}',$phone['number'],$this->items[ $id ]['code']);
            }
            else
            {
                $this->items[ $id ] = $this->items[ 'empty' ];
            }
            $this->items[ $id ]['name'] = $phone['number'];
        }
    }
}
