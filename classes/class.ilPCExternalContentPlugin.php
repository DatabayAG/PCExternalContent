<?php
/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv3, see docs/LICENSE
 *
 * @author Fred Neumann <fred.neumann@fau.de>
 * @author Cornel Musielak <cornel.musielak@fau.de>
 */
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilExternalContentSettings.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilExternalContentType.php');

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
     * @see getParentType() of classes extending ilPageObject
	 * @see PCExternalContent::getReturnUrl()
	 * @return string
	 */
	function isValidParentType($a_parent_type)
	{
		// TODO: test with these page types, add other types if possible, e.g. 'gdf'
		return in_array($a_parent_type, [
//		      'blp',      // Blog
//            'copa',     // Content Page
//            'lobj',     // Learning Objective
//            'dcpf',     // Data Collection Detailed View
//            'gdf',      // Glossary Definition
            'lm',       // Learning Module
//            'mep',      // Media Pool
//            'prtf',     // Portfolio
//            'prtt',     // Portfolio Template
//            'sahs',     // Scorm Learning Module
//            'qht',      // Test Question Hint
//            'qpl',      // Test Question
//            'qfbg',     // Test Question General Feedback
//            'qfbs',     // Test Question Specific Feedback
//            'wpg',      // Wiki
//            'auth',     // Login
            'cont',     // Container (Category, Course, Group, Folder)
//            'cstr',     // Container Start Objects
//            'stys',     // Page Layout
//            'impr',     // Imprint

        ]);
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
		    $oldSettings = new ilExternalContentSettings($settings_id);
		    $newSettings = new ilExternalContentSettings();
            $oldSettings->clone($newSettings);
            $newSettings->setObjId($this->getParentId());
            $newSettings->save();
            $a_properties['settings_id'] = $newSettings->getSettingsId();
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
            $exco_settings = new ilExternalContentSettings($settings_id);
            $exco_settings->delete();
		}
	}
}
