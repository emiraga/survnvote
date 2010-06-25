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
 * @package SmsIntegration
 */
class Sms
{
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
    static private $blackList = array('2888', '28882', 'CELCOM', '22990',
        '23131', '29292', 'ChannelC', '63008', 'Channel X'); //In Malaysia I receive a lot of spam.

    /* Number to send a message when requesting a balance report */
    static private $balanceNumber = '2888';
    /* Text message to send when requesting a balance report */
    static private $balanceMessage = 'BAL';

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
        global $vgDB, $vgSmsPrefix;
        
        $new = $vgDB->GetAll("SELECT ID, SenderNumber, TextDecoded FROM {$vgSmsPrefix}inbox "
                ."WHERE Processed = 'false'");
        $list = array();
        foreach($new as $sms)
        {
            if($sms['SenderNumber'] == Sms::$balanceNumber)
            {
                Sms::processed($sms['ID']);
                continue;
            }
            if(in_array($sms['SenderNumber'], Sms::$blackList))
            {
                Sms::delete($sms['ID']);
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
        global $vgDB, $vgSmsPrefix;
        
        $vgDB->Execute("UPDATE {$vgSmsPrefix}inbox SET Processed = 'true' WHERE ID = ?", array($id));
    }
    /**
     * Delete a record from sms incoming database.
     * Private called by a getNewSms() to remove items from Sms::$blackList
     * 
     * @param Integer $id
     */
    private static function delete($id)
    {
        global $vgDB, $vgSmsPrefix;

        $vgDB->Execute("DELETE FROM {$vgSmsPrefix}inbox WHERE ID = ?", array($id));
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
        global $vgDB, $vgSmsPrefix;

        $text = '';
        for($i=0; $i<strlen($message); $i++)
            $text .= '00' . bin2hex($message[$i]);

        $valid="255";
        $sender = 'Gammu 1.27.0';
        $phone = '';
        
        $vgDB->Execute("INSERT INTO {$vgSmsPrefix}outbox(Text,DestinationNumber,TextDecoded,InsertIntoDB,"
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
        global $vgDB, $vgSmsPrefix;
        
        $rec = $vgDB->GetAll("SELECT DestinationNumber, InsertIntoDB, TextDecoded "
                ."FROM {$vgSmsPrefix}outbox ORDER BY InsertIntoDB");
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
     * Report about latest outgoing SMS messages.
     * 
     * @param $limit how many results
     * @return Array a list of previous messages and their status
     */
    static function getReport($limit = 0)
    {
        global $vgDB, $vgSmsPrefix;
        
        $sql = "SELECT DestinationNumber, InsertIntoDB, Status, TextDecoded "
                ."FROM {$vgSmsPrefix}sentitems ORDER BY InsertIntoDB DESC";
        if($limit)
            $sql .= " LIMIT ".intval($limit);

        $rec = $vgDB->GetAll($sql);
        $result = array();
        foreach($rec as $message)
        {
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
     * Get latest incoming SMS messages.
     * 
     * @param $limit how many results
     * @return Array a list of incoming messages
     */
    static function getIncoming($limit = 0)
    {
        global $vgDB, $vgSmsPrefix;
        
        $sql = "SELECT SenderNumber, ReceivingDateTime, TextDecoded "
                ."FROM {$vgSmsPrefix}inbox ORDER BY ReceivingDateTime DESC";
        if($limit)
            $sql .= " LIMIT ".intval($limit);

        $rec = $vgDB->GetAll($sql);
        $result = array();
        foreach($rec as $message)
        {
            #if(in_array($message['SenderNumber'], Sms::$blackList) )
            #    continue;

            $result[] = array(
                    'number' => $message['SenderNumber'],
                    'date' => $message['ReceivingDateTime'],
                    'status' => 'Received',
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
        Sms::sendSMS(Sms::$balanceNumber, Sms::$balanceMessage);
    }
    /**
     * function getLatestBalance()
     * 
     * @return String balance
     */
    static function getLatestBalance()
    {
        global $vgDB, $vgSmsPrefix;
        
        $text = $vgDB->GetOne("SELECT TextDecoded FROM {$vgSmsPrefix}inbox "
                ."WHERE SenderNumber = ? ORDER BY ID DESC",
                array(Sms::$balanceNumber));

        //Change this to match format of your balance reports
        preg_match('@RM([^,]+)@i', $text, $matches); //Malaysian ringgits
        return $matches[0];
    }
    /**
     * function getBalanceReports()
     *
     * @return Array
     */
    static function getBalanceReports($limit = 0)
    {
        global $vgDB, $vgSmsPrefix;
        
        $sql = "SELECT TextDecoded, ReceivingDateTime FROM {$vgSmsPrefix}inbox "
                ."WHERE SenderNumber = ? ORDER BY ID DESC";
        if($limit)
            $sql .= " LIMIT ".intval($limit);
        
        $bal = $vgDB->GetAll($sql, array(Sms::$balanceNumber));
        $result = array();
        foreach($bal as $sms)
        {
            $text = $sms['TextDecoded'];

            //Change this to match format of your balance reports
            preg_match('@RM([^,]+)@i', $text, $matches);//Malaysian ringgits
            
            $result[] = array(
                'text' => $text,
                'date' => $sms['ReceivingDateTime'],
                'balance' => $matches[0],
            );
        }
        return $result;
    }
}

