<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/VO/PresentationVO.php");

/**
 * Description of PresentationDAO
 *
 * @author Emir Habul
 * @package DataAccessObject
 */
class PresentationDAO
{
    /**
     * Insert presentation object to database.
     * 
     * @param PresentationVO $presentation
     */
    static function insert(PresentationVO $presentation)
    {
        global $vgDB, $vgDBPrefix;
        $sql = "insert into {$vgDBPrefix}presentation(pageID, presentationID, ";
        $sql .= "name, active, startTime, endTime, numvotes, crowdID ) values (?,?,?,?,?,?,?,?)";
        $vgDB->Execute($sql,array(
            intval($presentation->getPageID()),
            intval($presentation->getPresentationID()),
            $presentation->getName(),
            $presentation->getActive(),
            $presentation->getStartTime(),
            $presentation->getEndTime(),
            $presentation->numvotes,
            $presentation->crowdID
        ));
    }
    /**
     * Get presentations of a survey.
     *
     * @param Integer $pageID
     * @return Array $presentations
     */
    static function &getFromPage($pageID)
    {
        global $vgDB, $vgDBPrefix;
        $sql = "select * from {$vgDBPrefix}presentation where pageID = ?";
        $rsPresentation = &$vgDB->Execute($sql, array(intval($pageID)));

        $presentations = array();
        $ids = array();
        while(!$rsPresentation->EOF)
        {
            $pres = new PresentationVO();
            $pres->setPageID($pageID);
            $pres->setPresentationID($rsPresentation->fields['presentationID']);
            $pres->setName($rsPresentation->fields['name']);
            $pres->setActive($rsPresentation->fields['active']);
            $pres->setStartTime($rsPresentation->fields['startTime']);
            $pres->setEndTime($rsPresentation->fields['endTime']);
            $pres->numvotes = $rsPresentation->fields['numvotes'];
            $pres->crowdID = $rsPresentation->fields['crowdID'];
            $presentations[] = $pres;
            $ids[] = intval($rsPresentation->fields['presentationID']);
            $rsPresentation->MoveNext();
        }
        $rsPresentation->Close();

        array_multisort($ids, SORT_NUMERIC, SORT_ASC, $presentations );
        return $presentations;
    }
    /**
     * Activate a presentation in a survey.
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
     * Delete presentation in a page which includes it.
     *
     * @param Integer $pageID id of a page
     */
    static function delete($pageID)
    {
        global $vgDB, $vgDBPrefix;

        $sql = "delete from {$vgDBPrefix}presentation where pageID = ?";
        $vgDB->Execute($sql, array(intval($pageID)));
    }
}

