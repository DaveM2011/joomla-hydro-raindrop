<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.hydroraindrop
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Joomla! Two Factor Authentication using Hydro Raindrop Plugin
 *
 * @since  3.2
 */
final class PlgSystemHydroraindrop extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $autoloadLanguage = true;

	/**
	 * Lets create the class
	 */
	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);
		// Just import the 2fa plugins
		JPluginHelper::importPlugin('twofactorauth');
	}
}
