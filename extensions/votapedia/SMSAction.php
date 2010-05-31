<?php
if (!defined('MEDIAWIKI') ) die();
/**
 * Description of SMSAction
 *
 * @author Emir Habul <emiraga@gmail.com>
 */
class SMSAction {
    /** @var String */ private $text = '';
    public function __construct()
    {
    }
    public function parseSMS($text)
    {
        $this->text = $text;

        if(strncasecmp($text, 'start', 4) == 0)
        {
            //@todo start surveys
            return;
        }
        $numbers = preg_split("/[^0-9]+/", $text);
        foreach($numbers as $choice)
        {
            //
        }
    }
}

?>