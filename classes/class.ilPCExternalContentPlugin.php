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
     * @see ilPCExternalContent::getReturnUrl()
     * @return string
     */
    public function isValidParentType(string $a_type): bool
    {
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
            'wpg',      // Wiki
            'auth',     // Login
            'cont',     // Container (Category, Course, Group, Folder)
            'cstr',     // Container Start Objects
            'impr',     // Imprint
        ]);
    }

    /**
     * Get the plugin instance
     * @return self
     */
    public static function getInstance()
    {
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
     */
    public function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        // nothing to do here yet
    }

    /**
     * This function is called when the page content is cloned
     */
    public function onClone(
        array &$a_properties,
        string $a_plugin_version
    ): void {
        $settings_id = $a_properties['settings_id'] ?? null;
        if (!empty($settings_id)) {
            $old_settings = new ilExternalContentSettings($settings_id);
            $new_settings = new ilExternalContentSettings();
            $old_settings->clone($new_settings);
            $new_settings->setObjId($this->getParentId());
            $new_settings->save();
            $a_properties['settings_id'] = $new_settings->getSettingsId();
        }
    }

    /**
     * This function is called before the page content is deleted
     */
    public function onDelete(
        array $a_properties,
        string $a_plugin_version,
        bool $move_operation = false
    ): void {
        $settings_id = $a_properties['settings_id'] ?? null;
        if (!empty($settings_id)) {
            $settings = new ilExternalContentSettings($settings_id);
            $settings->delete();
        }
    }
}
