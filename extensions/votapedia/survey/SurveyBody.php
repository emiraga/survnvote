<?php
if (!defined('MEDIAWIKI')) die();

/**
 * SurveyBody shows the main part of the survey
 *
 * @author Emir Habul
 */
class SurveyBody
{
    /** @var PageVO */   protected $page;
    /** @var MwParser */ protected $parser;
    /** @var Integer */  protected $type;
    function  __construct(PageVO &$page, MwParser &$parser)
    {
        $this->page =& $page;
        $this->parser =& $parser;
        $this->type = vSIMPLE_SURVEY;
    }
    /**
     *
     * @global $wgOut OutputPage
     * @return String html code
     */
    function getHTML()
    {
        global $wgOut;
        $output = '';
        $surveys =& $this->page->getSurveys();
        foreach ($surveys as &$survey)
        {
            /* @var $survey SurveyVO */
            $choices =& $survey->getChoices();

            if($this->page->getStatus()=='ready')
            {
                $output.='<tr><td colspan="2">';

                if($this->type != vSIMPLE_SURVEY)
                {
                    $output .= '<h5>'. wfMsg( 'survey-question', $survey->getQuestion() ) .'</h5>';
                }
                $output.='<ul>';
                $i=0;
                foreach ($choices as &$choice)
                {
                    /* @var $survey ChoiceVO */
                    $i++;

                    $choice = $this->parser->run($choice->getChoice());
                    if($choice)
                    {
                        $output.="<li STYLE=\"list-style-image: url(".vfGetColorImage().
                                ")\"> <label id=\"q$i\">$choice</label></li>";
                    }
                }
                $output.='</ul>';
            }
            elseif($this->page->getStatus() == 'active')
            {
                ;
            }
            elseif($this->page->getStatus() == 'ended')
            {
                ;
            }
        }
        return $output;
    }
}

class QuestionnaireBody extends SurveyBody
{
    /**
     *
     * @param  $page PageVO
     * @param  $parser MwParser
     */
    function  __construct(PageVO &$page, MwParser &$parser)
    {
        parent::__construct($page, $parser);
        $this->type = vQUESTIONNAIRE;
    }
}
?>