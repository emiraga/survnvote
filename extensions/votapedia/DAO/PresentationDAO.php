<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */

/**
 * Description of PresentationDAO
 *
 * @author Emir Habul
 * @package DataAccessObject
 */
class PresentationDAO {
    static function insert(PresentationVO $presentation)
    {
        global $vgDB, $vgDBPrefix;
        $sql = "insert into {$vgDBPrefix}presentation(pageID, presentationID, ";
        $sql .= "name, active, startTime, endTime ) values (?,?,?,?,?,?)";
        $vgDB->Execute($sql,array(
            $presentation->getPageID(),
            $presentation->getPresentationID(),
            $presentation->getName(),
            $presentation->getActive(),
            $presentation->getStartTime(),
            $presentation->getEndTime()
        ));
    }
    /**
     * Get presentations of a survey
     *
     * @param Integer $pageID
     * @return Array $presentations
     */
    static function &getFromPage($pageID)
    {
        global $vgDB, $vgDBPrefix;
        $sql = "select * from {$vgDBPrefix}presentation where pageID = ? order by presentationID";
        $rsPresentation = &$vgDB->Execute($sql, array($pageID));

        $presentations = array();

        while(!$rsPresentation->EOF)
        {
            $pres = new PresentationVO();
            $pres->setPageID($pageID);
            $pres->setPresentationID($rsPresentation->fields['presentationID']);
            $pres->setName($rsPresentation->fields['name']);
            $pres->setActive($rsPresentation->fields['active']);
            $pres->setStartTime($rsPresentation->fields['startTime']);
            $pres->setEndTime($rsPresentation->fields['endTime']);
            
            $presentations[] = $pres;
            $rsPresentation->MoveNext();
        }
        $rsPresentation->Close();

        return $presentations;
    }
    /**
     * Activate a presentation in a survey
     *
     * @param Integer $surveyID
     * @param Integer $presentationID
     */
    static function activate($pageID, $presentationID)
    {
        global $vgDB, $vgDBPrefix;

        $vgDB->StartTrans();

        $sql = "update {$vgDBPrefix}presentation set active = 0 where pageID = ?";
        $vgDB->Execute($sql, array($pageID));

        $sql = "update {$vgDBPrefix}presentation set active = 1 where pageID = ? and presentationID = ?";
        $vgDB->Execute($sql, array($pageID, $presentationID));

        $vgDB->CompleteTrans();
    }
    /**
     * Delete presentation in a page which includes it
     *
     * @param Integer $pageID id of a page
     */
    static function delete($pageID)
    {
        global $vgDB, $vgDBPrefix;

        $sql = "delete from {$vgDBPrefix}presentation where pageID = ?";
        $vgDB->Execute($sql, array($pageID));
    }
}

