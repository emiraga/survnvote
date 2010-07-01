<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SurveyView
 */

abstract class SurveyButtons
{
    /** @var String */ protected $type;
    /** @var Integer */protected $page_id;
    /** @var String */ protected $page_status;
    /** @var Boolean */protected $show_details = true;
    /** @var Boolean */protected $show_vote = false;
    /** @var Boolean */protected $has_control = false;
    /** @var Boolean */protected $show_renew = false;
    /** @var Boolean */protected $show_liveshow = true;
    /** @var Boolean */protected $show_edit = true;

    /**
     * Set type of survey
     *
     * @param String $type type of this tag
     */
    public function setType($type)
    {
        //messages in i18n are lower case
        $this->type = strtolower( $type );
    }
    /**
     *
     * @param Integer $page_id
     */
    function setPageID($page_id)
    {
        $this->page_id = $page_id;
    }
    /**
     * Set status
     * @param String $status
     */
    function setPageStatus($status)
    {
        $this->page_status = $status;
    }
    /**
     * Should it show button "details"
     * @param Boolean $show
     */
    function setDetailsButton($show)
    {
        $this->show_details = $show;
    }
    /**
     * Should it show button "vote"
     * @param Boolean $show
     */
    function setVoteButton($show)
    {
        $this->show_vote = $show;
    }
    /**
     * Should it show button "powerpoint"
     * @param Boolean $show
     */
    function setLiveShowButton($show)
    {
        $this->show_liveshow = $show;
    }
    /**
     * Should it show button "powerpoint"
     * @param Boolean $show
     */
    function setEditButton($show)
    {
        $this->show_edit = $show;
    }
    /**
     * Should it show button "renew"
     * @param Boolean $show
     */
    function setRenewButton($show)
    {
        $this->show_renew = $show;
    }
    /**
     * Does this user has control over survey.
     *
     * @param Boolean $control
     */
    function setHasControl($control)
    {
        $this->has_control = $control;
    }
    /**
     *
     * @param Integer $presID presentation ID
     * @return String HTML code of buttons
     */
    abstract function getHTML($presID);
}

/**
 * Display HTML buttons for the view of surveys.
 *
 * @package SurveyView
 */
class RealSurveyButtons extends SurveyButtons
{
    /**
     * Output a survey buttons.
     * 
     * @param Integer $presID presentation ID
     * @return String HTML code of buttons
     */
    function getHTML($presID)
    {
        $divname = "btnsSurvey{$this->page_id}-".rand();
        $output = "<div id='$divname'>";

        //Edit button
        if($this->has_control && $this->show_edit)
        {
            $output .='<input type="submit" name="wpSubmit" value="'.wfMsg('edit-'.$this->type).'">';
        }

        if($this->show_vote)
        {
            $output .='<input type="submit" name="wpSubmit" value="'.wfMsg('vote-'.$this->type).'">';
        }

        if($this->has_control)
        {
            if($this->page_status == 'ready')
            {
                $output.='<input type="submit" name="wpSubmit" value="'.wfMsg('start-'.$this->type).'" />';
            }
            elseif($this->page_status == 'active')
            {
                $output.='<input type="submit" name="wpSubmit" value="'.wfMsg('stop-'.$this->type)
                  .'" onClick="return confirm(\'Are you sure you want to stop this '.$this->type
                        .'? This operation cannot be undone.\')" />';
            }
            else // page_status is 'ended'
            {
                if($this->show_renew)
                    $output.='<input type="submit" name="wpSubmit" value="'.wfMsg('renew-'.$this->type).'" />';
            }

            //Powerpoint button
            if($this->show_liveshow)
            {
                $output .='<br /><input type="submit" name="wpSubmit" value="'
                    .wfMsg('view-liveshow').'">';
            }
        }
        //$output.= '<div style="float: right;">';
        if($this->show_details)
        {
            $output .='<input type="submit" name="wpSubmit" value="'
                .wfMsg('view-details').'">';
        }
        //$output .='</div>';
        $output .='</div>';
        return $output;
    }
}

class SurveyNoButtons extends SurveyButtons
{
    /**
     * Don't show anything.
     * 
     * @param Integer $presID presentation ID
     * @return String HTML code of buttons
     */
    function getHTML($presID)
    {
        return '';
    }
}

