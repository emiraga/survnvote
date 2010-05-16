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
	private $script;
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
			'code' => '<script>isquiz=false; writeHTML()</script>');
		
		$this->script = <<<END_SCRIPT
<script type="text/javascript">
	function htmlspecialchars(str) {
		if (typeof(str) == "string") {
			str = str.replace(/&/g, "&amp;"); /* must do &amp; first */
			str = str.replace(/"/g, "&quot;");
			str = str.replace(/'/g, "&#039;");
			str = str.replace(/</g, "&lt;");
			str = str.replace(/>/g, "&gt;");
		}
		return str;
	}
	function rhtmlspecialchars(str) {
		if (typeof(str) == "string") {
			str = str.replace(/&gt;/ig, ">");
			str = str.replace(/&lt;/ig, "<");
			str = str.replace(/&#039;/g, "'");
			str = str.replace(/&quot;/ig, '"');
			str = str.replace(/&amp;/ig, '&'); /* must do &amp; last */
		}
		return str;
	}
	var numQuestions = 0; //number of questions added (including deleted ones)
	var numChoices = new Array();
	function addChoice(buttonElement, num)
	{
		numChoices[num]++;
		var id = 'q'+num+"c"+numChoices[num];
		var choice =  $(buttonElement).parent().find("#choice");
		if(choice.val().length < 1)
			return false;
		$('#q'+num+'choices').append(
			'<div class="choiceItem" style="display:none" id="'+id+'div">'
			+(isquiz?'<input type="radio" name="q'+num+'correct" id="'+id+'" value="'+escape(choice.val())+'">':'&bull; ')
			+'<label for="'+id+'" id="label'+id+'">'+htmlspecialchars(choice.val())+'</label>'
			+'<input type=hidden name="q'+num+'choices[]" value="'+escape(choice.val())+'" />'
			+'<div class="rightButton2">'
			+'<input type="image" title="Move up choice" src="$gvScript/icons/arrow_up.png" onClick="return moveChoiceUp(this);" />'
			+'<img src="$gvScript/icons/spacer.gif" width="10px" />'
			+'<input type="image" title="Move down choice" src="$gvScript/icons/arrow_down.png" onClick="return moveChoiceDown(this);" />'
			+'<img src="$gvScript/icons/spacer.gif" width="10px" />'
			+'<input type="image" title="Delete choice" src="$gvScript/icons/comment_delete.png" onClick="return deleteChoice(this);" value="Delete" />'
			+'</div>'
			+'</div>'
		);
		sajax_do_call('SurveyView::getChoice', [choice.val()], function(o) { $("#label"+id).html(o.responseText); });
		
		choice.val('');
		$('#'+id+'div').show(0);
		return false;
	}
	function generateChoices(num)
	{
		return ''
		+ '<div id="q'+num+'choices" class="choicesBox"></div>'
		+ '<div><input type=text id="choice" size="50" onkeypress="if((event.keyCode||event.which)==13) return addChoice(this, '+num+');" />'
		+ '<input type=button onClick="return addChoice(this, '+num+');" value="Add choice"></div>';
	}
	function generateQuestion(question, num)
	{
		if(question.length < 3)
			return '';
		return '<div class="questionBox" id="question'+num+'" style="display:none">'
		+ '<fieldset id="questions">'
		+ '<legend id="questions">Question: '+htmlspecialchars(question)+'</legend>'
		+ '<input class="rightButton" type="image" title="Delete question" src="$gvScript/icons/file_delete.png" onClick="return deleteQuestion(this);" value="Delete">'
		+ '<img class="rightButton" src="$gvScript/icons/spacer.gif" width="10px" />'
		+ '<input class="rightButton" type="image" title="Move down question" src="$gvScript/icons/arrow_down.png"  onClick="return moveQuestionDown(this);" value="Down">'
		+ '<img class="rightButton" src="$gvScript/icons/spacer.gif" width="10px" />'
		+ '<input class="rightButton" type="image" title="Move up question" src="$gvScript/icons/arrow_up.png" onClick="return moveQuestionUp(this);" value="Up">'
		+ 'Choices:'
		+ '<input id="orderNum" type="hidden" name="orderNum[]" value="'+num+'">'
		+ '<input type="hidden" name="q'+num+'name" value="'+escape(question)+'">'
		+ generateChoices(num)
		+ '</fieldset></div>';
	}
	function deleteQuestion(buttonElement)
	{
		if(!confirm("Do you want to delete this question?"))
			return false;
		var par = $(buttonElement).parents(".questionBox");
		par.hide(0, function() { $(this).remove() } );
		// par.find("#orderNum").remove();
		return false;
	}
	function moveQuestionUp(buttonEl)
	{
		var question = $(buttonEl).parents(".questionBox");
		// question.slideUp(0, function() { $(this).after( $(this).prev() ); $(this).slideDown(0); }  ) 
		question.after( question.prev() );
		return false;
	}
	function moveQuestionDown(buttonEl)
	{
		var question = $(buttonEl).parents(".questionBox")
		// question.slideUp(0, function() { $(this).before( $(this).next() ); $(this).slideDown(0); }  ) 
		question.before( question.next() );
		return false;
	}

	function moveChoiceUp(buttonEl)
	{
		var choice = $(buttonEl).parents(".choiceItem");
		// choice.slideUp(0, function() { $(this).after( $(this).prev() ); $(this).slideDown(0); }  )
		choice.after( choice.prev() );
		return false;
	}
	function moveChoiceDown(buttonEl)
	{
		var choice = $(buttonEl).parents(".choiceItem");
		// choice.slideUp(0, function() { $(this).before( $(this).next() ); $(this).slideDown(0); }  ) 
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
		document.write('<div id="questions"></div><div>'
		+'<input type="text" name="newQuestion" id="newQuestion" size="50" onkeypress="if((event.keyCode||event.which)==13) return addQuestion();" />'
		+'<input type="button" id="btnAddQuestion" value="Add question" onClick="return addQuestion();" />'
		+'</div>');
	}
</script>
END_SCRIPT;

		$a = '<script>document.write("<b><a href=\'\' onClick=\\" previewdiv=$(\'#previewChoices\'); previewdiv.html(\'Loading...\'); sajax_do_call( \'SurveyView::getChoices\', [document.getElementById(\'choices\').value], function(o) { previewdiv.html(o.responseText); previewdiv.show(); });return false;\\"><img src=\\"'.$gvScript.'/icons/magnify.png\\" /> Preview choices</a></b><div id=previewChoices class=pBody style=\\"display: none\\"></div>");</script>';
	}
	function execute($par = null)
	{
		global $wgOut;
		$wgOut->addHTML(str_replace("\n",'',$this->script));
		
		parent::execute($par);
	}
}

?>