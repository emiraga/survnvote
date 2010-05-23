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

    public function __construct()
    {
        $this->viewsurv = Title::newFromText('Special:ViewSurvey');
        $this->prosurv = Title::newFromText('Special:ProcessSurvey');
        $this->setCreateTitle('Special:CreateSurvey');
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
    /**
     *
     * @param $title String
     */
    function setCreateTitle($title)
    {
        $this->cresurv = Title::newFromText($title);
    }
    /**
     * AJAX call, get the buttons of user which can edit the survey.
     *
     * @param $page_id Integer identifier of a survey
     * @param $title String title of a current page
     * @param $status String status of a survey, default 'ready'
     * @deprecated
     */
    static function ajaxButtons($page_id, $title, $status='ready', $specialpage = 'Special:CreateSurvey')
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
        $buttons->setCreateTitle($specialpage);

        return $buttons->getButtons();
    }
    function getButtons()
    {
        wfLoadExtensionMessages('Votapedia');
        global $wgUser;

        $output = '<tr>';
        $output .= '<td>';
        $output .= '<form id="page'.$this->page_id.'" action="'.$this->prosurv->escapeLocalURL().'" method="POST">'
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
            $output .='<form id="editpage'.$this->page_id.'" action="'.$this->cresurv->escapeLocalURL().'" method="POST">'
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
     * @deprecated
     */
    function getButtonsNoScript()
    {
        return '<tr><td><form id="page'.$this->page_id.'" action="'.$this->viewsurv->escapeLocalURL().'" method="POST">'
                .'<input type="hidden" name="id" value="'.$this->page_id.'">'
                .'<input type="submit" name="wpSubmit" value="'.wfMsg('control-survey').'" />'
                .'<input type="hidden" name="returnto" value="'.htmlspecialchars($this->wikititle).'" />'
                .'</form>'
                .'<td><form id="editpage'.$this->page_id.'" action="'.$this->cresurv->escapeLocalURL().'" method="POST">'
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
     * @deprecated
     */
    function getHTML_ajax()
    {
        die('deprecated');
        assert($this->page_author);
        //control button for those that don't have javascript
        $output = '<noscript>'.$this->getButtonsNoScript().'</noscript>';
        $divname = "btnsSurvey{$this->page_id}-".rand();
        $output.= ""
                ."<script type='text/javascript'>"
                ."document.write('<div id=$divname></div>');"
                ."if(wgUserName=='{$this->page_author}')"
                ."sajax_do_call('SurveyButtons::ajaxButtons',[{$this->page_id},wgPageName,'{$this->page_status}','{$this->cresurv->getFullText()}'],function(o){"
                ."document.getElementById('$divname').innerHTML=o.responseText;});</script>";
        return $output;
    }

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
