<?php
/**
 * @version      $Id: roundresult.php 61 2008-04-24 15:20:36Z julienv $
 * @package      JoomlaTracks
 * @copyright    Copyright (C) 2008 Julien Vonthron. All rights reserved.
 * @license      GNU/GPL, see LICENSE.php
 * Joomla Tracks is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
require_once('base.php');

/**
 * Joomla Tracks Component Front page Model
 *
 * @package     Tracks
 * @since       0.1
 */
class TracksModelTeam extends RModelAdmin
{
	protected $_id;
	protected $project_id;
	protected $_individuals;

	public function __construct($config = array())
	{
		parent::__construct($config);

		$id = JRequest::getInt('id');
		$project = JRequest::getInt('p');

		$this->setId($id);
		$this->project_id = $project;
	}


	function setId($id)
	{
		$this->_id = (int) $id;
		$this->_data = null;
		$this->_individuals = null;
	}

	function getItem()
	{
		if (empty($this->_data))
		{
			$query = ' SELECT t.* '
				. ' FROM #__tracks_teams as t '
				. ' WHERE t.id = ' . $this->_id;

			$this->_db->setQuery($query);

			if ($result = $this->_db->loadObject())
			{
				$this->_data = $result;
			}
		}

		return $this->_data;
	}

	function getIndividuals()
	{
		if (empty($this->_individuals))
		{
			$query = ' SELECT p.name as project_name, i.*, pi.number, pi.project_id, s.name, '
				. ' CASE WHEN CHAR_LENGTH( i.alias ) THEN CONCAT_WS( \':\', i.id, i.alias ) ELSE i.id END AS slug, '
				. ' CASE WHEN CHAR_LENGTH( p.alias ) THEN CONCAT_WS( \':\', p.id, p.alias ) ELSE p.id END AS projectslug '
				. ' FROM #__tracks_individuals AS i '
				. ' INNER JOIN #__tracks_participants AS pi ON pi.individual_id = i.id '
				. ' INNER JOIN #__tracks_projects AS p ON pi.project_id = p.id '
				. ' INNER JOIN #__tracks_seasons AS s ON s.id = p.season_id '
				. ' WHERE pi.team_id = ' . $this->_db->Quote($this->_id)
				. ($this->project_id ? ' AND p.id = ' . $this->_db->Quote($this->project_id) : '')
				. ' ORDER BY p.ordering, i.last_name ASC ';
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();

			// sort by projects
			$proj = array();
			foreach ((array) $res as $i)
			{
				if (!isset($proj[$i->project_id]))
				{
					$proj[$i->project_id] = array();
				}
				$proj[$i->project_id][] = $i;
			}

			$this->_individuals = $proj;
		}
		return $this->_individuals;
	}

	/**
	 * Method to validate the form data.
	 * Each field error is stored in session and can be retrieved with getFieldError().
	 * Once getFieldError() is called, the error is deleted from the session.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		$validData = parent::validate($form, $data, $group);

		$validData = $this->getPicture($validData, $data, 'picture');
		$validData = $this->getPicture($validData, $data, 'picture_small');
		$validData = $this->getPicture($validData, $data, 'vehicle_picture');

		return $validData;
	}

	/**
	 * Manage upload of picture
	 *
	 * @param   array   $validData  valid data
	 * @param   array   $data       post data
	 * @param   string  $field      field name
	 *
	 * @return array valid data
	 */
	protected function getPicture($validData, $data, $field)
	{
		$params = JComponentHelper::getParams('com_tracks');
		$files = JFactory::getApplication()->input->files->get('jform', '', array());
		$targetpath = 'images/' . $params->get('default_team_images_folder', 'tracks/teams');

		if (!isset($files[$field]) || !$picture = $files[$field])
		{
			return false;
		}

		if (!empty($picture['name']))
		{
			$base_Dir = JPATH_SITE . '/' . $targetpath . '/';

			if (!JFolder::exists($base_Dir))
			{
				JFolder::create($base_Dir);
			}

			// Check the image
			$check = TrackslibHelperImage::check($picture);

			if ($check === false)
			{
				throw new Exception('IMAGE CHECK FAILED', 500);
			}

			// Sanitize the image filename
			$filename = TrackslibHelperImage::sanitize($base_Dir, $picture['name']);
			$filepath = $base_Dir . $filename;

			if (!JFile::upload($picture['tmp_name'], $filepath))
			{
				throw new Exception(JText::_('COM_TRACKS_UPLOAD_FAILED'), 500);
			}
			else
			{
				$validData[$field] = $targetpath . '/' . $filename;
			}
		}

		return $validData;
	}
}
