<?php
if (!defined('MEDIAWIKI') ) die(); if (defined('VOTAPEDIA_TEST')) return;
/**
 * @package SmsIntegration
 */

/**
 * This is example class that provides SMS integration with external source.
 *
 * This Sms Integration class implements a working interface to gammu-smsd
 * link: http://www.gammu.org/wiki/index.php?title=Gammu:SMSD
 *
 * If you have another source of SMS, please modify this script accordingly
 * to adjust votapedia to your system.
 *
 * This class is being called by the Daemon.php.
 *
 * @package SmsIntegration
 */
class Sms
{
    /* Configure the name of GAMMU-SMSD database */
    static private $smsdDBname = 'smsd';

    /* Message used for sending confirmation code. */
    static public $msgConfim = 'Confirmation code is %s. Thank you for using www.votapedia.net.';
    /* Message used when user is using votapedia for the first time */
    static public $msgCreateUser = 'Thank for participating in Votapedia surveys. You can login to www.votapedia.net, Username:%s Password:%s';
    /* Message used when user is using votapedia for the first time */
    static public $msgCreateUserNoPass = 'Thank for participating in Votapedia surveys. You can login to www.votapedia.net, Username:%s';

    /* Command to send by SMS to check validity of account. */
    static public $cmdCheck = 'CHECK';
    /* Command to send by SMS to check validity of account. */
    static public $cmdConfirm = 'CONFIRM';

    /*Do not show messages from these numbers, these are special numbers which
     *are specific to mobile provider. */
    static public $blackList = array('2888', '28882', 'CELCOM', '22990', '23131','29292','ChannelC', '63008');

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
        global $vgDB;
        $smsdDBname = Sms::$smsdDBname;
        
        $new = $vgDB->GetAll("SELECT ID, SenderNumber, TextDecoded FROM $smsdDBname.inbox "
                ."WHERE Processed = 'false'");
        $list = array();
        foreach($new as $sms)
        {
            if(in_array($sms['SenderNumber'], Sms::$blackList))
            {
                Sms::processed($sms['ID']);
                continue;
            }
            
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
     * @param Integer $id id of previous sms
     */
    static function processed($id)
    {
        global $vgDB;
        $smsdDBname = Sms::$smsdDBname;
        
        $vgDB->Execute("UPDATE $smsdDBname.inbox SET Processed = 'true' WHERE ID = ?", array($id));
    }
    /**
     * function sendSMS($destination, $message)
     * 
     * Action which is performed when votapedia wants to send an SMS to certain number
     *
     * @param String $destination destination number
     * @param String $message message text
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
        $smsdDBname = Sms::$smsdDBname;
        
        $vgDB->Execute("INSERT INTO $smsdDBname.outbox(Text,DestinationNumber,TextDecoded,InsertIntoDB,"
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
        $smsdDBname = Sms::$smsdDBname;
        $rec = $vgDB->GetAll("SELECT DestinationNumber, InsertIntoDB, TextDecoded "
                ."FROM $smsdDBname.outbox ORDER BY InsertIntoDB");
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
        $smsdDBname = Sms::$smsdDBname;
        
        $rec = $vgDB->GetAll("SELECT DestinationNumber, InsertIntoDB, Status, TextDecoded "
                ."FROM $smsdDBname.sentitems ORDER BY InsertIntoDB");
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
     * balance checking to work.
     *
     * Otherwise, delete Sms::sendSMS statement below.
     */
    static function requestCheckBalance()
    {
        Sms::sendSMS('2888', 'BAL');
    }
    /**
     * function getLatestBalance()
     * 
     * @return String balance
     */
    static function getLatestBalance()
    {
        global $vgDB;
        $smsdDBname = Sms::$smsdDBname;
        
        $text = $vgDB->GetOne("SELECT TextDecoded FROM $smsdDBname.inbox "
                ."WHERE SenderNumber = ? ORDER BY ID DESC",
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
        $smsdDBname = Sms::$smsdDBname;
        
        $bal = $vgDB->GetAll("SELECT TextDecoded, ReceivingDateTime FROM $smsdDBname.inbox "
                ."WHERE SenderNumber = ? ORDER BY ID DESC",
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
