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
    function setDetailsButton($details)
    {
        $this->show_details = $details;
    }
    /*
     * @return HTML code of buttons
     */
    function getHTML($show_details = false)
    {
        $divname = "btnsSurvey{$this->page_id}-".rand();
        $output = "<div id='$divname'>";
        global $wgUser;

        //Edit button
        if($this->page_status == 'ready')
        {
            $output .='<input type="submit" name="wpSubmit" value="'.wfMsg('edit-survey').'">';
        }

        if($this->show_details)
            $output .='<input type="submit" name="wpSubmit" value="'.wfMsg('view-details').'">';

        if($this->page_status == 'ready')
        {
            $output.='<input type="submit" name="wpSubmit" value="'.wfMsg('start-survey').'" />';
        }
        elseif($this->page_status == 'active')
        {
            $output.='<input type="submit" name="wpSubmit" value="'.wfMsg('stop-survey').'" />';
        }
        $output .= '</div>';
        return $output;
    }
}
