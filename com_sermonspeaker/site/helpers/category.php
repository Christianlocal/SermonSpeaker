<?php
/**
 * @package     SermonSpeaker
 * @subpackage  Component.Site
 * @author      Thomas Hunziker <admin@sermonspeaker.net>
 * @copyright   © 2016 - Thomas Hunziker
 * @license     http://www.gnu.org/licenses/gpl.html
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Categories\Categories;

/**
 * SermonSpeaker Component Sermons Category Tree
 *
 * @since  6.0.0
 */
class SermonspeakerSermonsCategories extends Categories
{
	/**
	 * Constructor
	 *
	 * @param   array $options Obtions
	 *
	 * @since 6.0.0
	 */
	public function __construct($options = array())
	{
		if (!isset($options['table']))
		{
			$options['table'] = '#__sermon_sermons';
		}

		$options['extension'] = 'com_sermonspeaker.sermons';

		parent::__construct($options);
	}
}

/**
 * SermonSpeaker Component Series Category Tree
 *
 * @since  6.0.0
 */
class SermonspeakerSeriesCategories extends Categories
{
	/**
	 * Constructor
	 *
	 * @param   array $options Obtions
	 *
	 * @since 6.0.0
	 */
	public function __construct($options = array())
	{
		if (!isset($options['table']))
		{
			$options['table'] = '#__sermon_series';
		}

		$options['extension'] = 'com_sermonspeaker.series';

		parent::__construct($options);
	}
}

/**
 * SermonSpeaker Component Speakers Category Tree
 *
 * @since  6.0.0
 */
class SermonspeakerSpeakersCategories extends Categories
{
	/**
	 * Constructor
	 *
	 * @param   array $options Obtions
	 *
	 * @since 6.0.0
	 */
	public function __construct($options = array())
	{
		if (!isset($options['table']))
		{
			$options['table'] = '#__sermon_speakers';
		}

		$options['extension'] = 'com_sermonspeaker.speakers';

		parent::__construct($options);
	}
}
