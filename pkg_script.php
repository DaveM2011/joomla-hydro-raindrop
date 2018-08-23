<?php

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
			$this->install($adapter);
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
        $db  = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__extensions');
		$query->set($db->qn('enabled') . ' = ' . $db->q(1));
		$query->where($db->qn('element') . ' = ' . $db->q('hydroraindrop'));
		$query->where($db->qn('folder') . ' = ' . $db->q('system'));
		$db->setQuery($query);
		$db->execute();
    }
}
