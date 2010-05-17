<?php
if (!defined('MEDIAWIKI')) die();

class SurveyButtons
{
    protected $page_id;
    protected $page_status;
    protected $page_author;
    protected $wikititle;

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
    /**
     * AJAX call, get the buttons of user which can edit the survey.
     *
     * @param $page_id Integer identifier of a survey
     * @param $title String title of a current page
     * @param $status String status of a survey, default 'ready'
     */
    static function ajaxButtons($page_id, $title, $status='ready')
    {
        global $wgUser;
        if ($wgUser->isAnon())
        {
            return '';
        } //just in case

        $buttons =& new SurveyButtons();
        $buttons->setPageID(intval($page_id));
        $buttons->setPageStatus($status);
        $buttons->setWikiTitle($title);
        
        return $buttons->getButtons();
    }
    function getButtons()
    {
        wfLoadExtensionMessages('Votapedia');
        global $wgUser;

        $prosurv = Title::newFromText('Special:ProcessSurvey');
        $cresurv = Title::newFromText('Special:CreateSurvey');

        $output = '<tr>';
        $output .= '<td>';
        $output .= '<form id="page'.$this->page_id.'" action="'.$prosurv->escapeLocalURL().'" method="POST">'
                .'<input type="hidden" name="id" value="'.$this->page_id.'">'
                .'<input type="hidden" name="returnto" value="'.htmlspecialchars($this->wikititle).'" />'
                .'<input type="hidden" name="wpEditToken" value="'.htmlspecialchars( $wgUser->editToken() ).'">';
        if($this->page_status == 'ready')
        {
            $output.='<input type="submit" name="wpSubmit" value="'.wfMsg('start-survey').'" />';
        }
        elseif($this->page_status == 'active')
        {
            $output.='<input type="submit" name="wpSubmit" value="'.wfMsg('stop-survey').'" />';
        }
        else
        {
            ;
        }
        $output .= '</form>';
        $output .= '<td>';
        if($this->page_status == 'ready')
        {
            $output .='<form id="editpage'.$this->page_id.'" action="'.$cresurv->escapeLocalURL().'" method="POST">'
                    .'<input type="hidden" name="id" value="'.$this->page_id.'">'
                    .'<input type="submit" name="wpEditButton" value="'.wfMsg('edit-survey').'">'
                    .'<input type="hidden" name="returnto" value="'.htmlspecialchars($this->wikititle).'" />'
                    .'</form>';
        }
        return $output;
    }
    /**
     * Similar to getButtons function, but this is used when scripting
     * is not enabled in browser. Get limited buttons for a user.
     *
     * @return String HTML code of survey buttons
     */
    function getButtonsNoScript()
    {
        $viewsurv = Title::newFromText('Special:ViewSurvey');
        $cresurv = Title::newFromText('Special:CreateSurvey');

        return '<tr><td><form id="page'.$this->page_id.'" action="'.$viewsurv->escapeLocalURL().'" method="POST">'
                .'<input type="hidden" name="id" value="'.$this->page_id.'">'
                .'<input type="submit" name="wpSubmit" value="'.wfMsg('control-survey').'" />'
                .'<input type="hidden" name="returnto" value="'.htmlspecialchars($this->wikititle).'" />'
                .'</form>'
                .'<td><form id="editpage'.$this->page_id.'" action="'.$cresurv->escapeLocalURL().'" method="POST">'
                .'<input type="hidden" name="id" value="'.$this->page_id.'">'
                .'<input type="submit" name="wpEditButton" value="'.wfMsg('edit-survey').'">'
                .'<input type="hidden" name="returnto" value="'.htmlspecialchars($this->wikititle).'" />'
                .'</form>';
    }
    /**
     * Get HTML buttons for a page that is cacheable.
     * It contains javascript code which will load real buttons.
     *
     * @return HTML code
     */
    function getHTML()
    {
        assert($this->page_author);
        //control button for those that don't have javascript
        $output = '<noscript>'.$this->getButtonsNoScript().'</noscript>';

        $divname = "btnsSurvey{$this->page_id}-".rand();
        $output.= ""
                ."<script type='text/javascript'>"
                ."document.write('<div id=$divname></div>');"
                ."if(wgUserName=='{$this->page_author}')"
                ."sajax_do_call('SurveyButtons::ajaxButtons',[{$this->page_id},wgPageName,'{$this->page_status}'],function(o){"
                ."document.getElementById('$divname').innerHTML=o.responseText;});</script>";
        return $output;
    }
}

/**
 * Class used to display parts of HTML related to the buttons of survey.
 * Elements are NOT cache-able, meaning that they are context sensitive.
 *
 * @author Emir Habul
 *
 */
class SurveyButtonsNocache extends SurveyButtons
{
    /**
     * Get HTML buttons for a page that is not cacheable
     *
     * @return HTML code
     */
    function getHTML()
    {
        assert($this->page_id && $this->wikititle && $this->page_status);
        $divname = "btnsSurvey{$this->page_id}-".rand();
        $output = "<div id='$divname'>";
        $output .= $this->getButtons();
        $output .= '</div>';
        return $output;
    }
}
