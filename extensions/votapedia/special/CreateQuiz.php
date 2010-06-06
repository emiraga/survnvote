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
        $this->isQuiz = true;
        echo $this->isQuiz.'<br>';
    }
    /*function generateSurveysArray($values)
    {
        parent::generateSurveysArray($values);
    }*/
    protected function setPageVOvalues(PageVO &$page, &$values)
    {
        parent::setPageVOvalues($page, $values);
        $page->setType(vQUIZ);
    }
    function Validate()
    {
        $error = parent::Validate();
        return $error;
    }
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
    }
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

