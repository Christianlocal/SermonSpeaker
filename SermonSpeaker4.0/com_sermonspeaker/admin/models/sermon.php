<?php
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Sermon model.
 *
 * @package		Sermonspeaker.Administrator
 */
class SermonspeakerModelSermon extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 */
	protected $text_prefix = 'COM_SERMONSPEAKER';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		return true;
	}

	/**
	 * Method to test whether a records state can be changed.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		return true;
	}
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Sermon', $prefix = 'SermonspeakerTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_sermonspeaker.sermon', 'sermon', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		// Determine correct permissions to check.
		if ($this->getState('sermon.id')) {
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
		} else {
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sermonspeaker.edit.sermon.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		// Reading ID3 Tags if the Lookup Button was pressed
		if ($id3_file = JRequest::getString('file')){
			$data->sermon_path = $id3_file;
			require_once(JPATH_SITE.DS.'components'.DS.'com_sermonspeaker'.DS.'id3'.DS.'getid3'.DS.'getid3.php');
			$getID3 	= new getID3;
			$path		= JPATH_SITE.str_replace('/', DS, $id3_file);
			$FileInfo	= $getID3->Analyze($path);

			$id3 = array();
			if (array_key_exists('playtime_string', $FileInfo)){
				$id3['sermon_time']		= $FileInfo['playtime_string'];
			}
			if (array_key_exists('comments', $FileInfo)){
				if (array_key_exists('title', $FileInfo['comments'])){
					$id3['sermon_title']	= $FileInfo['comments']['title'][0];
				}
				if (array_key_exists('track_number', $FileInfo['comments'])){
					$id3['sermon_number']	= $FileInfo['comments']['track_number'][0]; // ID3v2 Tag
				} elseif (array_key_exists('track', $FileInfo['comments'])) {
					$id3['sermon_number']	= $FileInfo['comments']['track'][0]; // ID3v1 Tag
				}

				if (array_key_exists('comments', $FileInfo['comments'])){
					if ($params->get('fu_id3_comments') == 'ref'){
						if ($FileInfo['comments']['comments'][0] != ""){
							$id3['sermon_scripture'] = $FileInfo['comments']['comments'][0]; // ID3v2 Tag
						} else {
							$id3['sermon_scripture'] = $FileInfo['comments']['comment'][0]; // ID3v1 Tag
						}
					} else {
						if ($FileInfo['comments']['comments'][0] != ""){
							$id3['notes'] = $FileInfo['comments']['comments'][0]; // ID3v2 Tag
						} else {
							$id3['notes'] = $FileInfo['comments']['comment'][0]; // ID3v1 Tag
						}
					}
				}
				$db =& JFactory::getDBO();
				if (array_key_exists('album', $FileInfo['comments'])){
					$query = "SELECT id FROM #__sermon_series WHERE series_title like '".$FileInfo['comments']['album'][0]."';";
					$db->setQuery($query);
					$id3['series_id'] 	= $db->loadRow();
				}
				if (array_key_exists('artist', $FileInfo['comments'])){
					$query = "SELECT id FROM #__sermon_speakers WHERE name like '".$FileInfo['comments']['artist'][0]."';";
					$db->setQuery($query);
					$id3['speaker_id']	= $db->loadRow();
				}
			}
			foreach ($id3 as $key => $value){
				if ($value){
					$data->$key = $value;
				}
			}
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable(&$table)
	{
		jimport('joomla.filter.output');

		$table->sermon_title	= htmlspecialchars_decode($table->sermon_title, ENT_QUOTES);
		$table->alias			= JApplication::stringURLSafe($table->alias);
		if (empty($table->alias)) {
			$table->alias = JApplication::stringURLSafe($table->sermon_title);
			if (empty($table->alias)) {
				$table->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
			}
		}
		$time_arr = explode(':', $table->sermon_time);
		foreach ($time_arr as $time_int){
			$time_int = (int)$time_int;
			$time_int = str_pad($time_int, 2, '0', STR_PAD_LEFT);
		}
		if (count($time_arr) == 2) {
			$table->sermon_time = '00:'.$time_arr[0].':'.$time_arr[1];
		} elseif (count($tarr) == 3) {
			$table->sermon_time = $time_arr[0].':'.$time_arr[1].':'.$time_arr[2];
		}
		if (!empty($table->metakey)) {
			// only process if not empty
			$bad_characters = array("\n", "\r", "\"", "<", ">"); // array of characters to remove
			$after_clean = JString::str_ireplace($bad_characters, "", $table->metakey); // remove bad characters
			$keys = explode(',', $after_clean); // create array using commas as delimiter
			$clean_keys = array();
			foreach($keys as $key) {
				if (trim($key)) {  // ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}
			$table->metakey = implode(", ", $clean_keys); // put array back together delimited by ", "
		}

		if (empty($table->id)) {
			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__sermon_sermons');
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param	object	A record object.
	 * @return	array	An array of conditions to add to add to ordering queries.
	 * @since	1.6
	 */
	protected function getReorderConditions($table = null)
	{
		$condition = array();
		$condition[] = 'catid = '.(int) $table->catid;
		return $condition;
	}

	/**
	 * Changing the state of podcast. Copy of the parent function publish
	 */
	function podcast(&$pks, $value = 1)
	{
		// Initialise variables.
		$dispatcher	= JDispatcher::getInstance();
		$user		= JFactory::getUser();
		$table		= $this->getTable();
		$pks		= (array) $pks;

		// Include the content plugins for the change of state event.
//		JPluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk) {
			$table->reset();

			if ($table->load($pk)) {
				if (!$this->canEditState($table)) {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_STATE_NOT_PERMITTED'));
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->podcast($pks, $value, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		$context = $this->option.'.'.$this->name;

		// Trigger the onContentChangeState event.
//		$result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

		if (in_array(false, $result, true)) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	public function getSpeakers()
	{
		// Create a new query object.
		$db		= $this->getDbo();

		$query	= "SELECT speakers.id, speakers.name \n"
				. "FROM `#__sermon_speakers` AS speakers \n"
				. "ORDER BY speakers.name ASC";

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	public function getSeries()
	{
		// Create a new query object.
		$db		= $this->getDbo();

		$query	= "SELECT series.id, series.series_title \n"
				. "FROM `#__sermon_series` AS series \n"
				. "ORDER BY series.series_title ASC";

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}
}