<?php
if (!defined('MEDIAWIKI')) die();

class SurveyButtons
{
    /** @var Integer */protected $page_id;
    /** @var String */ protected $page_status;
    /** @var String */ protected $page_author;
    /** @var String */ protected $wikititle;
    /** @var Title */  protected $prosurv;
    /** @var Title */  protected $cresurv;
    /** @var Title */  protected $viewsurv;
    /** @var Boolean */protected $show_details = true;
    /** @var Boolean */protected $show_vote = false;
    /** @var Boolean */protected $has_control = false;

    public function __construct()
    {
    }
    function setPageID($page_id)
    {
        $this->page_id = $page_id;
    }
    function setPageStatus($status)
    {
        $this->page_status = $status;
    }
    function setPageAuthor($author)
    {
        $this->page_author = $author;
    }
    function setWikiTitle($title)
    {
        $this->wikititle = $title;
    }
    function setDetailsButton($show)
    {
        $this->show_details = $show;
    }
    function setVoteButton($show)
    {
        $this->show_vote = $show;
    }
    function setHasControl($control)
    {
        $this->has_control = $control;
    }    /*
     * @return HTML code of buttons
    */
    function getHTML($show_details = false)
    {
        $divname = "btnsSurvey{$this->page_id}-".rand();
        $output = "<div id='$divname'>";

        //Edit button
        if($this->has_control && $this->page_status == 'ready' )
        {
            $output .='<input type="submit" name="wpSubmit" value="'.wfMsg('edit-survey').'">';
        }

        if($this->show_vote)
        {
            $output .='<input type="submit" name="wpSubmit" value="'.wfMsg('vote-survey').'">';
        }

        if($this->has_control)
        {
            if($this->page_status == 'ready')
            {
                $output.='<input type="submit" name="wpSubmit" value="'.wfMsg('start-survey').'" />';
            }
            elseif($this->page_status == 'active')
            {
                $output.='<input type="submit" name="wpSubmit" value="'.wfMsg('stop-survey').'" onClick="return confirm(\'Are you sure you want to stop this survey? This operation cannot be undone.\')" />';
            }
        }
        //$output.= '<div style="float: right;">';
        if($this->show_details)
        {
            $output .='&nbsp;&nbsp;&nbsp;<input type="submit" name="wpSubmit" value="'.wfMsg('view-details').'">';
        }
        //$output .='</div>';
        $output .='</div>';
        return $output;
    }
}
