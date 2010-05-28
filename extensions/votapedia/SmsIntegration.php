<?php
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
class SmsIntegration {
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
    function getNewSms()
    {
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
    function processed($id)
    {
        $vgDB->Execute("UPDATE smsd.inbox SET Processed = 'false' WHERE ID = ?", array($id));
    }
}
