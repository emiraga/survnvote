<?php
if (!defined('MEDIAWIKI')) die();
/**
 * This package contains all value objects.
 *
 * @package ValueObject
 */

/**
 * An value object of a choice which follows PHP MVC suggestion.
 *
 * @author Bai Qifeng
 * @author Emir Habul
 * @package ValueObject
 */
class ChoiceVO
{
    public $choiceID;
    public $surveyID;
    public $pageID;
    public $choice;
    public $receiver;
    public $SMS;
    public $points;
    public $numvotes = 0;
}

