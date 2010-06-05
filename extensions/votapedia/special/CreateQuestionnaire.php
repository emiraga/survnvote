<?php
if (!defined('MEDIAWIKI')) die();

global $vgPath;
require_once("$vgPath/special/CreateSurvey.php" );

class spCreateQuestionnaire extends SpecialPage
{
    /** @var CreateQuestionnaire */ private $obj;
    public function __construct()
    {
        parent::__construct('CreateQuestionnaire');
        $this->obj = new CreateQuestionnaire();
        $this->includable( true ); //we can include this from other pages
        $this->setGroup('CreateQuestionnaire', 'votapedia');
    }
    function execute( $par = null )
    {
        $this->obj->execute($par);
    }
}

class CreateQuestionnaire extends CreateSurvey
{
    /** @var String */ private $script;
    /** @var Boolean */ private $isQuiz;
    /** @var PageVO */ var $page;

    /**
     * Constructor for CreateSurvey
     */
    function __construct()
    {
        parent::__construct();
        global $vgScript;
        $this->formitems['titleorquestion']['name'] = 'Title';
        $this->formitems['titleorquestion']['explanation'] = 'This will be the title of your Questionnaire page.';
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
        $this->spPageName = 'Special:CreateQuestionnaire';
        $this->tagname = vtagQUESTIONNAIRE;

        $this->isQuiz = false;
        $this->prev_questions = '';
        $this->prev_num_q = 0;
        $this->prev_num_ch = '';
    }
    function generateTemplates()
    {
        global $vgScript;
        $this->question_t = '<div class="questionBox" id="question%1$s">'
                . '<fieldset id="questions" style="float: none; margin: 0em;">'
                . '<legend id="q%1$slegend">Question: %2$s</legend>'
                . '<div style="float: right; top: -23px; position: relative;">'
                . '<input type="image" title="Move up question" src="'.$vgScript.'/icons/arrow_up.png" onClick="return moveQuestionUp(this);" value="Up">'
                . '<img src="'.$vgScript.'/icons/spacer.gif" width="10px" />'
                . '<input type="image" title="Move down question" src="'.$vgScript.'/icons/arrow_down.png"  onClick="return moveQuestionDown(this);" value="Down">'
                . '<img src="'.$vgScript.'/icons/spacer.gif" width="10px" />'
                . '<input type="image" title="Delete question" src="'.$vgScript.'/icons/file_delete.png" onClick="return deleteQuestion(this);" value="Delete">'
                . '</div>'
                . '<input id="orderNum" type="hidden" name="orderNum[]" value="%1$s">'
                . '<input type="hidden" name="q%1$sname" value="%3$s">'
                . '<div class="prefsectiontip" style="padding: 0">Choices:</div>'
                . '<div id="q%1$schoices" style="padding-right: 30px;"><!--PREV_CHOICES--></div>'
                . '<div><input type=text id="choice" size="50" onkeypress="if((event.keyCode||event.which)==13) return addChoice(this, %1$s);" />'
                . '<input type=button onClick="return addChoice(this, %1$s);" value="Add choice" class="btnAddChoice"></div>'
                .'</fieldset></div>';
        //Arguments: num , htmlspecialchars(question), escape(question)

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
		return sprintf('{$this->question_t}', num, htmlspecialchars(question), escape(question));
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
		sajax_do_call('SurveyView::getChoice', [newQuestion.val()], function(o) { $("#q"+numQuestions+"legend").html('Question: '+o.responseText); });

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
            $surveyVO->setType(vQUESTIONNAIRE);
            $surveyVO->setVotesAllowed(1);
            $surveyVO->setPoints(0);
            $surveys[] = $surveyVO;
        }
        return $surveys;
    }
    protected function setPageVOvalues(PageVO &$page, &$values)
    {
        parent::setPageVOvalues($page, $values);
        $page->setType(vQUESTIONNAIRE);
    }
    /**
     *
     * @global $wgRequest WebRequest
     * @return String error if any
     */
    function Validate()
    {
        $error = parent::Validate();

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
        return $error;
    }
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
            $survey->setType(vQUESTIONNAIRE);

            $choices = array();
            foreach($strchoices as $choice)
            {
                $choice = urldecode($choice);

                $chVO = new ChoiceVO();
                $chVO->setChoice($choice);
                $choices[] = $chVO;
            }

            $survey->setChoices($choices);
            $surveys[] = $survey;
        }
        return $surveys;
    }
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
                $choiceshtml .= sprintf($this->choice_t, $num, $id, $parser->run(trim($choice->getChoice()),false), urlencode($choice->getChoice()));
                $cnum++;
            }
            $this->prev_num_ch .= 'numChoices['.$num.'] = '.$cnum.";\n";
            $questionhtml = sprintf($this->question_t, $num , $parser->run(trim($question), false), urlencode($question) );
            $questionhtml = str_replace('<!--PREV_CHOICES-->',  $choiceshtml, $questionhtml);
            $this->prev_questions .= $questionhtml;
            $num++;
        }
        $this->prev_num_q = $num;
        $this->generateScript();
    }
    /**
     *
     * @param  $page PageVO
     */
    function fillValuesFromSurveys(&$surveys)
    {
        $this->generatePrevQuestions($surveys);
    }
    function processNewSurveySubmit()
    {
        $this->generatePrevQuestions($this->makeSurveysFromRequest());

        parent::processNewSurveySubmit();
    }
    function processNewSurvey()
    {
        parent::processNewSurvey(); //there are not previous questions

        $this->generateScript();
    }
    public function processEditSurvey()
    {
        parent::processEditSurvey(); //this method will call generatePrevQuestions
    }
    public function processEditSurveySubmit()
    {
        $this->generatePrevQuestions($this->makeSurveysFromRequest());

        parent::processEditSurveySubmit();
    }
    function execute($par = null)
    {
        global $wgOut;
        $this->form->setOnFormSubmit('$(".btnAddChoice").click(); return true;');
        $this->generateTemplates();
        parent::execute($par);

        $script = str_replace("<!--PREV_QUESTIONS-->", addslashes( $this->prev_questions ), $this->script);
        $script = str_replace("\n",'',$script);
        $wgOut->prependHTML($script);
    }
    protected function drawFormNew( $errors=null )
    {
        parent::drawFormNew($errors);
        global $wgOut;
        $wgOut->setPageTitle(wfMsg('title-new-questionnaire'));
    }
    protected function drawFormEdit( $page_id, $errors=null )
    {
        parent:: drawFormEdit( $page_id, $errors );
        global $wgOut;
        $wgOut->setPageTitle(wfMsg('title-edit-questionnaire'));
    }
}
?>