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

    /*Do not show messages from these numbers, these are special numbers which
     *are specific to mobile provider. */
    static public $blackList = array('2888', '28882', 'CELCOM', '22990');

    /**
     * function getNewSms()
     * 
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
        /* fake votes used for testing */
        return array(
            array('id' => 1001,'from' => '000','text' => '17',),
            array('id' => 1001,'from' => '001','text' => '17',),
            array('id' => 1001,'from' => '002','text' => '18',),
            array('id' => 1001,'from' => '003','text' => '17',),
            array('id' => 1001,'from' => '004','text' => '19',),
        ); /* */

        global $vgDB;
        $new = $vgDB->GetAll("SELECT ID, SenderNumber, TextDecoded FROM smsd.inbox WHERE Processed = 'false'");
        $list = array();
        foreach($new as $sms)
        {
            if(in_array($sms['SenderNumber'], Sms::$blackList))
                continue;
            
            $list[] = array(
                    'id' => $sms['ID'],
                    'from' => $sms['SenderNumber'],
                    'text' => $sms['TextDecoded'],
            );
        }
        return $list;
    }
    /**
     * function processed($id)
     *
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
        global $vgDB;
        $vgDB->Execute("UPDATE smsd.inbox SET Processed = 'true' WHERE ID = ?", array($id));
    }
    /**
     * function sendSMS($destination, $message)
     * 
     * Action which is performed when votapedia wants to send an SMS to certain number
     *
     * @param $destination destination number
     * @param $message message text
     */
    static function sendSMS($destination, $message)
    {
        $text = '';
        for($i=0; $i<strlen($message); $i++)
            $text .= '00' . bin2hex($message[$i]);

        $valid="255";
        $sender = 'Gammu 1.27.0';
        $phone = '';
        global $vgDB;
        $vgDB->Execute("INSERT INTO smsd.outbox(Text,DestinationNumber,TextDecoded,InsertIntoDB,"
                ."RelativeValidity,SenderID,CreatorID) VALUES(?,?,?,NOW(),?,?,?)",
                array($text,$destination,$message, $valid,'',$sender));
    }
    /**
     * function getPending()
     *
     * @return Array a list of messages which are waiting to be send.
     */
    static function getPending()
    {
        global $vgDB;
        $rec = $vgDB->GetAll("SELECT DestinationNumber, InsertIntoDB, TextDecoded FROM smsd.outbox ORDER BY InsertIntoDB");
        $result = array();
        foreach($rec as $message)
        {
            $result[] = array(
                    'number' => $message['DestinationNumber'],
                    'date' => $message['InsertIntoDB'],
                    'status' => 'Pending',
                    'text' => $message['TextDecoded'],
            );
        }
        return $result;
    }
    /**
     *
     * @return Array a list of previous messages and their status
     */
    static function getReport()
    {
        global $vgDB;
        $rec = $vgDB->GetAll("SELECT DestinationNumber, InsertIntoDB, Status, TextDecoded FROM smsd.sentitems ORDER BY InsertIntoDB");
        $result = array();
        foreach($rec as $message)
        {
            if(in_array($message['DestinationNumber'], Sms::$blackList))
                continue;
            
            $result[] = array(
                    'number' => $message['DestinationNumber'],
                    'date' => $message['InsertIntoDB'],
                    'status' => $message['Status'],
                    'text' => $message['TextDecoded'],
            );
        }
        return $result;
    }
    /**
     * My provider gives me option to check balance by sending SMS.
     * If you are lucky as me, you can try to configure this
     * balance checking to work. Otherwise leave it be.
     */
    static function requestCheckBalance()
    {
        Sms::sendSMS('2888', 'BAL');
    }
    /**
     * function getLatestBalance()
     * 
     * @return Float balance
     */
    static function getLatestBalance()
    {
        global $vgDB;
        $text = $vgDB->GetOne("SELECT TextDecoded FROM smsd.inbox WHERE SenderNumber = ? ORDER BY ID DESC",
                array('2888'));
        preg_match('@RM([^,]+)@i', $text, $matches);
        return $matches[0];
    }
    /**
     * function getBalanceReports()
     *
     * @return Array
     */
    static function getBalanceReports()
    {
        global $vgDB;
        $bal = $vgDB->GetAll("SELECT TextDecoded, ReceivingDateTime FROM smsd.inbox WHERE SenderNumber = ? ORDER BY ID DESC",
                array('2888'));
        $result = array();
        foreach($bal as $sms)
        {
            $text = $sms['TextDecoded'];
            preg_match('@RM([^,]+)@i', $text, $matches);
            $result[] = array(
                'text' => $text,
                'date' => $sms['ReceivingDateTime'],
                'balance' => $matches[0],
            );
        }
        return $result;
    }
}
