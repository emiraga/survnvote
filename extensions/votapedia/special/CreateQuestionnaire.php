<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ControlSurvey
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/special/CreateSurvey.php" );

/**
 * Special page CreateQuestionnaire
 *
 * @package MediaWikiInterface
 */
class spCreateQuestionnaire extends SpecialPage
{
    /** @var CreateQuestionnaire */ private $obj;

    /**
     * Construct this class
     */
    public function __construct()
    {
        parent::__construct('CreateQuestionnaire');
        $this->obj = new CreateQuestionnaire();
        $this->includable( false ); //we cannot include this from other pages
        $this->setGroup('CreateQuestionnaire', 'votapedia');
    }
    /**
     * Execute tag
     * 
     * @param String $par
     */
    function execute( $par = null )
    {
        global $wgOut;
        $wgOut->addWikiText("A '''Questionnaire''' is a survey with ''more than one question'' in it. If you have several questions to ask your audience, use this type of survey instead of the [[Special:CreateSurvey|simple survey]] so that you don't have to wait everyone to finish one question and then go to the next one. Like a simple survey, this type of survey also provides some options in the advanced survey creation page.");
        $this->obj->execute($par);
    }
}

/**
 * @package ControlSurvey
 */
class CreateQuestionnaire extends CreateSurvey
{
    /** @var String */  private   $script;
    /** @var Boolean */ protected $isQuiz;
    /** @var PageVO */  protected $page;

    /**
     * Constructor for CreateQuestionnaire
     */
    function __construct()
    {
        parent::__construct();
        global $vgScript;
        $this->spPageName = 'Special:CreateQuestionnaire';
        $this->tagname = vtagQUESTIONNAIRE;

        $this->isQuiz = false;
        $this->prev_questions = '';
        $this->prev_num_q = 0;
        $this->prev_num_ch = '';
    }
    /**
     * Set default values for $this->formitems
     */
    public function setFormItems()
    {
        parent::setFormItems();
        $this->formitems['titleorquestion']['name'] = 'Title';
        $this->formitems['titleorquestion']['explanation'] = 'This will be the title of your Questionnaire.';
        $this->formitems['choices']['type'] = 'html';
        $this->formitems['choices']['code'] = '<script>writeHTML()</script>';
        $this->formitems['choices']['name'] = 'Questions';

        $this->formitems['choices']['valid'] = function($v,$i,$js)
                {
                    if($js) return "";
                    return true;
                };
        $this->formitems['choices']['textafter'] = '';
        $this->formitems['choices']['textbefore'] = '';
    }
    /**
     * Generate templates which will be used by PHP and Javascript
     * 
     */
    function generateTemplates()
    {
        global $vgScript;
        $this->question_t = '<div class="questionBox" id="question%1$s">'
                . '<fieldset id="questions" style="float: none; margin: 0em;">'
                . '<legend id="lq%1$slegend">Question:</legend>'
                . '<div style="float: right; top: -23px; position: relative;">'
                . '<input type="image" title="Move up question" src="'.$vgScript.'/icons/arrow_up.png" onClick="return moveQuestionUp(this);" value="Up">'
                . '<img src="'.$vgScript.'/icons/spacer.gif" width="10px" />'
                . '<input type="image" title="Move down question" src="'.$vgScript.'/icons/arrow_down.png"  onClick="return moveQuestionDown(this);" value="Down">'
                . '<img src="'.$vgScript.'/icons/spacer.gif" width="10px" />'
                . '<input type="image" title="Delete question" src="'.$vgScript.'/icons/file_delete.png" onClick="return deleteQuestion(this);" value="Delete">'
                . '</div>'
                . '<div id="q%1$slegend">%2$s</div>'
                . '<input id="orderNum" type="hidden" name="orderNum[]" value="%1$s">'
                . '<input type="hidden" name="q%1$sname" value="%3$s">'
                .($this->isQuiz ?
                '<div>Points: <input size="2" type="text" name="q%1$spoints" value="%4$s" /></div>'
                :
                ''
                )
                . '<div class="prefsectiontip" style="padding: 0">Choices:</div>'
                . '<div id="q%1$schoices" style="padding-right: 30px;"><!--PREV_CHOICES--></div>'
                . '<div><input type=text id="choice" size="50" onkeypress="if((event.keyCode||event.which)==13) return addChoice(this, %1$s);" />'
                . '<input type=button onClick="return addChoice(this, %1$s);" value="Add choice" class="btnAddChoice"></div>'
                .'</fieldset></div>';
        //Arguments: num , htmlspecialchars(question), escape(question), escape(points)

        $this->choice_t = '<div class="choiceItem" id="%2$sdiv">'
                .($this->isQuiz?'<input type="radio" name="q%1$scorrect" id="%2$s" value="%4$s">':'&bull; ')
                .'<label for="%2$s" id="label%2$s">%3$s</label>'
                .'<input type=hidden name="q%1$schoices[]" value="%4$s" />'
                .'<div style="float: right;">'
                .'<input type="image" title="Move up choice" src="'.$vgScript.'/icons/arrow_up.png" onClick="return moveChoiceUp(this);" />'
                .'<img src="'.$vgScript.'/icons/spacer.gif" width="10px" />'
                .'<input type="image" title="Move down choice" src="'.$vgScript.'/icons/arrow_down.png" onClick="return moveChoiceDown(this);" />'
                .'<img src="'.$vgScript.'/icons/spacer.gif" width="10px" />'
                .'<input type="image" title="Delete choice" src="'.$vgScript.'/icons/comment_delete.png" onClick="return deleteChoice(this);" value="Delete" />'
                .'</div>'
                .'</div>';
        //Arguments: num, id, htmlspecialchars(choice.val()), escape(choice.val())

        $this->main_t = '<div id="questions"><!--PREV_QUESTIONS--></div>'
                .'<div><input type="text" name="newQuestion" id="newQuestion" size="50" onkeypress="if((event.keyCode||event.which)==13) return addQuestion();" />'
                .'<input type="button" id="btnAddQuestion" value="Add question" onClick="return addQuestion();" />'
                .'</div>';
    }
    /**
     * Generate Javascript code
     */
    function generateScript()
    {
        $this->script = <<<END_SCRIPT
<script type="text/javascript">
	var numQuestions = {$this->prev_num_q};
	var numChoices = new Array();
                {$this->prev_num_ch}
	function addChoice(buttonElement, num)
	{
		numChoices[num]++;
		var id = 'q'+num+"c"+numChoices[num];
		var choice =  $(buttonElement).parent().find("#choice");
		if(choice.val().length < 1)	return false;
		$('#q'+num+'choices').append(sprintf('{$this->choice_t}', num, id, htmlspecialchars(choice.val()), escape(choice.val())));
		sajax_do_call('SurveyView::getChoice', [choice.val()], function(o) { $("#label"+id).html(o.responseText); });
		choice.val('');
		$('#'+id+'div').show(0);
		return false;
	}
	function generateQuestion(question, num)
	{
		if(question.length < 1)	return '';
		return sprintf('{$this->question_t}', num, htmlspecialchars(question), escape(question), '10');
	}
	function deleteQuestion(buttonElement)
	{
		if(!confirm("Do you want to delete this question?")) return false;
		var par = $(buttonElement).parents(".questionBox");
		par.remove();
		return false;
	}
	function moveQuestionUp(buttonEl)
	{
		var question = $(buttonEl).parents(".questionBox");
		question.after( question.prev() );
		return false;
	}
	function moveQuestionDown(buttonEl)
	{
		var question = $(buttonEl).parents(".questionBox")
		question.before( question.next() );
		return false;
	}
	function moveChoiceUp(buttonEl)
	{
		var choice = $(buttonEl).parents(".choiceItem");
		choice.after( choice.prev() );
		return false;
	}
	function moveChoiceDown(buttonEl)
	{
		var choice = $(buttonEl).parents(".choiceItem");
		choice.before( choice.next() );
		return false;
	}
	function deleteChoice(buttonElement)
	{
		var par = $(buttonElement).parents(".choiceItem");
		par.hide(0, function() { $(this).remove() } );
		return false;
	}
	function addQuestion()
	{
		numQuestions++;
		numChoices[numQuestions] = 0;

		var newQuestion = $("#newQuestion"); 
		$("#questions").append( generateQuestion( newQuestion.val(), numQuestions ) );
		sajax_do_call('SurveyView::getChoice', [newQuestion.val()], function(o) { $("#q"+numQuestions+"legend").html(o.responseText); });

		newQuestion.val("");
		$("#question"+numQuestions).show(0, function() {
			$("#question"+numQuestions).find("input#choice").focus();
		});
		return false;
	}
	function writeHTML()
	{
		document.write('{$this->main_t}');
	}
</script>
END_SCRIPT;
    }
    /**
     * Generate array of SurveyVO based on the values provided.
     * 
     * @param Array $values
     * @return Array of SurveyVO
     */
    function generateSurveysArray($values)
    {
        global $wgRequest;
        $surveys = array();
        foreach($wgRequest->getIntArray('orderNum', array()) as $index)
        {
            $question = urldecode( $wgRequest->getVal("q{$index}name") );
            $choices = $wgRequest->getArray("q{$index}choices");
            $surveyVO = new SurveyVO();
            $surveyVO->generateChoices($choices, true);
            $surveyVO->setQuestion($question);
            $surveyVO->setPoints(0);
            $surveys[] = $surveyVO;
        }
        return $surveys;
    }
    /**
     * Specify values for PageVO, specific for Questionnaire.
     * 
     * @param PageVO $page
     * @param Array $values
     */
    protected function setPageVOvalues(PageVO &$page, &$values)
    {
        parent::setPageVOvalues($page, $values);
        $page->setType(vQUESTIONNAIRE);
    }
    /**
     * Check if user input is correct.
     * 
     * @return String error if any
     */
    function Validate()
    {
        $error = parent::Validate();
        if(!isset($this->page) || $this->page->getStatus( $this->page->getCurrentPresentationID() ) == 'ready')
        {
            global $wgRequest;
            $ordernum = $wgRequest->getIntArray('orderNum', array());
            foreach($ordernum as $index)
            {
                $choices = $wgRequest->getArray("q{$index}choices", array());
                if(count($choices) < 2)
                    $error .= "<li>Question must have at least two choices.</li>";
            }
            if(count($ordernum) == 0)
                $error .= "<li>There must be at least one question.</li>";
        }
        return $error;
    }
    /**
     * Generate array of SurveyVO based on the $wgRequest values
     *
     * @return Array of SurveyVO
     */
    function makeSurveysFromRequest()
    {
        global $wgRequest;
        $surveys = array();
        foreach($wgRequest->getIntArray('orderNum', array()) as $index)
        {
            $question = urldecode($wgRequest->getVal("q{$index}name"));
            $strchoices = $wgRequest->getArray("q{$index}choices", array());
            $survey = new SurveyVO();
            $survey->setQuestion($question);
            $choices = array();
            foreach($strchoices as $choice)
            {
                $choice = urldecode($choice);

                $chVO = new ChoiceVO();
                $chVO->choice = $choice;
                $choices[] = $chVO;
            }

            $survey->setChoices($choices);
            $surveys[] = $survey;
        }
        return $surveys;
    }
    /**
     * Generate Javascript code for previously added questions and choices.
     *
     * @param Array $surveys
     */
    function generatePrevQuestions(&$surveys)
    {
        $num = 1;
        $pars = new Parser();
        $parser = new MwParser($pars);
        $this->prev_num_ch = '';
        foreach($surveys as &$survey)
        {
            /* @var $survey SurveyVO */
            $question = $survey->getQuestion();
            $choices = $survey->getChoices();
            $choiceshtml = '';
            $cnum = 1;
            foreach($choices as $choice)
            {
                /* @var $choice ChoiceVO */
                $id = 'q'.$num."c".$cnum;
                $choiceshtml .= sprintf($this->choice_t, $num, $id, $parser->run(trim($choice->choice),false), urlencode($choice->choice));
                $cnum++;
            }
            $this->prev_num_ch .= 'numChoices['.$num.'] = '.$cnum.";\n";
            $questionhtml = sprintf($this->question_t, $num , $parser->run(trim($question), false),
                    urlencode($question), urlencode($survey->getPoints()) );
            $questionhtml = str_replace('<!--PREV_CHOICES-->',  $choiceshtml, $questionhtml);
            $this->prev_questions .= $questionhtml;
            $num++;
        }
        $this->prev_num_q = $num;
        $this->generateScript();
    }
    /**
     * Fill Values From Surveys
     * 
     * @param PageVO $page
     */
    function fillValuesFromSurveys(&$surveys)
    {
        $this->generatePrevQuestions($surveys);
    }
    /**
     * Process New Survey Submit
     */
    function processNewSurveySubmit()
    {
        $this->generatePrevQuestions($this->makeSurveysFromRequest());

        parent::processNewSurveySubmit();
    }
    /**
     * Process New Survey
     */
    function processNewSurvey()
    {
        parent::processNewSurvey(); //there are not previous questions

        $this->generateScript();
    }
    /**
     * Process Edit Survey
     */
    public function processEditSurvey()
    {
        parent::processEditSurvey(); //this method will call generatePrevQuestions
    }
    /**
     * Process Edit Survey Submit
     */
    public function processEditSurveySubmit()
    {
        $this->generatePrevQuestions($this->makeSurveysFromRequest());

        parent::processEditSurveySubmit();
    }
    /**
     *
     * @param String $par
     */
    function execute($par = null)
    {
        $this->initialize();
        
        global $wgOut;
        $this->form->setOnFormSubmit('$(".btnAddChoice").click(); return true;');
        $this->generateTemplates();
        parent::execute($par);

        $script = str_replace("<!--PREV_QUESTIONS-->", addslashes( $this->prev_questions ), $this->script);
        $script = str_replace("\n",'',$script);
        $wgOut->prependHTML($script);
    }
    /**
     * New form
     */
    protected function drawFormNew()
    {
        parent::drawFormNew();
        global $wgOut;
        $wgOut->setPageTitle(wfMsg('title-new-questionnaire'));
        $this->formButton = wfMsg('create-questionnaire');
    }
    /**
     * Edit form
     * 
     * @param Integer $page_id
     */
    protected function drawFormEdit( $page_id)
    {
        parent:: drawFormEdit( $page_id );
        global $wgOut;
        $wgOut->setPageTitle(wfMsg('title-edit-questionnaire'));
        $this->formButton = wfMsg('edit-questionnaire');
    }
}

