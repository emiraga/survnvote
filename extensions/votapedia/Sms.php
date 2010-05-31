<?php
if (!defined('MEDIAWIKI') ) die();
if (defined('VOTAPEDIA_TEST')) return;
/**
 * This is example class that provides SMS integration with external source.
 *
 * This SmsIntegration class implements a working interface to gammu-smsd
 * link: http://www.gammu.org/wiki/index.php?title=Gammu:SMSD
 *
 * If you have another source of SMS, please modify this script accordingly
 * to adjust votapedia to your system.
 *
 * This class is being called by the Daemon.php.
 *
 */
class Sms
{
    /* Message used for sending confirmation code. */
    static public $msgConfim = 'Confirmation code is %s. Thank you for using www.votapedia.net';

    /**
     * This function needs to return an array. Each element of array represents
     * one new sms that has arrived.
     *
     * Each element in returned array will be an associative array with three
     * keys, namely:
     *      id
     *      from
     *      text
     *
     * @return Array
     */
    static function getNewSms()
    {
        global $vgDB;
        $new = $vgDB->GetAll("SELECT ID, SenderNumber, TextDecoded FROM smsd.inbox WHERE Processed = 'false'");
        $list = array();
        foreach($new as $sms)
        {
            $list[] = array(
                    'id' => $sms['ID'],
                    'from' => $sms['SenderNumber'],
                    'text' => $sms['TextDecoded'],
            );
        }
        return $list;
    }
    /**
     * When we are done with processing of SMS, this function will be called.
     *
     * This allows you to delete or mark as processed old SMS, so that
     * they are not retrieved twice by the function getNewSms()
     *
     * @param $id Integer id of previous sms
     * @return None
     */
    static function processed($id)
    {
        $vgDB->Execute("UPDATE smsd.inbox SET Processed = 'true' WHERE ID = ?", array($id));
    }
    /**
     * Action which is performed when votapedia wants to send an SMS to certain number
     *
     * @param $destination destination number
     * @param $message message text
     */
    static function sendSMS($destination, $message)
    {
        $text = bin2hex($message);
        $valid="255";
        $date = vfDate();
        $sender = 'votapedia';
        $phone = '';
        global $vgDB;
        $vgDB->Execute("INSERT INTO smsd.outbox(Text,DestinationNumber,TextDecoded,SendingDateTime,"
                ."RelativeValidity,SenderID,DeliveryReport,CreatorID) VALUES(?,?,?,?,?,?,?,?)",
                array($text,$destination,$message,$date,$valid,'','no',$sender));
    }
}
