<?php
/**
* @version    $Id: competitions.php 15 2008-02-06 00:37:43Z julienv $ 
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

jimport('joomla.application.component.model');
require_once (JPATH_COMPONENT.DS.'models'.DS.'list.php');

/**
 * Joomla Tracks Component Seasons Model
 *
 * @package		Tracks
 * @since 0.1
 */
class TracksModelProjectsettings extends TracksModelList
{
	/**
	 * project id
	 *
	 * @var int
	 */
	var $_project_id;	
	/**
	 * list of xml files
	 *
	 * @var array
	 */
	var $_files = null;
	/**
	 * base folder for xml files
	 *
	 * @var unknown_type
	 */
	var $_xmlfolder = null;
	/**
	 * the records
	 *
	 * @var array
	 */
	var $_data = null;
	var $_total = null;
	
	function __construct()
	{
    $mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
    
    parent::__construct();
    
    $id = $mainframe->getUserState( $option.'project', 0 );
    $this->set('_project_id', $id);		
	}
	
	/**
	 * get the settings list.
	 * In the future, the base folder for xml files could depend on project type
	 *
	 * @return array
	 */
	function getData()
	{
		
		//first, list all the xml config files
		//TODO: make the base folder depend on project type
		$this->_xmlfolder = JPATH_COMPONENT.DS.'projectparameters'.DS.'default';

		$files = array();
		if ($handle = opendir($this->_xmlfolder)) 
    {
    	while ( $file = readdir($handle) )
    	{
    		if ( $file != "." && $file != ".." && strtolower(substr($file, -3))=='xml')
    		{
    			 $files[] = $file;
    		}
    	}
    }
    else {
      JError::raiseError(500, JText::_( 'FOLDERNOTFOUND' ).': '.$this->_xmlfolder );    	
    }
    $this->_files = $files;
    
    // now, we have to check that each files has an associated record. otherwise, create one !
    $query = ' SELECT * FROM #__tracks_project_settings AS ps WHERE ps.project_id = ' . $this->_db->Quote($this->_project_id);
    $this->_db->setQuery($query);
    // get the list of matching records
    $found = array();
    if ($records = $this->_db->loadObjectList())
    {
    	foreach ($records AS $r)
    	{
    		if (in_array($r->xml, $files)) {
    			$found[] = $r->xml;
    		}
    	}
    }
    $notfound = array_diff($files, $found);
    
    // add missing records
    foreach ($notfound AS $file)
    {
    	$new = & $this->getTable('Projectsetting');
    	$new->xml = $file;
    	$new->project_id = $this->_project_id;
    	if ($new->check() && $new->store()) {
    		$records[] = $new;
    	}
    }
    
    $this->_total = count($records);
    
    return $records;
	}
	
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT obj.*, u.name AS editor '
			. ' FROM #__tracks_projectsettings AS obj '
			. ' LEFT JOIN #__users AS u ON u.id = obj.checked_out '
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.viewcompetitions.filter_order',		'filter_order',		'obj.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.viewcompetitions.filter_order_Dir',	'filter_order_Dir',	'',				'word' );

		if ($filter_order == 'obj.ordering'){
			$orderby 	= ' ORDER BY obj.ordering '.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , obj.ordering ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$filter_state		= $mainframe->getUserStateFromRequest( $option.'.viewcompetitions.filter_state',		'filter_state',		'',				'word' );
		$search				= $mainframe->getUserStateFromRequest( $option.'.viewcompetitions.search',			'search',			'',				'string' );
		$search				= JString::strtolower( $search );

		$where = array();

		if ($search) {
			$where[] = 'LOWER(obj.name) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
		if ( $filter_state ) {
			if ( $filter_state == 'P' ) {
				$where[] = 'obj.published = 1';
			} else if ($filter_state == 'U' ) {
				$where[] = 'obj.published = 0';
			}
		}

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		return $where;
	}
	
  /**
   * Method to get the total number of items
   *
   * @access public
   * @return integer
   */
  function getTotal()
  {
    // Lets load the content if it doesn't already exist
    if (empty($this->_total))
    {
      $this->getData();
    }

    return $this->_total;
  }
}
?>
