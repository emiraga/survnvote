<?php

abstract class DataSource
{
    abstract function numRows();
    abstract function numCols();
    abstract function getTitle();
    abstract function getText($row, $col);
    function htmlEquiv($row, $col) { return false; }
    function headerCell($row, $col)  { return false; }
    function sortableCell($row, $col)  { return false; }
    function markRow($row)  { return false; }
    function bgColor($row, $col) { return false; }
}

class SurveyVotesData extends DataSource
{
    /** @var SurveyVO */protected $survey;
    /** @var VotesCount */protected $votescount;
    /** @var MwParser*/ protected $parser;
    protected $votes = array();
    protected $color = array();
    protected $numvotes;
    protected $highest;

    public function __construct(SurveyVO $survey, VotesCount &$votescount, MwParser &$parser, &$colorindex)
    {
        $this->survey =& $survey;
        $this->votescount =& $votescount;
        $this->parser =& $parser;
        
        $choices =& $this->survey->getChoices();
        $numvotes = 0;
        $highest = -1;
        $chnum = 1;
        foreach ($choices as &$choice)
        {
            /* @var $choice ChoiceVO */
            $this->votes[ $chnum ] = $this->votescount->get($survey->getSurveyID(), $choice->choiceID);
            $numvotes += $this->votes[ $chnum ];
            $highest = max($highest, $this->votes[ $chnum ]);

            $this->color[ $chnum ] = vfGetColor($colorindex);
            $chnum++;
        }

        if($numvotes == 0)
            $numvotes = 1;
        
        $this->numvotes = $numvotes;
        $this->highest = $highest;
    }
    public function numCols()
    {
        return 5;
    }
    public function numRows()
    {
        return $this->survey->getNumOfChoices() + 1;
    }
    public function getTitle()
    {
        return $this->survey->getQuestion();
    }
    public function getText($row, $col)
    {
        if($row == 1)
        {
            switch ($col)
            {
                case 1: return '#';
                case 2: return 'Choice';
                case 3: return 'Votes';
                case 4: return '%';
                case 5: return '';
            }
        }
        $row--;
        switch ($col)
        {
            case 1: return $row;
            case 2: return $this->survey->getChoiceByNum($row-1)->choice;
            case 3: return $this->votes[ $row ];
            case 4: return sprintf("%.2f", 100.0 * $this->votes[ $row ] / $this->numvotes, 0, 5);
            case 5: 
                if($this->survey->getAnswer() == $this->survey->getChoiceByNum($row-1)->choiceID)
                {
                    return 'correct';
                }
                return '';
        }
    }
    public function headerCell($row, $col)
    {
        return $row == 1;
    }
    public function markRow($row)
    {
        if($row == 1) return false;
        return $this->highest == $this->votes[ $row - 1 ];
    }
    public function htmlEquiv($row, $col)
    {
        if($col == 1 && $row > 1)
        {
            $row--;
            $colorpatch = "<div style=\"width: 25px; background-color: #{$this->color[$row]}\">$row</div>";
            return $colorpatch;
        }
        if($col == 5 && $row > 1)
        {
            $row--;
            if($this->survey->getAnswer() == $this->survey->getChoiceByNum($row-1)->choiceID)
            {
                global $vgScript;
                return "<td><img src='$vgScript/icons/correct.png' />";
            }
            return '';
        }
        return false;
    }
    public function sortableCell($row, $col)
    {
        return $row == 1 && ($col == 1 || $col == 3);
    }
}

class CrossTabData extends DataSource
{
    static function generate(PageVO $page, $presID)
    {
        //Get votes from users
        global $vgDB, $vgDBPrefix;
        $votes =& $vgDB->GetAll("SELECT userID, surveyID, choiceID FROM {$vgDBPrefix}vote WHERE pageID=? AND presentationID=?",
                array( $page->getPageID(), $presID ));
        //Record votes
        $userv = array();
        foreach($votes as &$vote)
        {
            $userid = $vote['userID'];
            if(!isset($userv[ $userid ]))
                $userv[$userid] = array();
            $userv[$userid][] = array($vote['surveyID'],$vote['choiceID']);
        }
        $surveys = &$page->getSurveys();
        $numsur = count($surveys);
        $sources = array();
        $mapsurveys = array();
        
        for($i=0;$i<$numsur;$i++)
        {
            $mapsurveys[ $surveys[$i]->getSurveyID() ] = $i;
            $sources[$i] = array();
            for($j=$i+1;$j<$numsur;$j++)
            {
                $sources[$i][$j] = new CrossTabData($surveys[$i], $surveys[$j]);
            }
        }
        
        //Update correlation table
        foreach($userv as $user => &$votes)
        {
            $nv = count($votes);
            for($i=0;$i<$nv;$i++)
            {
                for($j=$i+1;$j<$nv;$j++)
                {
                    list($sur1, $choice1) = $votes[$i];
                    list($sur2, $choice2) = $votes[$j];
                    $sources[ $mapsurveys[$sur1] ][ $mapsurveys[$sur2] ]->increaseValue($choice1, $choice2);
                }
            }
        }
        $result = array();
        for($i=0;$i<$numsur;$i++)
        {
            for($j=$i+1;$j<$numsur;$j++)
            {
                $result[] =& $sources[$i][$j];
            }
        }
        return $result;
    }

    protected $rows;
    protected $cols;
    protected $data;
    protected $title;
    protected $xaxis=array();
    protected $yaxis=array();

    private function __construct(SurveyVO &$survey1, SurveyVO &$survey2)
    {
        $this->rows = $survey1->getNumOfChoices();
        $this->cols = $survey2->getNumOfChoices();
        $this->data = array_fill(0, $this->rows+1, array_fill(0, $this->cols+1, 0));
        $this->title = '"'.$survey1->getQuestion().'" and "'.$survey2->getQuestion().'"';
        $ch1 =& $survey1->getChoices();
        foreach ($ch1 as &$choice)
            $this->xaxis[] = $choice->choice;
        $this->xaxis[] = 'Total';
        $ch2 =& $survey2->getChoices();
        foreach ($ch2 as &$choice)
            $this->yaxis[] = $choice->choice;
        $this->yaxis[] = 'Total';
    }
    public function numCols()
    {
        return $this->cols+2;
    }
    public function numRows()
    {
        return $this->rows+2;
    }
    public function getText($row, $col)
    {
        if($row + $col < 3)return '';
        if($row == 1)
        {
            return $this->yaxis[$col-2];
        }
        if($col == 1)
        {
            return $this->xaxis[$row-2];
        }
        return $this->data[$row-2][$col-2];
    }
    private function increaseValue($i, $j)
    {
        $this->data[$i-1][$j-1]++;
        $this->data[$this->rows][$j-1       ]++;
        $this->data[$i-1       ][$this->cols]++;
        $this->data[$this->rows][$this->cols]++;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function headerCell($row, $col)
    {
        return $row == 1 || $col == 1;
    }
}

class SurveyCorrelateData extends DataSource
{
    /* @var PageVO */ protected $page;
    /* @var Integer */ protected $numchoices;
    /* @var Array */ protected $corr;
    /* @var Integer */ protected $maxv;
    /* @var Array */ protected $colors;
    /* @var Array */ protected $choicenames;
    
    public function __construct(PageVO &$page, $presID)
    {
        $this->page =& $page;
        
        //initialize, maping for the choices and conpute number of choices
        $numchoices = 0;
        $mapchoices = array();
        $colorindex = 1;
        $surveys =& $this->page->getSurveys();
        foreach ($surveys as &$survey)
        {
            /* @var $survey SurveyVO */
            $choices =& $survey->getChoices();
            foreach ($choices as &$choice)
            {
                /* @var $choice ChoiceVO */
                $mapchoices[ $survey->getSurveyID().'_'.$choice->choiceID ] = $numchoices;
                $this->choicenames[ $numchoices ] =
                   '<abbr title="'.
                    addslashes($survey->getQuestion())."   Answer:"
                    .addslashes( $choice->choice )
                      .'">&nbsp;'
                    .''./*$cntsur.':'.*/$choice->choiceID
                  .'&nbsp;</abbr>';
                $this->colors[$numchoices] = vfGetColor($colorindex);
                $numchoices++;
            }
        }
        //fill with zeroes correlation matrix [NxN]  N=number of choices
        $this->corr = array_fill(0, $numchoices, array_fill(0, $numchoices, 0));

        //Get votes from users
        global $vgDB, $vgDBPrefix;
        $votes =& $vgDB->GetAll("SELECT userID, surveyID, choiceID FROM {$vgDBPrefix}vote WHERE pageID=? AND presentationID=?",
                array( $this->page->getPageID(), $presID ));
        //Record votes
        $userv = array();
        foreach($votes as &$vote)
        {
            $userid = $vote['userID'];
            if(!isset($userv[ $userid ]))
                $userv[$userid] = array();
            $userv[$userid][] = $mapchoices[ $vote['surveyID'].'_'.$vote['choiceID'] ];
        }
        //Update correlation table
        foreach($userv as $user => &$votes)
        {
            $nv = count($votes);
            for($i=0;$i<$nv;$i++)
            {
                for($j=$i+1;$j<$nv;$j++)
                {
                    $this->corr[ $votes[$i] ][ $votes[$j] ]++;
                    $this->corr[ $votes[$j] ][ $votes[$i] ]++;
                }
            }
        }

        //Compute maximum element
        $maxv = 2;
        for($i=0;$i<$numchoices;$i++)
        {
            for($j=$i+1;$j<$numchoices;$j++)
            {
                $maxv = max($maxv, $this->corr[$i][$j]);
            }
        }
        if($maxv == 0)
            $maxv = 1;

        $this->numchoices = $numchoices;
        $this->maxv = $maxv;
    }
    public function getTitle()
    {
        return 'Questions correlation';
    }
    public function numCols()
    {
        return 1 + $this->numchoices;
    }
    public function numRows()
    {
        return 1 + $this->numchoices;
    }
    public function getText($row, $col)
    {
        if( $row == 1 )
        {
            return $col - 1;
        }
        if($col == 1)
        {
            return $row - 1;
        }
        return $this->corr[$row-2][$col-2];
    }
    public function bgColor($row, $col)
    {
        if($row == $col)
            return false;
        if($row > 1 && $col > 1)
        {
            $c = 55 + ($this->maxv - $this->corr[$row-2][$col-2]) * 200 / $this->maxv;
            return sprintf("FF%02X%02X",$c,$c);
        }
        return false;
    }
    public function htmlEquiv($row, $col)
    {
        //if($row == 1)
        //    return $this->colors[$col-2];
        //return $this->colors[$row-2]; patch

        if($row == $col)
            return '';
        if($row == 1)
        {
            $color = $this->colors[$col-2];
            $value = $this->choicenames[$col-2];
            return "<div style=\"width: 16px; background-color: #$color\">$value</div>";
        }
        if($col == 1)
        {
            $color = $this->colors[$row-2];
            $value = $this->choicenames[$row-2];
            return "<div style=\"width: 16px; background-color: #$color\">$value</div>";
        }
        return false;
    }
    public function headerCell($row, $col)
    {
        return $row == 1 || $col == 1;
    }
}

class UsersCorrelateData extends DataSource
{
    /* @var PageVO */ protected $page;
    protected $numusers;
    protected $maxv;
    protected $usernames = array();
    protected $corr = array();
    
    public function __construct(PageVO &$page, $presID)
    {
        $this->page =& $page;
        
        //Get votes from users
        global $vgDB, $vgDBPrefix;
        $votes =& $vgDB->GetAll("SELECT userID, surveyID, choiceID FROM {$vgDBPrefix}vote WHERE pageID=? AND presentationID=?",
                array( $this->page->getPageID(), $presID ));

        //Record votes
        $userv = array();
        foreach($votes as &$vote)
        {
            $userid = $vote['userID'];
            if(!isset($userv[ $userid ]))
                $userv[$userid] = array();
            $userv[$userid][] = $vote['surveyID'].'_'.$vote['choiceID'];
        }
        
        $numusers = 0;
        $mapusers = array();
        $userdao = new UserDAO();
        foreach($userv as $user => $v)
        {
            $mapusers[$numusers] = $user;
            $user = $userdao->findByID($user);
            if($user)
            {
                $username = MwUser::convertDisplayName( $user->username );
            }
            else
            {
                $username = 'Unknown user';
            }
            $this->usernames[$numusers] = $username;
            $numusers++;
        }
        $this->maxv = $this->page->getNumOfSurveys();
        $this->numusers = $numusers;
        
        $this->corr = array();
        for($i=0;$i<$numusers;$i++)
        {
            $this->corr[$i] = array();
            for($j=0;$j<$numusers;$j++)
            {
                $this->corr[$i][$j] = count(array_intersect( $userv[ $mapusers[$i] ], $userv[ $mapusers[$j] ] ));
            }
        }
    }
    public function getTitle()
    {
        return 'Users correlation';
    }
    public function numCols()
    {
        return 1 + $this->numusers;
    }
    public function numRows()
    {
        return 1 + $this->numusers;
    }
    public function getText($row, $col)
    {
        if( $row == 1 )
        {
            return $col - 1;
        }
        if($col == 1)
        {
            return ($row - 1).': '.$this->usernames[ $row - 2 ];
        }
        return $this->corr[$row-2][$col-2];
    }
    public function bgColor($row, $col)
    {
        if($row == $col)
            return false;
        if($row > 1 && $col > 1)
        {
            $c = 55 + ($this->maxv - $this->corr[$row-2][$col-2]) * 200 / $this->maxv;
            return sprintf("FF%02X%02X",$c,$c);
        }
        return false;
    }
    public function htmlEquiv($row, $col)
    {
        if($row == $col)
            return '';
        if($row == 1)
        {
            return '<abbr title="'.$this->usernames[$col-2].'">&nbsp;'.($col-1).'&nbsp;</abbr>';
        }
        return false;
    }
    public function headerCell($row, $col)
    {
        return $row == 1 || $col == 1;
    }
}

class QuizResultsData extends DataSource
{
    /* @var PageVO */ protected $page;
    protected $numusers;
    protected $numquestions;
    protected $usernames = array();
    protected $points = array();
    # protected $questions = array();
    protected $questionnames = array();

    public function __construct(PageVO &$page, $presID)
    {
        $this->page =& $page;

        //initialize, maping for the choices and conpute number of choices
        $numquestions = 0;
        $surveys =& $this->page->getSurveys();
        $correct = array();
        $points = array();
        $negpoints = array();
        $mapquestions = array();
        foreach ($surveys as &$survey)
        {
            /* @var $survey SurveyVO */
            $mapquestions[ $survey->getSurveyID() ] = $numquestions;
            # $this->questions[ $numquestions ] = $survey->getQuestion();
            $this->questionnames[ $numquestions ] = 
               '<abbr title="'.
                addslashes($survey->getQuestion()) . ' ('.$survey->getPoints().' points)'
                  .'">&nbsp;'
                .''.($numquestions+1)
              .'&nbsp;</abbr>';
            $correct[ $numquestions ] = $survey->getAnswer();
            $points[ $numquestions ] = $survey->getPoints();
            if( $page->getSubtractWrong() )
            {
                $negpoints[ $numquestions ] =  - $survey->getPoints() / $survey->getNumOfChoices();
            }
            else
            {
                $negpoints[ $numquestions ] = 0;
            }
            $numquestions++;
        }
        
        //Get votes from users
        global $vgDB, $vgDBPrefix;
        $votes =& $vgDB->GetAll("SELECT userID, surveyID, choiceID FROM {$vgDBPrefix}vote WHERE pageID=? AND presentationID=?",
                array( $this->page->getPageID(), $presID ));

        //Record votes
        $userv = array();
        foreach($votes as &$vote)
        {
            $userid = $vote['userID'];
            if(!isset($userv[ $userid ]))
                $userv[$userid] = array();
            $userv[$userid][] = array( $mapquestions[ $vote['surveyID'] ], $vote['choiceID'] );
        }
        
        $numusers = 0;
        $mapusers = array();
        $userdao = new UserDAO();
        foreach($userv as $user => &$votes)
        {
            $mapusers[$numusers] = $user;
            $user = $userdao->findByID($user);
            if($user)
            {
                $username = MwUser::convertDisplayName( $user->username );
            }
            else
            {
                $username = 'Unknown user';
            }
            $this->usernames[$numusers] = $username;
            $this->points[ $numusers ] = array_fill(0, $numquestions+1, 0);
            foreach ($votes as $vote)
            {
                list($surveyID, $choiceID) = $vote;
                //
                if( $choiceID == $correct[ $surveyID ] )
                {
                    $this->points[ $numusers ][ $surveyID ] += $points[ $surveyID ];
                    $this->points[ $numusers ][ $numquestions ] += $points[ $surveyID ];
                }
                else
                {
                    $this->points[ $numusers ][ $surveyID ] += $negpoints[ $surveyID ];
                    $this->points[ $numusers ][ $numquestions ] += $negpoints[ $surveyID ];
                }
            }
            $numusers++;
        }
        $this->maxv = $this->page->getNumOfSurveys();
        $this->numusers = $numusers;
        $this->numquestions = $numquestions;
    }
    function getStatsCalc()
    {
        global $vgPath;
        require_once("$vgPath/misc/StatsCalc.php");
        $s = new StatsCalc();
        for($i=0; $i<$this->numusers; $i++)
        {
            $s->add($this->points[ $i ][ $this->numquestions ]);
        }
        return $s;
    }
    public function getTitle()
    {
        return 'Number of points';
    }
    public function numCols()
    {
        return 2 + $this->numquestions;
    }
    public function numRows()
    {
        return 1 + $this->numusers;
    }
    public function getText($row, $col)
    {
        if($row + $col < 3)
            return 'Voter';
        if($row == 1 )
        {
            if($col - 2 == $this->numquestions)
                return 'Total';
            return 'Question '.($col - 1);
        }
        if($col == 1)
        {
            return $this->usernames[ $row - 2 ];
        }
        return $this->points[$row-2][$col-2];
    }
    public function headerCell($row, $col)
    {
        return $row == 1;
    }
    public function sortableCell($row, $col)
    {
        return $row == 1;
    }
    public function markRow($row)
    {
        return false;
    }
  public function htmlEquiv($row, $col)
    {
        if($row == 1 && $col > 1 && $col-2 < $this->numquestions)
        {
            return $this->questionnames[$col-2];
        }
        return false;
    }
}

abstract class DataWriter
{
    protected $sources = array();

    public function addSource(DataSource &$s)
    {
        $this->sources[] = $s;
    }
    function write()
    {
        $out = $this->start();;
        foreach ($this->sources as &$source)
        {
            $out .= $this->writeSource($source);
        }
        $out .= $this->finalize();
        return $out;
    }
    abstract protected function start();
    abstract protected function writeSource(DataSource &$source);
    abstract protected function finalize();
}

class ExcelWrite extends DataWriter
{
    protected $excel;
    protected $filename;
    
    public function __construct($filename)
    {
        //lazy loading
        require_once 'Spreadsheet/Excel/Writer.php';
        $this->excel = new Spreadsheet_Excel_Writer();
        $this->filename = $filename;
    }
    protected function start()
    {
        // send client headers
        $this->excel->send($this->filename);
        return '';
    }
    protected function writeSource(DataSource &$source)
    {
        $sheet =& $this->excel->addWorksheet();
        $header =& $this->excel->addFormat();
        $header->setBold();
        $header->setBottom(1);
        $header->setTop(1);
        $header->setRight(1);

        $rows = $source->numRows();
        $cols = $source->numCols();
        $sheet->write(0,0,$source->getTitle());
        for($i=1;$i<=$rows;$i++)
        {
            for($j=1;$j<=$cols;$j++)
            {
                if($source->headerCell($i, $j))
                    $sheet->write($i, $j-1, $source->getText($i, $j) , $header );
                else
                    $sheet->write($i, $j-1, $source->getText($i, $j) );
            }
        }
        return '';
    }
    protected function finalize()
    {
        if ($this->excel->close() !== true) {
            throw new Exception( 'ERROR: Could not save spreadsheet.' );
        }
        return '';
    }
}

class HtmlWrite extends DataWriter
{
    protected $style='sortable surveyout';
    function setStyle($style)
    {
        $this->style = $style;
    }
    protected function start()
    {
        return '';
    }
    protected function writeSource(DataSource &$source)
    {
        $out = '';
        $out .= '<h3>'.$source->getTitle().'</h3>';
        $out .= '<table class="'.$this->style.'" style="text-align:center;">';

        $rows = $source->numRows();
        $cols = $source->numCols();
        for($i=1;$i<=$rows;$i++)
        {
            if($source->markRow($i))
                $out .= '<tr class="surveyoutMark">';
            else
                $out .= '<tr>';
            
            for($j=1;$j<=$cols;$j++)
            {
                $extra = '';
                if($source->bgColor($i, $j))
                {
                    $extra .= " bgcolor='#{$source->bgColor($i, $j)}'";
                }
                if($source->headerCell($i, $j) !== false)
                {
                    if($source->sortableCell($i, $j) !== false)
                        $out .= "<th$extra>";
                    else
                        $out .= "<th class='unsortable'$extra>";
                }
                else
                    $out .= "<td$extra>";

                $html = $source->htmlEquiv($i, $j);
                if($html !== false)
                    $out .= $html;
                else
                    $out .= $source->getText($i, $j);
            }
            $out .= '</tr>';
        }
        $out .= '</table>';
        return $out;
    }
    protected function finalize()
    {
        return '';
    }
}

