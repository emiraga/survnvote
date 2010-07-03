<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ControlSurvey
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/special/CreateQuestionnaire.php" );

/**
 * @package MediaWikiInterface
 */
class spCreateQuiz extends SpecialPage
{
    /** @var CreateQuestionnaire */ private $obj;
    /**
     * Construct spCreateQuiz
     */
    public function __construct()
    {
        parent::__construct('CreateQuiz');
        $this->obj = new CreateQuiz();
        $this->includable( false ); //we cannot include this from other pages
        $this->setGroup('CreateQuiz', 'votapedia');
    }
    /**
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        global $wgOut;
        $wgOut->addWikiText("A '''Quiz''' is a survey designed for ''student assessment''. The questions and choices have the same syntax as the [[Special:CreateQuestionnaire| Questionnaires]] which means you can have more than one questions in it. The only difference is that you can allocate certain points to each question and define a correct answer so that participants get the points if they choose the correct answer. After the quiz finishes, you can check the answer of all students, and students can check their score by visiting the quiz page.");
        $this->obj->execute($par);
    }
}

/**
 * @package ControlSurvey
 */
class CreateQuiz extends CreateQuestionnaire
{
    /**
     * Constructor for CreateSurvey
     */
    function __construct()
    {
        parent::__construct();
        $this->spPageName = 'Special:CreateQuiz';
        $this->isQuiz = true;
        $this->tagname = vtagQUIZ;
    }
    /**
     * Set default values for $this->formitems
     */
    public function setFormItems()
    {
        parent::setFormItems();
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
    }
    /**
     * Specify values for PageVO, specific for Quiz.
     *
     * @param PageVO $page
     * @param Array $val
     * @return String error if any
     */
    protected function setPageVOvalues(PageVO &$page, &$values)
    {
        $error = parent::setPageVOvalues($page, $values);
        $page->setType(vQUIZ);
        $page->setSubtractWrong( isset($values['subtractwrong']) && (bool)$values['subtractwrong'] );
        return $error;
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
    /**
     * Generate array of SurveyVO based on the values provided.
     *
     * @param Array $values
     * @return Array of SurveyVO
     */
    function generateSurveysArray($values)
    {
        $surveys =& parent::generateSurveysArray($values);
        $i = 0;
        global $wgRequest;
        foreach($wgRequest->getIntArray('orderNum', array()) as $index)
        {
            if($wgRequest->getCheck("q{$index}points"))
            {
                $surveys[$i]->setPoints($wgRequest->getInt("q{$index}points"));
            }
            if($wgRequest->getCheck("q{$index}correct"))
            {
                $surveys[$i]->setAnswerByChoice( urldecode(  $wgRequest->getVal("q{$index}correct",'') ) );
            }
            $i++;
        }
        return $surveys;
    }
    /**
     * Generate Javascript code for previously added questions and choices.
     *
     * @param Array $surveys
     */
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
    /**
     * Fill Values From Surveys
     *
     * @param PageVO $page
     */
    public function fillFormValuesFromPage(PageVO &$page)
    {
        parent::fillFormValuesFromPage($page);
        $this->form->setValue('subtractwrong', $page->getSubtractWrong());
    }
    /**
     *
     * @param String $par
     */
    function execute($par = null)
    {
        $this->initialize();
        
        parent::execute($par);
    }
    /**
     * New form
     */
    protected function drawFormNew()
    {
        parent::drawFormNew();
        global $wgOut;
        $wgOut->setPageTitle(wfMsg('title-new-quiz'));
        $this->formButton = wfMsg('create-quiz');
    }
    /**
     * Edit form
     * 
     * @param Integer $page_id
     */
    protected function drawFormEdit( $page_id )
    {
        parent:: drawFormEdit( $page_id );
        global $wgOut;
        $wgOut->setPageTitle(wfMsg('title-edit-quiz'));
        $this->formButton = wfMsg('edit-quiz');
    }
}

