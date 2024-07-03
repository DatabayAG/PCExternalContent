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
    /** @var self */
    protected static $instance;


	/**
	 * Check if parent type is valid
     * @see getParentType() of classes extending ilPageObject
	 * @see PCExternalContent::getReturnUrl()
	 * @return string
	 */
	function isValidParentType(string $a_type): bool
	{
		// TODO: test with these page types, add other types if possible, e.g. 'gdf'
		return in_array($a_type, [
		    'blp',      // Blog
            'copa',     // Content Page
            'lobj',     // Learning Objective
            'dcpf',     // Data Collection Detailed View
            'gdf',      // Glossary Definition
            'lm',       // Learning Module
            'mep',      // Media Pool
            'prtf',     // Portfolio
            'prtt',     // Portfolio Template
            'sahs',     // Scorm Learning Module
//            'qht',      // Test Question Hint
//            'qpl',      // Test Question
//            'qfbg',     // Test Question General Feedback
//            'qfbs',     // Test Question Specific Feedback
            'wpg',      // Wiki
            'auth',     // Login
            'cont',     // Container (Category, Course, Group, Folder)
            'cstr',     // Container Start Objects
//            'stys',     // Page Layout
            'impr',     // Imprint

        ]);
	}

    /**
     * Get the plugin instance
     * @return self
     */
    public static function getInstance() {
        global $DIC;
        if (!isset(self::$instance)) {
            /** @var ilComponentFactory $factory */
            $factory = $DIC["component.factory"];
            self::$instance = $factory->getPlugin('pcxxco');
        }
        return self::$instance;
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
	public function onClone(
        array &$a_properties,
        string $a_plugin_version
    ): void
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
	public function onDelete(
        array $a_properties,
        string $a_plugin_version,
        bool $move_operation = false
    ): void
	{
		$settings_id = $a_properties['settings_id'];
		if (!empty($settings_id))
		{
            $exco_settings = new ilExternalContentSettings($settings_id);
            $exco_settings->delete();
		}
	}
}
