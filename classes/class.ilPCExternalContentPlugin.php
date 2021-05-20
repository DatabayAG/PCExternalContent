<?php
/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv3, see docs/LICENSE
 *
 * @author Fred Neumann <fred.neumann@fau.de>
 * @author Cornel Musielak <cornel.musielak@fau.de>
 */
 
/**
 * External Content Page Component plugin
 */
class ilPCExternalContentPlugin extends ilPageComponentPlugin
{
	/**
	 * Get plugin name 
	 *
	 * @return string
	 */
	function getPluginName()
	{
		return "PCExternalContent";
	}


	/**
	 * Check if parent type is valid
	 *
	 * @return string
	 */
	function isValidParentType($a_parent_type)
	{
		// TODO: test with these page types, add other types if possible, e.g. 'glo'
		return in_array($a_parent_type, ['cat', 'crs', 'grp', 'fold', 'lm', 'copa']);
	}


	/**
	 * Handle an event
	 * @param string	$a_component
	 * @param string	$a_event
	 * @param mixed		$a_parameter
	 */
	public function handleEvent($a_component, $a_event, $a_parameter)
	{
		// nothing to do here yet
	}

	/**
	 * This function is called when the page content is cloned
	 * @param array 	$a_properties		properties saved in the page, (should be modified if neccessary)
	 * @param string	$a_plugin_version	plugin version of the properties
	 */
	public function onClone(&$a_properties, $a_plugin_version)
	{
		$settings_id = $a_properties['settings_id'];
		if (!empty($settings_id))
		{
			// TODO: clone the settings via ilExternalContentSettings
			// TODO: write back the settings_id of the clone to the properties
		}
	}


	/**
	 * This function is called before the page content is deleted
	 * @param array 	$a_properties		properties saved in the page (will be deleted afterwards)
	 * @param string	$a_plugin_version	plugin version of the properties
	 */
	public function onDelete($a_properties, $a_plugin_version)
	{
		$settings_id = $a_properties['settings_id'];
		if (!empty($settings_id))
		{
			// TODO: get and delete the ilExternalContentSettings
		}
	}


}
