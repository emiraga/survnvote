<?php
if (!defined('MEDIAWIKI')) die();
global $gvPath;
require_once("$gvPath/special/CreateSurvey.php" );

class spCreateQuestionnaire extends SpecialPage
{
	private $obj;
	public function __construct()
	{
		parent::__construct('CreateQuestionnaire');
		$this->obj = new CreateQuestionnaire();
		$this->includable( true ); //we can include this from other pages
	}
	function execute( $par = null )
	{
		$this->obj->execute($par);
	}
}

class CreateQuestionnaire extends CreateSurvey
{
	private $script; /** string javascript code */
	private $isQuiz; /** boolean */
	/**
	 * Constructor for CreateSurvey
	 */
	function __construct() {
		parent::__construct();
		global $gvScript;
		$this->formitems['titleorquestion']['name'] = 'Title';
		$this->formitems['titleorquestion']['explanation'] = 'This will be the title of your Questionnaire page.';
		$this->formitems['choices'] = array('type' => 'html',
			'name' => 'Questions',
			'code' => '<script>writeHTML()</script>');
		$this->isQuiz = false;
	}
	function generateScript()
	{
		global $gvScript;
		$this->question_t = '<div class="questionBox" id="question%1$s" style="display:none">'
		. '<fieldset id="questions" style="float: none; margin: 0em;">'
		. '<legend id="questions">Question: %2$s</legend>'
		. '<div style="float: right; top: -23px; position: relative;">'
			. '<input type="image" title="Move up question" src="'.$gvScript.'/icons/arrow_up.png" onClick="return moveQuestionUp(this);" value="Up">'
			. '<img src="'.$gvScript.'/icons/spacer.gif" width="10px" />'
			. '<input type="image" title="Move down question" src="'.$gvScript.'/icons/arrow_down.png"  onClick="return moveQuestionDown(this);" value="Down">'
			. '<img src="'.$gvScript.'/icons/spacer.gif" width="10px" />'
			. '<input type="image" title="Delete question" src="'.$gvScript.'/icons/file_delete.png" onClick="return deleteQuestion(this);" value="Delete">'
		. '</div>'
		. '<input id="orderNum" type="hidden" name="orderNum[]" value="%1$s">'
		. '<input type="hidden" name="q%1$sname" value="%3$s">'
		. '<div class="prefsectiontip" style="padding: 0">Choices:</div>'
		. '<div id="q%1$schoices" style="padding-right: 30px;"></div>'
		. '<div><input type=text id="choice" size="50" onkeypress="if((event.keyCode||event.which)==13) return addChoice(this, %1$s);" />'
		. '<input type=button onClick="return addChoice(this, %1$s);" value="Add choice"></div>'
		.'</fieldset></div>';
		//Arguments: num , htmlspecialchars(question), escape(question)
		
		$this->choice_t = '<div class="choiceItem" style="display:none" id="%2$sdiv">'
			.($this->isQuiz?'<input type="radio" name="q%1$scorrect" id="%2$s" value="%4$s">':'&bull; ')
			.'<label for="%2$s" id="label%2$s">%3$s</label>'
			.'<input type=hidden name="q%1$schoices[]" value="%4$s" />'
			.'<div style="float: right;">'
			.'<input type="image" title="Move up choice" src="'.$gvScript.'/icons/arrow_up.png" onClick="return moveChoiceUp(this);" />'
			.'<img src="'.$gvScript.'/icons/spacer.gif" width="10px" />'
			.'<input type="image" title="Move down choice" src="'.$gvScript.'/icons/arrow_down.png" onClick="return moveChoiceDown(this);" />'
			.'<img src="'.$gvScript.'/icons/spacer.gif" width="10px" />'
			.'<input type="image" title="Delete choice" src="'.$gvScript.'/icons/comment_delete.png" onClick="return deleteChoice(this);" value="Delete" />'
			.'</div>'
			.'</div>';
		//Arguments: num, id, htmlspecialchars(choice.val()), escape(choice.val())
		
		$this->main_t = '<div id="questions"></div>'
		.'<div><input type="text" name="newQuestion" id="newQuestion" size="50" onkeypress="if((event.keyCode||event.which)==13) return addQuestion();" />'
		.'<input type="button" id="btnAddQuestion" value="Add question" onClick="return addQuestion();" />'
		.'</div>';
		
		$this->script = <<<END_SCRIPT
<script type="text/javascript">
	var numQuestions = 0; //number of questions added (including deleted ones)
	var numChoices = new Array();
	function addChoice(buttonElement, num)
	{
		numChoices[num]++;
		var id = 'q'+num+"c"+numChoices[num];
		var choice =  $(buttonElement).parent().find("#choice");
		if(choice.val().length < 1)	return false;
		$('#q'+num+'choices').append(sprintf('{$this->choice_t}',num, id, htmlspecialchars(choice.val()), escape(choice.val())));
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
	function execute($par = null)
	{
		$this->generateScript();
		//echo htmlspecialchars($this->main_t);
		global $wgOut;
		$wgOut->addHTML(str_replace("\n",'',$this->script));
		
		parent::execute($par);
	}
}
?>