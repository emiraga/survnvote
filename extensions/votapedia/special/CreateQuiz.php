<?php
if (!defined('MEDIAWIKI')) die();

global $vgPath;
require_once("$vgPath/special/CreateQuestionnaire.php" );

class spCreateQuiz extends SpecialPage
{
    /** @var CreateQuestionnaire */ private $obj;
    public function __construct()
    {
        parent::__construct('CreateQuiz');
        $this->obj = new CreateQuiz();
        $this->includable( true ); //we can include this from other pages
        $this->setGroup('CreateQuiz', 'votapedia');
    }
    function execute( $par = null )
    {
        $this->obj->execute($par);
    }
}

class CreateQuiz extends CreateQuestionnaire
{
    /**
     * Constructor for CreateSurvey
     */
    function __construct()
    {
        parent::__construct();
        $this->spPageName = 'Special:CreateQuiz';
        $this->formitems['titleorquestion']['explanation'] = 'This will be the title of your Quiz.';
        $this->formitems['showresultsend']['default'] = true;

        $this->formitems['subtractwrong'] = array(
                        'type' => 'checkbox',
                        'name' => 'Marking',
                        'default' => 'on',
                        'checklabel' => 'Subtract wrong answers.',
                        'valid' => function($v,$i,$js)
                        {
                            if($js) return "";
                            return true;
                        },
                        'explanation' => ' If checked, each wrong answer will get minus point. The subtracted point is calculated based on the point of that question divided by the number of choices.',
                        'learn_more' => 'Details of Quiz Marking',
                );
        $this->formpages[0]['items'][] = 'subtractwrong';
        $this->isQuiz = true;
        $this->tagname = vtagQUIZ;
    }
    function generateSurveysArray($values)
    {
        $surveys =& parent::generateSurveysArray($values);
        $i = 0;
        global $wgRequest;
        foreach($wgRequest->getIntArray('orderNum', array()) as $index)
        {
            $surveys[$i]->setPoints($wgRequest->getVal("q{$index}points"));
            $surveys[$i]->setAnswerByChoice( $wgRequest->getVal("q{$index}correct",'') );
            $surveys[$i]->setType( vQUIZ );
            $i++;
        }
        return $surveys;
    }
    protected function setPageVOvalues(PageVO &$page, &$values)
    {
        parent::setPageVOvalues($page, $values);
        $page->setType(vQUIZ);
        if(isset($values['subtractwrong']) && $values['subtractwrong'])
            $page->setSubtractWrong( true );
        else
            $page->setSubtractWrong( false );
    }
    function Validate()
    {
        $error = parent::Validate();
        if(!isset($this->page) || $this->page->getStatus() == 'ready')
        {
            global $wgRequest;
            $ordernum = $wgRequest->getIntArray('orderNum', array());
            foreach($ordernum as $index)
            {
                if(! $wgRequest->getCheck("q{$index}correct"))
                {
                    $error .= "<li>You must provide correct answer to question.</li>";
                }
            }
            $points = intval($wgRequest->getVal("q{$index}points"));
            if($points <= 0)
            {
                $error .= "<li>Number ov points for question must be positive.</li>";
            }
        }
        return $error;
    }
    function makeSurveysFromRequest()
    {
        $surveys =& parent::makeSurveysFromRequest();
        $i=0;
        global $wgRequest;
        foreach($wgRequest->getIntArray('orderNum', array()) as $index)
        {
            if($wgRequest->getCheck("q{$index}correct"))
            {
                $surveys[$i]->setAnswerByChoice($wgRequest->getVal("q{$index}correct"));
                $surveys[$i]->setType(vQUIZ);
                $surveys[$i]->setPoints($wgRequest->getVal("q{$index}points"));
            }
            $i++;
        }
        return $surveys;
    }
    public function generatePrevQuestions(&$surveys)
    {
        parent::generatePrevQuestions($surveys);
        $num = 1;
        foreach($surveys as &$survey)
        {
            /* @var $survey SurveyVO */
            $search = "<input type=\"radio\" name=\"q{$num}correct\" id=\"q{$num}c{$survey->getAnswer()}\" ";
            $this->prev_questions = str_replace($search, $search.'checked ', $this->prev_questions);
            $num++;
        }
    }
    public function fillFormValuesFromPage(PageVO &$page)
    {
        parent::fillFormValuesFromPage($page);
        $this->form->setValue('subtractwrong', $page->getSubtractWrong());
    }
/*
    function processNewSurveySubmit()
    {
        parent::processNewSurveySubmit();
    }
    function processNewSurvey()
    {
        parent::processNewSurvey(); //there are not previous questions
    }
    public function processEditSurvey()
    {
        parent::processEditSurvey(); //this method will call generatePrevQuestions
    }
    public function processEditSurveySubmit()
    {
        parent::processEditSurveySubmit();
    }*/
    function execute($par = null)
    {
        parent::execute($par);
    }
    protected function drawFormNew()
    {
        parent::drawFormNew();
        global $wgOut;
        $wgOut->setPageTitle(wfMsg('title-new-quiz'));
        $this->formButton = wfMsg('create-quiz');
    }
    protected function drawFormEdit( $page_id )
    {
        parent:: drawFormEdit( $page_id );
        global $wgOut;
        $wgOut->setPageTitle(wfMsg('title-edit-quiz'));
        $this->formButton = wfMsg('edit-quiz');
    }
}

