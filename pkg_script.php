<?php

/**
 * @package     Joomla.Package
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

final class pkg_hydroraindropInstallerScript
{
	/**
	 * Called after any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
    public function postflight($route, JAdapterInstance $adapter)
    {
        if (in_array($route, array('install', 'update', 'discover_install'))) {
			return $this->install($adapter);
		} else if ($route == 'uninstall') {
			return $this->uninstall($adapter);
		}
    }
	
	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
    public function install(JAdapterInstance $adapter)
    {
        $db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__extensions');
		$query->set($db->qn('enabled') . ' = ' . $db->q(1));
		$query->where($db->qn('element') . ' = ' . $db->q('hydroraindrop'));
		$query->where($db->qn('folder') . ' = ' . $db->q('system'));
		$db->setQuery($query);
		$db->execute();
		return true;
	}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function uninstall(JAdapterInstance $adapter)
    {
        $db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('profile_key') . ' = ' . $db->quote('profile.HydroRaindropToken')
		);
		$query->delete($db->quoteName('#__user_profiles'));
		$query->where($conditions);
		$db->setQuery($query);
		$db->execute();
		return true;
    }
}
