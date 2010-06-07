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
        $this->setGroup('MyPhones', 'votapedia');
        global $vgScript;
        
        $this->target = Skin::makeSpecialUrl('MyPhones');
        $this->items = array(
                'empty' => array(
                        'type' => 'null',
                ),
                'newnumber' => array(
                        'type' => 'html',
                        'name' => 'Add phone number',
                        'explanation' => 'Mobile phone number with country prefix, for example: +60172318195.',
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
                        'code' => "Phone number needs confirmation.<form action=\"{$this->target}\" method=\"POST\">"
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
                'deleted' => array(
                        'type' => 'html',
                        'name' => 'NUMBER GOES HERE',
                        //'explanation' => 'Confirm your phone number by entering confirmation code.',
                        //'learn_more' => 'Details of Phone confirmation',
                        'code' => 'This number has been deleted.',
                        'icon' => $vgScript.'/icons/mobile.png',
                ),
        );
        $this->display = array();
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        global $wgOut;
        try
        {
            $this->dao = new UserphonesDAO(vfUser());
            global $wgOut, $wgRequest;
            if($wgRequest->getVal('wpSubmit') == wfMsg('add-number'))
            {
                if(! vfUser()->checkEditToken())
                    die('Wrong edit token');
                $phone = vfProcessNumber( $wgRequest->getVal('newnumber') );
                $id = $this->dao->addNewPhone($phone);
                global $vgEnableSMS;
                if($vgEnableSMS == false)
                {
                    //SMS is disabled, we have to assume that number is correct
                    $code = $this->dao->getConfirmCode($id);
                    $this->dao->verifyCode($id, $code);
                }
                $wgOut->redirect($this->target, 302);
                return;
            }
            elseif($wgRequest->getVal('wpSubmit') == wfMsg('request-code'))
            {
                if(! vfUser()->checkEditToken())
                    die('Wrong edit token');
                $id = intval($wgRequest->getVal('id'));
                if($this->dao->checkConfirmAllowed())
                {
                    $code = $this->dao->getConfirmCode($id);
                    $number = $this->dao->getPhoneNumber($id);
                    global $vgPath;
                    require_once("$vgPath/Sms.php");
                    Sms::sendSMS($number, sprintf(Sms::$msgConfim, $code));
                    $wgOut->setPageTitle("SMS sent");
                    $wgOut->addHTML( vfSuccessBox("Sms message with confirmation code for $number will be delivered soon.") );
                    $wgOut->addWikiText("\n[[Special:SmsReport|View the progress of your SMS on this page]]\n\n");
                    $wgOut->addReturnTo( Title::newFromText('Special:MyPhones') );
                    return;
                }
            }
            elseif($wgRequest->getVal('wpSubmit') == wfMsg('submit-code') )
            {
                if(! vfUser()->checkEditToken())
                    die('Wrong edit token');
                $code = $wgRequest->getVal('code');
                $code = preg_replace('/[^0-9]/', '', $code);
                $id = $wgRequest->getVal('id');
                $this->dao->verifyCode($id, $code);
                $wgOut->redirect($this->target, 302);
                return;
            }
            elseif($wgRequest->getVal('wpSubmit') == wfMsg('delete-number')  )
            {
                if(! vfUser()->checkEditToken())
                    die('Wrong edit token');
                $id = intval($wgRequest->getVal('id'));
                $this->dao->deletePhone($id);
                $wgOut->redirect($this->target, 302);
                return;
            }
            //View the list
            $wgOut->setPageTitle(wfMsg('myphones'));
            $this->listPhones();
            $form = new FormControl($this->items);
            $this->display[] = 'newnumber';
            $form->AddPage('', $this->display);
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
    /**
     * 
     */
    function listPhones()
    {
        $list = $this->dao->getList();
        $num = count($list);

        global $wgOut, $vgScript;
        $wgOut->addHTML("<p><i>You have added a total of $num phone number(s).</i></p>");

        $this->display[] = 'empty';
        foreach($list as $phone)
        {
            $id = $phone['id'];

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
            elseif($phone['status'] == vPHONE_DELETED)
            {
                $this->items[ $id ] = $this->items[ 'deleted' ];

            }
            $this->items[ $id ]['name'] = $phone['number'];
            $this->display[] = $id;
            $this->display[] = 'empty';

            if($phone['status'] != vPHONE_DELETED)
            {

                $this->items[ $id ]['afterall'] = "<form style=\"text-align:center;\"action=\"{$this->target}\" method=\"POST\">"
                        ."<input type=\"hidden\" name=\"id\" value=\"$id\">"
                        ."<input onclick=\"return confirm('Are you sure you want to delete this number?');\" title=\"Delete this number\" type=\"image\" src=\"$vgScript/icons/file_delete.png\" name=\"wpSubmit\" value=\"".wfMsg('delete-number')."\">"
                        .'<input type="hidden" name="wpEditToken" value="'. vfUser()->editToken() .'">'
                        ." Delete</form>";
            }else
            {
                $this->items[ $id ]['name'] = '';
                $this->items[ $id ]['aftername'] = "<strike>$phone[number]</strike>";
            }
        }
    }
}

