<?php

function startswith($a, $b)
{
    return strcasecmp( substr($a, 0, strlen($b)) , $b) == 0;
}


function str_replace_once($search, $replace, $subject) {
    $firstChar = strpos($subject, $search);
    if($firstChar !== false) {
        $beforeStr = substr($subject,0,$firstChar);
        $afterStr = substr($subject, $firstChar + strlen($search));
        return $beforeStr.$replace.$afterStr;
    } else {
        return $subject;
    }
}

class DebugADODB
{
    protected $cn;
    protected $debug;

    public function __construct()
    {
        $this->cn = vfConnectDatabase();
        $this->debug = false;
    }
    function profileSQL($sql)
    {
        if(startswith($sql, 'select'))
        {
            if(strstr($sql, 'smsd.inbox'))
                return;
            
            global $vgDBPrefix;
            
            $this->cn->debug = false;
            $r = $this->cn->GetAll("EXPLAIN $sql");
            $this->cn->debug = $this->debug;

            foreach ($r as $a)
            {
                if( $a['Extra'] == ''
                 || $a['Extra'] == 'Using index'
                 || $a['Extra'] == 'Impossible WHERE noticed after reading const tables'
                )   continue;
                
                if($a['Extra'] == "Using where" || $a['Extra'] == 'Using where; Using index' || $a['Extra'] == 'Using where; Using join buffer')
                {
                    if($a['key'] == 'receivers_released' && startswith($sql, "select * from {$vgDBPrefix}page WHERE receivers_released = 0 AND endTime <="))
                        continue;

                    if($a['key'] == 'receivers_released' && $sql == "select * from {$vgDBPrefix}page WHERE receivers_released = 0")
                        continue;

                    if($a['key'] == 'userID' && startswith($sql, "SELECT phoneID FROM {$vgDBPrefix}phone WHERE userID ="))
                        continue;

                    if( startswith($sql, "select numvotes,surveyID,choiceID FROM {$vgDBPrefix}choice LEFT JOIN {$vgDBPrefix}survey USING (surveyID) WHERE {$vgDBPrefix}survey.pageID =") )
                        continue;

                    if( startswith($sql, "select numvotes,surveyID,choiceID FROM {$vgDBPrefix}choice LEFT JOIN {$vgDBPrefix}survey USING (surveyID) WHERE {$vgDBPrefix}survey.pageID =") )
                        continue;

                    if( ($a['key'] == 'userID' || $a['key'] == 'surveyID') && startswith($sql, "select choiceID from {$vgDBPrefix}vote where userID = "))
                        continue;

                    if( ($a['key'] == 'userID' || $a['key'] == 'surveyID') && startswith($sql, "select voteID, choiceID from {$vgDBPrefix}vote where userID ="))
                        continue;

                    if($a['key'] == 'SMS' && startswith($sql, "SELECT pageID, surveyID, choiceID FROM {$vgDBPrefix}choice WHERE SMS ="))
                        continue;

                    if( startswith($sql, 'SELECT count(voteID) as count, max(voteID) as maxch FROM ') )
                        continue;
                }
                echo "<pre>\n\n";
                echo $a['Extra']."\n";
                echo '      '.$sql."\n";
                var_dump($r);
                die('');
            }
        }
    }
    function profile($sql,$params = false)
    {
        if($params)
        {
            if( is_array( $params[0] ) )
            {
                foreach ($params as $param)
                {
                    $this->profile($sql, $param);
                }
            }
            else
            {
                foreach ($params as $param)
                {
                    if(is_int($param))
                    {
                        ;
                    }
                    elseif(is_string($param))
                    {
                        $param = str_replace('?', '.', $param);
                        $param = "'".addslashes($param)."'";
                    } elseif(is_bool($param))
                    {
                        if($param)
                            $param = '1';
                        else
                            $param = '0';
                    } elseif(is_null($param))
                    {
                        $param = 'NULL';
                    }
                    else {
                        var_dump($param);
                        die('unknown type');
                    }
                    $sql = str_replace_once('?', $param, $sql);
                }
                if( strstr($sql, '?') )
                {
                    echo $sql."\n\n";
                    die('substitution is not complete');
                }
                $this->profileSQL($sql);
            }
        }
        else
        {
            if( is_string($sql) )
            {
                $this->profileSQL($sql);
            }
        }
    }
    function enableOutput()
    {
        $this->cn->debug = true;
        $this->debug = true;
    }
    function disableOutput()
    {
        $this->cn->debug = false;
        $this->debug = false;
    }
    function GetOne($sql,$params = false)
    {
        $this->profile($sql, $params);
        return $this->cn->GetOne($sql,$params);
    }
    function GetAll($sql,$params = false)
    {
        $this->profile($sql, $params);
        return $this->cn->GetAll($sql,$params);
    }
    function Execute($sql,$params = false)
    {
        $this->profile($sql, $params);
        return $this->cn->Execute($sql,$params);
    }
    function Prepare($sql,$params = false)
    {
        $this->profile($sql, $params);
        return $this->cn->Prepare($sql,$params);
    }
    function Insert_ID()
    {
        return $this->cn->Insert_ID();
    }
    function StartTrans()
    {
        return $this->cn->StartTrans();
    }
    function CompleteTrans()
    {
        return $this->cn->CompleteTrans();
    }
    function HasFailedTrans()
    {
        return $this->cn->HasFailedTrans();
    }
}

/**
 * Connect database without parameters
 * @return ADOConnection
 */
function vfConnectDebugDatabase()
{
    return new DebugADODB();
}

