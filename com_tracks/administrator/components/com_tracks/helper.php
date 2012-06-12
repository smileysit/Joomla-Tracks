<?php
/**
* @version    $Id: helper.php 133 2008-06-08 10:24:29Z julienv $ 
* @package    JoomlaTracks
* @copyright	Copyright (C) 2008 Julien Vonthron. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla Tracks is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Tracks Component Helper
 *
 * @static
 * @package   JoomlaTracks
 * @since 0.2
 */
class TracksHelper
{

	/**
	 * returns select list filled with element of query
	 *
	 * @param string $query
	 * @param boolean $active
	 * @return html select
	 */
  function filterProject($query, $active = NULL)
  {
    // Initialize variables
    $db = & JFactory::getDBO();

    $projects[] = JHTML::_('select.option', '0', '- '.JText::_('COM_TRACKS_Select_Project').' -');
    $db->setQuery($query);
    $projects = array_merge($projects, $db->loadObjectList());

    $project = JHTML::_('select.genericlist',  $projects, 'projectid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $active);

    return $project;
  }
  

  /**
   * returns select list filled with element of query
   *
   * @param string $query
   * @param boolean $active
   * @return html select
   */
  function filterCompetition($query, $active = NULL)
  {
    // Initialize variables
    $db = & JFactory::getDBO();

    $objects[] = JHTML::_('select.option', '0', '- '.JText::_('COM_TRACKS_Select_Competition').' -');
    if ($query == '')
    {
      $query = 'SELECT id AS value, name AS text' .
        ' FROM #__tracks_competitions' .
        ' ORDER BY ordering, name';
    }
    $db->setQuery($query);
    $objects = array_merge($objects, $db->loadObjectList());

    $list = JHTML::_('select.genericlist',  $objects, 'competitionid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $active);

    return $list;
  }
  

  /**
   * returns select list filled with element of query
   *
   * @param string $query
   * @param boolean $active
   * @return html select
   */
  function filterSeason($query, $active = NULL)
  {
    // Initialize variables
    $db = & JFactory::getDBO();

    $objects[] = JHTML::_('select.option', '0', '- '.JText::_('COM_TRACKS_Select_Season').' -');
    if ($query == '')
    {
      $query = 'SELECT id AS value, name AS text' .
        ' FROM #__tracks_seasons' .
        ' ORDER BY ordering, name';
    }
    $db->setQuery($query);
    $objects = array_merge($objects, $db->loadObjectList());

    $list = JHTML::_('select.genericlist',  $objects, 'seasonid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $active);

    return $list;
  }
  
 /**
  * Select list of active users
  */
  function usersSelect( $name, $active, $nouser = 0, $javascript = NULL, $order = 'name', $reg = 1 )
  {
    $db =& JFactory::getDBO();

    $and = '';
    if ( $reg ) {
    // does not include registered users in the list
      $and = ' AND gid > 18';
    }

    $query = 'SELECT id AS value, CONCAT(name, " (", username, ")") AS text'
    . ' FROM #__users'
    . ' WHERE block = 0'
    . $and
    . ' ORDER BY '. $order
    ;
    $db->setQuery( $query );
    if ( $nouser ) {
      $users[] = JHTML::_('select.option',  '0', '- '. JText::_('COM_TRACKS_No_User' ) .' -' );
      $users = array_merge( $users, $db->loadObjectList() );
    } else {
      $users = $db->loadObjectList();
    }

    $users = JHTML::_('select.genericlist',   $users, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

    return $users;
  }
  
  /**
   * return true if mootools upgrade is enabled
   * 
   * @return boolean
   */
  function isMootools12()
  {
  	$version = new JVersion();
		if ($version->RELEASE == '1.5' && $version->DEV_LEVEL >= 19 && JPluginHelper::isEnabled( 'system', 'mtupgrade' ) ) {
			return true;
		}
		else {
			return false;
		}
  }
  
	/**
	 * put some data
	 * 
	 * 
	 */
	public static function sampledata()
	{
		$season_id = self::sampleGetSeason();
		$competition_id = self::sampleGetCompetition();
		
		$individuals = self::sampleGetIndividuals();
		$teams = self::sampleGetTeams();
		$rounds = self::sampleGetRounds();
		$subroundtypes = self::sampleGetSubRoundTypes();
		
		// project
		$project = JTable::getInstance('project', 'table');
		$project->name = "Sample";
		$project->season_id= $season_id;
		$project->competition_id = $competition_id;
		$project->published = 1;
		
		if (!$project->check() || !$project->store()) {
			throw new Exception($project->getError());
		}
		
		// project individual
		$pis = array();
		foreach ($individuals as $k => $i) 
		{
			$pi = JTable::getInstance('projectindividual', 'table');
			$pi->project_id = $project->id;
			$pi->individual_id = $i;
			$pi->team_id = $teams[$k % 5];
			$pi->number = $k + 1;	
			
			if (!$pi->check() || !$pi->store()) {
				throw new Exception($pi->getError());
			}
			$pis[] = $pi;
		}
				
		// project rounds
		$o = 1;
		$start_date = strtotime('today - 4 weeks');
		foreach ($rounds as $k => $r)
		{
			$row = JTable::getInstance('projectround', 'table');
			$row->round_id = $r;
			$row->project_id = $project->id;
			$row->ordering = $o++;
			$row->published = 1;
			$row->start_date = strftime('%Y-%m-%d 14:00', $start_date);
			$row->end_date = strftime('%Y-%m-%d 22:00', $start_date+3600*24);
			
			if (!$row->check() || !$row->store()) {
				throw new Exception($row->getError());
			}
			
			// subrounds
			$sr = JTable::getInstance('subround', 'table');
			$sr->projectround_id = $row->id;
			$sr->type = $subroundtypes[0];
			$sr->published = 1;
			$sr->ordering = 1;
			$sr->start_date = strftime('%Y-%m-%d 14:00', $start_date);
			$sr->end_date = strftime('%Y-%m-%d 16:00', $start_date);
			if (!$sr->check() || !$sr->store()) {
				throw new Exception($sr->getError());
			}
			self::sampleSetResults($sr->id, $pis);
			
			$sr = JTable::getInstance('subround', 'table');
			$sr->projectround_id = $row->id;
			$sr->type = $subroundtypes[1];
			$sr->published = 1;
			$sr->ordering = 2;
			$sr->start_date = strftime('%Y-%m-%d 14:00', $start_date+3600*24);
			$sr->end_date = strftime('%Y-%m-%d 16:00', $start_date+3600*24);
			if (!$sr->check() || !$sr->store()) {
				throw new Exception($sr->getError());
			}
			self::sampleSetResults($sr->id, $pis);
			
			$start_date += 3600*24*7;
		}
	}
	
	protected static function sampleGetSeason()
	{		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('s.id');
		$query->from('#__tracks_seasons AS s');
		$db->setQuery($query);
		$season_id = $db->loadResult();

		if (!$season_id) 
		{
			// create a season
			$row = JTable::getInstance('season', 'table');
			$row->name = 'test';
			$row->published = 1;
				
			if (!$row->check() || !$row->store()) {
				throw new Exception($row->getError());
			}
			$season_id = $row->id;
		}
		return $season_id;
	}
	
	protected static function sampleGetCompetition()
	{		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__tracks_competitions');
		$db->setQuery($query);
		$id = $db->loadResult();

		if (!$id) 
		{
			// create a new one
			$row = JTable::getInstance('competition', 'table');
			$row->name = 'test';
			$row->published = 1;
				
			if (!$row->check() || !$row->store()) {
				throw new Exception($row->getError());
			}
			$id = $row->id;
		}
		return $id;
	}
	
	protected function sampleGetIndividuals()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		$query->select('id');
		$query->from('#__tracks_individuals');
		$query->where('last_name LIKE "individual%"');
		$db->setQuery($query, 0, 10);
		$res = $db->loadColumn();
		
		if (count($res) == 10) {
			return $res;
		}
		
		// not enough people, add some
		if (!$res) {
			$res = array();
		}
		
		// add individuals
		$firsts = array('adam', 'alfred', 'bart', 'mickael', 'simon',
		'louis', 'roger', 'william', 'robert', 'bart', 'ross', 'mich',
		'lewis', 'blake', 'joe', 'john');
		
		$k = count($res)+1;
		for ($i = 0, $n = 10 - count($res); $i < $n; $i++)
		{
			$row = JTable::getInstance('individual', 'table');
			$key = array_rand($firsts);
			$row->first_name = $firsts[$key];
			$row->last_name = 'individual'.($k++);
			
			$countries = TracksCountries::getCountries();
			$key = array_rand($countries);
			$row->country_code = $key;
			
			if (!$row->check() || !$row->store()) {
				throw new Exception($row->getError());
			}
			$res[] = $row->id;
		}
		return $res;
	}
	
	
	protected function sampleGetRounds()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__tracks_rounds');
		$query->where('name LIKE "Round%"');
		$db->setQuery($query, 0, 10);
		$res = $db->loadColumn();

		if (count($res) == 10) {
			return $res;
		}

		// not enough, add some
		if (!$res) {
			$res = array();
		}
			
		$k = count($res)+1;
		for ($i = 0, $n = 10 - count($res); $i < $n; $i++)
		{
			$row = JTable::getInstance('round', 'table');
			$row->name = 'Round '.($k++);
			$row->published = 1;

			if (!$row->check() || !$row->store()) {
				throw new Exception($row->getError());
			}
			$res[] = $row->id;
		}
		return $res;
	}
	
	protected function sampleGetTeams($nb = 5)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
			
		$query->select('id');
		$query->from('#__tracks_teams');
		$query->where('name LIKE "Team%"');
		$db->setQuery($query, 0, $nb);
		$res = $db->loadColumn();

		if (count($res) == $nb) {
			return $res;
		}

		// not enough, add some
		if (!$res) {
			$res = array();
		}
			
		$k = count($res)+1;
		for ($i = 0, $n = $nb - count($res); $i < $n; $i++)
		{
			$row = JTable::getInstance('team', 'table');
			$row->name = 'Team '.$k;
			$row->short_name = 'Team '.$k;
			$row->acronym = 'T'.$k;
			$countries = TracksCountries::getCountries();
			$key = array_rand($countries);
			$row->country_code = $key;

			if (!$row->check() || !$row->store()) {
				throw new Exception($row->getError());
			}
			$k++;
			$res[] = $row->id;
		}
		return $res;
	}
	
	protected static function sampleGetSubRoundTypes()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__tracks_subroundtypes');
		$query->where('name LIKE "sample%"');
		$db->setQuery($query, 0, 2);
		$res = $db->loadColumn();

		if (count($res) == 2) {
			return $res;
		}

		// not enough, add some
		if (!$res) {
			$res = array();
		}
			
		$row = JTable::getInstance('subroundtype', 'table');
		$row->name = 'sample qualification';

		if (!$row->check() || !$row->store()) {
			throw new Exception($row->getError());
		}
		$res[] = $row->id;
			
		$row = JTable::getInstance('subroundtype', 'table');
		$row->name = 'sample race';
		$row->points_attribution = '20,15,10,8,6,4,2,1';;

		if (!$row->check() || !$row->store()) {
			throw new Exception($row->getError());
		}
		$res[] = $row->id;
		
		return $res;
	}
	
	protected static function sampleSetResults($subround_id, $projectindividuals, $setnull = false)
	{
		$rank = 1;
		shuffle($projectindividuals);
		foreach ($projectindividuals as $pi) 
		{
			$row = JTable::getInstance('projectroundresult', 'table');
			$row->subround_id = $subround_id;
			$row->individual_id = $pi->individual_id;
			$row->team_id = $pi->team_id;
			$row->rank = $setnull ? 0 : $rank++;
			
			if (!$row->check() || !$row->store()) {
				throw new Exception($row->getError());
			}
		}
		return true;
	}
}
