<?php
/**
 * MediaWiki Adapter desing pattern.
 * Idea is to have all functions related to MediaWiki in one place
 * 
 */
class MwAdapter
{
	/**
	 * Purge the cache of a page with a given title
	 * 
	 * @param $title string title of wiki page
	 */
	function purgePage($title)
	{
		$params = new FauxRequest(array('action' => 'purge','titles' => $title));
		$api = new ApiMain($params, true);
		$api->execute();
		$data = & $api->getResultData();
		if(!isset($data['purge'][0]['purged']))
			throw new Exception('Page purging has failed');
	}
	/**
	 * Get a list of subcategories of a category
	 * 
	 * @param $category Name of a category
	 * @return array with a list of categories
	 */
	function getSubCategories($category) // = )
	{
		$params = new FauxRequest(array(
			'cmtitle' => $category,
			'action' => 'query',
			'list' => 'categorymembers',
			'cmprop' => 'title',
			//'cmsort' => 'timestamp',
		));
		$api = new ApiMain($params);
		$api->execute();
		$data = & $api->getResultData();
		$result = array();
		foreach($data['query']['categorymembers'] as $subcat)
		{
			$result[] = $subcat['title'];
		}
		return $result;
	}
	/**
	 * Purge all members that belong to one category
	 * 
	 * @param $category name of category
	 */
	function purgeCategoryMembers( $category )
	{
		//Purge all pages that have this survey included.
		$members = $this->getSubCategories( $category );
		foreach($members as $m)
		{
			$this->purgePage(Title::newFromText( $m )->getDBkey());
		}
	}	
}
